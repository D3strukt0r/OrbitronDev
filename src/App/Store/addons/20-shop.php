<?php

use App\Account\Entity\User;
use App\Store\Entity\Order;
use App\Store\Entity\Product;
use App\Store\Entity\Store;
use App\Store\StoreAcp;

StoreAcp::addGroup(array(
    'parent' => 'root',
    'id'     => 'shop',
    'title'  => 'Shop',
));

StoreAcp::addMenu(array(
    'parent' => 'shop',
    'id'     => 'catalogue',
    'title'  => 'Catalogue/Products',
    'href'   => 'catalogue',
    'screen' => 'acp_html_catalogue',
));

StoreAcp::addMenu(array(
    'parent' => 'shop',
    'id'     => 'orders',
    'title'  => 'Orders',
    'href'   => 'orders',
    'screen' => 'acp_html_orders',
));
StoreAcp::addMenu(array(
    'parent' => 'null',
    'id'     => 'state_1',
    'title'  => 'Change to sent',
    'href'   => 'change_order_statement_to_1',
    'screen' => 'acp_html_change_order_status_to_1',
));
StoreAcp::addMenu(array(
    'parent' => 'null',
    'id'     => 'state_2',
    'title'  => 'Change to Processed',
    'href'   => 'change_order_statement_to_2',
    'screen' => 'acp_html_change_order_status_to_2',
));

StoreAcp::addMenu(array(
    'parent' => 'shop',
    'id'     => 'vouchers',
    'title'  => 'Vouchers',
    'href'   => 'vouchers',
    'screen' => 'acp_html_vouchers',
));

/**
 * @param \Twig_Environment $twig
 * @param \Controller\StoreController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_catalogue($twig, $controller)
{
    $em = $controller->getEntityManager();
    $request = $controller->getRequest();

    //////////// TEST IF STORE EXISTS ////////////
    /** @var \App\Store\Entity\Store $store */
    $store = $em->getRepository(\App\Store\Entity\Store::class)->findOneBy(array('url' => $controller->parameters['store']));
    if (is_null($store)) {
        return $controller->render('error/error404.html.twig');
    }
    //////////// END TEST IF STORE EXISTS ////////////

    /** @var \App\Store\Entity\Product[] $productList */
    $productList = $em->getRepository(\App\Store\Entity\Product::class)->findBy(array('store' => $store), array('last_edited' => 'DESC'));

    $userLanguage = !is_null($request->query->get('lang')) ? $request->query->get('lang') : 'en';
    $userCurrency = !is_null($request->query->get('currency')) ? $request->query->get('currency') : 'USD';

    return $twig->render('store/theme_admin1/catalogue.html.twig', array(
        'products' => $productList,
        'language' => $userLanguage,
        'currency' => $userCurrency,
    ));
}

/**
 * @param \Twig_Environment $twig
 * @param \Controller\StoreController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_orders($twig, $controller)
{
    $em = $controller->getEntityManager();

    //////////// TEST IF STORE EXISTS ////////////
    /** @var null|\App\Store\Entity\Store $store */
    $store = $em->getRepository(Store::class)->findOneBy(array('url' => $controller->parameters['store']));
    if (is_null($store)) {
        return $controller->render('error/error404.html.twig');
    }
    //////////// END TEST IF STORE EXISTS ////////////

    $userLanguage = 'en'; // TODO: Make this editable by the user
    $userCurrency = 'USD';  // TODO: Make this editable by the user

    /** @var \App\Store\Entity\Order[] $orders */
    $orders = $em->getRepository(Order::class)->findBy(array('store' => $store));
    $ordersData = array();

    foreach ($orders as $index => $order) {
        // Format product list
        $productList = array();
        foreach ($order->getProductList() as $key => $item) {
            /** @var \App\Store\Entity\Product $product */
            $product = $em->getRepository(Product::class)->findOneBy(array('id' => $item['id']));
            $productList[$key] = $product->toArray();

            $names = $product->getNames();
            $productList[$key]['name'] = (isset($names[$userLanguage]) ? $names[$userLanguage] : (isset($names['en']) ? $names['en'] : null));

            $descriptions = $product->getDescriptions();
            $productList[$key]['description'] = (isset($descriptions[$userLanguage]) ? $descriptions[$userLanguage] : (isset($descriptions['en']) ? $descriptions['en'] : null));

            $prices = $product->getPrices();
            $productList[$key]['price'] = (isset($prices[$userCurrency]) ? $prices[$userCurrency] : (isset($prices['USD']) ? $prices['USD'] : null));

            $salePrices = $product->getSalePrices();
            $productList[$key]['price_sale'] = (isset($salePrices[$userCurrency]) ? $salePrices[$userCurrency] : (isset($salePrices['USD']) ? $salePrices['USD'] : null));

            $productList[$key]['in_sale'] = !is_null($productList[$key]['price_sale']) ? true : false;
        }
        $ordersData[$index] = $productList;

        // TODO: Format delivery type

    }

    return $twig->render('store/theme_admin1/orders.html.twig', array(
        'orders' => $orders,
        'orders_data' => $ordersData,
        'current_store' => $store,
        'language' => $userLanguage,
        'currency' => $userCurrency,
    ));
}

