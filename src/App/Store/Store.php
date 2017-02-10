<?php

namespace App\Store;

use App\Core\DatabaseConnection;
use Container\DatabaseContainer;

class Store
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getStoreList()
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetStoreList = $database->prepare('SELECT `url` FROM `stores`');
        if (!$oGetStoreList->execute()) {
            throw new \Exception('Cannot get list with all stores');
        } else {
            $aStores = array();
            $aStoreData = $oGetStoreList->fetchAll();
            foreach ($aStoreData as $iListId => $aStoreData) {
                array_push($aStores, $aStoreData['url']);
            }
            return $aStores;
        }
    }

    /**
     * @param $user_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getOwnerStoreList($user_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $fUserId = (float)$user_id;

        $oGetStoreList = $database->prepare('SELECT * FROM `stores` WHERE `owner_id`=:user_id');
        if (!$oGetStoreList->execute(array(':user_id' => $fUserId))) {
            throw new \Exception('Cannot get list with all stores you own');
        } else {
            $aStores = array();
            $aStoreData = $oGetStoreList->fetchAll();
            foreach ($aStoreData as $aStoreData) {
                array_push($aStores, $aStoreData);
            }
            return $aStores;
        }
    }

    /**
     * @param $store_url
     *
     * @return mixed
     * @throws \Exception
     */
    public static function url2Id($store_url)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetStoreId = $database->prepare('SELECT `id` FROM `stores` WHERE `url`=:store_url LIMIT 1');
        $bGetStoreIdQuerySuccessful = $oGetStoreId->execute(array(
            ':store_url' => $store_url,
        ));
        if (!$bGetStoreIdQuerySuccessful) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $aStoreData = $oGetStoreId->fetchAll();
            $store_id = $aStoreData[0]['id'];
            return $store_id;
        }
    }

    /**
     * @param $store_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function storeExists($store_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oStoreExists = $database->prepare('SELECT NULL FROM `stores` WHERE `id`=:store_id LIMIT 1');
        $bStoreExistsQuerySuccessful = $oStoreExists->execute(array(
            ':store_id' => $store_id,
        ));
        if (!$bStoreExistsQuerySuccessful) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($oStoreExists->rowCount() > 0) {
                return true;
            }
            return false;
        }
    }

    /******************************************************************************/

    private $iStoreId;
    private $aStoreData;

    /**
     * Store constructor.
     *
     * @param $store_id
     *
     * @throws \Exception
     */
    public function __construct($store_id)
    {
        $this->iStoreId = $store_id;

        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetStoreData = $database->prepare('SELECT * FROM `stores` WHERE `id`=:store_id LIMIT 1');
        $bGetStoreDataSuccessful = $oGetStoreData->execute(array(
            ':store_id' => $this->iStoreId,
        ));
        if (!$bGetStoreDataSuccessful) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $aStoreData = $oGetStoreData->fetchAll();
            $this->aStoreData = $aStoreData[0];
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->aStoreData[$key];
        return $value;
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public function setVar($key, $value)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oUpdateTable = $database->prepare('UPDATE `stores` SET :key=:value WHERE `id`=:store_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'      => $key,
            ':value'    => $value,
            ':store_id' => $this->iStoreId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new \RuntimeException('Could not execute sql');
        }
    }
}