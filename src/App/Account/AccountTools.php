<?php

namespace App\Account;

use Container\DatabaseContainer;
use Container\TranslatingContainer;
use Exception;
use Kernel;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class AccountTools
{
    private static $aBlockedNames = array('admin', 'administrator', 'mod', 'moderator', 'guest', 'undefined');
    private static $aBlockedNameParts = array('mod', 'system', 'admin');

    /**
     * @param $email
     *
     * @return int
     */
    public static function isValidEmail($email)
    {
        $sEmail = (string)$email;
        return preg_match('/^[a-z0-9_\.-]+@([a-z0-9]+([\-]+[a-z0-9]+)*\.)+[a-z]{2,7}$/i', $sEmail);
    }

    /**
     * @param $username
     *
     * @return bool
     */
    public static function isValidName($username)
    {
        $sUsername = (string)$username;
        if (preg_match('/^[a-z0-9_]+$/i', $sUsername) && strlen($sUsername) >= 3 && strlen($sUsername) <= 32) {
            return true;
        }
        return false;
    }

    /**
     * @param $username
     *
     * @return bool
     * @throws \Exception
     */
    public static function isNameTaken($username)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }
        $sUsername = (string)$username;

        $oIsTaken = $database->prepare('SELECT NULL FROM `users` WHERE `username`=:username');
        if (!$oIsTaken->execute(array(':username' => $sUsername))) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oIsTaken->queryString . ')');
        }
        return ($oIsTaken->rowCount() > 0 ? true : false);
    }

    /**
     * @param $user_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function idExists($user_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $fUserId = (float)$user_id;

        $oIdExists = $database->prepare('SELECT NULL FROM `users` WHERE `user_id`=:id LIMIT 1');
        if (!$oIdExists->execute(array(':id' => $fUserId))) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oIdExists->queryString . ')');
        }
        return ($oIdExists->rowCount() ? true : false);
    }

    /**
     * @param $username
     *
     * @return bool
     */
    public static function isNameBlocked($username)
    {
        $sUsername = (string)$username;

        foreach (self::$aBlockedNames as $bl) {
            if (strtolower($sUsername) == strtolower($bl)) {
                return true;
            }
        }

        foreach (self::$aBlockedNameParts as $bl) {
            if (strpos(strtolower($sUsername), strtolower($bl)) != false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $user_email
     *
     * @return bool
     * @throws \Exception
     */
    public static function userExist($user_email)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $sUsernameOrEmail = (string)$user_email;

        $oUserExists = $database->prepare('SELECT NULL FROM `users` WHERE `username`=:username LIMIT 1');
        $oUserExists->execute(array(':username' => $sUsernameOrEmail));
        if ($oUserExists->rowCount()) {
            return true;
        } else {
            $oUserExists = $database->prepare('SELECT NULL FROM `users` WHERE `email`=:email LIMIT 1');
            $oUserExists->execute(array(':email' => $sUsernameOrEmail));
            if ($oUserExists->rowCount()) {
                return true;
            }
            return false;
        }
    }

    /**
     * @param $input
     *
     * @return string
     * @throws Exception
     */
    public static function hash($input)
    {
        return password_hash($input, PASSWORD_DEFAULT);
    }

    /**
     * @param UserInfo $user
     * @param          $password
     *
     * @return string
     * @internal param $input
     *
     */
    public static function passwordMatches($user, $password)
    {
        return password_verify($password, $user->getFromUser('password'));
    }

    /**
     * @param $username
     *
     * @return float
     * @throws \Exception
     */
    public static function name2Id($username)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $sUsername = (string)$username;

        $oGetId = $database->prepare('SELECT `user_id` FROM `users` WHERE `username`=:username LIMIT 1');
        if (!$oGetId->execute(array(':username' => $username))) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oGetId->queryString . ')');
        }
        $aUserData = $oGetId->fetchAll();
        return (float)$aUserData[0]['user_id'];
    }

    /**
     * @param $email
     *
     * @return int
     * @throws \Exception
     */
    public static function email2Id($email)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $sEmail = (string)$email;

        $oGetId = $database->prepare('SELECT `user_id` FROM `users` WHERE `email`=:email LIMIT 1');
        if (!$oGetId->execute(array(':email' => $email))) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oGetId->queryString . ')');
        }
        if ($oGetId->rowCount() == 0) {
            return 0;
        }
        $aUserData = $oGetId->fetchAll();
        return (int)$aUserData[0]['user_id'];
    }

    /**
     * @param      $user_id
     * @param bool $link
     * @param bool $styles
     *
     * @return string
     * @throws \Exception
     */
    public static function formatUsername($user_id, $link = true, $styles = true)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $translator = TranslatingContainer::$translator;

        $fUserId = (float)$user_id;
        $bLink = (bool)$link;
        $bStyles = (bool)$styles;

        if (!self::idExists($fUserId)) {
            return '<s>' . $translator->trans('Unknown user') . '</s>';
        }
        $oUser = new UserInfo($fUserId);

        $sPrefix = '';
        $username = $oUser->getFromUser('username');
        $sSuffix = '';

        if ($link) {
            $user = new UserInfo($fUserId);
            $sPrefix .= '<a href="' . Kernel::$kernel->get('router')->generate('app_account_user',
                    array('username' => $user->getFromUser('username'))) . '">';
            $sSuffix .= '</a>';
        }

        if ($styles) {
            if ($oUser->isPremium()) {
                $sPrefix .= '<span style="color:orange">';
                $sSuffix .= '</span>';
            } elseif ($oUser->isEnterprise()) {
                $sPrefix .= '<span style="color:green">';
                $sSuffix .= '</span>';
            }
        }
        return stripslashes(trim($sPrefix . $username . $sSuffix));
    }
}