/**
 * @param \Twig_Environment $twig
 * @param \Controller\StoreController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_vouchers($twig, $controller)
{
    $em = $controller->getEntityManager();

    //////////// TEST IF STORE EXISTS ////////////
    /** @var null|\App\Store\Entity\Store $store */
    $store = $em->getRepository(Store::class)->findOneBy(array('url' => $controller->parameters['store']));
    if (is_null($store)) {
        return $controller->render('error/error404.html.twig');
    }
    //////////// END TEST IF STORE EXISTS ////////////

    /** @var \App\Store\Entity\Voucher[] $vouchers */
    $vouchers = $em->getRepository(\App\Store\Entity\Voucher::class)->findBy(array('store' => $store));

    return $twig->render('store/theme_admin1/vouchers.html.twig', array(
        'vouchers' => $vouchers,
    ));
}

/**
 * @param \Twig_Environment $twig
 * @param \Controller\StoreController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_change_order_status_to_1($twig, $controller)
{
    $em = $controller->getEntityManager();
    $request = $controller->getRequest();

    //////////// TEST IF STORE EXISTS ////////////
    /** @var null|\App\Store\Entity\Store $store */
    $store = $em->getRepository(Store::class)->findOneBy(array('url' => $controller->parameters['store']));
    if (is_null($store)) {
        return $controller->render('error/error404.html.twig');
    }
    //////////// END TEST IF STORE EXISTS ////////////

    /** @var \App\Account\Entity\User $user */
    $user = $em->find(User::class, USER_ID);

    if (LOGGED_IN && $store->getOwner()->getId() == $user->getId()) {
        /** @var \App\Store\Entity\Order $update */
        $update = $em->getRepository(Order::class)->findOneBy(array('id' => $request->query->get('order_id')));
        $update->setStatus(Order::STATUS_IN_PRODUCTION);
        $em->flush();
    }

    header('Location: '.$controller->generateUrl('app_store_store_admin', array('store' => $store->getUrl(), 'page' => 'orders')));
    exit;
}

/**
 * @param \Twig_Environment $twig
 * @param \Controller\StoreController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_change_order_status_to_2($twig, $controller)
{
    $em = $controller->getEntityManager();
    $request = $controller->getRequest();

    //////////// TEST IF STORE EXISTS ////////////
    /** @var null|\App\Store\Entity\Store $store */
    $store = $em->getRepository(Store::class)->findOneBy(array('url' => $controller->parameters['store']));
    if (is_null($store)) {
        return $controller->render('error/error404.html.twig');
    }
    //////////// END TEST IF STORE EXISTS ////////////

    /** @var \App\Account\Entity\User $user */
    $user = $controller->getEntityManager()->find(User::class, USER_ID);

    if (LOGGED_IN && $store->getOwner()->getId() == $user->getId()) {
        /** @var \App\Store\Entity\Order $update */
        $update = $em->getRepository(Order::class)->findOneBy(array('id' => $request->query->get('order_id')));
        $update->setStatus(Order::STATUS_SENT);
        $em->flush();
    }

    header('Location: '.$controller->generateUrl('app_store_store_admin', array('store' => $store->getUrl(), 'page' => 'orders')));
    exit;
}
