<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\UserInfo;
use App\Blog\Blog;
use App\Blog\BlogPost;
use Container\DatabaseContainer;
use Controller;
use Form\RecaptchaType;
use PDO;
use ReCaptcha\ReCaptcha;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class BlogController extends Controller
{
    public function indexAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $blogList = Blog::getBlogList();
        foreach ($blogList as $key => $blog) {
            $user                       = new UserInfo($blog['owner_id']);
            $blogList[$key]['username'] = $user->getFromUser('username');
        }

        return $this->render('blog/list-blogs.html.twig', array(
            'current_user' => $currentUser->aUser,
            'blog_list'    => $blogList,
        ));
    }

    public function newBlogAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $createBlogForm = $this->createFormBuilder()
            ->add('name', TextType::class, array(
                'label'       => 'Blog name',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a name')),
                ),
            ))
            ->add('url', TextType::class, array(
                'label'       => 'Blog url',
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


        /** @var Request $request */
        $request = $this->get('kernel')->getRequest();
        $createBlogForm->handleRequest($request);
        if ($createBlogForm->isValid()) {
            $errorMessages   = array();
            $captcha         = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($_POST['g-recaptcha-response'], $request->getClientIp());
            if (!$captchaResponse->isSuccess()) {
                $createBlogForm->get('recaptcha')->addError(new FormError('The Captcha is not correct'));
            } else {
                if (strlen($blogName = trim($createBlogForm->get('name')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createBlogForm->get('name')->addError(new FormError('Please give your blog a name'));
                } elseif (strlen($blogName) < 4) {
                    $errorMessages[] = '';
                    $createBlogForm->get('name')->addError(new FormError('Your blog must have minimally 4 characters'));
                }
                if (strlen($blogUrl = trim($createBlogForm->get('url')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createBlogForm->get('url')->addError(new FormError('Please give your blog an unique url to access it'));
                } elseif (strlen($blogUrl) < 3) {
                    $errorMessages[] = '';
                    $createBlogForm->get('url')->addError(new FormError('Your blog url must have minimally 3 characters'));
                } elseif (preg_match('/[^a-z_\-0-9]/i', $blogUrl)) {
                    $errorMessages[] = '';
                    $createBlogForm->get('url')->addError(new FormError('Only use a-z, A-Z, 0-9, _, -'));
                } elseif (in_array($blogUrl, array('new-forum', 'admin'))) {
                    $errorMessages[] = '';
                    $createBlogForm->get('url')->addError(new FormError('It\'s prohibited to use this url'));
                } elseif (Blog::urlExists($blogUrl)) {
                    $errorMessages[] = '';
                    $createBlogForm->get('url')->addError(new FormError('This url is already in use'));
                }

                if (!count($errorMessages)) {
                    $database = DatabaseContainer::getDatabase();

                    $addBlog   = $database->prepare('INSERT INTO `blogs`(`name`,`url`,`owner_id`) VALUES (:name,:url,:user_id)');
                    $blogAdded = $addBlog->execute(array(
                        ':name'    => $blogName,
                        ':url'     => $blogUrl,
                        ':user_id' => USER_ID,
                    ));

                    if ($blogAdded) {
                        $getBlog = $database->prepare('SELECT `url` FROM `blogs` WHERE `url`=:url LIMIT 1');
                        $getBlog->bindValue(':url', $blogUrl, PDO::PARAM_STR);
                        $getBlog->execute();
                        $blogData = $getBlog->fetchAll(PDO::FETCH_ASSOC);

                        return $this->redirectToRoute('app_blog_blog_index', array('blog' => $blogData[0]['url']));
                    } else {
                        $errorMessage = $addBlog->errorInfo();
                        $createBlogForm->addError(new FormError('We could not create your blog. (ERROR: '.$errorMessage[2].')'));
                    }
                }
            }
        }

        return $this->render('blog/create-new-blog.html.twig', array(
            'current_user'     => $currentUser->aUser,
            'create_blog_form' => $createBlogForm->createView(),
        ));
    }

    public function blogIndexAction()
    {
        // Does the blog even exist?
        if (!Blog::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $blogId                           = Blog::url2Id($this->parameters['blog']);
        $blog                             = new Blog($blogId);
        $blog->blogData['owner_username'] = AccountHelper::formatUsername($blog->getVar('owner_id'), false, false);
        $blog->blogData['page_links']     = json_decode($blog->getVar('page_links'), true);

        // Get all posts
        /** @var Request $request */
        $request                    = $this->get('kernel')->getRequest();
        $pagination                 = array();
        $pagination['item_limit']   = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : 5; // TODO: Page Limit should be variable by user
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \PDOStatement $getPosts */
        $getPosts = $this->get('database')->prepare('SELECT * FROM `blog_posts` WHERE `blog_id`=:blog_id ORDER BY `published` DESC LIMIT :offset,:row_count');
        $getPosts->bindValue(':blog_id', $blog->getVar('id'), PDO::PARAM_INT);
        $getPosts->bindValue(':offset', ($pagination['current_page'] - 1) * $pagination['item_limit'], PDO::PARAM_INT);
        $getPosts->bindValue(':row_count', $pagination['item_limit'], PDO::PARAM_INT);
        $getPosts->execute();
        $posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);
        foreach ($posts as $index => $post) {
            $posts[$index]['username'] = AccountHelper::formatUsername($post['author_id']);
        }

        // Pagination
        // Reference: http://www.strangerstudios.com/sandbox/pagination/diggstyle.php
        /** @var \PDOStatement $getPostCount */
        $getPostCount = $this->get('database')->prepare('SELECT NULL FROM `blog_posts` WHERE `blog_id`=:blog_id');
        $getPostCount->bindValue(':blog_id', $blog->getVar('id'), PDO::PARAM_INT);
        $getPostCount->execute();
        $pagination['total_items'] = $getPostCount->rowCount();
        $pagination['adjacents']   = 1;

        $pagination['next_page']     = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count']   = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1']  = $pagination['pages_count'] - 1;

        return $this->render('blog/theme1/index.html.twig', array(
            'current_user' => $currentUser->aUser,
            'current_blog' => $blog->blogData,
            'posts'        => $posts,
            'pagination'   => $pagination,
        ));
    }

    public function blogPostAction()
    {
        // Does the blog even exist?
        if (!Blog::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }

        // Does the post even exist?
        if (!BlogPost::postExists($this->parameters['post'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $blogId = Blog::url2Id($this->parameters['blog']);
        $blog   = new Blog($blogId);
        $post   = new BlogPost($this->parameters['post']);

        return $this->render('blog/theme1/post.html.twig', array(
            'current_user' => $currentUser->aUser,
            'current_blog' => $blog->blogData,
            'current_post' => $post->postData,
        ));
    }

    public function blogWritePostAction()
    {
        echo 'Write Post (Coming Soon)';
    }

    public function blogWriteCommentAction()
    {
        echo 'Write Comment (Coming Soon)';
    }

    public function blogSearchAction()
    {
        // Does the blog even exist?
        if (!Blog::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $currentUser = new UserInfo(USER_ID);

        $blogId = Blog::url2Id($this->parameters['blog']);
        $blog   = new Blog($blogId);

        return $this->render('blog/theme1/search.html.twig', array(
            'current_user' => $currentUser->aUser,
            'current_blog' => $blog->blogData,
        ));
    }

    public function blogRssAction()
    {
        // Does the blog even exist?
        if (!Blog::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }

        $blogUrl = $this->parameters['blog'];
        $blogId  = Blog::url2Id($blogUrl);
        $blog    = new Blog($blogId);

        $feed    = new Feed();
        $channel = new Channel();
        $channel
            ->title($blog->getVar('name'))
            ->url('https://blog.orbitrondev.org/'.$blog->getVar('url'))
            ->description($blog->getVar('description'))
            ->language($blog->getVar('language'))
            ->copyright($blog->getVar('copyright'))
            ->pubDate(strtotime(date('D, j M Y H:i:s O', $blog->getVar('published'))))
            ->lastBuildDate(strtotime(date('D, j M Y H:i:s O', $blog->getVar('updated'))))
            ->ttl(60)
            ->appendTo($feed);

        $postList = BlogPost::getPostList($blog->getVar('id'));
        foreach ($postList as $postData) {
            $post = new BlogPost($postData['post_id']);
            $item = new Item();
            $item
                ->title($post->getVar('title'))
                ->url('https://blog.orbitrondev.org/'.$blog->getVar('url').'/?p='.$post->getVar('post_id'))
                ->description('<div>'.$post->getVar('description').'</div>')
                ->guid('https://blog.orbitrondev.org/'.$blog->getVar('url').'/?p='.$post->getVar('post_id'), true)
                ->pubDate(strtotime(date('D, j M Y H:i:s O', $blog->getVar('published'))))
                ->appendTo($channel);
        }

        header('Content-Type: application/rss+xml; charset=utf-8');

        return $feed->render();
    }

    public function blogAdminAction()
    {
        echo 'Admin (Coming Soon)';
    }
}
