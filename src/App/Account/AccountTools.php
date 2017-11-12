<?php

namespace App\Account;

use Container\DatabaseContainer;
use Container\TranslatingContainer;
use Kernel;
use PDO;
use RuntimeException;

class AccountTools
{
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
        $sqlSuccessful = $oIdExists->execute();
        if (!$sqlSuccessful) {
            throw new RuntimeException('[Database]: Cannot execute sql ('.$oIdExists->queryString.')');
        }

        return ($oIdExists->rowCount() ? true : false);
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
            return '<s>'.$translator->trans('Unknown user').'</s>';
        }
        $oUser = new UserInfo($user_id);

        $sPrefix = '';
        $username = $oUser->getFromUser('username');
        $sSuffix = '';

        if ($link) {
            $user = new UserInfo($user_id);
            $sPrefix .= '<a href="'.Kernel::getIntent()->get('router')->generate('app_account_user', array('username' => $user->getFromUser('username'))).'">';
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

        return stripslashes(trim($sPrefix.$username.$sSuffix));
    }
}
