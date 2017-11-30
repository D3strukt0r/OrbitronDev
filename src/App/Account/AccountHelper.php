<?php

namespace App\Account;

use App\Account\Entity\OAuthClient;
use App\Account\Entity\OAuthScope;
use App\Account\Entity\SubscriptionType;
use App\Account\Entity\User;
use App\Account\Entity\UserProfiles;
use App\Account\Entity\UserSubscription;
use Container\TranslatingContainer;
use Kernel;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AccountHelper
{
    public static $settings = array(
        'username'     => array(
            'min_length'    => 3,
            'max_length'    => 50,
            'blocked'       => array('admin', 'administrator', 'mod', 'moderator', 'guest', 'undefined'),
            'blocked_parts' => array('mod', 'system', 'admin'),
            'pattern'       => '/^[a-z0-9_]+$/i', // Accepted: a-z, A-Z, 1-9 and _
        ),
        'password'     => array(
            'min_length' => 7,
            'max_length' => 100,
            'salt'       => 'random',
        ),
        'email'        => array(
            'pattern' => '/^[a-z0-9_\.-]+@([a-z0-9]+([\-]+[a-z0-9]+)*\.)+[a-z]{2,7}$/i',
        ),
        'subscription' => array(
            'default' => 1,
        ),
        'login'        => array(
            'session_email'    => 'USER_EM',
            'session_password' => 'USER_PW',
            'cookie_name'      => 'account',
            'cookie_expire'    => '+1 month',
            'cookie_path'      => '/',
            'cookie_domain'    => 'orbitrondev.org',
        ),
    );

    public static $publicDir;
    public static $srcDir;
    public static $twigDir;

    public static function buildPaths()
    {
        self::$publicDir = Kernel::getIntent()->getRootDir() . '/web/app/account';
        self::$srcDir = Kernel::getIntent()->getRootDir() . '/src/App/Account';
        self::$twigDir = Kernel::getIntent()->getRootDir() . '/app/views/account';
    }

    /**
     * Add a new user. Username, Email, and password is required twice. Returns
     * the user id.
     *
     * @param string $username
     * @param string $password
     * @param string $passwordVerify
     * @param string $email
     *
     * @return int|string
     */
    public static function addUser($username, $password, $passwordVerify, $email)
    {
        // Check username
        if (strlen($username) == 0) {
            return 'username:insert_username';
        } elseif (strlen($username) < self::$settings['username']['min_length']) {
            return 'username:username_short';
        } elseif (strlen($username) > self::$settings['username']['max_length']) {
            return 'username:username_long';
        } elseif (self::usernameExists($username)) {
            return 'username:user_exists';
        } elseif (self::usernameBlocked($username)) {
            return 'username:blocked_name';
        } elseif (!self::usernameValid($username)) {
            return 'username:not_valid_name';
        } // Check E-Mail
        elseif (strlen($email) == 0) {
            return 'email:insert_email';
        } elseif (!self::emailValid($email)) {
            return 'email:email_not_valid';
        } // Check password
        elseif (strlen($password) == 0) {
            return 'password:insert_password';
        } elseif (strlen($password) < self::$settings['password']['min_length']) {
            return 'password:password_too_short';
        } elseif ($password != $passwordVerify) {
            return 'password_verify:passwords_do_not_match';
        }

        // Add user to database
        $request = \Kernel::getIntent()->getRequest();
        $entityManager = \Kernel::getIntent()->getEntityManager();
        $user = new User();
        $user
            ->setUsername($username)
            ->setPassword($password)
            ->setEmail($email)
            ->setEmailVerified(false)
            ->setCreatedOn(new \DateTime())
            ->setLastOnlineAt(new \DateTime())
            ->setCreatedIp($request->getClientIp())
            ->setLastIp($request->getClientIp())
            ->setDeveloperStatus(false)
            ->setCredits(0);


        $userProfile = new UserProfiles();
        $userProfile->setUser($user);
        $user->setProfile($userProfile);

        /** @var SubscriptionType $defaultSubscription */
        $defaultSubscription = $entityManager->find(SubscriptionType::class, self::$settings['subscription']['default']);

        $userSubscription = new UserSubscription();
        $userSubscription
            ->setUser($user)
            ->setSubscription($defaultSubscription)
            ->setActivatedAt(new \DateTime())
            ->setExpiresAt(new \DateTime());
        $user->setSubscription($userSubscription);

        $entityManager->persist($user);
        $entityManager->flush();

        return (int)$user->getId();
    }

    public static function removeUser(User $user)
    {
        // TODO: Removing user function does not work yet
        \Kernel::getIntent()->getEntityManager()->remove($user);
        \Kernel::getIntent()->getEntityManager()->remove($user->getProfile());
        \Kernel::getIntent()->getEntityManager()->remove($user->getSubscription());
        \Kernel::getIntent()->getEntityManager()->flush();
    }

    public static function login(Response &$response, $usernameOrEmail, $password, $remember = false)
    {
        if (strlen($usernameOrEmail) === 0) {
            return 'email:insert_username';
        } elseif (strlen($password) === 0) {
            return 'password:insert_password';
        } elseif (!self::userExists($usernameOrEmail)) {
            return 'email:user_does_not_exist';
        }

        // TODO: Maybe add function like validateLogin()
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(array('username' => $usernameOrEmail));
        if (is_null($user)) {
            /** @var User $user */
            $user = $em->getRepository(User::class)->findOneBy(array('email' => $usernameOrEmail));
            if (is_null($user)) {
                return 'email:user_does_not_exist';
            }
        }
        /** @var \App\Account\Repository\UserRepository $userRepo */
        $userRepo = $em->getRepository(User::class);
        if (!$userRepo->checkUserCredentials($usernameOrEmail, $password)) {
            return 'password:wrong_password';
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = \Kernel::getIntent()->get('session');
        $session->set(self::$settings['login']['session_email'], $user->getEmail());
        $session->set(self::$settings['login']['session_password'], $password); // TODO: Password is published FIX THAT

        if ($remember) {
            $response->headers->setCookie(new Cookie(
                self::$settings['login']['cookie_name'],
                base64_encode(json_encode(array(
                    'email' => $session->get(self::$settings['login']['session_email']),
                    'password' => $session->get(self::$settings['login']['session_password']),
                ))),
                strtotime(self::$settings['login']['cookie_expire']),
                self::$settings['login']['cookie_path'],
                self::$settings['login']['cookie_domain']
            ));
        }

        return true;
    }

    public static function logout()
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = \Kernel::getIntent()->get('session');
        $session->remove(self::$settings['login']['session_email']);
        $session->remove(self::$settings['login']['session_password']);

        setcookie(
            self::$settings['login']['cookie_name'],
            "",
            0,
            self::$settings['login']['cookie_path'],
            self::$settings['login']['cookie_domain']
        );
    }

    /**
     * Checks whether the username or email exists in the database. Returns
     * true when the username or email exist once in the database.
     *
     * @param string $usernameOrEmail
     *
     * @return bool
     */
    public static function userExists($usernameOrEmail)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(array('username' => $usernameOrEmail));
        if (is_null($user)) {
            $user = $em->getRepository(User::class)->findOneBy(array('email' => $usernameOrEmail));
            if (is_null($user)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks whether the username is already existing in the database. Returns
     * true when the username is already existing once in the database.
     *
     * @param string $username
     *
     * @return bool
     */
    public static function usernameExists($username)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(array('username' => $username));

        if (is_null($user)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the username is blocked or has any blocked parts in it.
     * Returns true when the name is blocked or has a blocked part. Returns
     * false if ok.
     *
     * @param string $username
     *
     * @return bool
     */
    public static function usernameBlocked($username)
    {
        foreach (self::$settings['username']['blocked'] as $bl) {
            if (strtolower($username) == strtolower($bl)) {
                return true;
            }
        }

        foreach (self::$settings['username']['blocked_parts'] as $bl) {
            if (strpos(strtolower($username), strtolower($bl)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the username corresponds the desired pattern. Returns
     * true when the string matches the pattern.
     *
     * @param string $username
     *
     * @return int
     */
    public static function usernameValid($username)
    {
        return preg_match(self::$settings['username']['pattern'], $username);
    }

    /**
     * Returns the username in a beautiful formatted way
     *
     * @param int  $user_id
     * @param bool $link
     * @param bool $styles
     *
     * @return string
     * @throws \Exception
     */
    public static function formatUsername($user_id, $link = true, $styles = true)
    {
        $translator = TranslatingContainer::$translator;

        /** @var \App\Account\Entity\User $selectedUser */
        $selectedUser = Kernel::getIntent()->getEntityManager()->find(User::class, $user_id);

        if (is_null($selectedUser)) {
            return '<s>'.$translator->trans('Unknown user').'</s>';
        }

        $sPrefix = '';
        $username = $selectedUser->getUsername();
        $sSuffix = '';

        if ($link) {
            $sPrefix .= '<a href="'.Kernel::getIntent()->get('router')->generate('app_account_user', array('username' => $selectedUser->getUsername())).'">';
            $sSuffix .= '</a>';
        }

        if ($styles) {
            if ($selectedUser->getSubscription()->getSubscription()->getTitle() == 'Premium') {
                $sPrefix .= '<span style="color:orange">';
                $sSuffix .= '</span>';
            } elseif ($selectedUser->getSubscription()->getSubscription()->getTitle() == 'Enterprise') {
                $sPrefix .= '<span style="color:green">';
                $sSuffix .= '</span>';
            }
        }

        return stripslashes(trim($sPrefix.$username.$sSuffix));
    }

    /**
     * Checks whether the email is already existing in the database. Returns
     * true when the email is already existing once in the database.
     *
     * @param string $email
     *
     * @return bool
     */
    public static function emailExists($email)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(array('email' => $email));

        if (is_null($user)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the email corresponds the desired pattern. Returns
     * true when the string matches the pattern.
     *
     * @param string $email
     *
     * @return int
     */
    public static function emailValid($email)
    {
        return preg_match(self::$settings['email']['pattern'], $email);
    }

    /**
     * Checks if the given password matches the one of the user. A user
     * entity of User is required for that. Returns true if it matches
     * or false if not
     *
     * @param User   $user
     * @param string $password
     *
     * @return bool
     */
    public static function passwordMatches(User $user, $password)
    {
        return password_verify($password, $user->getPassword());
    }

    public static function changeSession($email = null, $password = null)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = \Kernel::getIntent()->get('session');

        if (!is_null($email)) {
            $session->set(self::$settings['login']['session_email'], $email);
        }
        if (!is_null($password)) {
            $session->set(self::$settings['login']['session_password'], $password);
        }

        setcookie(
            self::$settings['login']['cookie_name'],
            base64_encode(json_encode(array(
                'email' => $session->get(self::$settings['login']['session_email']),
                'password' => $session->get(self::$settings['login']['session_password']),
            ))),
            strtotime(self::$settings['login']['cookie_expire']),
            self::$settings['login']['cookie_path'],
            self::$settings['login']['cookie_domain']
        );
    }

    public static function updateSession()
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = \Kernel::getIntent()->get('session');
        $request = \Kernel::getIntent()->getRequest();

        if ($request->cookies->has(self::$settings['login']['cookie_name'])) {
            $rememberData = json_decode(base64_decode($request->cookies->get(self::$settings['login']['cookie_name'])), true);
        }
        if (($session->has(self::$settings['login']['session_email']) && $session->has(self::$settings['login']['session_password'])) || isset($rememberData)) {
            if (isset($rememberData) && $rememberData['email'] && $rememberData['password']) {
                $session->set(self::$settings['login']['session_email'], $rememberData['email']);
                $session->set(self::$settings['login']['session_password'], $rememberData['password']);
            }

            $em = \Kernel::getIntent()->getEntityManager();
            /** @var User $user */
            $user = $em->getRepository(User::class)->findOneBy(array('email' => $session->get(self::$settings['login']['session_email'])));
            // TODO: Replace function with something like validateLogin()
            if (!is_null($user)) {
                if (self::passwordMatches($user, $session->get(self::$settings['login']['session_password']))) {
                    define('LOGGED_IN', true);
                    define('USER_ID', $user->getId());
                    define('USER_NAME', $user->getEmail());
                    define('USER_HASH', $user->getPassword());

                    $user
                        ->setLastOnlineAt(new \DateTime())
                        ->setLastIp($request->getClientIp());
                    $em->flush();

                    // TODO: Maybe update cookie every time a page is requested?
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            define('LOGGED_IN', false);
            define('USER_ID', -1);
            define('USER_NAME', 'Guest');
            define('USER_HASH', null);
        }
        define('ACCOUNT_SESSION_UPDATED', true);
        return true;
    }

    /**
     * @return \App\Account\Entity\OAuthScope[]
     */
    public static function getAllScopes()
    {
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var \App\Account\Entity\OAuthScope[] $scopes */
        $scopes = $em->getRepository(OAuthScope::class)->findAll();
        return $scopes;
    }

    /**
     * @param integer $user_id
     *
     * @return \App\Account\Entity\OAuthClient
     */
    public static function getDeveloperApps($user_id)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var \App\Account\Entity\OAuthClient $clients */
        $clients = $em->getRepository(OAuthClient::class)->findBy(array('user_id' => $user_id));

        return $clients;
    }

    /**
     * @param integer $clientId
     *
     * @return \App\Account\Entity\OAuthClient
     */
    public static function getAppInformation($clientId)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var \App\Account\Entity\OAuthClient $client */
        $client = $em->getRepository(OAuthClient::class)->findOneBy(array('client_identifier' => $clientId));
        return $client;
    }

    /**
     * @param string $clientName
     * @param string $clientSecret
     * @param string $redirectUri
     * @param array  $scopes
     * @param int    $userId
     *
     * @return string
     */
    public static function addApp($clientName, $clientSecret, $redirectUri, $scopes, $userId)
    {
        /** @var \App\Account\Entity\User $user */
        $user = \Kernel::getIntent()->getEntityManager()->find(User::class, $userId);
        $addClient = new OAuthClient();
        $addClient
            ->setClientIdentifier($clientName)
            ->setClientSecret($clientSecret)
            ->setRedirectUri($redirectUri)
            ->setScopes($scopes)
            ->setUsers($user->getId());

        \Kernel::getIntent()->getEntityManager()->persist($addClient);
        \Kernel::getIntent()->getEntityManager()->flush();

        return $addClient->getId();
    }

    public static function addDefaultSubscriptionTypes()
    {
        $basicSubscription = new SubscriptionType();
        $basicSubscription
            ->setTitle('Basic')
            ->setPrice('0')
            ->setPermissions(array());

        $premiumSubscription = new SubscriptionType();
        $premiumSubscription
            ->setTitle('Premium')
            ->setPrice('10')
            ->setPermissions(array('web_service', 'support'));

        $enterpriseSubscription = new SubscriptionType();
        $enterpriseSubscription
            ->setTitle('Enterprise')
            ->setPrice('30')
            ->setPermissions(array('web_service', 'web_service_multiple', 'support'));

        \Kernel::getIntent()->getEntityManager()->persist($basicSubscription);
        \Kernel::getIntent()->getEntityManager()->persist($premiumSubscription);
        \Kernel::getIntent()->getEntityManager()->persist($enterpriseSubscription);
        \Kernel::getIntent()->getEntityManager()->flush();
    }

    public static function addDefaultScopes()
    {
        $scope1 = new OAuthScope();
        $scope1
            ->setScope('user_info')
            ->setName('User info\'s')
            ->setDefault(true);

        \Kernel::getIntent()->getEntityManager()->persist($scope1);
        \Kernel::getIntent()->getEntityManager()->flush();
    }
}
