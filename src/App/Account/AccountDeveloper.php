<?php

namespace App\Account;

use Container\DatabaseContainer;
use Exception;
use PDO;

class AccountDeveloper
{
    /**
     * @param int $user_id
     *
     * @return array|bool
     * @throws \Exception
     */
    public static function getApps($user_id)
    {
        $database = DatabaseContainer::getDatabase();

        $oGetApps = $database->prepare('SELECT * FROM `oauth_clients` WHERE `user_id`=:user_id');
        $oGetApps->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $oGetApps->execute();
        if ($oGetApps->rowCount() > 0) {
            $aAppList = $oGetApps->fetchAll(PDO::FETCH_ASSOC);

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
        $database = DatabaseContainer::getDatabase();

        $getScopes = $database->prepare('SELECT * FROM `oauth_scopes`');
        $getScopes->execute();

        $scopesList = $getScopes->fetchAll(PDO::FETCH_ASSOC);

        return $scopesList;
    }

    /**
     * @param int $clientId
     *
     * @return bool
     */
    public static function getClientInformation($clientId)
    {
        $database = DatabaseContainer::getDatabase();

        $getClient = $database->prepare('SELECT * FROM `oauth_clients` WHERE `client_id`=:client_id LIMIT 1');
        $getClient->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $getClient->execute();

        if ($getClient->rowCount() > 0) {
            $clientInfo = $getClient->fetchAll(PDO::FETCH_ASSOC);

            return $clientInfo[0];
        }

        return false;
    }

    /**
     * @param int    $clientId
     * @param string $clientName
     * @param string $clientSecret
     * @param string $redirectUri
     * @param array  $scopes
     * @param int    $userId
     *
     * @return bool
     */
    public static function addApp($clientId, $clientName, $clientSecret, $redirectUri, $scopes = array(), $userId)
    {
        $database = DatabaseContainer::getDatabase();

        $getClient = $database->prepare('INSERT INTO `oauth_clients`(`client_id`, `client_name`, `client_secret`, `redirect_uri`, `scope`, `user_id`) VALUES (:client_id, :client_name, :client_secret, :redirect_uri, :scopes, :user_id)');
        $getClient->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $getClient->bindValue(':client_name', $clientName, PDO::PARAM_STR);
        $getClient->bindValue(':client_secret', $clientSecret, PDO::PARAM_STR);
        $getClient->bindValue(':redirect_uri', $redirectUri, PDO::PARAM_STR);
        $getClient->bindValue(':scopes', implode(' ', $scopes), PDO::PARAM_STR);
        $getClient->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $getClient->execute();

        if ($getClient->rowCount() > 0) {
            $clientInfo = $getClient->fetchAll(PDO::FETCH_ASSOC);

            return $clientInfo[0];
        }

        return false;
    }

    /*************************************************************************************************/

    private $appId;
    private $notFound = false;
    public  $appData;

    /**
     * AccountDeveloper constructor.
     *
     * @param int $app_id
     *
     * @throws \Exception
     */
    public function __construct($app_id)
    {
        $this->appId = (int)$app_id;
        $this->sync();
    }

    public static function intent($app_id)
    {
        $class = new self($app_id);

        return $class;
    }

    public function sync()
    {
        $database = DatabaseContainer::getDatabase();

        $dbSync = $database->prepare('SELECT * FROM `oauth_clients` WHERE `client_id`=:client_id LIMIT 1');
        $dbSync->bindValue(':client_id', $this->appId, PDO::PARAM_INT);
        $sqlSuccess = $dbSync->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($dbSync->rowCount() > 0) {
                $data          = $dbSync->fetchAll(PDO::FETCH_ASSOC);
                $this->appData = $data[0];
            } else {
                $this->appData  = null;
                $this->notFound = true;
            }
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return !$this->notFound;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getVar($key)
    {
        if (!$this->exists()) {
            return null;
        }

        return $this->appData[$key];
    }

    /**
     * @param string $name
     *
     * @return $this|null
     */
    public function setName($name)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `oauth_clients` SET `client_name`=:value WHERE `client_id`=:client_id');
        $update->bindValue(':client_id', $this->appId, PDO::PARAM_INT);
        $update->bindValue(':value', $name, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param string $uri
     *
     * @return $this|null
     */
    public function setRedirectUri($uri)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `oauth_clients` SET `redirect_uri`=:value WHERE `client_id`=:client_id');
        $update->bindValue(':client_id', $this->appId, PDO::PARAM_INT);
        $update->bindValue(':value', $uri, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param array $scopes
     *
     * @return $this|null
     */
    public function setScopes($scopes)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `oauth_clients` SET `scope`=:value WHERE `client_id`=:client_id');
        $update->bindValue(':client_id', $this->appId, PDO::PARAM_INT);
        // TODO: Before binding, check whether the scopes really exist
        $update->bindValue(':value', implode(' ', $scopes), PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }
}