<?php

namespace App\Account;

use Container\DatabaseContainer;
use PDO;

class UserInfo
{
    private $userId = null;
    public $aUser = array();
    public $aProfile = array();
    public $aSubscription = array();

    /**
     * @param $fUserId
     *
     * @throws \Exception
     */
    public function __construct($fUserId)
    {
        $database = DatabaseContainer::getDatabase();

        // Save user id
        $this->userId = $fUserId;

        if ($this->userId == -1) {
            $this->aUser['user_id'] = $this->userId;
            return;
        }

        // Save user data
        $oGetUserData = $database->prepare('SELECT * FROM `users` WHERE `user_id`=:user_id LIMIT 1');
        $oGetUserData->bindValue(':user_id', $fUserId, PDO::PARAM_INT);
        $oGetUserData->execute();
        $aUserData = $oGetUserData->fetchAll(PDO::FETCH_ASSOC);
        if (count($aUserData) !== 0) {
            foreach ($aUserData[0] as $key => $value) {
                $this->aUser[$key] = $value;
            }
        }

        // Save user profile
        $oGetProfile = $database->prepare('SELECT * FROM `user_profiles` WHERE `user_id`=:user_id LIMIT 1');
        $oGetProfile->bindValue(':user_id', $fUserId, PDO::PARAM_INT);
        $oGetProfile->execute();
        $aProfileData = $oGetProfile->fetchAll(PDO::FETCH_ASSOC);
        if (count($aProfileData) !== 0) {
            foreach ($aProfileData[0] as $key => $value) {
                $this->aProfile[$key] = $value;
            }
        }

        // Save user subscription
        $oGetSubscription = $database->prepare('SELECT * FROM `user_subscriptions` WHERE `user_id`=:user_id LIMIT 1');
        $oGetSubscription->bindValue(':user_id', $fUserId, PDO::PARAM_INT);
        $oGetSubscription->execute();
        $aSubscriptionData = $oGetSubscription->fetchAll(PDO::FETCH_ASSOC);
        if (count($aSubscriptionData) !== 0) {
            foreach ($aSubscriptionData[0] as $key => $value) {
                $this->aSubscription[$key] = $value;
            }
        }
    }

    /**************************************************************************/

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getFromUser($key)
    {
        return $this->aUser[$key];
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getFromProfile($key)
    {
        return $this->aProfile[$key];
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getFromSubscription($key)
    {
        return $this->aSubscription[$key];
    }

    /**************************************************************************/
    // TODO: Add friendship function

    public static function isOnline($userId)
    {
        global $db;

        $result = $db->Query('SELECT online FROM user_info WHERE id = "' . $userId . '" LIMIT 1');
        $row = $db->FetchAssoc($result);
        return $row['online'];
    }

    public static function friendShipExist($useroneid, $usertwoid)
    {
        global $db;

        $q = $db->Query('SELECT user_two_id FROM uesr_messenger_friendships WHERE user_one_id = "' . $useroneid . '" AND user_two_id = "' . $usertwoid . '"');

        if ($db->NumRows($q) > 0) {
            return true;
        }
        return false;
    }

    public static function getFriendCount($id, $onlineOnly = false)
    {
        global $db;

        $i = 0;
        $q = $db->Query('SELECT user_two_id FROM user_messenger_friendships WHERE user_one_id = "' . $id . '"');

        while ($friend = $db->FetchAssoc($q)) {
            if ($onlineOnly) {
                $isOnline = $db->Result($db->Query('SELECT online FROM user_about WHERE id = "' . $friend['user_two_id'] . '" LIMIT 1'),
                    0);

                if ($isOnline == '1') {
                    $i++;
                }
            } else {
                $i++;
            }
        }
        return $i;
    }
}
