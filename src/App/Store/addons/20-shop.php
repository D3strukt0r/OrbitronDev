<?php

use App\Account\UserInfo;
use App\Store\Store;
use App\Store\StoreAcp;
use App\Store\StoreProduct;
use Container\DatabaseContainer;

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
    $storeId = Store::url2Id($controller->parameters['store']);
    $store   = new Store($storeId);

    $productList = StoreProduct::getProductList($store->getVar('id'));

    $userLanguage = 'en'; // TODO: Make this editable by the user
    $userCurrency = 'dollar';  // TODO: Make this editable by the user

    foreach ($productList as $index => $product) {
        $productList[$index]['short_description'] = $product['short_description_' . $userLanguage];
        $productList[$index]['price'] = $product['price_' . $userCurrency];
        $productList[$index]['in_sale'] = is_null($product['price_sale_' . $userCurrency]) ? false : true;
        $productList[$index]['price_sale'] = $productList[$index]['in_sale'] ? $product['price_sale_' . $userCurrency] : null; // TODO: Show it when there is a sale
    }

    return $twig->render('store/theme_admin1/catalogue.html.twig', array(
        'products' => $productList,
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
    $database = DatabaseContainer::getDatabase();
    $storeId = Store::url2Id($controller->parameters['store']);
    $store   = new Store($storeId);
    $userLanguage = 'en'; // TODO: Make this editable by the user
    $userCurrency = 'dollar';  // TODO: Make this editable by the user

    $getOrders = $database->prepare('SELECT * FROM `store_orders` WHERE `store_id`=:store_id');
    $getOrders->bindValue(':store_id', $storeId);
    $sqlSuccess = $getOrders->execute();

    $orders = array();
    if (!$sqlSuccess) {
        throw new \Exception('Cannot get list with all vouchers');
    } else {
        $orders = $getOrders->fetchAll(PDO::FETCH_ASSOC);
    }


    foreach ($orders as $index => $order) {
        // Format product list
        $order['product_list'] = str_replace('\\"', '"', $order['product_list']);
        $cart = json_decode($order['product_list'], true);
        $tmpProduct = array();
        foreach ($cart as $index2 => $productInCart) {
            $product = new StoreProduct($productInCart['id']);

            // Add some information
            $product->productData['short_description'] = $product->productData['short_description_' . $userLanguage];
            $product->productData['price'] = $product->productData['price_' . $userCurrency];
            $product->productData['in_sale'] = is_null($product->productData['price_sale_' . $userCurrency]) ? false : true;
            $product->productData['price_sale'] = $product->productData['in_sale'] ? $product->productData['price_sale_' . $userCurrency] : null;

            $productData = array_merge($cart[$index2], $product->productData);
            $tmpProduct[$index2] = $productData;
        }
        $orders[$index]['product_list'] = $tmpProduct;

        // TODO: Format delivery type

    }

    return $twig->render('store/theme_admin1/orders.html.twig', array(
        'orders' => $orders,
        'current_store' => $store->storeData,
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
    $database = DatabaseContainer::getDatabase();
    $storeId = Store::url2Id($controller->parameters['store']);

    $getVouchers = $database->prepare('SELECT * FROM `store_voucher` WHERE `store_id`=:store_id');
    $getVouchers->bindValue(':store_id', $storeId);
    $sqlSuccess = $getVouchers->execute();

    $vouchers = array();
    if (!$sqlSuccess) {
        throw new \Exception('Cannot get list with all vouchers');
    } else {
        $vouchers = $getVouchers->fetchAll(PDO::FETCH_ASSOC);
    }

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
    $database = DatabaseContainer::getDatabase();
    $request = Kernel::getIntent()->getRequest();
    $storeId = Store::url2Id($controller->parameters['store']);
    $store = new Store($storeId);
    $user = new UserInfo(USER_ID);

    if (LOGGED_IN && $store->getVar('owner_id') == $user->getFromUser('user_id')) {
        $updateStatus = $database->prepare('UPDATE `store_orders` SET `status`=\'1\' WHERE `id`=:order_id AND `store_id`=:store_id');
        $updateStatus->bindValue(':store_id', $store->getVar('id'));
        $updateStatus->bindValue(':order_id', $request->query->get('order_id'));
        $sqlSuccessful = $updateStatus->execute();
    }

    header('Location: '.$controller->generateUrl('app_store_store_admin', array('store' => $store->getVar('url'), 'page' => 'orders')));
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
    $database = DatabaseContainer::getDatabase();
    $request = Kernel::getIntent()->getRequest();
    $storeId = Store::url2Id($controller->parameters['store']);
    $store = new Store($storeId);
    $user = new UserInfo(USER_ID);

    if (LOGGED_IN && $store->getVar('owner_id') == $user->getFromUser('user_id')) {
        $updateStatus = $database->prepare('UPDATE `store_orders` SET `status`=\'2\' WHERE `id`=:order_id AND `store_id`=:store_id');
        $updateStatus->bindValue(':store_id', $store->getVar('id'));
        $updateStatus->bindValue(':order_id', $request->query->get('order_id'));
        $sqlSuccessful = $updateStatus->execute();
    }

    header('Location: '.$controller->generateUrl('app_store_store_admin', array('store' => $store->getVar('url'), 'page' => 'orders')));
    exit;
}
