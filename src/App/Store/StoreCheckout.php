<?php

namespace App\Store;


use App\Core\DatabaseConnection;

class StoreCheckout
{
    private $cartId = null;
    private $savedIn = null;
    private $products = array();

    /**
     * StoreCheckout constructor.
     *
     * @param float             $cartId
     * @param bool              $isRegisteredUser
     * @param \Account\UserInfo $user
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
                /** @var \PDO $database */
                $database = DatabaseConnection::$database;
                if (is_null($database)) {
                    throw new \Exception('A database connection is required');
                }

                $sql = $database->prepare('SELECT * FROM `store_carts` WHERE `user_id`=:user_id');
                $sql->execute(array(
                    ':user_id' => $user->getFromUser('id'),
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
                setcookie('store_cart', $this->products, time() + 60 * 60 * 24 * 30);
            } else {
                // Get information from existing cookie
                $this->products = json_decode($_COOKIE['store_cart'], true);
            }
        }
    }

    /**
     * Creates a new cart in the database
     *
     * @param \Account\UserInfo $user
     *
     * @return string
     * @throws \Exception
     */
    private function createNewCart($user)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $sql = $database->prepare('INSERT INTO `store_carts`(`user_id`, `products`) VALUES (:user_id, :products)');
        $sql->execute(array(
            ':user_id'  => $user->getFromUser('id'),
            ':products' => '{}',
        ));

        return $database->lastInsertId();
    }

    /**
     * Add a product to the cart
     *
     * @param \Store\Store        $store   Required to get the ID
     * @param \Store\StoreProduct $product Required to know which product
     * @param int                 $count   Amount to be added
     */
    public function addToCart($store, $product, $count = 1)
    {
        $storeId = $store->getVar('id');
        $productId = $product->getVar('id');

        // How many are already in the cart?
        $alreadyInCart = 0;
        if (isset($this->products[$storeId][$productId])) {
            $alreadyInCart = $this->products[$storeId][$productId]['amount'];
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
            /** @var \PDO $database */
            $database = DatabaseConnection::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }

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
            /** @var \PDO $database */
            $database = DatabaseConnection::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }

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
     * @param \Account\UserInfo $user
     *
     * @return bool
     * @throws \Exception
     */
    public static function cartExistsForUser($user)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $sql = $database->prepare('SELECT NULL FROM `store_carts` WHERE `user_id`=:user_id');
        $sql->execute(array(
            ':user_id' => $user,
        ));

        if ($sql->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $user
     *
     * @return int|null
     * @throws \Exception
     */
    public static function getCartIdFromUser($user)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $sql = $database->prepare('SELECT `cart_id` FROM `store_carts` WHERE `user_id`=:user_id');
        $sql->execute(array(
            ':user_id' => $user,
        ));

        if ($sql->rowCount() > 0) {
            $cartData = $sql->fetchAll(\PDO::FETCH_ASSOC);
            return (int)$cartData[0]['cart_id'];
        }
        return null;
    }
}