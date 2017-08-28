<?php

namespace App\Account;

use Container\DatabaseContainer;
use Container\TranslatingContainer;
use Exception;
use Kernel;
use PDO;

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
     * @param string $username
     *
     * @return bool
     */
    public static function isValidName($username)
    {
        if (preg_match('/^[a-z0-9_]+$/i', $username) && strlen($username) >= 3 && strlen($username) <= 32) {
            return true;
        }
        return false;
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws \Exception
     */
    public static function isNameTaken($username)
    {
        $database = DatabaseContainer::getDatabase();

        $oIsTaken = $database->prepare('SELECT NULL FROM `users` WHERE `username`=:username');
        $oIsTaken->bindValue(':username', $username, PDO::PARAM_STR);
        if (!$oIsTaken->execute()) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oIsTaken->queryString . ')');
        }
        return ($oIsTaken->rowCount() > 0 ? true : false);
    }

    /**
     * @param int $user_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function idExists($user_id)
    {
        $database = DatabaseContainer::getDatabase();

        $oIdExists = $database->prepare('SELECT NULL FROM `users` WHERE `user_id`=:id LIMIT 1');
        $oIdExists->bindValue(':id', $user_id, PDO::PARAM_INT);
        if (!$oIdExists->execute()) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oIdExists->queryString . ')');
        }
        return ($oIdExists->rowCount() ? true : false);
    }

    /**
     * @param string $username
     *
     * @return bool
     */
    public static function isNameBlocked($username)
    {
        foreach (self::$aBlockedNames as $bl) {
            if (strtolower($username) == strtolower($bl)) {
                return true;
            }
        }

        foreach (self::$aBlockedNameParts as $bl) {
            if (strpos(strtolower($username), strtolower($bl)) != false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $user_email
     *
     * @return bool
     * @throws \Exception
     */
    public static function userExist($user_email)
    {
        $database = DatabaseContainer::getDatabase();

        $oUserExists = $database->prepare('SELECT NULL FROM `users` WHERE `username`=:username LIMIT 1');
        $oUserExists->bindValue(':username', $user_email, PDO::PARAM_STR);
        $oUserExists->execute();
        if ($oUserExists->rowCount()) {
            return true;
        } else {
            $oUserExists = $database->prepare('SELECT NULL FROM `users` WHERE `email`=:email LIMIT 1');
            $oUserExists->bindValue(':email', $user_email, PDO::PARAM_STR);
            $oUserExists->execute();
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
     * @param string $username
     *
     * @return float
     * @throws \Exception
     */
    public static function name2Id($username)
    {
        $database = DatabaseContainer::getDatabase();

        $oGetId = $database->prepare('SELECT `user_id` FROM `users` WHERE `username`=:username LIMIT 1');
        $oGetId->bindValue(':username', $username, PDO::PARAM_STR);
        if (!$oGetId->execute()) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oGetId->queryString . ')');
        }
        $aUserData = $oGetId->fetchAll(PDO::FETCH_ASSOC);
        return (float)$aUserData[0]['user_id'];
    }

    /**
     * @param string $email
     *
     * @return int
     * @throws \Exception
     */
    public static function email2Id($email)
    {
        $database = DatabaseContainer::getDatabase();

        $oGetId = $database->prepare('SELECT `user_id` FROM `users` WHERE `email`=:email LIMIT 1');
        $oGetId->bindValue(':email', $email, PDO::PARAM_STR);
        if (!$oGetId->execute()) {
            throw new \RuntimeException('[Database]: Cannot execute sql (' . $oGetId->queryString . ')');
        }
        if ($oGetId->rowCount() == 0) {
            return 0;
        }
        $aUserData = $oGetId->fetchAll(PDO::FETCH_ASSOC);
        return (int)$aUserData[0]['user_id'];
    }

    /**
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

        if (!self::idExists($user_id)) {
            return '<s>' . $translator->trans('Unknown user') . '</s>';
        }
        $oUser = new UserInfo($user_id);

        $sPrefix = '';
        $username = $oUser->getFromUser('username');
        $sSuffix = '';

        if ($link) {
            $user = new UserInfo($user_id);
            $sPrefix .= '<a href="' . Kernel::getIntent()->get('router')->generate('app_account_user', array('username' => $user->getFromUser('username'))) . '">';
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
