<?php

namespace Controller;

use App\Account\AccountAcp;
use App\Account\AccountApi;
use App\Account\AccountHelper;
use App\Account\Entity\OAuthAccessToken;
use App\Account\Entity\OAuthAuthorizationCode;
use App\Account\Entity\OAuthClient;
use App\Account\Entity\OAuthRefreshToken;
use App\Account\Entity\OAuthScope;
use App\Account\Entity\User;
use App\Account\Form\ConfirmEmailType;
use App\Account\Form\ForgotType;
use App\Account\Form\LoginType;
use App\Account\Form\RegisterType;
use App\Account\Form\ResetPasswordType;
use App\Blog\BlogHelper;
use App\Core\Token;
use App\Forum\ForumHelper;
use App\Store\StoreHelper;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use ReCaptcha\ReCaptcha;
use Swift_Message;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AccountController extends \Controller
{
    public function indexAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login');
        } else {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }
    }

    public function logoutAction()
    {
        $update = AccountHelper::updateSession();

        if (is_null($update) || (defined('LOGGED_IN') && LOGGED_IN)) {
            $response = new RedirectResponse($this->getRequest()->getUri());
            AccountHelper::logout();
            return $response;
        }
        $request = $this->getRequest();
        $redirectUrl = strlen($request->query->get('redir')) > 0 ? $request->query->get('redir') : $this->generateUrl('app_account_login');

        return $this->redirect($redirectUrl);
    }

    public function loginAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        if (LOGGED_IN) {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }

        $request = $this->getRequest();
        $loginForm = $this->createForm(LoginType::class);
        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $resultCodes = array(
                'wrong_password'   => $this->container->get('translator')->trans('Incorrect password'),
                'insert_username'  => $this->container->get('translator')->trans('Please enter your username'),
                'insert_password'  => $this->container->get('translator')->trans('Please enter your password'),
                'user_does_not_exist' => $this->container->get('translator')->trans('This user doesn\'t exist'),
                'unknown_error'    => $this->container->get('translator')->trans('Unknown error'),
            );
            $loginData = $loginForm->getData();

            $response = new RedirectResponse($loginData['redirect']);
            $loginResult = AccountHelper::login(
                $response,
                $loginData['email'],
                $loginData['password'],
                $loginData['remember']
            );

            if ($loginResult === true) {
                return $response;
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
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        if (LOGGED_IN) {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }

        $request = $this->getRequest();
        $registerForm = $this->createForm(RegisterType::class);
        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $resultCodes = array(
                'insert_username'        => $this->container->get('translator')->trans('You have to insert an username'),
                'username_short'         => $this->container->get('translator')->trans('Your username must be between 3 and 20 letters/numbers etc'),
                'username_long'          => $this->container->get('translator')->trans('Your username must be between 3 and 20 letters/numbers etc'),
                'user_exists'            => $this->container->get('translator')->trans('This user is already in use'),
                'blocked_name'           => $this->container->get('translator')->trans('This username has been blocked by an administrator'),
                'insert_email'           => $this->container->get('translator')->trans('You have to insert an email'),
                'email_not_valid'        => $this->container->get('translator')->trans('This E-Mail is not valid. The format has to be example@example.com'),
                'insert_password'        => $this->container->get('translator')->trans('You have to insert a password'),
                'password_too_short'     => $this->container->get('translator')->trans('Your password is too short (min. 7 characters)'),
                'passwords_do_not_match' => $this->container->get('translator')->trans('Your passwords don\'t match'),
                'captcha_error'          => $this->container->get('translator')->trans('The captcha was not correct'),
            );

            $captcha = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
            if (!$captchaResponse->isSuccess()) {
                $registerResult = 'recaptcha:captcha_error';
            } else {
                $registerData = $registerForm->getData();
                $registerResult = AccountHelper::addUser(
                    $registerData['username'],
                    $registerData['password'],
                    $registerData['password_verify'],
                    $registerData['email']
                );
            }

            if (is_int($registerResult)) {
                $tokenGenerator = new Token();
                $token = $tokenGenerator->generateToken('confirm_email', (new \DateTime())->modify('+1 day'));

                $message = (new Swift_Message())
                    ->setSubject('[Account] Email activation')
                    ->setFrom(array('no-reply-account@orbitrondev.org' => 'OrbitronDev'))
                    ->setTo(array($registerForm->get('email')->getData()))
                    ->setBody($this->renderView('account/mail/register.html.twig', array(
                        'username' => $registerForm->get('username')->getData(),
                        'email'    => $registerForm->get('email')->getData(),
                        'token'    => $token,
                    )), 'text/html');
                $mailSent = $this->get('mailer')->send($message);

                if ($mailSent) {
                    $this->addFlash('successful', 'Your confirmation email has been send! Also check your Junk-Folder!');
                } else {
                    $this->addFlash('failed', 'Could not send confirmation email. Please send the confirmation mail for you E-Mail address again trough your account settings');
                }
                $url = $request->query->has('page') ? urldecode($request->query->get('page')) : $this->generateUrl('app_account_panel', array('page' => 'home'));
                $response = new RedirectResponse($url);
                AccountHelper::login(
                    $response,
                    $registerForm->get('email')->getData(),
                    $registerForm->get('password')->getData()
                );
                return $response;
            } elseif ($registerResult === false) {
                $this->addFlash('error', $this->container->get('translator')->trans('Unknown error'));
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
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        $params = array();
        $params['user_id'] = USER_ID;
        $params['current_user'] = $em->find(User::class, USER_ID);
        $params['view_navigation'] = '';

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login');
        }

        AccountAcp::includeLibs();

        $view = 'acp_not_found';

        foreach (AccountAcp::getAllMenus('root') as $sMenu => $aMenuInfo) {
            $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
            $url = $this->generateUrl('app_account_panel', array('page' => $aMenuInfo['href']));
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

        foreach (AccountAcp::getAllGroups() as $sGroup => $aGroupInfo) {
            if (is_null($aGroupInfo['display']) || strlen($aGroupInfo['display']) == 0) {
                foreach (AccountAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
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

            foreach (AccountAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
                $url = $this->generateUrl('app_account_panel', array('page' => $aMenuInfo['href']));
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

        return $this->render('account/panel.html.twig', $params);
    }

    public function apiAction()
    {
        // Get function name
        $function = $this->parameters['function'];
        $functionProcess = explode('_', $function);
        foreach ($functionProcess as $key => $item) {
            $functionProcess[$key] = ucfirst($item);
        }
        $function = implode('', $functionProcess);
        $function = lcfirst($function);

        // Prepare all parameters
        if (strlen($this->parameters['parameters']) > 0) {
            $rawParameters = explode('&', $this->parameters['parameters']);
            $parameters = array();
            foreach ($rawParameters as $value) {
                $pair = explode('=', $value);
                $parameters[$pair[0]] = $pair[1];
            }
        } else {
            $request = $this->getRequest();
            $parameters = $request->request->all();
        }

        $result = AccountApi::$function($parameters);
        if (is_array($result)) {
            return $this->json($result);
        } elseif (is_null($result)) {
            return '';
        } else {
            return $result;
        }
    }

    public function usersAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        $username = $this->parameters['username'];
        if (AccountHelper::usernameExists($this->parameters['username'])) {
            /** @var \App\Account\Entity\User $currentUser */
            $currentUser = $em->getRepository(User::class)->findOneBy(array('username' => $username));
            return $this->render('account/user.html.twig', array(
                'logged_in_user_id'    => USER_ID,
                'user_exists'          => true,
                'current_user'         => $currentUser,
                'service_allowed'      => in_array('web_service', $currentUser->getSubscription()->getSubscription()->getPermissions()) ? true : false,
                'blogs'                => BlogHelper::getOwnerBlogList($currentUser),
                'forums'               => ForumHelper::getOwnerForumList($currentUser),
                'stores'               => StoreHelper::getOwnerStoreList($currentUser),
            ));
        } else {
            return $this->render('account/user.html.twig', array(
                'user_exists' => false,
            ));
        }
    }

    public function forgotAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        $request = $this->getRequest();
        if (LOGGED_IN) {
            return $this->redirectToRoute('app_account_panel', array('page' => 'home'));
        }

        $forgotForm = $this->createForm(ForgotType::class);

        if (!is_null($token = $request->query->get('token'))) {
            $token = new Token($token);
            $job = $token->getJob();
            if (is_null($job) || $job === false) {
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
                $resetForm = $this->createForm(ResetPasswordType::class);
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
                    } elseif (strlen($password) < AccountHelper::$settings['password']['min_length']) {
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
                    /** @var \App\Account\Entity\User $user */
                    $user = $em->find(User::class, $userId);
                    $user->setPassword($password);
                    $em->flush();
                    $token->remove();

                    $this->addFlash('success', 'Successfully changed your password');
                    return $this->render('account/forgot-password-form.html.twig', array(
                        'reset_form' => $resetForm->createView(),
                        'redirect'   => $this->generateUrl('app_account_login'),
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
                $captchaResponse = $captcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
                if (!$captchaResponse->isSuccess()) {
                    $forgotForm->get('recaptcha')->addError(new FormError('The captcha was not correct'));
                } else {

                    if (AccountHelper::emailExists($forgotForm->get('email')->getData())) {
                        /** @var \App\Account\Entity\User $user */
                        $user = $em->getRepository(User::class)->findOneBy(array('email' => $forgotForm->get('email')->getData()));
                        $tokenGenerator = new Token();
                        $token = $tokenGenerator->generateToken('reset_password', (new \DateTime())->modify('+1 day'), array('user_id' => $user->getId()));

                        $message = (new Swift_Message())
                            ->setSubject('[Account] Reset password')
                            ->setFrom(array('no-reply-account@orbitrondev.org' => 'OrbitronDev'))
                            ->setTo(array($user->getEmail()))
                            ->setBody($this->renderView('account/mail/reset-password.html.twig', array(
                                'email' => $user->getEmail(),
                                'token' => $token,
                            )), 'text/html');
                        $this->get('mailer')->send($message);

                        // Email sent
                        $this->addFlash('success', 'Email sent');
                        return $this->render('account/forgot-password.html.twig', array(
                            'forgot_form' => $forgotForm->createView(),
                        ));
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
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);
        $request = $this->getRequest();
        $sendEmailForm = $this->createForm(ConfirmEmailType::class);

        if (!is_null($token = $request->query->get('token'))) {
            $token = new Token($token);
            $job = $token->getJob();
            if (is_null($job) || $job === false) {
                $errorMessage = 'Token not found';
                if (is_string($job) && $job != 'confirm_email') {
                    $errorMessage = 'This token is not for email activation';
                }
                $this->addFlash('failure', $errorMessage);
                return $this->render('account/confirm-email.html.twig', array(
                    'send_email_form' => $sendEmailForm->createView(),
                ));
            } else {
                $currentUser->setEmailVerified(true);
                $em->flush();
                $token->remove();
                $this->addFlash('success', 'Successfully verified your email');
                return $this->render('account/confirm-email.html.twig');
            }
        } else {
            $sendEmailForm->handleRequest($request);
            if ($sendEmailForm->isSubmitted()) {
                $tokenGenerator = new Token();
                $token = $tokenGenerator->generateToken('confirm_email', (new \DateTime())->modify('+1 day'));

                $message = (new Swift_Message())
                    ->setSubject('[Account] Email activation')
                    ->setFrom(array('no-reply-account@orbitrondev.org' => 'OrbitronDev'))
                    ->setTo(array($currentUser->getEmail()))
                    ->setBody($this->renderView('account/mail/confirm-email.html.twig', array(
                        'username' => $currentUser->getUsername(),
                        'email'    => $currentUser->getEmail(),
                        'token'    => $token,
                    )), 'text/html');
                $this->get('mailer')->send($message);

                $this->addFlash('success', 'Email sent');
            }

            return $this->render('account/confirm-email.html.twig', array(
                'send_email_form' => $sendEmailForm->createView(),
            ));
        }
    }

    /** @var \OAuth2\Server $oauthServer */
    private $oauthServer = null;

    public function oauthServer()
    {
        $em = $this->getEntityManager();

        /** @var \App\Account\Repository\OAuthClientRepository $clientStorage */
        $clientStorage  = $em->getRepository(OAuthClient::class);
        /** @var \App\Account\Repository\UserRepository $userStorage */
        $userStorage = $em->getRepository(User::class);
        /** @var \App\Account\Repository\OAuthAccessTokenRepository $accessTokenStorage */
        $accessTokenStorage  = $em->getRepository(OAuthAccessToken::class);
        /** @var \App\Account\Repository\OAuthAuthorizationCodeRepository $authorizationCodeStorage */
        $authorizationCodeStorage = $em->getRepository(OAuthAuthorizationCode::class);
        /** @var \App\Account\Repository\OAuthRefreshTokenRepository $refreshTokenStorage */
        $refreshTokenStorage = $em->getRepository(OAuthRefreshToken::class);

        // Pass the doctrine storage objects to the OAuth2 server class
        $this->oauthServer = new \OAuth2\Server(array(
            'client_credentials' => $clientStorage,
            'user_credentials'   => $userStorage,
            'access_token'       => $accessTokenStorage,
            'authorization_code' => $authorizationCodeStorage,
            'refresh_token'      => $refreshTokenStorage,
        ), array(
            'refresh_token_lifetime' => 2419200,
        ));

        // Get all SCOPES
        $scopesList = AccountHelper::getAllScopes();

        $defaultScope = '';
        $supportedScopes = array();

        foreach ($scopesList as $scope) {
            if ($scope->isDefault()) {
                $defaultScope = $scope->getScope();
            }
            $supportedScopes[] = $scope->getScope();
        }
        $memory = new \OAuth2\Storage\Memory(array(
            'default_scope'    => $defaultScope,
            'supported_scopes' => $supportedScopes,
        ));
        $scopeUtil = new \OAuth2\Scope($memory);
        $this->oauthServer->setScopeUtil($scopeUtil);

        // Add all grant types
        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $this->oauthServer->addGrantType(new ClientCredentials($clientStorage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->oauthServer->addGrantType(new AuthorizationCode($authorizationCodeStorage));

        // Add the "Refresh Token" grant type
        $this->oauthServer->addGrantType(new RefreshToken($refreshTokenStorage, array(
            // the refresh token grant request will have a "refresh_token" field
            // with a new refresh token on each request
            'always_issue_new_refresh_token' => true,
        )));
    }

    public function oauthAuthorizeAction()
    {
        $this->oauthServer();
        $em = $this->getEntityManager();

        $request2 = $this->getRequest();
        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();

        // validate the authorize request
        if (!$this->oauthServer->validateAuthorizeRequest($request, $response)) {
            //return $this->oauthServer->getResponse();
            $response->send();
        }
        // display an authorization form
        // Get all information about the Client requesting an Auth code
        $clientInfo = AccountHelper::getAppInformation($request2->query->get('client_id'));

        $scopes = array();
        foreach ($clientInfo->getScopes() as $scope) {
            /** @var \App\Account\Entity\OAuthScope $getScope */
            $getScope = $em->getRepository(OAuthScope::class)->findOneBy(array('scope' => $scope));
            if (!is_null($getScope)) {
                $scopes[] = $getScope->getName();
            }
        }
        if (empty($_POST)) {
            return $this->render('account/oauth-authorize.html.twig', array(
                'client_info' => $clientInfo,
                'scopes'      => $scopes,
            ));
        }

        // print the authorization code if the user has authorized your client
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        $is_authorized = ($_POST['authorized'] === 'Yes');
        $this->oauthServer->handleAuthorizeRequest($request, $response, $is_authorized, USER_ID);
        // if ($is_authorized) {
        //     // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
        //     $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
        //     exit("SUCCESS! Authorization Code: $code");
        // }
        $response->send();
    }

    // curl https://account.orbitrondev.org/oauth/token -d 'grant_type=authorization_code&code=AUTHORIZATION_CODE&client_id=testclient&client_secret=testpass&redirect_uri=http://d3strukt0r.esy.es'
    public function oauthTokenAction()
    {
        $this->oauthServer();

        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        $request = \OAuth2\Request::createFromGlobals();
        /** @var \OAuth2\Response $response */
        $response = $this->oauthServer->handleTokenRequest($request);
        $response->send();
    }

    // curl https://account.orbitrondev.org/oauth/resource -d 'access_token=YOUR_TOKEN'
    // TODO: That has nothing lost in here. This goes into the API section
    public function oauthResourceAction()
    {
        $this->oauthServer();
        $request = \OAuth2\Request::createFromGlobals();

        // Handle a request to a resource and authenticate the access token
        if (!$this->oauthServer->verifyResourceRequest($request, null, null)) {
            $this->oauthServer->getResponse()->send();
            exit;
        }

        $token = $this->oauthServer->getAccessTokenData($request);

        echo json_encode(array(
            'success' => true,
            'message' => 'You accessed my APIs!',
            'user_id' => $token['user_id'],
        ));
    }

    public function oneTimeSetupAction()
    {
        if ($this->getRequest()->query->get('key') == $this->get('config')['parameters']['setup_key']) {
            $text = '';
            AccountHelper::addDefaultSubscriptionTypes();
            $text .= 'Subscription types added<br />';
            AccountHelper::addDefaultScopes();
            $text .= 'Scopes added<br />';
            return $text;
        }
        return 'No setup key given, or key not correct.';
    }
}
