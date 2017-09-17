<?php

namespace App\Store;

use App\Account\UserInfo;
use Container\DatabaseContainer;

class StoreCheckout
{
    private $cartId = null;
    private $savedIn = null;
    private $products = array();

    /**
     * StoreCheckout constructor.
     *
     * @param float         $cartId
     * @param bool|null     $isRegisteredUser
     * @param UserInfo|null $user
     *
     * @throws \Exception
     */
    public function __construct($cartId, $isRegisteredUser = false, $user = null)
    {
        if ($isRegisteredUser == true) {
            if (is_null($cartId)) {
                // Create new cart
                $this->cartId = $this->createNewCart($user);
            } else {
                // Access existing cart
                $database = DatabaseContainer::getDatabase();

                $sql = $database->prepare('SELECT * FROM `store_carts` WHERE `user_id`=:user_id');
                $sql->execute(array(
                    ':user_id' => $user->getFromUser('user_id'),
                ));

                if ($sql->rowCount() > 0) {
                    $cartData = $sql->fetchAll(\PDO::FETCH_ASSOC);

                    $this->savedIn = 'database';
                    $this->cartId = $cartData[0]['cart_id'];
                    $this->products = json_decode($cartData[0]['products'], true);
                } else {
                    // Cart does not really exist
                    $this->cartId = $this->createNewCart($user);
                }
            }
        } else {
            // If user is not registered
            $this->savedIn = 'cookie';

            if (!isset($_COOKIE['store_cart'])) {
                // Create new cookie
                setcookie('store_cart', json_encode($this->products), time() + 60 * 60 * 24 * 30);
            } else {
                // Get information from existing cookie
                $this->products = json_decode($_COOKIE['store_cart'], true);
            }
        }
    }

    /**
     * Creates a new cart in the database
     *
     * @param UserInfo $user
     *
     * @return string
     * @throws \Exception
     */
    private function createNewCart($user)
    {
        $database = DatabaseContainer::getDatabase();

        $sql = $database->prepare('INSERT INTO `store_carts`(`user_id`, `products`) VALUES (:user_id, :products)');
        $sql->execute(array(
            ':user_id'  => $user->getFromUser('user_id'),
            ':products' => '{}',
        ));

        return $database->lastInsertId();
    }

    /**
     * Add a product to the cart
     *
     * @param Store        $store   Required to get the ID
     * @param StoreProduct $product Required to know which product
     * @param int          $count   Amount to be added
     */
    public function addToCart($store, $product, $count = 1)
    {
        $storeId = $store->getVar('id');
        $productId = (int)$product->getVar('id');

        // How many are already in the cart?
        $alreadyInCart = 0;
        if (isset($this->products[$storeId][$productId])) {
            $alreadyInCart = $this->products[$storeId][$productId]['count'];
        }

        // Add it to the cart
        $this->products[$storeId][$productId]['id'] = $productId;
        $this->products[$storeId][$productId]['count'] = $alreadyInCart + $count;

        $this->syncWithSource();
    }

    /**
     * Update database or cookie
     *
     * @throws \Exception
     */
    private function syncWithSource()
    {
        $formatted = json_encode($this->products);

        if ($this->savedIn == 'cookie') {
            setcookie('store_cart', $this->products, time() + 60 * 60 * 24 * 30);
        } elseif ($this->savedIn == 'database') {
            $database = DatabaseContainer::getDatabase();

            $sql = $database->prepare('UPDATE `store_carts` SET `products`=:products WHERE `cart_id`=:cart_id');
            $sql->execute(array(
                ':cart_id'  => $this->cartId,
                ':products' => $formatted,
            ));
        }
    }

    /**
     * Clears the cart
     */
    public function clearCart()
    {
        if ($this->savedIn == 'database') {
            $database = DatabaseContainer::getDatabase();

            $sql = $database->prepare('UPDATE `store_carts` SET `products`=:products WHERE `cart_id`=:cart_id');
            $sql->execute(array(
                ':cart_id'  => $this->cartId,
                ':products' => '{}',
            ));
            return true;
        } elseif ($this->savedIn == 'cookie') {
            setcookie('store_cart', '{}', time() + 60 * 60 * 24 * 30);
            return true;
        }
        return false;
    }

    /**
     * Does there already exist a cart for the user?
     *
     * @param UserInfo $user
     *
     * @return bool
     * @throws \Exception
     */
    public static function cartExistsForUser($user)
    {
        $database = DatabaseContainer::getDatabase();

        $sql = $database->prepare('SELECT NULL FROM `store_carts` WHERE `user_id`=:user_id');
        $sql->execute(array(
            ':user_id' => $user->getFromUser('user_id'),
        ));

        if ($sql->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param UserInfo $user
     *
     * @return int|null
     * @throws \Exception
     */
    public static function getCartIdFromUser($user)
    {
        $database = DatabaseContainer::getDatabase();

        $sql = $database->prepare('SELECT `cart_id` FROM `store_carts` WHERE `user_id`=:user_id');
        $sql->execute(array(
            ':user_id' => $user->getFromUser('user_id'),
        ));

        if ($sql->rowCount() > 0) {
            $cartData = $sql->fetchAll(\PDO::FETCH_ASSOC);
            return (int)$cartData[0]['cart_id'];
        }
        return null;
    }

    /**
     * @param int $store_id
     *
     * @return array|null
     */
    public function getProductsForStore($store_id)
    {
        if (!Store::storeExists($store_id)) {
            return null;
        }
        if (count($this->products) == 0) {
            return array();
        }

        return $this->products[$store_id];
    }

    /**
     * @param int   $store_id
     * @param array $order_info
     *
     * @return $this
     */
    public function makeOrder($store_id, $order_info)
    {
        // Update "stock_available" for every product in cart
        $currentStoreProducts = $this->products[$store_id];
        foreach ($currentStoreProducts as $key => $productInfo) {
            $product = new StoreProduct($productInfo['id']);
            $currentStock = $product->getVar('stock_available');
            $newStock = $currentStock - $productInfo['count'];
            $product->setStockAvailable($newStock);
        }

        // Save the order
        $database = DatabaseContainer::getDatabase();
        $productList = str_replace('"', '\\"', json_encode($this->products[$store_id]));
        $addOrder = $database->prepare('INSERT INTO `store_orders`(`name`,`email`,`phone`,`street`,`zip_code`,`city`,`country`,`delivery_type`,`product_list`,`store_id`) VALUES (:name,:email,:phone,:street,:zip_code,:city,:country,:delivery_type,:product_list,:store_id)');
        $addOrder->bindValue(':name', $order_info['name']);
        $addOrder->bindValue(':email', $order_info['email']);
        $addOrder->bindValue(':phone', $order_info['phone']);
        $addOrder->bindValue(':street', $order_info['location_street'].' '.$order_info['location_street_number']);
        $addOrder->bindValue(':zip_code', $order_info['location_postal_code']);
        $addOrder->bindValue(':city', $order_info['location_city']);
        $addOrder->bindValue(':country', $order_info['location_country']);
        $addOrder->bindValue(':delivery_type', $order_info['delivery_type']);
        $addOrder->bindValue(':product_list', $productList);
        $addOrder->bindValue(':store_id', $store_id);
        $sqlSuccess = $addOrder->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            return $this;
        }
    }
}
