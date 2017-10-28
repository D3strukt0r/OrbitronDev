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
                    || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server')
                ) {
                    header('HTTP/1.0 403 Forbidden');
                    exit('You are not allowed to access this this page, while in development.');
                }
            }

            Debug::enable();
            //ini_set('display_startup_errors', '1');
            //set_exception_handler('\\Kernel::exception');
        } elseif ($this->environment == 'production' || $this->environment == 'prod') {
            error_reporting(0);
        }

        $this->request = Request::createFromGlobals();

        try {
            $config = Yaml::parse(file_get_contents($this->getRootDir() . '/app/config/parameters.yml'));
            $this->set('config', $config);
        } catch (ParseException $e) {
            throw new Exception("Unable to load Parameters. Unable to parse the YAML string: %s", $e->getMessage());
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
            $className = '\\Controller\\' . $controller[0]; // Specify Class with namespace
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
        \App\Core\CronJob::execute();
    }

    /**
     * Database
     */
    public function loadMailer()
    {
        new SwiftMailerContainer($this);
    }

    /**
     * Database
     */
    public function loadDatabase()
    {
        if ($this->has('router') && !$this->has('routing.error')) {
            if (isset($this->get('routing')['database']) && $this->get('routing')['database'] == false) {
                return;
            }
        }
        new DoctrineContainer($this);
        new DatabaseContainer($this);
        return;
    }

    /**
     * Logger
     */
    public function loadLogger()
    {
        $log = new Logger('Root');
        $log->pushHandler(new StreamHandler($this->getRootDir() . '/var/debug.log', Logger::DEBUG));
        $this->set('logger', $log);
    }

    /**
     * Session
     */
    public function loadSession()
    {
        new SessionContainer($this);
        return;

        // Session by Manuele Vaccari
        echo '';
        $session_name = '_session'; // Set a custom session name
        $secure = false; // Set to true if using https.
        $httponly = false; // This stops javascript being able to access the session id.
        //ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
        $cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams['lifetime'], '/', 'orbitrondev.org', $secure, $httponly);
        session_name($session_name); // Sets the session name to the one set above.
        session_start(); // Start the php session
        //session_regenerate_id(true); // regenerated the session, delete the old one.
    }

    /**
     * Templating
     */
    public function loadTemplating()
    {
        // Symfony Templating
        new TemplatingContainer($this);
        return;

        // Templating by Manuele Vaccari and Noel Pineiro
        // Router and Templater are together. Templates are also in the "/views" directory but with the ".phtml" ending and support PHP functions, NO TWIG
        echo '';
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
        return;
    }

    public function getRootDir()
    {
        return realpath(dirname(__DIR__));
    }

    public function getCacheDir()
    {
        return realpath(dirname(__DIR__) . '/var/cache/');
    }

    public function getLogDir()
    {
        return realpath(dirname(__DIR__) . '/var/logs');
    }

    /**
     * @return \Kernel
     * @throws \Exception
     */
    public static function getIntent()
    {
        if(is_null(self::$kernel)) {
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
     * @param \Exception $exception
     */
    public static function exception($exception)
    {
        ?>
        <title>Server ERROR</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" type="text/css" />
        <style type="text/css">
            hr {
                margin-top: 20px;
                margin-bottom: 20px;
                border: 0;
                border-top: 1px solid #eee;
            }

            .col-center {
                float: none;
                margin: 0 auto;
            }

            .error-message {
                margin-top: 50px;
            }
        </style>
        <div class="col-sm-6 col-lg-6 col-center error-message">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <img src="/assets/img/error.png" title="Error" style="float: left" />
                    &nbsp;&nbsp;
                    <b>Server error in file <?= $exception->getFile() ?> line <?= $exception->getLine() ?></b>
                </div>
                <div class="panel-body">
                    <?= $exception->getMessage() ?>
                    <hr />
                    <pre><?= $exception ?></pre>
                    <hr />
                    <i>Script execution was aborted. We apoligize for the possible inconvenience. If this problem is
                       persistant, please contact an Administrator.</i>
                </div>
            </div>
        </div>
        <?php
    }
}
