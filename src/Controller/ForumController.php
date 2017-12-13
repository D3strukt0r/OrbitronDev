<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Forum\Entity\Board;
use App\Forum\Entity\Forum;
use App\Forum\Entity\Post;
use App\Forum\Entity\Thread;
use App\Forum\Form\NewForumType;
use App\Forum\Form\PostType;
use App\Forum\Form\ThreadType;
use App\Forum\ForumAcp;
use App\Forum\ForumHelper;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\FormError;

class ForumController extends \Controller
{
    public function indexAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        /** @var \App\Forum\Entity\Forum[] $forumList */
        $forumList = $em->getRepository(Forum::class)->findAll();

        return $this->render('forum/list-forums.html.twig', array(
            'current_user' => $currentUser,
            'forums_list'  => $forumList,
        ));
    }

    public function newForumAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $createForumForm = $this->createForm(NewForumType::class);

        $request = $this->getRequest();
        $createForumForm->handleRequest($request);
        if ($createForumForm->isValid()) {
            $errorMessages   = array();
            $captcha         = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
            if (!$captchaResponse->isSuccess()) {
                $createForumForm->get('recaptcha')->addError(new FormError('The Captcha is not correct'));
            } else {
                if (strlen($forumName = trim($createForumForm->get('name')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createForumForm->get('name')->addError(new FormError('Please give your forum a name'));
                } elseif (strlen($forumName) < 4) {
                    $errorMessages[] = '';
                    $createForumForm->get('name')->addError(new FormError('Your forum must have minimally 4 characters'));
                }
                if (strlen($forumUrl = trim($createForumForm->get('url')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('Please give your forum an unique url to access it'));
                } elseif (strlen($forumUrl) < 3) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('Your forum must url have minimally 3 characters'));
                } elseif (preg_match('/[^a-z_\-0-9]/i', $forumUrl)) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('Only use a-z, A-Z, 0-9, _, -'));
                } elseif (in_array($forumUrl, array('new-forum', 'admin'))) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('It\'s prohibited to use this url'));
                } elseif (ForumHelper::urlExists($forumUrl)) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('This url is already in use'));
                }

                if (!count($errorMessages)) {
                    try {
                        $newForum = new Forum();
                        $newForum
                            ->setName($forumName)
                            ->setUrl($forumUrl)
                            ->setOwner($currentUser)
                            ->setCreated(new \DateTime());
                        $em->persist($newForum);
                        $em->flush();

                        return $this->redirectToRoute('app_forum_forum_index', array('forum' => $newForum->getUrl()));
                    } catch (\Exception $e) {
                        $createForumForm->addError(new FormError('We could not create your forum. ('.$e->getMessage().')'));
                    }
                }
            }
        }

        return $this->render('forum/create-new-forum.html.twig', array(
            'current_user'      => $currentUser,
            'create_forum_form' => $createForumForm->createView(),
        ));
    }

    public function forumIndexAction()
    {
        $em = $this->getEntityManager();

        //////////// TEST IF FORUM EXISTS ////////////
        /** @var \App\Forum\Entity\Forum $forum */
        $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $this->parameters['forum']));
        if (is_null($forum)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF FORUM EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        // Get all boards
        /** @var \App\Forum\Entity\Board[] $boardTree */
        $boardTree = $em->getRepository(Board::class)->findBy(array('forum' => $forum, 'parent_board' => null));

        return $this->render('forum/theme1/index.html.twig', array(
            'current_user'  => $currentUser,
            'current_forum' => $forum,
            'board_tree'    => $boardTree,
        ));
    }

    public function forumBoardAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF FORUM EXISTS ////////////
        /** @var \App\Forum\Entity\Forum $forum */
        $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $this->parameters['forum']));
        if (is_null($forum)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF FORUM EXISTS ////////////

        //////////// TEST IF BOARD EXISTS ////////////
        /** @var \App\Forum\Entity\Board $board */
        $board = $em->getRepository(Board::class)->findOneBy(array('id' => $this->parameters['board']));
        if (is_null($board)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// TEST IF BOARD EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        // Breadcrumb
        $breadcrumb = ForumHelper::getBreadcrumb($board);

        // Get all boards
        $boardTree = $em->getRepository(Board::class)->findBy(array('forum' => $forum, 'parent_board' => $board));

        // Get all threads
        $pagination                 = array();
        $pagination['item_limit']   = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : ForumHelper::DEFAULT_SHOW_THREAD_COUNT;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \App\Forum\Entity\Thread[] $threads */
        $threads = $em->getRepository(Thread::class)->findBy(
            array('board' => $board),
            array('last_post_time' => 'DESC'),
            $pagination['item_limit'],
            ($pagination['current_page'] - 1) * $pagination['item_limit']
        );

        // Pagination
        // Reference: http://www.strangerstudios.com/sandbox/pagination/diggstyle.php
        /** @var \App\Forum\Entity\Thread[] $threadCount */
        $threadCount = $em->getRepository(Thread::class)->findBy(array('board' => $board));
        $pagination['total_items'] = count($threadCount);
        $pagination['adjacents']   = 1;

        $pagination['next_page']     = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count']   = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1']  = $pagination['pages_count'] - 1;

        return $this->render('forum/theme1/board.html.twig', array(
            'current_user'  => $currentUser,
            'current_forum' => $forum,
            'current_board' => $board,
            'breadcrumb'    => $breadcrumb,
            'board_tree'    => $boardTree,
            'threads'       => $threads,
            'pagination'    => $pagination,
        ));
    }

    public function forumThreadAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF FORUM EXISTS ////////////
        /** @var \App\Forum\Entity\Forum $forum */
        $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $this->parameters['forum']));
        if (is_null($forum)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF FORUM EXISTS ////////////

        //////////// TEST IF THREAD EXISTS ////////////
        /** @var \App\Forum\Entity\Thread $thread */
        $thread = $em->getRepository(Thread::class)->findOneBy(array('id' => $this->parameters['thread']));
        if (is_null($thread)) {
            return $this->render('error/error404.html.twig');
        }
        $thread->setViews($thread->getViews() + 1);
        $em->flush();

        $board = $thread->getBoard();
        //////////// END TEST IF THREAD EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        // Breadcrumb
        $breadcrumb = ForumHelper::getBreadcrumb($board);

        // Get all posts
        $pagination                 = array();
        $pagination['item_limit']   = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : ForumHelper::DEFAULT_SHOW_THREAD_COUNT;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \App\Forum\Entity\Post[] $posts */
        $posts = $em->getRepository(Post::class)->findBy(
            array('thread' => $thread),
            array('post_number' => 'ASC'),
            $pagination['item_limit'],
            ($pagination['current_page'] - 1) * $pagination['item_limit']
        );

        // Pagination
        /** @var \App\Forum\Entity\Post[] $postCount */
        $postCount = $em->getRepository(Post::class)->findBy(array('thread' => $thread));
        $pagination['total_items'] = count($postCount);
        $pagination['adjacents']   = 1;

        $pagination['next_page']     = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count']   = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1']  = $pagination['pages_count'] - 1;

        return $this->render('forum/theme1/thread.html.twig', array(
            'current_user'   => $currentUser,
            'current_forum'  => $forum,
            'current_board'  => $board,
            'current_thread' => $thread,
            'posts'          => $posts,
            'breadcrumb'     => $breadcrumb,
            'pagination'     => $pagination,
        ));
    }

    public function forumCreatePostAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF FORUM EXISTS ////////////
        /** @var \App\Forum\Entity\Forum $forum */
        $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $this->parameters['forum']));
        if (is_null($forum)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF FORUM EXISTS ////////////

        //////////// TEST IF THREAD EXISTS ////////////
        /** @var \App\Forum\Entity\Thread $thread */
        $thread = $em->getRepository(Thread::class)->findOneBy(array('id' => $this->parameters['thread']));
        if (is_null($thread)) {
            return $this->render('error/error404.html.twig');
        }

        $board = $thread->getBoard();
        //////////// END TEST IF THREAD EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        // Breadcrumb
        $breadcrumb = ForumHelper::getBreadcrumb($board);

        $createPostForm = $this->createForm(PostType::class, null, array('thread' => $thread));

        if ($request->isMethod('POST')) {
            $createPostForm->handleRequest($request);

            if ($createPostForm->isSubmitted() && $createPostForm->isValid()) {
                $formData = $createPostForm->getData();

                try {
                    $time = new \DateTime();

                    // Add post entity
                    $newPost = new Post();
                    $newPost
                        ->setThread($thread)
                        ->setUser($currentUser)
                        ->setPostNumber($thread->getReplies() + 2)
                        ->setSubject($formData['title'])
                        ->setMessage($formData['message'])
                        ->setCreatedOn($time);
                    $em->persist($newPost);

                    // Update thread count and last post user and time
                    $thread->setReplies($thread->getReplies() + 1);
                    $thread->setLastPostUser($currentUser);
                    $thread->setLastPostTime($time);

                    $board->setPostCount($board->getPostCount() + 1);
                    $board->setLastPostUser($currentUser);
                    $board->setLastPostTime($time);
                    foreach ($breadcrumb as $item) {
                        $item->setPostCount($item->getPostCount() + 1);
                        $item->setLastPostUser($currentUser);
                        $item->setLastPostTime($time);
                    }

                    $em->flush();

                    return $this->render('forum/theme1/create-post.html.twig', array(
                        'current_user'     => $currentUser,
                        'current_forum'    => $forum,
                        'current_board'    => $board,
                        'current_thread'   => $thread,
                        'breadcrumb'       => $breadcrumb,
                        'post_created'     => true,
                        'create_post_form' => $createPostForm->createView(),
                    ));
                } catch (\Exception $e) {
                    return $this->render('forum/theme1/create-post.html.twig', array(
                        'current_user'     => $currentUser,
                        'current_forum'    => $forum,
                        'current_board'    => $board,
                        'current_thread'   => $thread,
                        'breadcrumb'       => $breadcrumb,
                        'post_created'     => false,
                        'create_post_form' => $createPostForm->createView(),
                    ));
                }
            }
        }

        return $this->render('forum/theme1/create-post.html.twig', array(
            'current_user'     => $currentUser,
            'current_forum'    => $forum,
            'current_board'    => $board,
            'current_thread'   => $thread,
            'breadcrumb'       => $breadcrumb,
            'create_post_form' => $createPostForm->createView(),
        ));
    }

    public function forumCreateThreadAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF FORUM EXISTS ////////////
        /** @var \App\Forum\Entity\Forum $forum */
        $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $this->parameters['forum']));
        if (is_null($forum)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF FORUM EXISTS ////////////

        //////////// TEST IF BOARD EXISTS ////////////
        /** @var \App\Forum\Entity\Board $board */
        $board = $em->getRepository(Board::class)->findOneBy(array('id' => $this->parameters['board']));
        if (is_null($board)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// TEST IF BOARD EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        // Breadcrumb
        $breadcrumb = ForumHelper::getBreadcrumb($board);

        $createThreadForm = $this->createForm(ThreadType::class, null, array('board' => $board));

        if ($request->isMethod('POST')) {
            $createThreadForm->handleRequest($request);

            if ($createThreadForm->isSubmitted() && $createThreadForm->isValid()) {
                $formData = $createThreadForm->getData();

                try {
                    // Add thread and post entity
                    $time = new \DateTime();
                    $newThread = new Thread();
                    $newThread
                        ->setUser($currentUser)
                        ->setBoard($board)
                        ->setTopic($formData['title'])
                        ->setCreatedOn($time)
                        ->setLastPostUser($currentUser)
                        ->setLastPostTime($time);
                    $newPost = new Post();
                    $newPost
                        ->setUser($currentUser)
                        ->setPostNumber(1)
                        ->setSubject($formData['title'])
                        ->setMessage($formData['message'])
                        ->setCreatedOn($time);
                    $newThread->addPost($newPost);
                    $em->persist($newThread);
                    $em->persist($newPost);

                    // Update thread count and last post user and time
                    $board->setThreadCount($board->getThreadCount() + 1);
                    $board->setLastPostUser($currentUser);
                    $board->setLastPostTime($time);
                    foreach ($breadcrumb as $item) {
                        $item->setThreadCount($item->getThreadCount() + 1);
                        $item->setLastPostUser($currentUser);
                        $item->setLastPostTime($time);
                    }

                    $em->flush();

                    return $this->render('forum/theme1/create-thread.html.twig', array(
                        'current_user'       => $currentUser,
                        'current_forum'      => $forum,
                        'current_board'      => $board,
                        'breadcrumb'         => $breadcrumb,
                        'thread_created'     => true,
                        'new_thread_id'      => $newThread->getId(),
                        'create_thread_form' => $createThreadForm->createView(),
                    ));
                } catch (\Exception $e) {
                    return $this->render('forum/theme1/create-thread.html.twig', array(
                        'current_user'       => $currentUser,
                        'current_forum'      => $forum,
                        'current_board'      => $board,
                        'breadcrumb'         => $breadcrumb,
                        'thread_created'     => false,
                        'create_thread_form' => $createThreadForm->createView(),
                    ));
                }
            }
        }

        return $this->render('forum/theme1/create-thread.html.twig', array(
            'current_user'       => $currentUser,
            'current_forum'      => $forum,
            'current_board'      => $board,
            'breadcrumb'         => $breadcrumb,
            'create_thread_form' => $createThreadForm->createView(),
        ));
    }

    public function forumAdminAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF FORUM EXISTS ////////////
        /** @var \App\Forum\Entity\Forum $forum */
        $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $this->parameters['forum']));
        if (is_null($forum)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF FORUM EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $params = array();
        $params['user_id']         = USER_ID;
        $params['current_user']    = $currentUser;
        $params['current_forum']   = $forum;
        $params['view_navigation'] = '';

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $request->getUri()));
        }
        if (USER_ID != (int)$forum->getOwner()->getId()) {
            return $this->render('forum/theme_admin1/no-permission.html.twig');
        }

        ForumAcp::includeLibs();

        $view = 'acp_not_found';

        foreach (ForumAcp::getAllMenus('root') as $sMenu => $aMenuInfo) {
            $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
            $url = $this->generateUrl('app_forum_forum_admin', array('forum' => $forum->getUrl(), 'page' => $aMenuInfo['href']));
            $params['view_navigation'] .= '<li class="nav-item '.$selected.'" data-toggle="tooltip" data-placement="right" title="'.$aMenuInfo['title'].'">
                    <a class="nav-link" href="'.$url.'">
                        <i class="'.$aMenuInfo['icon'].'"></i>
                        <span class="nav-link-text">'.$aMenuInfo['title'].'</span>
                    </a>
                </li>';

            if (strlen($selected) > 0) {
                if (is_callable($aMenuInfo['screen'])) {
                    $view = $aMenuInfo['screen'];
                } else {
                    $view = 'acp_function_error';
                }
            }
        }

        foreach (ForumAcp::getAllGroups() as $sGroup => $aGroupInfo) {
            if (is_null($aGroupInfo['display']) || strlen($aGroupInfo['display']) == 0) {
                foreach (ForumAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                    $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
                    if (strlen($selected) > 0) {
                        if (is_callable($aMenuInfo['screen'])) {
                            $view = $aMenuInfo['screen'];
                        } else {
                            $view = 'acp_function_error';
                        }
                    }
                }
                continue;
            }
            $params['view_navigation'] .= '<li class="nav-item" data-toggle="tooltip" data-placement="right" title="Components">
                    <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapse_'.$aGroupInfo['id'].'" data-parent="#menu">
                        <i class="'.$aGroupInfo['icon'].'"></i>
                        <span class="nav-link-text">'.$aGroupInfo['title'].'</span>
                    </a>
                    <ul class="sidenav-second-level collapse" id="collapse_'.$aGroupInfo['id'].'">';

            foreach (ForumAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
                $url = $this->generateUrl('app_forum_forum_admin', array('forum' => $forum->getUrl(), 'page' => $aMenuInfo['href']));
                $params['view_navigation'] .= '<li class="nav-item '.$selected.'" data-toggle="tooltip" data-placement="right" title="'.strip_tags($aMenuInfo['title']).'">
                    <a class="nav-link" href="'.$url.'">
                        <i class="'.$aMenuInfo['icon'].'"></i>
                        <span class="nav-link-text">'.$aMenuInfo['title'].'</span>
                    </a>
                </li>';
                if (strlen($selected) > 0) {
                    if (is_callable($aMenuInfo['screen'])) {
                        $view = $aMenuInfo['screen'];
                    } else {
                        $view = 'acp_function_error';
                    }
                }
            }

            $params['view_navigation'] .= '</ul></li>';
        }


        $response = call_user_func($view, $this);
        if (is_string($response)) {
            $params['view_body'] = $response;
        }

        return $this->render('forum/theme_admin1/panel.html.twig', $params);
    }
}
