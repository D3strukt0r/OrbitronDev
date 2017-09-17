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

    /**
     * Checks whether the given product exists
     *
     * @param int $product_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function productExists($product_id)
    {
        $database = DatabaseContainer::getDatabase();

        $productExists = $database->prepare('SELECT NULL FROM `store_products` WHERE `id`=:product_id LIMIT 1');
        $productExists->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $sqlSuccess = $productExists->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($productExists->rowCount() > 0) {
                return true;
            }

            return false;
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
     * Set the new Rating count
     *
     * @param int $count
     *
     * @return $this
     */
    public function setRatingCount($count)
    {
        if ($this->productData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `store_products` SET `rating_count`=:value WHERE `id`=:product_id');
        $update->bindValue(':product_id', $this->productId, PDO::PARAM_INT);
        $update->bindValue(':value', $count, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->productData['rating_count'] = $count;
        }

        return $this;
    }

    /**
     * Set the new Rating cache
     *
     * @param int $rating
     *
     * @return $this
     */
    public function setRatingCache($rating)
    {
        if ($this->productData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `store_products` SET `rating_cache`=:value WHERE `id`=:product_id');
        $update->bindValue(':product_id', $this->productId, PDO::PARAM_INT);
        $update->bindValue(':value', $rating, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->productData['rating_cache'] = $rating;
        }

        return $this;
    }

    /**
     * Set the new Rating cache
     *
     * @param int $new_stock
     *
     * @return $this
     */
    public function setStockAvailable($new_stock)
    {
        if ($this->productData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `store_products` SET `stock_available`=:value WHERE `id`=:product_id');
        $update->bindValue(':product_id', $this->productId, PDO::PARAM_INT);
        $update->bindValue(':value', $new_stock, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->productData['stock_available'] = $new_stock;
        }

        return $this;
    }
}
