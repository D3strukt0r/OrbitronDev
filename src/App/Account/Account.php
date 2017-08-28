<?php

namespace App\Account;

use Container\DatabaseContainer;
use Exception;
use Kernel;
use PDO;

class Account
{
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
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $confirm_password
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function register($username, $email, $password, $confirm_password)
    {
        // Check username
        if (strlen($username) == 0) {
            return 'username:insert_username';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            return 'username:username_short_long';
        } elseif (AccountTools::isNameTaken($username)) {
            return 'username:user_exists';
        } elseif (AccountTools::isNameBlocked($username)) {
            return 'username:blocked_name';
        } elseif (!AccountTools::isValidName($username)) {
            return 'username:not_valid_name'; // This username is not valid. Just use a-z, A-Z, 1-9 and _
        } // Check E-Mail
        elseif (strlen($email) == 0) {
            return 'email:insert_email';
        } elseif (!AccountTools::isValidEmail($email)) {
            return 'email:email_not_valid';
        } // Check password
        elseif (strlen($password) == 0) {
            return 'password:insert_password';
        } elseif (strlen($password) < 8) {
            return 'password:password_too_short';
        } elseif ($password != $confirm_password) {
            return 'password_verify:passwords_dont_match';
        }

        /**************************************************************************************************/

        $hashed_password = AccountTools::hash($password);
        $user_id = AccountAdmin::addUser($username, $hashed_password, $email);
        return $user_id;
    }

    /**
     * @param string $usernameOrEmail
     * @param string $password
     * @param bool   $cookies
     *
     * @return bool|string
     */
    public static function login($usernameOrEmail, $password, $cookies = false)
    {
        if (strlen($usernameOrEmail) === 0) {
            return 'email:insert_username';
        } elseif (strlen($password) === 0) {
            return 'password:insert_password';
        } elseif (!AccountTools::userExist($usernameOrEmail)) {
            return 'email:user_dont_exists';
        }

        $check = self::validateLogin($usernameOrEmail, $password);

        if ($check[0]) {
            if (!$check[1]) {
                $_SESSION['USER_EM'] = AccountAdmin::getUserVar(AccountTools::name2Id($usernameOrEmail), 'email');
            } else {
                $_SESSION['USER_EM'] = AccountAdmin::getUserVar(AccountTools::email2Id($usernameOrEmail), 'email');
            }

            $_SESSION['USER_PW'] = $password;

            if ($cookies) {
                self::changeSession(null, null, true);
            }
            return true;
        } else {
            return 'password:wrong_password';
        }
    }

    /**
     *
     */
    public static function logout()
    {
        unset($_COOKIE['account']);
        setcookie('account', '', strtotime('-1 month'), '/', 'orbitrondev.org');
        unset($_SESSION['USER_EM'], $_SESSION['USER_PW']);
    }

    /**************************************************************************************************/

    /**
     * @param $username
     * @param $password
     *
     * @return int
     * @throws \Exception
     */
    public static function validateUser($username, $password)
    {
        $database = DatabaseContainer::getDatabase();

        $validate = $database->prepare('SELECT `password` FROM `users` WHERE `username`=:username LIMIT 1');
        $validate->execute(array(
            ':username' => $username,
        ));
        $rows = $validate->rowCount();
        $data = $validate->fetchAll(PDO::FETCH_ASSOC);
        if ($rows > 0 && password_verify($password, $data[0]['password'])) {
            return $rows;
        } else {
            return 0;
        }
    }

    /**
     * @param $email
     * @param $password
     *
     * @return int
     * @throws \Exception
     */
    public static function validateUserByEmail($email, $password)
    {
        $database = DatabaseContainer::getDatabase();

        $validate = $database->prepare('SELECT `password` FROM `users` WHERE `email`=:email');
        $validate->execute(array(
            ':email' => $email,
        ));
        $data = $validate->fetchAll(PDO::FETCH_ASSOC);
        $rows = $validate->rowCount();
        if ($rows > 0 && password_verify($password, $data[0]['password'])) {
        } else {
            $rows = 0;
        }
        return $rows;
    }

    /**
     * @param $user_mail
     * @param $password
     *
     * @return array
     */
    public static function validateLogin($user_mail, $password)
    {
        if ($user = self::validateUser($user_mail, $password)) {
            return array(1, 0, 1);
        } elseif ($emails = self::validateUserByEmail($user_mail, $password)) {
            return array(1, 1, $emails);
        } else {
            return array(0, null, null);
        }
    }

    /**************************************************************************************************/

    /**
     * @param null $email
     * @param string|null $password
     * @param bool|null $remember
     */
    public static function changeSession($email = null, $password = null, $remember = null)
    {
        $accountData = array(
            'remember' => false,
            'email'    => $_SESSION['USER_EM'],
            'password' => $_SESSION['USER_PW'],
        );

        if (!is_null($email)) {
            $accountData['email'] = $email;
        }
        if (!is_null($password)) {
            $accountData['password'] = $password;
        }
        if (!is_null($remember) && $remember) {
            $accountData['remember'] = true;
        }

        $_SESSION['USER_EM'] = $accountData['email'];
        $_SESSION['USER_PW'] = $accountData['password'];
        if ($accountData['remember']) {
            setcookie('account', base64_encode(json_encode($accountData)), strtotime('+1 month'), '/', 'orbitrondev.org');
        }
    }

    /**
     *
     */
    public static function updateSession()
    {
        if (isset($_COOKIE['account'])) {
            $remember_data = json_decode(base64_decode($_COOKIE['account']), true);
        }
        if ((isset($_SESSION['USER_EM']) && isset($_SESSION['USER_PW'])) || (isset($remember_data) && $remember_data['remember'])) {
            if (isset($remember_data) && $remember_data['remember']) {
                $_SESSION['USER_EM'] = $remember_data['email'];
                $_SESSION['USER_PW'] = $remember_data['password'];
            }
            $email = $_SESSION['USER_EM'];
            $password = $_SESSION['USER_PW'];

            if (self::validateLogin($email, $password)) {
                define('LOGGED_IN', true);
                define('USER_ID', AccountTools::email2Id($email));
                define('USER_NAME', $email);
                define('USER_HASH', $password);

                if (isset($remember_data) && $remember_data['remember']) {
                    $plus_one_month = strtotime('+1 month');
                    $account_data = array(
                        'remember' => true,
                        'email'    => $_SESSION['USER_EM'],
                        'password' => $_SESSION['USER_PW'],
                    );
                    $remember = base64_encode(json_encode($account_data));
                    setcookie('account', $remember, $plus_one_month, '/', 'orbitrondev.org');
                }
            } else {
                unset($_SESSION['USER_EM'], $_SESSION['USER_PW']);
                self::logout();
            }
        } else {
            define('LOGGED_IN', false);
            define('USER_ID', -1);
            define('USER_NAME', 'Guest');
            define('USER_HASH', null);
            define('USER_SUBSCRIPTION', null);
        }
        define('ACCOUNT_SESSION_UPDATED', true);
    }
}
