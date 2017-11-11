<?php

namespace App\Account;

use App\Account\Entity\OAuthClient;
use App\Account\Entity\User;
use Container\DatabaseContainer;
use Exception;
use PDO;
use RuntimeException;

class AccountDeveloper
{
    /**
     * @param int $user_id
     *
     * @return \App\Account\Entity\OAuthClient
     */
    public static function getApps($user_id)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var \App\Account\Entity\OAuthClient $clients */
        $clients = $em->getRepository(OAuthClient::class)->findBy(array('user_id' => $user_id));

        return $clients;
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
     * @return \App\Account\Entity\OAuthClient
     */
    public static function getClientInformation($clientId)
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
     * @return bool
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

    /*************************************************************************************************/

    private $appId;
    private $notFound = false;
    public $appData;

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
            throw new RuntimeException('Could not execute sql');
        } else {
            if ($dbSync->rowCount() > 0) {
                $data = $dbSync->fetchAll(PDO::FETCH_ASSOC);
                $this->appData = $data[0];
            } else {
                $this->appData = null;
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
            throw new RuntimeException('Could not execute sql');
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
            throw new RuntimeException('Could not execute sql');
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
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }
}
