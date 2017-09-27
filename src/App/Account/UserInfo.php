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
     * UserInfo constructor.
     *
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

    /**
     * @param string $username
     */
    public function updateUsername($username)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `users` SET `username`=:value WHERE `user_id`=:id');
        $update->bindValue(':id', $this->getFromUser('user_id'), PDO::PARAM_INT);
        $update->bindValue(':value', $username, PDO::PARAM_STR);
        $update->execute();
    }

    /**
     * @param string $password
     */
    public function updatePassword($password)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `users` SET `password`=:value WHERE `user_id`=:id');
        $update->bindValue(':id', $this->getFromUser('user_id'), PDO::PARAM_INT);
        $update->bindValue(':value', $password, PDO::PARAM_STR);
        $update->execute();
    }

    /**
     * @param string $email
     */
    public function updateEmail($email)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `users` SET `email`=:value WHERE `user_id`=:id');
        $update->bindValue(':id', $this->getFromUser('user_id'), PDO::PARAM_INT);
        $update->bindValue(':value', $email, PDO::PARAM_STR);
        $update->execute();

        $update = $database->prepare('UPDATE `users` SET `email_verified`=:value WHERE `user_id`=:id');
        $update->bindValue(':id', $this->getFromUser('user_id'), PDO::PARAM_INT);
        $update->bindValue(':value', '0', PDO::PARAM_STR);
        $update->execute();
    }

    /**
     * @param bool $state
     */
    public function updateEmailVerification($state)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `users` SET `email_verified`=:value WHERE `user_id`=:id');
        $update->bindValue(':id', $this->getFromUser('user_id'), PDO::PARAM_INT);
        $update->bindValue(':value', ($state ? '1' : '0'), PDO::PARAM_STR);
        $update->execute();
    }

    /**
     * @param string $pictureName
     */
    public function updateProfilePicture($pictureName)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `profile_picture`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $pictureName,
        ));
    }

    /**
     * @param string $name
     */
    public function updateFirstName($name)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `firstname`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $name,
        ));
    }

    /**
     * @param string $name
     */
    public function updateLastName($name)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `lastname`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $name,
        ));
    }

    /**
     * @param int $gender
     */
    public function updateGender($gender)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `gender`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $gender,
        ));
    }

    /**
     * @param int $birthday
     */
    public function updateBirthday($birthday)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `birthday`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $birthday,
        ));
    }

    /**
     * @param string $website
     */
    public function updateWebsite($website)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `website`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $website,
        ));
    }

    /**
     * @param int $usage
     */
    public function updateUsage($usage)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `usages`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $usage,
        ));
    }

    /**
     * @param string $street
     */
    public function updateStreet($street)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `location_street`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $street,
        ));
    }

    /**
     * @param string $streetnumber
     */
    public function updateStreetNumber($streetnumber)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `location_street_number`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $streetnumber,
        ));
    }

    /**
     * @param int $postalcode
     */
    public function updatePostalCode($postalcode)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `location_zip`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $postalcode,
        ));
    }

    /**
     * @param string $city
     */
    public function updateCity($city)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `location_city`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $city,
        ));
    }

    /**
     * @param string $country
     */
    public function updateCountry($country)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `user_profiles` SET `location_country`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $country,
        ));
    }

    /**
     * @param bool $state
     */
    public function updateUserDeveloper($state)
    {
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `users` SET `developer`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $state ? 1 : 0,
        ));
    }


    /**************************************************************************/

    /**
     * @return float
     */
    public function getCredits()
    {
        return (float)$this->getFromUser('credits');
    }

    /**
     * @param $newAmount
     *
     * @return float
     */
    public function setCredits($newAmount)
    {
        $database = DatabaseContainer::$database;

        $update = $database->prepare('UPDATE `users` SET `credits`=:value WHERE `user_id`=:id');
        $update->execute(array(
            ':id'    => $this->getFromUser('user_id'),
            ':value' => $newAmount,
        ));
        return $this->getCredits();
    }

    /**
     * @param $amount
     *
     * @return float
     */
    public function giveCredits($amount)
    {
        return $this->setCredits($this->getCredits() + $amount);
    }

    /**
     * @param $amount
     *
     * @return float
     */
    public function takeCredits($amount)
    {
        return $this->setCredits($this->getCredits() - $amount);
    }

    /**************************************************************************/

    /* TODO: Unnecessary
    public function serviceEnabled()
    {
        $iServiceEnabled = (int)\Account\AccountAdmin::getUserVar($this->user_id, 'serviceEnabled');
        if($iServiceEnabled === 1) {
            return true;
        }
        return false;
    }*/

    /**
     * @return bool
     */
    public function serviceAllowed()
    {
        if ($this->isPremium() || $this->isEnterprise()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isNormal()
    {
        if ((int)$this->getFromSubscription('subscription_id') == 1) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isPremium()
    {
        if ((int)$this->getFromSubscription('subscription_id') == 2) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEnterprise()
    {
        if ((int)$this->getFromSubscription('subscription_id') == 3) {
            return true;
        }
        return false;
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
