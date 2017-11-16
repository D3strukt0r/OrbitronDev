<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Blog\BlogHelper;
use App\Blog\Entity\Blog;
use App\Blog\Entity\Post;
use App\Blog\Form\NewBlogType;
use Controller;
use ReCaptcha\ReCaptcha;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogController extends Controller
{
    public function indexAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $this->getEntityManager()->find(User::class, USER_ID);

        return $this->render('blog/list-blogs.html.twig', array(
            'current_user' => $currentUser,
            'blog_list'    => BlogHelper::getBlogList(),
        ));
    }

    public function newBlogAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $createBlogForm = $this->createForm(NewBlogType::class);
        $request = $this->getRequest();
        $createBlogForm->handleRequest($request);
        if ($createBlogForm->isValid()) {
            $errorMessages   = array();
            $captcha         = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
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
                } elseif (BlogHelper::urlExists($blogUrl)) {
                    $errorMessages[] = '';
                    $createBlogForm->get('url')->addError(new FormError('This url is already in use'));
                }

                if (!count($errorMessages)) {
                    try {
                        $newBlog = new Blog();
                        $newBlog
                            ->setName($blogName)
                            ->setUrl($blogUrl)
                            ->setOwner($currentUser)
                            ->setCreated(new \DateTime())
                            ->setClosed(false);
                        $em->persist($newBlog);
                        $em->flush();

                        return $this->redirectToRoute('app_blog_blog_index', array('blog' => $newBlog->getUrl()));
                    } catch (\Exception $e) {
                        $createBlogForm->addError(new FormError('We could not create your blog. ('.$e->getMessage().')'));
                    }
                }
            }
        }

        return $this->render('blog/create-new-blog.html.twig', array(
            'create_blog_form' => $createBlogForm->createView(),
        ));
    }

    public function blogIndexAction()
    {
        $em = $this->getEntityManager();

        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Blog\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(array('url' => $this->parameters['blog']));
        if (!BlogHelper::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        // Get all posts
        /** @var Request $request */
        $request                    = $this->get('kernel')->getRequest();
        $pagination                 = array();
        $pagination['item_limit']   = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : 5;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \App\Blog\Entity\Post[] $posts */
        $posts = $em->getRepository(Post::class)->findBy(
            array('blog' => $blog),
            array('published_on' => 'DESC'),
            $pagination['item_limit'],
            ($pagination['current_page'] - 1) * $pagination['item_limit']
        );

        // Pagination
        // Reference: http://www.strangerstudios.com/sandbox/pagination/diggstyle.php
        /** @var \App\Blog\Entity\Post[] $getPostCount */
        $getPostCount = $em->getRepository(Post::class)->findBy(array('blog' => $blog));
        $pagination['total_items'] = count($getPostCount);
        $pagination['adjacents']   = 1;

        $pagination['next_page']     = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count']   = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1']  = $pagination['pages_count'] - 1;

        return $this->render('blog/theme1/index.html.twig', array(
            'current_user' => $currentUser,
            'current_blog' => $blog,
            'posts'        => $posts,
            'pagination'   => $pagination,
        ));
    }

    public function blogPostAction()
    {
        $em = $this->getEntityManager();

        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Blog\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(array('url' => $this->parameters['blog']));
        if (!BlogHelper::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        //////////// TEST IF POST EXISTS ////////////
        /** @var \App\Blog\Entity\Post $post */
        $post = $em->find(Post::class, $this->parameters['post']);
        if (is_null($post)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF POST EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        return $this->render('blog/theme1/post.html.twig', array(
            'current_user' => $currentUser,
            'current_blog' => $blog,
            'current_post' => $post,
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
        $em = $this->getEntityManager();

        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Blog\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(array('url' => $this->parameters['blog']));
        if (!BlogHelper::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        return $this->render('blog/theme1/search.html.twig', array(
            'current_user' => $currentUser,
            'current_blog' => $blog,
        ));
    }

    public function blogRssAction()
    {
        $em = $this->getEntityManager();

        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Blog\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(array('url' => $this->parameters['blog']));
        if (!BlogHelper::urlExists($this->parameters['blog'])) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        $feed    = new Feed();
        $channel = new Channel();
        $channel
            ->title($blog->getName())
            ->url($this->generateUrl('app_blog_blog_index', array('blog' => $blog->getUrl()), UrlGeneratorInterface::ABSOLUTE_URL))
            ->description($blog->getDescription())
            ->language($blog->getLanguage())
            ->copyright($blog->getCopyright())
            ->pubDate($blog->getCreated()->getTimestamp())
            ->lastBuildDate($blog->getCreated()->getTimestamp())
            ->ttl(60)
            ->appendTo($feed);

        /** @var \App\Blog\Entity\Post[] $postList */
        $postList = $em->getRepository(Post::class)->findBy(array('blog' => $blog));
        foreach ($postList as $post) {
            $item = new Item();
            $item
                ->title($post->getTitle())
                ->url($this->generateUrl('app_blog_blog_post', array('blog' => $blog->getUrl(), 'post' => $post->getId()), UrlGeneratorInterface::ABSOLUTE_URL))
                ->description('<div>'.$post->getDescription().'</div>')
                ->guid($this->generateUrl('app_blog_blog_post', array('blog' => $blog->getUrl(), 'post' => $post->getId()), UrlGeneratorInterface::ABSOLUTE_URL), true)
                ->pubDate($blog->getCreated()->getTimestamp())
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
