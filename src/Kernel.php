<?php

use Container\DatabaseContainer;
use Container\DoctrineContainer;
use Container\RoutingContainer;
use Container\SessionContainer;
use Container\SwiftMailerContainer;
use Container\TemplatingContainer;
use Container\TranslatingContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Kernel
{
    const ENVIRONMENT_DEVELOPMENT = 'dev';
    const ENVIRONMENT_PRODUCTION = 'prod';

    public $components = array();
    public $environment = null;

    /** @var Kernel $kernel */
    private static $kernel = null;

    private $request = null;

    public function __construct($env, $render_response = true)
    {
        $this->components['kernel'] = $this;
        Kernel::$kernel = $this; // For external access

        $this->environment = $env;
        if ($this->environment == 'development' || $this->environment == 'dev') {
            if (DEV_ONLY_INTERNAL) {
                if (isset($_SERVER['HTTP_CLIENT_IP'])
                    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                    || !(in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server')
                ) {
                    header('HTTP/1.0 403 Forbidden');
                    exit('You are not allowed to access this this page, while in development.');
                }
            }
            Debug::enable();
        } elseif ($this->environment == 'production' || $this->environment == 'prod') {
            error_reporting(0);
        }

        $this->request = Request::createFromGlobals();

        try {
            $config = Yaml::parse(file_get_contents($this->getRootDir().'/config/parameters.yml'));
            $this->set('config', $config);
        } catch (ParseException $e) {
            throw new ParseException("Unable to load Parameters. Unable to parse the YAML string: %s", $e->getMessage());
        }

        // Load components
        $this->set('kernel', $this);
        $this->loadLogger();
        $this->loadSession();
        $this->loadTranslation();
        $this->loadRouting();
        $this->loadDatabase();
        $this->loadTemplating();
        $this->loadMailer();
        $this->runCronJob();

        // Load template
        if (!$render_response) {
            return;
        }
        if ($this->has('routing.error')) {
            $error = $this->get('routing.error');
            $response = new Response($this->get('twig')->render('error/error404.html.twig', array('status_code' => $error->getCode(), 'status_text' => $error->getMessage())), 404);
            //$response = new NotFoundHttpException('Not found', $this->get('routing.error'));
            $response->prepare($this->getRequest());
            $response->send();
        } else {
            $controller = explode('::', $this->components['routing']['_controller']); // Split in Class and Function
            $className = '\\Controller\\'.$controller[0]; // Specify Class with namespace
            $functionName = $controller[1];

            /** @var Controller $class */
            $class = new $className;
            $class->setContainer($this); // Save this class in Controller
            $class->setParameters($this->components['routing']);
            /** @var Response $response */
            $response = $class->$functionName($this); // Execute Controller
            if (is_object($response) && $response instanceof Response) {
                $response->prepare($this->getRequest());
                $response->send();
            } elseif (is_string($response)) {
                $responseObj = new Response();
                $responseObj->setContent($response);
                $responseObj->prepare($this->getRequest());
                $responseObj->send();
            }
        }
    }


    public function has(string $component)
    {
        return isset($this->components[$component]);
    }

    public function get(string $component)
    {
        return $this->components[$component];
    }

    public function set(string $component, $value)
    {
        return $this->components[$component] = $value;
    }


    /**
     * Cron Job
     */
    public function runCronJob()
    {
        if ($this->has('router') && !$this->has('routing.error')) {
            if (isset($this->get('routing')['cron_job']) && $this->get('routing')['cron_job'] === false) {
                return;
            }
        }
        \App\Core\CronJob::execute();
    }

    /**
     * Database
     */
    public function loadMailer()
    {
        if ($this->has('mailer')) {
            return;
        }

        new SwiftMailerContainer($this);
    }

    /**
     * Database
     */
    public function loadDatabase()
    {
        if ($this->has('database')) {
            return;
        }

        if ($this->has('router') && !$this->has('routing.error')) {
            if (isset($this->get('routing')['database']) && $this->get('routing')['database'] === false) {
                return;
            }
        }
        new DoctrineContainer($this);
        new DatabaseContainer($this);
    }

    /**
     * Logger
     */
    public function loadLogger()
    {
        if ($this->has('logger')) {
            return;
        }

        // Create directories
        if (!file_exists($this->getRootDir().'/var/log')) {
            mkdir($this->getRootDir().'/var/log', 0777, true);
        }

        $log = new Logger('Root');
        $log->pushHandler(new StreamHandler($this->getRootDir().'/var/log/debug.log', Logger::DEBUG));
        $this->set('logger', $log);
    }

    /**
     * Session
     */
    public function loadSession()
    {
        if ($this->has('session')) {
            return;
        }

        new SessionContainer($this);
    }

    /**
     * Templating
     */
    public function loadTemplating()
    {
        // Symfony Templating
        new TemplatingContainer($this);

        // Templating by Manuele Vaccari and Noel Pineiro
        // Router and Templater are together. Templates are also in the "/views" directory but with the ".phtml" ending and support PHP functions, NO TWIG
        /*
        $config = array(
            'save' => 'FILE', // 'FILE' or 'DB'
            'sql'  => array(
                'hostname' => '',
                'username' => '',
                'password' => '',
                'database' => '',
                'prefix'   => '',
            ),

            'gheader'  => 'navigation',
            'gfooter'  => 'footer',
            'notfound' => array(
                'page'    => 'home/404',
                'gheader' => false,
                'gfooter' => false,
            ),
        );
        $request_data = \App\Core\Router::init($config, parse_url(\App\Core\BrowserInfo::fullUrl(), PHP_URL_PATH),
            parse_url(\App\Core\BrowserInfo::fullUrl(), PHP_URL_HOST));

        $request = $server = array();
        $request['branch'] = $request_data['request']['branch'];
        $request['var'] = (isset($request_data['request']['var']) ? $request_data['request']['var'] : array());
        $server['basedir'] = $request_data['server']['basedir'];

        define('SERVER_KEY', 'website_request_data'); // DO NOT CHANGE
        $_SERVER[SERVER_KEY]['branch'] = $request_data['request']['branch'];
        $_SERVER[SERVER_KEY]['var'] = (isset($request_data['request']['var']) ? $request_data['request']['var'] : array());
        $_SERVER[SERVER_KEY]['basedir'] = $request_data['server']['basedir'];

        include $request_data['template']['target'];
        */
    }

    /**
     * Translation
     */
    public function loadRouting()
    {
        new RoutingContainer($this);
    }

    /**
     * Translation
     */
    public function loadTranslation()
    {
        // Symfony Translator
        new TranslatingContainer($this);
    }

    public function getRootDir()
    {
        return realpath(dirname(__DIR__));
    }

    public function getCacheDir()
    {
        $dir = $this->getRootDir().'/var/cache';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return realpath($dir);
    }

    public function getLogDir()
    {
        $dir = $this->getRootDir().'/var/logs';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return realpath($dir);
    }

    /**
     * @return \Kernel
     * @throws \Exception
     */
    public static function getIntent()
    {
        if (is_null(self::$kernel)) {
            throw new Exception('Kernel not initiated');
        } else {
            return self::$kernel;
        }
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        $request = is_null($this->request) ? Request::createFromGlobals() : $this->request;
        /** @var Session $session */
        $session = $this->get('session');
        $request->setSession($session);

        return $request;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.entitymanager');

        return $em;
    }

    /**
     * @param \Exception $exception
     */
    public static function exception(\Exception $exception)
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Server ERROR</title>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4/dist/css/bootstrap.min.css" />
        </head>
        <body class="bg-dark">
        <div class="col-sm-6 col-lg-6 mx-auto mt-5">
            <div class="card bg-danger text-white">
                <div class="card-header">
                    <img src="/img/error.png" title="Error" class="ml-auto mr-2" />
                    <b>Server error in file <?= $exception->getFile() ?> line <?= $exception->getLine() ?></b>
                </div>
                <div class="card-body">
                    <?= $exception->getMessage() ?>
                    <hr />
                    <pre><?= $exception ?></pre>
                    <hr />
                    <i>Script execution was aborted. We apologise for the possible inconvenience. If this problem is
                       persistent, please contact an Administrator.</i>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
    }
}
