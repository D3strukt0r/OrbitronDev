<?php

namespace App\Account;

use Container\DatabaseContainer;
use Exception;
use PDO;

class AccountAdmin
{
    /**
     * @param $username
     * @param $hashed_password
     * @param $email
     *
     * @return float
     * @throws \Exception
     */
    public static function addUser($username, $hashed_password, $email)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $sUsername = (string)$username;
        $sHashedPassword = (string)$hashed_password;
        $sEmail = (string)$email;
        $request = \Kernel::$kernel->getRequest();

        // Insert to table "users"
        $oAddUser = $database->prepare('INSERT INTO `users`(`username`,`password`,`email`,`created`,`last_online`,`last_ip`,`registration_ip`) VALUES (:username,:password,:email,:created,:lastOnline,:lastIp,:registrationIp)');
        $oAddUser->execute(array(
            ':username'       => $sUsername,
            ':password'       => $sHashedPassword,
            ':email'          => $sEmail,
            ':created'        => time(),
            ':lastOnline'     => time(),
            ':lastIp'         => $request->server->get('REMOTE_ADDR'),
            ':registrationIp' => $request->server->get('REMOTE_ADDR'),
        ));

        // Get "user_id"
        $oGetUserId = $database->prepare('SELECT `user_id` FROM `users` WHERE `username`=:username LIMIT 1');
        $oGetUserId->execute(array(
            ':username' => $sUsername,
        ));
        $oUserData = $oGetUserId->fetchAll(PDO::FETCH_ASSOC);
        $fUserId = (float)$oUserData[0]['user_id'];

        // Insert to "user_profiles"
        $oAddToProfile = $database->prepare('INSERT INTO `user_profiles`(`user_id`) VALUES (:user_id)');
        $oAddToProfile->execute(array(
            ':user_id' => $fUserId,
        ));

        // Insert to "user_subscriptions"
        $oAddToSubscription = $database->prepare('INSERT INTO `user_subscriptions`(`user_id`,`subscription_id`,`timestamp_activated`,`timestamp_expire`) VALUES (:user_id,:sub_id,:activated,:expire)');
        $oAddToSubscription->execute(array(
            ':user_id'   => $fUserId,
            ':sub_id'    => 1,
            ':activated' => time(),
            ':expire'    => -1,
        ));

        // Return User ID
        return $fUserId;
    }

    /**
     * @param $id
     *
     * @throws \Exception
     */
    public static function removeUser($id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $user_id = (float)$id;

        // D<gelete all data
        $sql = $database->prepare('DELETE FROM `users` WHERE `user_id`=:id LIMIT 1');
        $sql->execute(array(':id' => $user_id));
        $sql = $database->prepare('DELETE FROM `user_profiles` WHERE `user_id`=:id LIMIT 1');
        $sql->execute(array(':id' => $user_id));
        $sql = $database->prepare('DELETE FROM `user_subscriptions` WHERE `user_id`=:id LIMIT 1');
        $sql->execute(array(':id' => $user_id));
    }

    /**
     * @param $id
     * @param $var
     *
     * @return null
     * @throws \Exception
     */
    public static function getUserVar($id, $var)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }

        $sql = $database->prepare('SELECT * FROM `users` WHERE `user_id`=:id LIMIT 1');
        $sql->execute(array(
            ':id' => $id,
        ));
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);
        $value = (isset($data[0][$var]) ? $data[0][$var] : null);
        return $value;
    }

    /**
     * @param $id
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public static function setUserVar($id, $key, $value)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }

        $sql = $database->prepare('SHOW COLUMNS FROM `users` LIKE \'' . $key . '\'');
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $data = $database->prepare('UPDATE `users` SET `' . $key . '`=:value WHERE `user_id`=:id');
            $data->execute(array(
                ':id'    => $id,
                ':value' => $value,
            ));
        } else {
            throw new \RuntimeException('The database "<b>' . $key . '</b>" does not exist.');
        }
    }
}
