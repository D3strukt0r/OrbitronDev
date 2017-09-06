<?php

namespace App\Store;

use Container\DatabaseContainer;
use PDO;

class Store
{
    /**
     * Get a list of all existing stores
     *
     * @return array
     * @throws \Exception
     */
    public static function getStoreList()
    {
        $database = DatabaseContainer::getDatabase();

        $getAllStores = $database->prepare('SELECT `name`,`url`,`owner_id` FROM `stores`');
        $sqlSuccess   = $getAllStores->execute();

        if (!$sqlSuccess) {
            throw new \Exception('Cannot get list with all stores');
        } else {
            return $getAllStores->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Get all stores which belong to the given User
     *
     * @param int $user_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getOwnerStoreList($user_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getAllStores = $database->prepare('SELECT * FROM `stores` WHERE `owner_id`=:user_id');
        $getAllStores->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $sqlSuccess = $getAllStores->execute();

        if (!$sqlSuccess) {
            throw new \Exception('Cannot get list with all stores you own');
        } else {
            $storeList     = array();
            $storeDataList = $getAllStores->fetchAll(PDO::FETCH_ASSOC);
            foreach ($storeDataList as $currentStoreData) {
                array_push($storeList, $currentStoreData);
            }

            return $storeList;
        }
    }

    /**
     * Checks whether the given url exists, in other words, if the store exists
     *
     * @param string $url
     *
     * @return bool
     * @throws \Exception
     */
    public static function urlExists($url)
    {
        $database = DatabaseContainer::getDatabase();

        $getUrl = $database->prepare('SELECT NULL FROM `stores` WHERE `url`=:url');
        $getUrl->bindValue(':url', $url, PDO::PARAM_STR);
        $getUrl->execute();

        if ($getUrl->rowCount()) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether the given store exists
     *
     * @param int $store_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function storeExists($store_id)
    {
        $database = DatabaseContainer::getDatabase();

        $storeExists = $database->prepare('SELECT NULL FROM `stores` WHERE `id`=:store_id LIMIT 1');
        $storeExists->bindValue(':store_id', $store_id, PDO::PARAM_INT);
        $sqlSuccess = $storeExists->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($storeExists->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }

    /**
     * Converts the given URL to the existing id of the store.
     * Hint: always use "urlExists()" before using this function
     *
     * @param string $store_url
     *
     * @return mixed
     * @throws \Exception
     */
    public static function url2Id($store_url)
    {
        $database = DatabaseContainer::getDatabase();

        $getStoreId = $database->prepare('SELECT `id` FROM `stores` WHERE `url`=:store_url LIMIT 1');
        $getStoreId->bindValue(':store_url', $store_url, PDO::PARAM_STR);
        $sqlSuccess = $getStoreId->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $storeData = $getStoreId->fetchAll(PDO::FETCH_ASSOC);

            return $storeData[0]['id'];
        }
    }

    /******************************************************************************/

    private $storeId;
    public  $storeData;

    /**
     * Store constructor.
     *
     * @param int $store_id
     *
     * @throws \Exception
     */
    public function __construct($store_id)
    {
        $this->storeId = $store_id;

        $database = DatabaseContainer::getDatabase();

        $getData = $database->prepare('SELECT * FROM `stores` WHERE `id`=:store_id LIMIT 1');
        $getData->bindValue(':store_id', $this->storeId, PDO::PARAM_INT);
        $sqlSuccess = $getData->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($getData->rowCount() > 0) {
                $data            = $getData->fetchAll(PDO::FETCH_ASSOC);
                $this->storeData = $data[0];
            } else {
                $this->storeData = null;
            }
        }
    }

    /**
     * Get information of current store
     *
     * @param $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->storeData[$key];

        return $value;
    }

    /**
     * Set the new store name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        if ($this->storeData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `stores` SET `name`=:value WHERE `id`=:store_id');
        $update->bindValue(':store_id', $this->storeId, PDO::PARAM_INT);
        $update->bindValue(':value', $name, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->storeData['name'] = $name;
        }

        return $this;
    }

    /**
     * Set the new URL
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        if ($this->storeData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `stores` SET `url`=:value WHERE `id`=:store_id');
        $update->bindValue(':store_id', $this->storeId, PDO::PARAM_INT);
        $update->bindValue(':value', $url, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->storeData['url'] = $url;
        }

        return $this;
    }

    /**
     * Set the given User to be the new Owner
     *
     * @param int $owner_id
     *
     * @return $this
     */
    public function setOwnerId($owner_id)
    {
        if ($this->storeData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forums` SET `owner_id`=:value WHERE `id`=:store_id');
        $update->bindValue(':store_id', $this->storeId, PDO::PARAM_INT);
        $update->bindValue(':value', $owner_id, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->storeData['owner_id'] = $owner_id;
        }

        return $this;
    }
}
