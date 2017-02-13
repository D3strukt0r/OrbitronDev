<?php

namespace Controller;

use App\Account\Account;
use App\Account\AccountTools;
use App\Account\UserInfo;
use App\Forum\Forum;
use App\Forum\ForumBoard;
use App\Forum\ForumThread;
use Container\DatabaseContainer;
use Controller;
use Decoda\Decoda;
use Form\RecaptchaType;
use PDO;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class ForumController extends Controller
{
    public function indexAction()
    {
        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);

        $forumList = Forum::getForumList();
        foreach ($forumList as $key => $forum) {
            $user = new UserInfo($forum['owner_id']);
            $forumList[$key]['username'] = $user->getFromUser('username');
        }

        return $this->render('forum/list-forums.html.twig', array(
            'current_user' => $currentUser->aUser,
            'forums_list'  => $forumList,
        ));
    }

    public function newForumAction()
    {
        Account::updateSession();
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


        $request = Request::createFromGlobals();
        $createForumForm->handleRequest($request);
        if ($createForumForm->isValid()) {
            $errorMessages = array();
            $captcha = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($_POST['g-recaptcha-response'], $request->getClientIp());
            if (!$captchaResponse->isSuccess()) {
                $createForumForm->get('recaptcha')->addError(new FormError('The Captcha is not correct'));
            } else {
                if (strlen($forumName = trim($createForumForm->get('name')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createForumForm->get('name')->addError(new FormError('Please give your forum a name'));
                } elseif (strlen($forumName) <= 4) {
                    $errorMessages[] = '';
                    $createForumForm->get('name')->addError(new FormError('Your forum must have minimally 4 characters'));
                }
                if (strlen($forumUrl = trim($createForumForm->get('url')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('Please give your forum an unique url to access it'));
                } elseif (strlen($forumUrl) <= 4) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('Your forum must url have minimally 3 characters'));
                } elseif (preg_match('/[^a-z_\-0-9]/i', $forumUrl)) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('Only use a-z, A-Z, 0-9, _, -'));
                } elseif (in_array($forumUrl, array('new-forum', 'admin'))) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('It\'s permitted to use this url'));
                } elseif (Forum::urlExists($forumUrl)) {
                    $errorMessages[] = '';
                    $createForumForm->get('url')->addError(new FormError('This url is already in use'));
                }

                if (!count($errorMessages)) {
                    $database = DatabaseContainer::$database;
                    if (is_null($database)) {
                        throw new \Exception('A database connection is required');
                    }

                    $addForum = $database->prepare('INSERT INTO `forums`(`name`,`url`,`owner_id`) VALUES (:name,:url,:user_id)');
                    $addForum->execute(array(
                        ':name'    => $forumName,
                        ':url'     => $forumUrl,
                        ':user_id' => USER_ID,
                    ));

                    $getForum = $database->prepare('SELECT `url` FROM `forums` WHERE `url`=:url LIMIT 1');
                    $getForum->execute(array(
                        ':url' => $forumUrl,
                    ));
                    $forumData = $getForum->fetchAll(PDO::FETCH_ASSOC);
                    $this->redirectToRoute('app_forum_forum_index', array('forum' => $forumData[0]['url']));
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

        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);

        $forumId = Forum::url2Id($this->parameters['forum']);
        $forum = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountTools::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links'] = json_decode($forum->getVar('page_links'), true);

        // Get all boards
        /** @var \PDOStatement $getBoards */
        $getBoards = $this->get('database')->prepare('SELECT * FROM `forum_boards` WHERE `forum_id`=:forum_id AND `type`=2 AND `parent_id`=0');
        $getBoards->execute(array(
            ':forum_id' => $forum->getVar('id'),
        ));
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
                $subboards[$index2]['last_post_username'] = AccountTools::formatUsername($subboard['last_post_user_id']);
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

        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);

        $forumId = Forum::url2Id($this->parameters['forum']);
        $forum = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountTools::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links'] = json_decode($forum->getVar('page_links'), true);

        $board = new ForumBoard($this->parameters['board']);

        // Breadcrumb
        $breadcrumb = Forum::getBreadcrumb($board->getVar('id'));
        foreach ($breadcrumb as $key => $value) {
            $boardData = new ForumBoard($value);
            $breadcrumb{$key} = $boardData->boardData;
        }

        // Get all boards
        /** @var \PDOStatement $getBoards */
        $getBoards = $this->get('database')->prepare('SELECT * FROM `forum_boards` WHERE `forum_id`=:forum_id AND `type`=1 AND `parent_id`=:board_id');
        $getBoards->execute(array(
            ':forum_id' => $forum->getVar('id'),
            ':board_id' => $board->getVar('id'),
        ));
        $boardTree = $getBoards->fetchAll(PDO::FETCH_ASSOC);

        // Get all threads
        /** @var Request $request */
        $request = $this->get('kernel')->getRequest();
        $pagination['item_limit'] = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : ForumThread::DefaultShowThreadAmount;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \PDOStatement $getThreads */
        $getThreads = $this->get('database')->prepare('SELECT * FROM `forum_threads` WHERE `board_id`=:board_id ORDER BY `last_post_time` DESC LIMIT ' . ($pagination['current_page'] - 1) * $pagination['item_limit'] . ',' . $pagination['item_limit']);
        $getThreads->execute(array(
            ':board_id' => $board->getVar('id'),
        ));
        $threads = $getThreads->fetchAll(PDO::FETCH_ASSOC);
        foreach ($threads as $index => $thread) {
            $threads[$index]['username'] = AccountTools::formatUsername($thread['user_id']);
        }
        foreach ($threads as $index => $thread) {
            $threads[$index]['last_post_username'] = AccountTools::formatUsername($thread['last_post_user_id']);
        }

        // Pagination
        /** @var \PDOStatement $getThreadCount */
        $getThreadCount = $this->get('database')->prepare('SELECT NULL FROM `forum_threads` WHERE `board_id`=:board_id');
        $getThreadCount->execute(array(
            ':board_id' => $board->getVar('id'),
        ));
        $pagination['total_items'] = $getThreadCount->rowCount();
        $pagination['adjacents'] = 1;

        $pagination['next_page'] = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count'] = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1'] = $pagination['pages_count'] - 1;

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

        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);

        $forumId = Forum::url2Id($this->parameters['forum']);
        $forum = new Forum($forumId);
        $forum->forumData['owner_username'] = AccountTools::formatUsername($forum->getVar('owner_id'), false, false);
        $forum->forumData['page_links'] = json_decode($forum->getVar('page_links'), true);

        $thread = new ForumThread($this->parameters['thread']);
        $board = new ForumBoard($thread->getVar('board_id'));

        // Breadcrumb
        $breadcrumb = Forum::getBreadcrumb($board->getVar('id'));
        foreach ($breadcrumb as $key => $value) {
            $boardData = new ForumBoard($value);
            $breadcrumb{$key} = $boardData->boardData;
        }

        // Get all posts
        /** @var Request $request */
        $request = $this->get('kernel')->getRequest();
        $pagination['item_limit'] = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : ForumThread::DefaultShowThreadAmount;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \PDOStatement $getPosts */
        $getPosts = $this->get('database')->prepare('SELECT * FROM `forum_posts` WHERE `thread_id`=:thread_id ORDER BY `id` DESC LIMIT :offset,:row_count');
        $getPosts->bindValue(':thread_id', $thread->getVar('id'), PDO::PARAM_INT);
        $getPosts->bindValue(':offset', ($pagination['current_page'] - 1) * $pagination['item_limit'], PDO::PARAM_INT);
        $getPosts->bindValue(':row_count', $pagination['item_limit'], PDO::PARAM_INT);
        $getPosts->execute();
        $posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as $index => $post) {
            $posts[$index]['formatted_username'] = AccountTools::formatUsername($post['user_id']);
        }
        foreach ($posts as $index => $post) {
            $bbParser = new Decoda($post['message']);
            $bbParser->defaults();
            $posts[$index]['formatted_message'] = nl2br($bbParser->parse());
        }

        // Pagination
        /** @var \PDOStatement $getThreadCount */
        $getThreadCount = $this->get('database')->prepare('SELECT NULL FROM `forum_posts` WHERE `thread_id`=:thread_id');
        $getThreadCount->execute(array(
            ':thread_id' => $thread->getVar('id'),
        ));
        $pagination['total_items'] = $getThreadCount->rowCount();
        $pagination['adjacents'] = 1;

        $pagination['next_page'] = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count'] = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1'] = $pagination['pages_count'] - 1;

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
}