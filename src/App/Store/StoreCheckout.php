<?php

namespace App\Store;

use Container\DatabaseContainer;
use Kernel;

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
     * @param \App\Account\Entity\User|null $user
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
                if (self::cartExistsForUser($user)) {
                    $cartData = $this->getDatabase($user);

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

            $request = Kernel::getIntent()->getRequest();
            if (!is_null($request->cookies->get('store_cart'))) {
                // Create new cookie
                $this->setCookie($this->products);
            } else {
                // Get information from existing cookie
                $this->products = $this->getCookie();
            }
        }
    }

    /**
     * Creates a new cart in the database
     *
     * @param \App\Account\Entity\User $user
     *
     * @return string
     * @throws \Exception
     */
    private function createNewCart($user)
    {
        $database = DatabaseContainer::getDatabase();

        $sql = $database->prepare('INSERT INTO `store_carts`(`user_id`, `products`) VALUES (:user_id, :products)');
        $sql->execute(array(
            ':user_id'  => $user->getId(),
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
     * Remove a product from the cart
     *
     * @param int      $storeId
     * @param int      $product
     * @param int|null $count
     */
    public function removeFromCart($storeId, $product, $count = null)
    {
        if (isset($this->products[$storeId][$product])) {
            if ((!is_null($count) || $count > 0) && $this->products[$storeId][$product]['count'] > $count) {
                // Only remove the amount
                $this->products[$storeId][$product]['count'] -= $count;
            } else {
                // Remove the product
                unset($this->products[$storeId][$product]);
            }
            $this->syncWithSource();
        }
    }

    /**
     * Update database or cookie
     *
     * @throws \Exception
     */
    private function syncWithSource()
    {
        if ($this->savedIn == 'cookie') {
            $this->setCookie($this->products);
        } elseif ($this->savedIn == 'database') {
            $this->setDatabase($this->products);
        }
    }

    /**
     * Updates the cookie
     *
     * @param $data
     */
    private function setCookie($data)
    {
        setcookie('store_cart', base64_encode(json_encode($data, JSON_FORCE_OBJECT)), time() + 60 * 60 * 24 * 30, '/', 'orbitrondev.org');
    }

    /**
     * Gets the cookie
     *
     * @return array
     */
    private function getCookie()
    {
        $request = Kernel::getIntent()->getRequest();

        return json_decode(base64_decode($request->cookies->get('store_cart')), true);
    }

    /**
     * @param \App\Account\Entity\User $user
     *
     * @return array
     */
    private function getDatabase($user)
    {
        $database = DatabaseContainer::getDatabase();

        $sql = $database->prepare('SELECT * FROM `store_carts` WHERE `user_id`=:user_id');
        $sql->execute(array(
            ':user_id' => $user->getId(),
        ));

        return $sql->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param array $data
     */
    private function setDatabase($data)
    {
        $database = DatabaseContainer::getDatabase();
        $sql = $database->prepare('UPDATE `store_carts` SET `products`=:products WHERE `cart_id`=:cart_id');
        $sql->execute(array(
            ':cart_id'  => $this->cartId,
            ':products' => json_encode($data, JSON_FORCE_OBJECT),
        ));
    }

    /**
     * Clears the cart
     */
    public function clearCart()
    {
        $this->products = array();
        $this->syncWithSource();
    }

    /**
     * Does there already exist a cart for the user?
     *
     * @param \App\Account\Entity\User $user
     *
     * @return bool
     * @throws \Exception
     */
    public static function cartExistsForUser($user)
    {
        $database = DatabaseContainer::getDatabase();

        $sql = $database->prepare('SELECT NULL FROM `store_carts` WHERE `user_id`=:user_id');
        $sql->execute(array(
            ':user_id' => $user->getId(),
        ));

        if ($sql->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param \App\Account\Entity\User $user
     *
     * @return int|null
     * @throws \Exception
     */
    public static function getCartIdFromUser($user)
    {
        $database = DatabaseContainer::getDatabase();

        $sql = $database->prepare('SELECT `cart_id` FROM `store_carts` WHERE `user_id`=:user_id');
        $sql->execute(array(
            ':user_id' => $user->getId(),
        ));

        if ($sql->rowCount() > 0) {
            $cartData = $sql->fetchAll(\PDO::FETCH_ASSOC);
            return (int)$cartData[0]['cart_id'];
        }
        return null;
    }

    /**
     * @param int  $store_id
     * @param bool $additional_info
     * @param bool $add_total
     *
     * @return array|null
     */
    public function getCart($store_id, $additional_info = false, $add_total = false)
    {
        if (!Store::storeExists($store_id)) {
            return null;
        }
        if (count($this->products) == 0) {
            return array();
        }

        $cart = $this->products[$store_id];

        if ($additional_info) {

            $totalCount = 0;
            $totalPrice = 0;

            $userLanguage = 'en'; // TODO: Make this editable by the user
            $userCurrency = 'dollar';  // TODO: Make this editable by the user

            foreach ($cart as $key => $product) {
                $product2 = new StoreProduct($product['id']);
                $cart[$key] = $product2->productData;
                $cart[$key]['description'] = $product2->getVar('long_description_' . $userLanguage);
                $cart[$key]['price'] = $product2->getVar('price_' . $userCurrency);
                $cart[$key]['in_sale'] = is_null($product2->getVar('price_sale_' . $userCurrency)) ? false : true;
                $cart[$key]['price_sale'] = $cart[$key]['in_sale'] ? $product2->getVar('price_sale_' . $userCurrency) : null;
                $cart[$key]['in_cart'] = $product['count'];
                $cart[$key]['subtotal'] = $product['count'] * ($cart[$key]['in_sale'] ? $cart[$key]['price_sale'] : $cart[$key]['price']);

                if ($add_total) {
                    $totalCount += $cart[$key]['in_cart'];
                    $totalPrice += $cart[$key]['subtotal'];
                }
            }

            if ($add_total) {
                $cart['system']['id'] = 0; // Needed, so it won't be displayed in the checkout
                $cart['system']['total_count'] = $totalCount;
                $cart['system']['total_price'] = $totalPrice;
            }
        }

        return $cart;
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
