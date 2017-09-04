<?php

namespace App\Store;

use Container\DatabaseContainer;
use PDO;
use RuntimeException;

class StoreProduct
{
    /**
     * @param int $store_id
     * @param int $category
     *
     * @return array
     * @throws \Exception
     */
    public static function getProductList($store_id, $category = 0)
    {
        $database = DatabaseContainer::getDatabase();

        $oGetProductList = $database->prepare('SELECT * FROM `store_products` WHERE `store_id`=:store_id ORDER BY `updated` DESC');
        $oGetProductListSuccessful = $oGetProductList->execute(array(
            ':store_id' => $store_id,
        ));
        if (!$oGetProductListSuccessful) {
            throw new RuntimeException('Could not execute sql');
        } else {
            if (@$oGetProductList->rowCount() == 0) {
                return array();
            } else {
                $product_list = $oGetProductList->fetchAll(PDO::FETCH_ASSOC);
                return $product_list;
            }
        }
    }

    /******************************************************************************/

    private $productId;
    public  $productData;

    /**
     * StoreProduct constructor.
     *
     * @param int $product_id
     *
     * @throws \Exception
     */
    public function __construct($product_id)
    {
        $this->productId = $product_id;

        $database = DatabaseContainer::getDatabase();

        $getProductData = $database->prepare('SELECT * FROM `store_products` WHERE `id`=:product_id LIMIT 1');
        $getProductData->bindValue(':product_id', $this->productId, PDO::PARAM_INT);
        $sqlSuccess = $getProductData->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            if ($getProductData->rowCount() > 0) {
                $data               = $getProductData->fetchAll(PDO::FETCH_ASSOC);
                $this->productData = $data[0];
            } else {
                $this->productData = null;
            }
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->productData[$key];

        return $value;
    }

    /**
     * TODO: Replace this function with a specific one for every column
     *
     * @param string $key
     * @param string $value
     *
     * @throws \Exception
     *
     * @deprecated This function shouldn't be used anymore
     */
    public function setVar($key, $value)
    {
        $database = DatabaseContainer::getDatabase();

        $oUpdateTable = $database->prepare('UPDATE `store_products` SET :key=:value WHERE `id`=:product_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'        => $key,
            ':value'      => $value,
            ':product_id' => $this->productId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new RuntimeException('Could not execute sql');
        }
    }
}