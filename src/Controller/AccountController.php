<?php

namespace Controller;

use App\Account\AccountAcp;
use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Account\Form\LoginType;
use App\Account\Form\RegisterType;
use App\Core\Token;
use ReCaptcha\ReCaptcha;
use Swift_Message;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AccountController extends \Controller
{
    public function indexAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_admin_logout');
        }

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_admin_login');
        } else {
            return $this->redirectToRoute('app_admin_panel', array('page' => 'home'));
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
        $redirectUrl = strlen($request->query->get('redir')) > 0 ? $request->query->get('redir') : $this->generateUrl('app_admin_login');

        return $this->redirect($redirectUrl);
    }

    public function loginAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_admin_logout');
        }

        if (LOGGED_IN) {
            return $this->redirectToRoute('app_admin_panel', array('page' => 'home'));
        }

        $request = $this->getRequest();
        $loginForm = $this->createForm(LoginType::class);
        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $resultCodes = array(
                'wrong_password'      => $this->container->get('translator')->trans('Incorrect password'),
                'insert_username'     => $this->container->get('translator')->trans('Please enter your username'),
                'insert_password'     => $this->container->get('translator')->trans('Please enter your password'),
                'user_does_not_exist' => $this->container->get('translator')->trans('This user doesn\'t exist'),
                'unknown_error'       => $this->container->get('translator')->trans('Unknown error'),
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
            return $this->redirectToRoute('app_admin_logout');
        }

        if (LOGGED_IN) {
            return $this->redirectToRoute('app_admin_panel', array('page' => 'home'));
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
                $url = $request->query->has('page') ? urldecode($request->query->get('page')) : $this->generateUrl('app_admin_panel', array('page' => 'home'));
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
            return $this->redirectToRoute('app_admin_logout');
        }

        $params = array();
        $params['user_id'] = USER_ID;
        $params['current_user'] = $em->find(User::class, USER_ID);
        $params['view_navigation'] = '';

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_admin_login');
        }

        AccountAcp::includeLibs();

        $view = 'acp_not_found';

        foreach (AccountAcp::getAllMenus('root') as $sMenu => $aMenuInfo) {
            $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
            $url = $this->generateUrl('app_admin_panel', array('page' => $aMenuInfo['href']));
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
                $url = $this->generateUrl('app_admin_panel', array('page' => $aMenuInfo['href']));
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

    public function usersAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_admin_logout');
        }

        $username = $this->parameters['username'];
        if (AccountHelper::usernameExists($this->parameters['username'])) {
            /** @var \App\Account\Entity\User $currentUser */
            $currentUser = $em->getRepository(User::class)->findOneBy(array('username' => $username));

            return $this->render('account/user.html.twig', array(
                'logged_in_user_id' => USER_ID,
                'user_exists'       => true,
                'current_user'      => $currentUser,
                'service_allowed'   => in_array('web_service', $currentUser->getSubscription()->getSubscription()->getPermissions()) ? true : false,
            ));
        } else {
            return $this->render('account/user.html.twig', array(
                'user_exists' => false,
            ));
        }
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
