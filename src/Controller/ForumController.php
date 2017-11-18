<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\UserInfo;
use App\Forum\Forum;
use App\Forum\ForumAcp;
use App\Forum\ForumBoard;
use App\Forum\ForumPost;
use App\Forum\ForumThread;
use Decoda\Decoda;
use Decoda\Hook\EmoticonHook;
use Form\RecaptchaType;
use PDO;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\NotBlank;

class ForumController extends \Controller
{
    public function indexAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $forumList = Forum::getForumList();
        foreach ($forumList as $key => $forum) {
            $user                        = new UserInfo($forum['owner_id']);
            $forumList[$key]['username'] = $user->getFromUser('username');
        }

        return $this->render('forum/list-forums.html.twig', array(
            'current_user' => $currentUser->aUser,
            'forums_list'  => $forumList,
        ));
    }

    public function newForumAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $createForumForm = $this->createFormBuilder()
            ->add('name', TextType::class, array(
                'label'       => 'Forum name',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a name')),
                ),
            ))
            ->add('url', TextType::class, array(
                'label'       => 'Forum url',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a url')),
                ),
            ))
            ->add('recaptcha', RecaptchaType::class, array(
                'private_key'    => '6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll',
                'public_key'     => '6Ldec_4SAAAAAJ_TnvICnltNqgNaBPCbXp-wN48B',
                'recaptcha_ajax' => false,
                'attr'           => array(
                    'options' => array(
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal',
                        'defer' => true,
                        'async' => true,
                    ),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Create',
            ))
            ->getForm();

        $request = $this->getRequest();
        $createForumForm->handleRequest($request);
        if ($createForumForm->isValid()) {
            $errorMessages   = array();
            $captcha         = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($_POST['g-recaptcha-response'], $request->getClientIp());
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
                } elseif (Forum::urlExists($forumUrl)) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('This url is already in use'));
                }

                if (!count($errorMessages)) {
                    /** @var \PDOStatement $addForum */
                    $addForum = $this->get('database')->prepare('INSERT INTO `forums`(`name`,`url`,`owner_id`) VALUES (:name,:url,:user_id)');
                    $forumAdded = $addForum->execute(array(
                        ':name'    => $forumName,
                        ':url'     => $forumUrl,
                        ':user_id' => USER_ID,
                    ));

                    if ($forumAdded) {
                        /** @var \PDOStatement $getForum */
                        $getForum = $this->get('database')->prepare('SELECT `url` FROM `forums` WHERE `url`=:url LIMIT 1');
                        $getForum->execute(array(
                            ':url' => $forumUrl,
                        ));
                        $forumData = $getForum->fetchAll(PDO::FETCH_ASSOC);

                        return $this->redirectToRoute('app_forum_forum_index', array('forum' => $forumData[0]['url']));
                    } else {
                        $errorMessage = $addForum->errorInfo();
                        $createForumForm->addError(new FormError('We could not create your forum. (ERROR: '.$errorMessage[2].')'));
                    }
                }
            }
        }

        return $this->render('forum/create-new-forum.html.twig', array(
            'current_user'      => $currentUser->aUser,
            'create_forum_form' => $createForumForm->createView(),
        ));
    }

    public function forumIndexAction()
    {
        // Does the forum even exist?
        if (!Forum::urlExists($this->parameters['forum'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $forumId                            = Forum::url2Id($this->parameters['forum']);
        $forum                              = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountHelper::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links']     = json_decode($forum->getVar('page_links'), true);

        // Get all boards
        /** @var \PDOStatement $getBoards */
        $getBoards = $this->get('database')->prepare('SELECT * FROM `forum_boards` WHERE `forum_id`=:forum_id AND `parent_id`=0');
        $getBoards->bindValue(':forum_id', $forum->getVar('id'), PDO::PARAM_INT);
        $getBoards->execute();
        $boardTree = $getBoards->fetchAll(PDO::FETCH_ASSOC);

        foreach ($boardTree as $index => $board) {
            /** @var \PDOStatement $getSubBoards */
            $getSubBoards = $this->get('database')->prepare('SELECT * FROM `forum_boards` WHERE `forum_id`=:forum_id AND `type`=1 AND `parent_id`=:board_id');
            $getSubBoards->execute(array(
                ':forum_id' => $forum->getVar('id'),
                ':board_id' => $board['id'],
            ));

            // Get all subboards
            $subboards = $getSubBoards->fetchAll(PDO::FETCH_ASSOC);
            foreach ($subboards as $index2 => $subboard) {
                $subboards[$index2]['last_post_username'] = AccountHelper::formatUsername($subboard['last_post_user_id']);
            }
            $boardTree[$index]['subboards'] = $subboards;
        }

        return $this->render('forum/theme1/index.html.twig', array(
            'current_user'  => $currentUser->aUser,
            'current_forum' => $forum->forumData,
            'board_tree'    => $boardTree,
        ));
    }

    public function forumBoardAction()
    {
        // Does the forum even exist?
        if (!Forum::urlExists($this->parameters['forum'])) {
            return $this->render('error/error404.html.twig');
        }
        // Does the board even exist?
        if (!ForumBoard::boardExists($this->parameters['board'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $forumId                            = Forum::url2Id($this->parameters['forum']);
        $forum                              = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountHelper::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links']     = json_decode($forum->getVar('page_links'), true);

        $board = new ForumBoard((int)$this->parameters['board']);

        // Breadcrumb
        $breadcrumb = Forum::getBreadcrumb((int)$board->getVar('id'));
        foreach ($breadcrumb as $key => $value) {
            $boardData        = new ForumBoard($value);
            $breadcrumb{$key} = $boardData->boardData;
        }

        // Get all boards
        /** @var \PDOStatement $getBoards */
        $getBoards = $this->get('database')->prepare('SELECT * FROM `forum_boards` WHERE `forum_id`=:forum_id AND `parent_id`=:board_id');
        $getBoards->bindValue(':forum_id', $forum->getVar('id'), PDO::PARAM_INT);
        $getBoards->bindValue(':board_id', $board->getVar('id'), PDO::PARAM_INT);
        $getBoards->execute();
        $boardTree = $getBoards->fetchAll(PDO::FETCH_ASSOC);

        foreach ($boardTree as $index => $boardInList) {
            /** @var \PDOStatement $getSubBoards */
            $getSubBoards = $this->get('database')->prepare('SELECT * FROM `forum_boards` WHERE `forum_id`=:forum_id AND `type`=1 AND `parent_id`=:board_id');
            $getSubBoards->execute(array(
                ':forum_id' => $forum->getVar('id'),
                ':board_id' => $boardInList['id'],
            ));

            // Get all subboards
            $subboards = $getSubBoards->fetchAll(PDO::FETCH_ASSOC);
            foreach ($subboards as $index2 => $subboard) {
                $subboards[$index2]['last_post_username'] = AccountHelper::formatUsername($subboard['last_post_user_id']);
            }
            $boardTree[$index]['subboards'] = $subboards;
        }
        foreach ($boardTree as $index => $boardRoot) {
            $boardTree[$index]['last_post_username'] = AccountHelper::formatUsername($boardRoot['last_post_user_id']);
        }

        // Get all threads
        $request                    = $this->getRequest();
        $pagination                 = array();
        $pagination['item_limit']   = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : ForumThread::DefaultShowThreadAmount;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \PDOStatement $getThreads */
        $getThreads = $this->get('database')->prepare('SELECT * FROM `forum_threads` WHERE `board_id`=:board_id ORDER BY `last_post_time` DESC LIMIT :offset,:row_count');
        $getThreads->bindValue(':board_id', $board->getVar('id'), PDO::PARAM_INT);
        $getThreads->bindValue(':offset', ($pagination['current_page'] - 1) * $pagination['item_limit'], PDO::PARAM_INT);
        $getThreads->bindValue(':row_count', $pagination['item_limit'], PDO::PARAM_INT);
        $getThreads->execute();
        $threads = $getThreads->fetchAll(PDO::FETCH_ASSOC);
        foreach ($threads as $index => $thread) {
            $threads[$index]['username'] = AccountHelper::formatUsername($thread['user_id']);
        }
        foreach ($threads as $index => $thread) {
            $threads[$index]['last_post_username'] = AccountHelper::formatUsername($thread['last_post_user_id']);
        }

        // Pagination
        // Reference: http://www.strangerstudios.com/sandbox/pagination/diggstyle.php
        /** @var \PDOStatement $getThreadCount */
        $getThreadCount = $this->get('database')->prepare('SELECT NULL FROM `forum_threads` WHERE `board_id`=:board_id');
        $getThreadCount->bindValue(':board_id', $board->getVar('id'), PDO::PARAM_INT);
        $getThreadCount->execute();
        $pagination['total_items'] = $getThreadCount->rowCount();
        $pagination['adjacents']   = 1;

        $pagination['next_page']     = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count']   = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1']  = $pagination['pages_count'] - 1;

        return $this->render('forum/theme1/board.html.twig', array(
            'current_user'  => $currentUser->aUser,
            'current_forum' => $forum->forumData,
            'current_board' => $board->boardData,
            'breadcrumb'    => $breadcrumb,
            'board_tree'    => $boardTree,
            'threads'       => $threads,
            'pagination'    => $pagination,
        ));
    }

    public function forumThreadAction()
    {
        // Does the forum even exist?
        if (!Forum::urlExists($this->parameters['forum'])) {
            return $this->render('error/error404.html.twig');
        }
        // Does the thread even exist?
        if (!ForumThread::threadExists($this->parameters['thread'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $forumId                            = Forum::url2Id($this->parameters['forum']);
        $forum                              = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountHelper::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links']     = json_decode($forum->getVar('page_links'), true);

        $thread = new ForumThread($this->parameters['thread']);
        $thread->addView();

        $board = new ForumBoard($thread->getVar('board_id'));

        // Breadcrumb
        $breadcrumb = Forum::getBreadcrumb($board->getVar('id'));
        foreach ($breadcrumb as $key => $value) {
            $boardData        = new ForumBoard($value);
            $breadcrumb{$key} = $boardData->boardData;
        }

        // Get all posts
        $request                    = $this->getRequest();
        $pagination                 = array();
        $pagination['item_limit']   = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : ForumThread::DefaultShowThreadAmount;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \PDOStatement $getPosts */
        $getPosts = $this->get('database')->prepare('SELECT * FROM `forum_posts` WHERE `thread_id`=:thread_id ORDER BY `id` DESC LIMIT :offset,:row_count');
        $getPosts->bindValue(':thread_id', $thread->getVar('id'), PDO::PARAM_INT);
        $getPosts->bindValue(':offset', ($pagination['current_page'] - 1) * $pagination['item_limit'], PDO::PARAM_INT);
        $getPosts->bindValue(':row_count', $pagination['item_limit'], PDO::PARAM_INT);
        $getPosts->execute();
        $posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as $index => $post) {
            $posts[$index]['formatted_username'] = AccountHelper::formatUsername($post['user_id']);
            // TODO: Remove this as soon as Usernames are added automatically in the database
            $posts[$index]['username'] = AccountHelper::formatUsername($post['user_id'], false, false);
        }
        foreach ($posts as $index => $post) {
            $bbParser = new Decoda($post['message']);
            $bbParser->defaults();
            $bbParser->addHook(new EmoticonHook());
            $posts[$index]['formatted_message'] = nl2br($bbParser->parse());
        }

        // Pagination
        /** @var \PDOStatement $getThreadCount */
        $getThreadCount = $this->get('database')->prepare('SELECT NULL FROM `forum_posts` WHERE `thread_id`=:thread_id');
        $getThreadCount->execute(array(
            ':thread_id' => $thread->getVar('id'),
        ));
        $pagination['total_items'] = $getThreadCount->rowCount();
        $pagination['adjacents']   = 1;

        $pagination['next_page']     = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count']   = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1']  = $pagination['pages_count'] - 1;

        return $this->render('forum/theme1/thread.html.twig', array(
            'current_user'   => $currentUser->aUser,
            'current_forum'  => $forum->forumData,
            'current_board'  => $board->boardData,
            'current_thread' => $thread->threadData,
            'posts'          => $posts,
            'breadcrumb'     => $breadcrumb,
            'pagination'     => $pagination,
        ));
    }

    public function forumCreatePostAction()
    {
        // Does the forum even exist?
        if (!Forum::urlExists($this->parameters['forum'])) {
            return $this->render('error/error404.html.twig');
        }
        // Does the thread even exist?
        if (!ForumThread::threadExists($this->parameters['thread'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $forumId                            = Forum::url2Id($this->parameters['forum']);
        $forum                              = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountHelper::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links']     = json_decode($forum->getVar('page_links'), true);

        $thread = new ForumThread($this->parameters['thread']);
        $board  = new ForumBoard($thread->getVar('board_id'));

        // Breadcrumb
        $breadcrumb = Forum::getBreadcrumb($board->getVar('id'));
        foreach ($breadcrumb as $key => $value) {
            $boardData        = new ForumBoard($value);
            $breadcrumb{$key} = $boardData->boardData;
        }

        $createPostForm = $this->createFormBuilder()
            ->add('title', TextType::class, array(
                'label'       => 'Post title',
                'attr'        => array(
                    'placeholder' => 'RE:'.$thread->getVar('topic'),
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a title')),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a url')),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Post reply',
            ))
            ->getForm();

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $createPostForm->handleRequest($request);

            if ($createPostForm->isSubmitted() && $createPostForm->isValid()) {
                $formData = $createPostForm->getData();
                $postId   = ForumPost::createPost($thread->getVar('id'), (1 + (int)$thread->getVar('replies')), USER_ID, $formData['title'], $formData['message']);

                if (ForumPost::postExists($postId)) {
                    return $this->render('forum/theme1/create-post.html.twig', array(
                        'current_user'     => $currentUser->aUser,
                        'current_forum'    => $forum->forumData,
                        'current_board'    => $board->boardData,
                        'current_thread'   => $thread->threadData,
                        'breadcrumb'       => $breadcrumb,
                        'post_created'     => true,
                        'create_post_form' => $createPostForm->createView(),
                    ));
                } else {
                    return $this->render('forum/theme1/create-post.html.twig', array(
                        'current_user'     => $currentUser->aUser,
                        'current_forum'    => $forum->forumData,
                        'current_board'    => $board->boardData,
                        'current_thread'   => $thread->threadData,
                        'breadcrumb'       => $breadcrumb,
                        'post_created'     => false,
                        'create_post_form' => $createPostForm->createView(),
                    ));
                }
            }
        }

        return $this->render('forum/theme1/create-post.html.twig', array(
            'current_user'     => $currentUser->aUser,
            'current_forum'    => $forum->forumData,
            'current_board'    => $board->boardData,
            'current_thread'   => $thread->threadData,
            'breadcrumb'       => $breadcrumb,
            'create_post_form' => $createPostForm->createView(),
        ));
    }

    public function forumCreateThreadAction()
    {
        // Does the forum even exist?
        if (!Forum::urlExists($this->parameters['forum'])) {
            return $this->render('error/error404.html.twig');
        }
        // Does the board even exist?
        if (!ForumBoard::boardExists($this->parameters['board'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $forumId                            = Forum::url2Id($this->parameters['forum']);
        $forum                              = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountHelper::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links']     = json_decode($forum->getVar('page_links'), true);

        $board = new ForumBoard($this->parameters['board']);

        // Breadcrumb
        $breadcrumb = Forum::getBreadcrumb($board->getVar('id'));
        foreach ($breadcrumb as $key => $value) {
            $boardData        = new ForumBoard($value);
            $breadcrumb{$key} = $boardData->boardData;
        }

        $createThreadForm = $this->createFormBuilder()
            ->add('parent', HiddenType::class, array(
                'data' => $board->getVar('id'),
            ))
            ->add('title', TextType::class, array(
                'label'       => 'Thread name',
                'attr'        => array(
                    'placeholder' => 'e.g. I need help',
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a title')),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a message')),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Create thread',
            ))
            ->getForm();

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $createThreadForm->handleRequest($request);

            if ($createThreadForm->isSubmitted() && $createThreadForm->isValid()) {
                $formData = $createThreadForm->getData();
                $threadId = ForumThread::createThread($formData['parent'], $formData['title'], $formData['message'], USER_ID);

                if (ForumThread::threadExists($threadId)) {
                    return $this->render('forum/theme1/create-thread.html.twig', array(
                        'current_user'       => $currentUser->aUser,
                        'current_forum'      => $forum->forumData,
                        'current_board'      => $board->boardData,
                        'breadcrumb'         => $breadcrumb,
                        'thread_created'     => true,
                        'new_thread_id'      => $threadId,
                        'create_thread_form' => $createThreadForm->createView(),
                    ));
                } else {
                    return $this->render('forum/theme1/create-thread.html.twig', array(
                        'current_user'       => $currentUser->aUser,
                        'current_forum'      => $forum->forumData,
                        'current_board'      => $board->boardData,
                        'breadcrumb'         => $breadcrumb,
                        'thread_created'     => false,
                        'create_thread_form' => $createThreadForm->createView(),
                    ));
                }
            }
        }

        return $this->render('forum/theme1/create-thread.html.twig', array(
            'current_user'       => $currentUser->aUser,
            'current_forum'      => $forum->forumData,
            'current_board'      => $board->boardData,
            'breadcrumb'         => $breadcrumb,
            'create_thread_form' => $createThreadForm->createView(),
        ));
    }

    public function forumAdminAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        $params = array();
        $request                   = $this->getRequest();
        $params['user_id']         = USER_ID;
        $currentUser               = new UserInfo(USER_ID);
        $params['current_user']    = $currentUser->aUser;
        $forumId                   = Forum::url2Id($this->parameters['forum']);
        $forum                     = new Forum($forumId);
        $params['current_forum']   = $forum->forumData;
        $params['view_navigation'] = '';

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $request->getUri()));
        }
        if (USER_ID != (int)$forum->getVar('owner_id')) {
            return $this->render('forum/theme_admin1/no-permission.html.twig');
        }

        ForumAcp::includeLibs();

        $view = 'acp_not_found';

        foreach (ForumAcp::getAllMenus('root') as $sMenu => $aMenuInfo) {
            $selected                  = ($this->parameters['page'] === $aMenuInfo['href'] ? 'class="active"' : '');
            $params['view_navigation'] .= '<li><a href="'.$this->generateUrl('app_forum_forum_admin',
                    array(
                        'forum' => $forum->getVar('url'),
                        'page'  => $aMenuInfo['href'],
                    )).'" '.$selected.'>'.$aMenuInfo['title'].'</a></li>';

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
                    $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? ' class="active"' : '');
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
            $params['view_navigation'] .= '<li><a href="#">'.$aGroupInfo['title'].'<span class="fa arrow"></span></a><ul class="nav nav-second-level collapse">';

            foreach (ForumAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                $selected                  = ($this->parameters['page'] === $aMenuInfo['href'] ? 'class="active"' : '');
                $params['view_navigation'] .= '<li><a href="'.$this->generateUrl('app_forum_forum_admin',
                        array(
                            'forum' => $forum->getVar('url'),
                            'page'  => $aMenuInfo['href'],
                        )).'" '.$selected.'>'.$aMenuInfo['title'].'</a></li>';
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


        $response = call_user_func($view, $this->container->get('twig'), $this);
        if (is_string($response)) {
            $params['view_body'] = $response;
        }

        return $this->render('forum/theme_admin1/panel.html.twig', $params);
    }
}
