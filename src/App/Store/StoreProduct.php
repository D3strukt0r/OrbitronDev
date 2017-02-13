<?php

namespace App\Store;

use App\Core\DatabaseConnection;

class StoreProduct
{
    /**
     * @param     $store_id
     * @param int $category
     *
     * @return array
     * @throws \Exception
     */
    public static function getProductList($store_id, $category = 0)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetProductList = $database->prepare('SELECT * FROM `store_products` WHERE `store_id`=:store_id ORDER BY `updated` DESC');
        $oGetProductListSuccessful = $oGetProductList->execute(array(
            ':store_id' => $store_id,
        ));
        if (!$oGetProductListSuccessful) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if (@$oGetProductList->rowCount() == 0) {
                return array();
            } else {
                $product_list = $oGetProductList->fetchAll(\PDO::FETCH_ASSOC);
                return $product_list;
            }
        }
    }

    /******************************************************************************/

    private $iProductId;
    private $aProductData;

    /**
     * StoreProduct constructor.
     *
     * @param int $product_id
     *
     * @throws \Exception
     */
    public function __construct($product_id)
    {
        $this->iProductId = $product_id;

        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetProductData = $database->prepare('SELECT * FROM `store_products` WHERE `id`=:product_id LIMIT 1');
        $bGetProductDataSuccessful = $oGetProductData->execute(array(
            ':product_id' => $this->iProductId,
        ));
        if (!$bGetProductDataSuccessful) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $aProductData = $oGetProductData->fetchAll();
            $this->aProductData = $aProductData[0];
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->aProductData[$key];
        return $value;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @throws \Exception
     */
    public function setVar($key, $value)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oUpdateTable = $database->prepare('UPDATE `store_products` SET :key=:value WHERE `id`=:product_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'        => $key,
            ':value'      => $value,
            ':product_id' => $this->iProductId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new \RuntimeException('Could not execute sql');
        }
    }
}