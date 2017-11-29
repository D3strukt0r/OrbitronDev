<?php

namespace App\Store;

use App\Account\Entity\User;
use App\Store\Entity\Cart;
use App\Store\Entity\DeliveryType;
use App\Store\Entity\Order;
use App\Store\Entity\Product;
use App\Store\Entity\Store;

class StoreCheckout
{
    private $cartId = null;
    private $savedIn = null;
    private $products = array();

    /**
     * @param float|null               $cartId
     * @param bool|null                $isRegisteredUser
     * @param \App\Account\Entity\User $user
     *
     * @throws \Exception
     */
    public function __construct($cartId = null, $isRegisteredUser = false, User $user = null)
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
                    $this->cartId = $cartData->getId();
                    $this->products = $cartData->getProducts();
                } else {
                    // Cart does not really exist
                    $this->cartId = $this->createNewCart($user);
                }
            }
        } else {
            // If user is not registered
            $this->savedIn = 'cookie';

            $request = \Kernel::getIntent()->getRequest();
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
     * @return int
     * @throws \Exception
     */
    private function createNewCart(User $user)
    {
        $em = \Kernel::getIntent()->getEntityManager();

        $newCart = new Cart();
        $newCart
            ->setUser($user)
            ->setProducts(array());

        $em->persist($newCart);
        $em->flush();

        return $newCart->getId();
    }

    /**
     * Add a product to the cart
     *
     * @param \App\Store\Entity\Store   $store   Required to get the ID
     * @param \App\Store\Entity\Product $product Required to know which product
     * @param int                       $count   Amount to be added
     */
    public function addToCart(Store $store, Product $product, $count = 1)
    {
        $storeId = $store->getId();
        $productId = (int)$product->getId();

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
        $request = \Kernel::getIntent()->getRequest();

        return json_decode(base64_decode($request->cookies->get('store_cart')), true);
    }

    /**
     * @param \App\Account\Entity\User $user
     *
     * @return null|\App\Store\Entity\Cart
     */
    private function getDatabase($user)
    {
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var \App\Store\Entity\Cart $get */
        $get = $em->getRepository(Cart::class)->findOneBy(array('user' => $user));

        return $get;
    }

    /**
     * @param array $data
     */
    private function setDatabase($data)
    {
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var \App\Store\Entity\Cart $update */
        $update = $em->getRepository(Cart::class)->findOneBy(array('id' => $this->cartId));
        $update->setProducts($data);

        $em->flush();
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
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var \App\Store\Entity\Cart $get */
        $get = $em->getRepository(Cart::class)->findOneBy(array('user' => $user));

        if (!is_null($get)) {
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
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var null|\App\Store\Entity\Cart $get */
        $get = $em->getRepository(Cart::class)->findOneBy(array('user' => $user));

        if (!is_null($get)) {
            return $get->getId();
        }
        return null;
    }

    /**
     * @param \App\Store\Entity\Store $store
     * @param bool $additional_info
     * @param bool $add_total
     *
     * @return array|null
     */
    public function getCart($store, $additional_info = false, $add_total = false)
    {
        if (is_null($store)) {
            return null;
        }
        if (count($this->products) == 0) {
            return array();
        }

        $cart = $this->products[$store->getId()];

        if ($additional_info) {

            $totalCount = 0;
            $totalPrice = 0;

            $request = \Kernel::getIntent()->getRequest();
            $userLanguage = !is_null($request->query->get('lang')) ? $request->query->get('lang') : 'en';
            $userCurrency = !is_null($request->query->get('currency')) ? $request->query->get('currency') : 'USD';

            foreach ($cart as $key => $item) {
                /** @var \App\Store\Entity\Product $product */
                $product = \Kernel::getIntent()->getEntityManager()->getRepository(Product::class)->findOneBy(array('id' => $item['id']));
                $cart[$key] = $product->toArray();

                $names = $product->getNames();
                $cart[$key]['name'] = (isset($names[$userLanguage]) ? $names[$userLanguage] : (isset($names['en']) ? $names['en'] : null));

                $descriptions = $product->getDescriptions();
                $cart[$key]['description'] = (isset($descriptions[$userLanguage]) ? $descriptions[$userLanguage] : (isset($descriptions['en']) ? $descriptions['en'] : null));

                $prices = $product->getPrices();
                $cart[$key]['price'] = (isset($prices[$userCurrency]) ? $prices[$userCurrency] : (isset($prices['USD']) ? $prices['USD'] : null));

                $salePrices = $product->getSalePrices();
                $cart[$key]['price_sale'] = (isset($salePrices[$userCurrency]) ? $salePrices[$userCurrency] : (isset($salePrices['USD']) ? $salePrices['USD'] : null));

                $cart[$key]['in_sale'] = !is_null($cart[$key]['price_sale']) ? true : false;
                $cart[$key]['in_cart'] = $item['count'];
                $cart[$key]['subtotal'] = $item['count'] * ($cart[$key]['in_sale'] ? $cart[$key]['price_sale'] : $cart[$key]['price']);

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
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var null|\App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('id' => $store_id));

        // Update "stock_available" for every product in cart
        $currentStoreProducts = $this->products[$store_id];
        foreach ($currentStoreProducts as $key => $productInfo) {
            /** @var \App\Store\Entity\Product $product */
            $product = $em->getRepository(Product::class)->findOneBy(array('id' => $productInfo['id']));
            $currentStock = $product->getStock();
            $newStock = $currentStock - $productInfo['count'];
            $product->setStock($newStock);
        }
        $em->flush();

        // Save the order
        /** @var \App\Store\Entity\DeliveryType|null $deliveryType */
        $deliveryType = $em->getRepository(DeliveryType::class)->findOneBy(array('id' => $order_info['delivery_type']));

        $newOrder = new Order();
        $newOrder
            ->setStore($store)
            ->setName($order_info['name'])
            ->setEmail($order_info['email'])
            ->setPhone($order_info['phone'])
            ->setStreet($order_info['location_street'].' '.$order_info['location_street_number'])
            ->setZipCode($order_info['location_postal_code'])
            ->setCity($order_info['location_city'])
            ->setCountry($order_info['location_country'])
            ->setDeliveryType($deliveryType)
            ->setProductList($this->products[$store_id]);
        $em->persist($newOrder);
        $em->flush();

        return $this;
    }
}
