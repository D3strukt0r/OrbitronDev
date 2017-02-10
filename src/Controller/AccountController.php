<?php

namespace Controller;

use App\Account\AccountAcp;
use App\Account\AccountApi;
use App\Account\AccountDeveloper;
use App\Account\AccountTools;
use App\Account\UserInfo;
use App\Blog\Blog;
use App\Core\Token;
use App\Forum\Forum;
use App\Store\Store;
use Container\DatabaseContainer;
use Controller;
use App\Account\Account;
use Exception;
use Form\RecaptchaType;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use ReCaptcha\ReCaptcha;
use Swift_Message;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountController extends Controller
{
    public function indexAction()
    {
        Account::updateSession();

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login');
        } else {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }
    }

    public function logoutAction()
    {
        Account::updateSession();

        if (LOGGED_IN) {
            Account::logout();
        }
        return $this->redirectToRoute('app_account_login');
    }

    public function loginAction()
    {
        Account::updateSession();

        if (LOGGED_IN) {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }

        $request = Request::createFromGlobals();

        $loginForm = $this->createFormBuilder()
            ->add('redirect', HiddenType::class, array(
                'data' => strlen($request->query->get('redir')) > 0 ? $request->query->get('redir') : $this->generateUrl('app_account_panel',
                    array('page' => 'home')),
            ))
            ->add('email', EmailType::class, array(
                'label'       => 'E-mail',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email address')),
                    new Email(array('message' => 'Please enter a VALID email address')),
                ),
            ))
            ->add('password', PasswordType::class, array(
                'label'       => 'Password',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
                ),
            ))
            ->add('remember', CheckboxType::class, array(
                'label'    => 'Keep me logged in',
                'required' => false,
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Log in',
            ))
            ->getForm();

        $loginForm->handleRequest($request);
        if ($loginForm->isValid()) {

            $resultCodes = array(
                'wrong_password'   => $this->container->get('translator')->trans('Incorrect password'),
                'insert_username'  => $this->container->get('translator')->trans('Please enter your username'),
                'insert_password'  => $this->container->get('translator')->trans('Please enter your password'),
                'user_dont_exists' => $this->container->get('translator')->trans('This user doesn\'t exist'),
                'unknown_error'    => $this->container->get('translator')->trans('Unknown error'),
            );
            $loginResult = Account::login($loginForm->get('email')->getData(), $loginForm->get('password')->getData(),
                $loginForm->get('remember')->getData());

            if ($loginResult === true) {
                return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
            } else {
                $errorMessage = explode(':', $loginResult);
                $loginErrorMessage = $resultCodes[$errorMessage[1]];
                if ($errorMessage[0] == 'form') {
                    $loginForm->addError(new FormError($loginErrorMessage));
                } else {
                    $loginForm->get($errorMessage[0])->addError(new FormError($loginErrorMessage));
                }
            }
        }

        return $this->render('account/login.html.twig', array(
            'login_form' => $loginForm->createView(),
        ));
    }

    public function registerAction()
    {
        Account::updateSession();

        if (LOGGED_IN) {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }

        $registerForm = $this->createFormBuilder()
            ->add('username', TextType::class, array(
                'label'       => 'Username',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your username')),
                ),
            ))
            ->add('email', EmailType::class, array(
                'label'       => 'E-mail',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email address')),
                    new Email(array('message' => 'Please enter a VALID email address')),
                ),
            ))
            ->add('password', PasswordType::class, array(
                'label'       => 'Password',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
                ),
            ))
            ->add('password_verify', PasswordType::class, array(
                'label'       => 'Repeat Password',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
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
                'mapped'         => false,
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Register',
            ))
            ->getForm();

        $request = Request::createFromGlobals();
        $registerForm->handleRequest($request);
        if ($registerForm->isValid()) {

            $resultCodes = array(
                'insert_username'      => $this->container->get('translator')->trans('You have to insert an username'),
                'username_short_long'  => $this->container->get('translator')->trans('Your username must be between 3 and 20 letters/numbers etc'),
                'user_exists'          => $this->container->get('translator')->trans('This user is already in use'),
                'blocked_name'         => $this->container->get('translator')->trans('This username has been blocked by an administrator'),
                'insert_email'         => $this->container->get('translator')->trans('You have to insert an email'),
                'email_not_valid'      => $this->container->get('translator')->trans('This E-Mail is not valid. The format has to be example@example.com'),
                'insert_password'      => $this->container->get('translator')->trans('You have to insert a password'),
                'password_too_short'   => $this->container->get('translator')->trans('Your password is too short (min. 7 characters)'),
                'passwords_dont_match' => $this->container->get('translator')->trans('Your passwords don\'t match'),
                'captcha_error'        => $this->container->get('translator')->trans('The captcha was not correct'),
            );

            $captcha = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($_POST['g-recaptcha-response'], $request->getClientIp());
            if (!$captchaResponse->isSuccess()) {
                $registerResult = 'recaptcha:captcha_error';
            } else {
                $registerResult = Account::register(
                    $registerForm->get('username')->getData(),
                    $registerForm->get('email')->getData(),
                    $registerForm->get('password')->getData(),
                    $registerForm->get('password_verify')->getData()
                );
            }

            if (is_int($registerResult)) {

                $message = Swift_Message::newInstance()
                    ->setSubject('[Account] Email activation')
                    ->setFrom(array('no-reply-account@orbitrondev.org' => 'OrbitronDev'))
                    ->setTo(array($registerForm->get('email')->getData()))
                    ->setBody($this->renderView('account/mail/register.html.twig', array(
                        'username' => $registerForm->get('username')->getData(),
                        'email'    => $registerForm->get('email')->getData(),
                    )), 'text/html');
                $this->get('mailer')->send($message);

                // TODO: As soon as the user arrives the panel, he should see the error
                //if(!$mail->send()) {
                //$_SESSION['Register']['ErrorMessage'] = _('Could not send email. TRY AGAIN!');
                //} else {
                //$_SESSION['Register']['SuccessMessage'] = _('Your email has been send! Check also your Junk-Folder!');
                //}

                Account::login($registerForm->get('email')->getData(), $registerForm->get('password')->getData());
                if (isset($_POST['page'])) {
                    $requested_page = urldecode($_POST['page']);

                    header('Location: ' . $requested_page);
                    exit;
                } else {
                    header('Location: ' . $this->generateUrl('app_account_panel', array('page' => 'home')));
                    exit;
                }
            } elseif ($registerResult === false) {
                $_SESSION['RegisterError']['Message'] = $this->container->get('translator')->trans('Unknown error');
            } else {
                $errorMessage = explode(':', $registerResult);
                $registerErrorMessage = $resultCodes[$errorMessage[1]];
                if ($errorMessage[0] == 'form') {
                    $registerForm->addError(new FormError($registerErrorMessage));
                } else {
                    $registerForm->get($errorMessage[0])->addError(new FormError($registerErrorMessage));
                }
            }
        }

        return $this->render('account/register.html.twig', array(
            'register_form' => $registerForm->createView(),
        ));
    }

    public function panelAction()
    {
        $params = array();
        Account::updateSession();
        $params['user_id'] = USER_ID;
        $currentUser = new UserInfo(USER_ID);
        $params['current_user'] = $currentUser->aUser;
        $params['view_navigation'] = '';

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login');
        }

        AccountAcp::includeLibs();

        $view = 'acp_not_found';

        foreach (AccountAcp::getAllMenus('root') as $sMenu => $aMenuInfo) {
            $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'class="active"' : '');
            $params['view_navigation'] .= '<li><a href="' . $this->generateUrl('app_account_panel',
                    array('page' => $aMenuInfo['href'])) . '" ' . $selected . '>' . $aMenuInfo['title'] . '</a></li>';

            if (strlen($selected) > 0) {
                if (is_callable($aMenuInfo['screen'])) {
                    $view = $aMenuInfo['screen'];
                } else {
                    $view = 'acp_function_error';
                }
            }
        }

        foreach (AccountAcp::getAllGroups() as $sGroup => $aGroupInfo) {
            if (is_null($aGroupInfo['display']) || strlen($aGroupInfo['display']) == 0) {
                foreach (AccountAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
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
            $params['view_navigation'] .= '<li><a href="#">' . $aGroupInfo['title'] . '<span class="fa arrow"></span></a><ul class="nav nav-second-level collapse">';

            foreach (AccountAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'class="active"' : '');
                $params['view_navigation'] .= '<li><a href="' . $this->generateUrl('app_account_panel',
                        array('page' => $aMenuInfo['href'])) . '" ' . $selected . '>' . $aMenuInfo['title'] . '</a></li>';
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

        return $this->render('account/panel.html.twig', $params);
    }

    public function apiAction()
    {
        $function = $this->parameters['function'];
        if (strlen($this->parameters['parameters']) > 0) {
            $rawParameters = explode('&', $this->parameters['parameters']);
            $parameters = array();
            foreach ($rawParameters as $value) {
                $pair = explode('=', $value);
                $parameters[$pair[0]] = $pair[1];
            }
        } else {
            $request = Request::createFromGlobals();
            $parameters = $request->request->all();
        }
        $result = AccountApi::$function($parameters);
        return $this->json($result);
    }

    public function usersAction()
    {
        Account::updateSession();
        $username = $this->parameters['username'];
        if (AccountTools::userExist($this->parameters['username'])) {
            $userId = AccountTools::name2Id($username);
            $currentUser = new UserInfo($userId);
            return $this->render('account/user.html.twig', array(
                'logged_in_user_id'    => USER_ID,
                'user_exists'          => true,
                'current_user'         => $currentUser->aUser,
                'current_user_profile' => $currentUser->aProfile,
                'current_user_sub'     => $currentUser->aSubscription,
                'service_allowed'      => $currentUser->serviceAllowed() ? true : false,
                'blogs'                => Blog::getOwnerBlogList($currentUser->getFromUser('user_id')),
                'forums'               => Forum::getOwnerForumList($currentUser->getFromUser('user_id')),
                'stores'               => Store::getOwnerStoreList($currentUser->getFromUser('user_id')),
            ));
        } else {
            return $this->render('account/user.html.twig', array(
                'user_exists' => false,
            ));
        }
    }

    public function forgotAction()
    {
        Account::updateSession();
        $request = Request::createFromGlobals();
        if (LOGGED_IN) {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }

        $forgotForm = $this->createFormBuilder()
            ->add('email', EmailType::class, array(
                'label'       => 'E-mail',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email address')),
                    new Email(array('message' => 'Please enter a VALID email address')),
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
                'mapped'         => false,
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Send',
            ))
            ->getForm();

        if (!is_null($token = $request->query->get('token'))) {
            $token = new Token($token);
            $job = $token->getJob();
            if (is_null($job) || !$job) {
                if (is_string($job) && $job != 'reset_password') {
                    // Wrong token
                    $forgotForm->addError(new FormError('This token is not for resetting a password'));
                } else {
                    $forgotForm->addError(new FormError('Token not found'));
                }
                return $this->render('account/forgot-password.html.twig', array(
                    'forgot_form' => $forgotForm->createView(),
                ));
            } else {
                $resetForm = $this->createFormBuilder()
                    ->add('password', PasswordType::class, array(
                        'label'       => 'Password',
                        'constraints' => array(
                            new NotBlank(array('message' => 'Please enter your password')),
                        ),
                    ))
                    ->add('password_verify', PasswordType::class, array(
                        'label'       => 'Repeat Password',
                        'constraints' => array(
                            new NotBlank(array('message' => 'Please enter your password')),
                        ),
                    ))
                    ->add('send', SubmitType::class, array(
                        'label' => 'Change password',
                    ))
                    ->getForm();

                $resetForm->handleRequest($request);
                if ($resetForm->isSubmitted()) {
                    // Reset Email
                    $password = trim($resetForm->get('password')->getData());
                    $passwordVerify = trim($resetForm->get('password_verify')->getData());

                    if (strlen($password) == 0) {
                        $resetForm->get('password')->addError(new FormError('You have to insert a password'));
                        return $this->render('account/forgot-password-form.html.twig', array(
                            'reset_form' => $resetForm->createView(),
                        ));
                    } elseif (strlen($password) < 8) {
                        $resetForm->get('password')->addError(new FormError('Your password is too short (min. 7 characters)'));
                        return $this->render('account/forgot-password-form.html.twig', array(
                            'reset_form' => $resetForm->createView(),
                        ));
                    } elseif ($password != $passwordVerify) {
                        $resetForm->get('password_verify')->addError(new FormError('Your passwords don\'t match'));
                        return $this->render('account/forgot-password-form.html.twig', array(
                            'reset_form' => $resetForm->createView(),
                        ));
                    }

                    $userId = $token->getInformation()['user_id'];
                    $user = new UserInfo($userId);

                    $user->updatePassword(AccountTools::hash($password));
                    $token->remove();
                    return $this->render('account/forgot-password-form.html.twig', array(
                        'reset_form'      => $resetForm->createView(),
                        'success_message' => 'Successfully changed your password',
                        'redirect'        => $this->generateUrl('app_account_login'),
                    ));
                }

                return $this->render('account/forgot-password-form.html.twig', array(
                    'reset_form' => $resetForm->createView(),
                ));

            }
        } else {
            $forgotForm->handleRequest($request);
            if ($forgotForm->isSubmitted()) {

                $captcha = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
                $captchaResponse = $captcha->verify($_POST['g-recaptcha-response'], $request->getClientIp());
                if (!$captchaResponse->isSuccess()) {
                    $forgotForm->get('recaptcha')->addError(new FormError('The captcha was not correct'));
                } else {

                    $userId = AccountTools::email2Id($forgotForm->get('email')->getData());
                    if (AccountTools::idExists($userId)) {
                        $user = new UserInfo($userId);
                        $tokenGenerator = new Token();
                        $token = $tokenGenerator->generateToken('reset_password', strtotime('+1 day'),
                            array('user_id' => $user->getFromUser('user_id')));

                        $message = Swift_Message::newInstance()
                            ->setSubject('[Account] Reset password')
                            ->setFrom(array('info@orbitrondev.org' => 'OrbitronDev'))
                            ->setTo(array($user->getFromUser('email')))
                            ->setBody($this->renderView('account/mail/reset-password.html.twig', array(
                                'email' => $user->getFromUser('email'),
                                'token' => $token,
                            )), 'text/html');
                        $this->get('mailer')->send($message);

                        $params = array(
                            'forgot_form'     => $forgotForm->createView(),
                            'success_message' => 'Email sent',
                        );

                        // Email sent
                        return $this->render('account/forgot-password.html.twig', $params);
                    } else {
                        // Email does not exist
                        $forgotForm->get('email')->addError(new FormError('A user with this email does not exist.'));
                    }
                }
            }

            // Enter email to send mail
            return $this->render('account/forgot-password.html.twig', array(
                'forgot_form' => $forgotForm->createView(),
            ));
        }
    }

    public function confirmAction()
    {
        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);
        $request = Request::createFromGlobals();
        $sendEmailForm = $this->createFormBuilder()
            ->add('send', SubmitType::class, array(
                'label' => 'Send Email',
            ))
            ->getForm();

        if (!is_null($token = $request->query->get('token'))) {
            $token = new Token($token);
            $job = $token->getJob();
            if (is_null($job) || !$job) {
                $errorMessage = 'Token not found';
                if (is_string($job) && $job != 'confirm_email') {
                    $errorMessage = 'This token is not for email activation';
                }
                return $this->render('account/confirm-email.html.twig', array(
                    'error_message'   => $errorMessage,
                    'send_email_form' => $sendEmailForm->createView(),
                ));
            } else {
                $currentUser->updateEmailVerification(true);
                $successMessage = 'Successful verified your email';
                return $this->render('account/confirm-email.html.twig', array(
                    'success_message' => $successMessage,
                ));
            }
        } else {
            $sendEmailForm->handleRequest($request);
            if ($sendEmailForm->isSubmitted()) {
                $tokenGenerator = new Token();
                $token = $tokenGenerator->generateToken('confirm_email', strtotime('+1 day'));

                $message = Swift_Message::newInstance()
                    ->setSubject('[Account] Email activation')
                    ->setFrom(array('team-orbitron@hotmail.com' => 'OrbitronDev'))
                    ->setTo(array($currentUser->getFromUser('email')))
                    ->setBody($this->renderView('account/mail/confirm-email.html.twig', array(
                        'username' => $currentUser->getFromUser('username'),
                        'email'    => $currentUser->getFromUser('email'),
                        'token'    => $token,
                    )), 'text/html');
                $this->get('mailer')->send($message);


                $params = array(
                    'send_email_form' => $sendEmailForm->createView(),
                    'success_message' => 'Email sent',
                );

                return $this->render('account/confirm-email.html.twig', $params);
            }

            return $this->render('account/confirm-email.html.twig', array(
                'send_email_form' => $sendEmailForm->createView(),
            ));
        }
    }

    /** @var \OAuth2\Storage\Pdo $oauthStorage */
    private $oauthStorage = null;
    /** @var \OAuth2\Server $oauthServer */
    private $oauthServer = null;

    public function oauthServer()
    {
        $config = $this->get('config');

        // $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
        $dsn = 'mysql:dbname=' . $config['parameters']['database_name'] . ';host=' . $config['parameters']['database_host'];
        $this->oauthStorage = new \OAuth2\Storage\Pdo(array(
            'dsn'      => $dsn,
            'username' => $config['parameters']['database_user'],
            'password' => $config['parameters']['database_password'],
        ));

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $this->oauthServer = new \OAuth2\Server($this->oauthStorage, array(
            'always_issue_new_refresh_token' => true,
            'refresh_token_lifetime'         => 2419200,
        ));

        // Get all SCOPES
        $scopesList = AccountDeveloper::getAllScopes();

        $defaultScope = '';
        $supportedScopes = array();

        foreach ($scopesList as $scope) {
            if ($scope['is_default']) {
                $defaultScope = $scope['scope'];
            }
            $supportedScopes[] = $scope['scope'];
        }
        $memory = new \OAuth2\Storage\Memory(array(
            'default_scope'    => $defaultScope,
            'supported_scopes' => $supportedScopes,
        ));
        $scopeUtil = new \OAuth2\Scope($memory);
        $this->oauthServer->setScopeUtil($scopeUtil);

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $this->oauthServer->addGrantType(new ClientCredentials($this->oauthStorage));

        // Add the "Refresh Token" grant type
        $this->oauthServer->addGrantType(new RefreshToken($this->oauthStorage, array(
            'always_issue_new_refresh_token' => true,
        )));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->oauthServer->addGrantType(new AuthorizationCode($this->oauthStorage));
    }

    public function oauthAuthorizeAction()
    {
        $this->oauthServer();

        $request2 = Request::createFromGlobals();
        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();

        // validate the authorize request
        if (!$this->oauthServer->validateAuthorizeRequest($request, $response)) {
            //return $this->oauthServer->getResponse();
            $response->send();
            die;
        }
        // display an authorization form
        $clientInfo = AccountDeveloper::getClientInformation($request2->query->get('client_id')); // Get all information about the Client requesting an Auth code

        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $scopes = array();
        foreach (explode(' ', trim($clientInfo['scope'])) as $scope) {
            $getScope = $database->prepare('SELECT * FROM `oauth_scopes` WHERE `scope`=:scope LIMIT 1');
            $getScope->execute(array(
                ':scope' => $scope,
            ));
            if ($getScope->rowCount() > 0) {
                $scopeInfo = $getScope->fetchAll(\PDO::FETCH_ASSOC);
                $scopes[] = $scopeInfo[0]['name'];
            }
        }
        if (empty($_POST)) {
            return $this->render('account/oauth-authorize.html.twig', array(
                'client_info' => $clientInfo,
                'scopes'      => $scopes,
            ));
        }

        // print the authorization code if the user has authorized your client
        Account::updateSession();
        $is_authorized = ($_POST['authorized'] === 'Yes');
        $this->oauthServer->handleAuthorizeRequest($request, $response, $is_authorized, USER_ID);
        // if ($is_authorized) {
        //     // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
        //     $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
        //     exit("SUCCESS! Authorization Code: $code");
        // }
        return $response->send();
    }

    // curl https://account.orbitrondev.org/oauth/token -d 'grant_type=authorization_code&code=AUTHORIZATION_CODE&client_id=testclient&client_secret=testpass&redirect_uri=http://d3strukt0r.esy.es'
    public function oauthTokenAction()
    {
        $this->oauthServer();

        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        $this->oauthServer->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send();
    }

    // curl https://account.orbitrondev.org/oauth/resource -d 'access_token=YOUR_TOKEN'
    // TODO: That has nothing lost in here. This goes into the API section
    public function oauthResourceAction()
    {
        $this->oauthServer();

        // Handle a request to a resource and authenticate the access token
        if (!$this->oauthServer->verifyResourceRequest(\OAuth2\Request::createFromGlobals(), null, null)) {
            $this->oauthServer->getResponse()->send();
            die;
        }

        $token = $this->oauthServer->getAccessTokenData(\OAuth2\Request::createFromGlobals());

        echo json_encode(array(
            'success' => true,
            'message' => 'You accessed my APIs!',
            'user_id' => $token['user_id'],
        ));
    }
}