<?php

namespace App\Account;

use Container\DatabaseContainer;
use Exception;

class AccountDeveloper
{
    /**
     * @param $user_id
     *
     * @return array|bool
     * @throws \Exception
     */
    public static function getApps($user_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }
        $iUserId = (int)$user_id;

        $oGetApps = $database->prepare('SELECT * FROM `oauth_clients` WHERE `user_id`=:user_id');
        $oGetApps->execute(array(
            ':user_id' => $iUserId,
        ));
        if ($oGetApps->rowCount() > 0) {
            $aAppList = $oGetApps->fetchAll(\PDO::FETCH_ASSOC);
            return $aAppList;
        }
        return false;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getAllScopes()
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }

        $getScopes = $database->prepare('SELECT * FROM `oauth_scopes`');
        $getScopes->execute();

        $scopesList = $getScopes->fetchAll(\PDO::FETCH_ASSOC);
        return $scopesList;
    }

    public static function getClientInformation($clientId)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }

        $getClient = $database->prepare('SELECT * FROM `oauth_clients` WHERE `client_id`=:client_id LIMIT 1');
        $getClient->execute(array(
            ':client_id' => $clientId,
        ));
        if ($getClient->rowCount() > 0) {
            $clientInfo = $getClient->fetchAll(\PDO::FETCH_ASSOC);
            return $clientInfo[0];
        }
        return false;
    }

    public static function addApp($clientId, $clientSecret, $redirectUri, $scopes = array(), $userId)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new Exception('A database connection is required');
        }

        $getClient = $database->prepare('INSERT INTO `oauth_clients`(`client_id`, `client_name`, `client_secret`, `redirect_uri`, `scope`, `user_id`) VALUES (:client_id, :client_name, :client_secret, :redirect_uri, :scopes, :user_id)');
        $getClient->execute(array(
            ':client_id'     => $clientId,
            ':client_name'   => $clientId,
            ':client_secret' => $clientSecret,
            ':redirect_uri'  => $redirectUri,
            ':scopes'        => implode(' ', $scopes),
            ':user_id'       => $userId,
        ));
        if ($getClient->rowCount() > 0) {
            $clientInfo = $getClient->fetchAll(\PDO::FETCH_ASSOC);
            return $clientInfo[0];
        }
        return false;
    }
}