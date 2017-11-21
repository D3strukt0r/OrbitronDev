<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Store\Entity\Store;
use App\Store\Form\AddCommentType;
use App\Store\Form\AddToCartType;
use App\Store\Form\CheckoutType;
use App\Store\Form\NewStoreType;
use App\Store\StoreAcp;
use App\Store\StoreCheckout;
use App\Store\StoreComments;
use App\Store\StoreHelper;
use App\Store\StoreProduct;
use Container\DatabaseContainer;
use Kernel;
use ReCaptcha\ReCaptcha;
use Swift_Image;
use Swift_Message;
use Symfony\Component\Form\FormError;

class StoreController extends \Controller
{
    public function indexAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $this->getEntityManager()->find(User::class, USER_ID);

        return $this->render('store/list-stores.html.twig', array(
            'current_user' => $currentUser,
            'store_list'  => StoreHelper::getStoreList(),
        ));
    }

    public function newStoreAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $createStoreForm = $this->createForm(NewStoreType::class);

        $request = $this->getRequest();
        $createStoreForm->handleRequest($request);
        if ($createStoreForm->isValid()) {
            $errorMessages   = array();
            $captcha         = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($_POST['g-recaptcha-response'], $request->getClientIp());
            if (!$captchaResponse->isSuccess()) {
                $createStoreForm->get('recaptcha')->addError(new FormError('The Captcha is not correct'));
            } else {
                if (strlen($storeName = trim($createStoreForm->get('name')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createStoreForm->get('name')->addError(new FormError('Please give your store a name'));
                } elseif (strlen($storeName) < 4) {
                    $errorMessages[] = '';
                    $createStoreForm->get('name')->addError(new FormError('Your store must have minimally 4 characters'));
                }
                if (strlen($storeUrl = trim($createStoreForm->get('url')->getData())) == 0) {
                    $errorMessages[] = '';
                    $createStoreForm->get('url')->addError(new FormError('Please give your store an unique url to access it'));
                } elseif (strlen($storeUrl) < 3) {
                    $errorMessages[] = '';
                    $createStoreForm->get('url')->addError(new FormError('Your store must url have minimally 3 characters'));
                } elseif (preg_match('/[^a-z_\-0-9]/i', $storeUrl)) {
                    $errorMessages[] = '';
                    $createStoreForm->get('url')->addError(new FormError('Only use a-z, A-Z, 0-9, _, -'));
                } elseif (in_array($storeUrl, array('new-forum', 'admin'))) {
                    $errorMessages[] = '';
                    $createStoreForm->get('url')->addError(new FormError('It\'s prohibited to use this url'));
                } elseif (StoreHelper::urlExists($storeUrl)) {
                    $errorMessages[] = '';
                    $createStoreForm->get('url')->addError(new FormError('This url is already in use'));
                }

                if (!count($errorMessages)) {
                    try {
                        $newStore = new Store();
                        $newStore
                            ->setName($storeName)
                            ->setUrl($storeUrl)
                            ->setOwner($currentUser)
                            ->setCreated(new \DateTime());
                        $em->persist($newStore);
                        $em->flush();

                        return $this->redirectToRoute('app_store_store_index', array('store' => $newStore->getUrl()));
                    } catch (\Exception $e) {
                        $createStoreForm->addError(new FormError('We could not create your forum. ('.$e->getMessage().')'));
                    }
                }
            }
        }

        return $this->render('store/create-new-store.html.twig', array(
            'current_user'      => $currentUser,
            'create_store_form' => $createStoreForm->createView(),
        ));
    }

    public function storeIndexAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $productList = StoreProduct::getProductList($store->getId());
        $userLanguage = !is_null($request->query->get('lang')) ? $request->query->get('lang') : 'en';
        $userCurrency = !is_null($request->query->get('currency')) ? $request->query->get('currency') : 'dollar';

        foreach ($productList as $index => $product) {
            $productList[$index]['short_description'] = $product['short_description_' . $userLanguage];
            $productList[$index]['price'] = $product['price_' . $userCurrency];
            $productList[$index]['in_sale'] = is_null($product['price_sale_' . $userCurrency]) ? false : true;
            $productList[$index]['price_sale'] = $productList[$index]['in_sale'] ? $product['price_sale_' . $userCurrency] : null;
        }

        // Shopping cart widget
        if (LOGGED_IN) {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), false);
        }
        $cart = $rawCart->getCart($store->getId(), true, true);

        return $this->render('store/theme1/index.html.twig', array(
            'current_user'  => $currentUser,
            'current_store' => $store,
            'product_list'  => $productList,
            'cart'          => $cart,
        ));
    }

    public function storeProductAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        // Does the product even exist?
        if (!StoreProduct::productExists($this->parameters['product'])) {
            return $this->render('error/error404.html.twig');
        }

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $userLanguage = !is_null($request->query->get('lang')) ? $request->query->get('lang') : 'en';
        $userCurrency = !is_null($request->query->get('currency')) ? $request->query->get('currency') : 'dollar';
        $product      = new StoreProduct($this->parameters['product']);

        $product->productData['description'] = $product->getVar('long_description_' . $userLanguage);
        $product->productData['price'] = $product->getVar('price_' . $userCurrency);
        $product->productData['in_sale'] = is_null($product->getVar('price_sale_' . $userCurrency)) ? false : true;
        $product->productData['price_sale'] = $product->productData['in_sale'] ? $product->getVar('price_sale_' . $userCurrency) : null;

        $addToCartForm = $this->createForm(AddToCartType::class, null, array('store' => $store, 'product' => $product));

        $addCommentForm = $this->createForm(AddCommentType::class, null, array('product' => $product));

        // Add product to cart
        $addToCartForm->handleRequest($request);
        if ($addToCartForm->isSubmitted() && $addToCartForm->isValid()) {
            $formData = $addToCartForm->getData();
            // TODO: As product_count is TextType, we have to convert it to a Int
            $formData['product_count'] = intval($formData['product_count']);

            if (LOGGED_IN) {
                // Is registered user
                /** @var \App\Account\Entity\User $user */
                $user = $this->getEntityManager()->find(User::class, USER_ID);

                if (StoreCheckout::cartExistsForUser($user)) {
                    // Cart exists
                    $cartId = StoreCheckout::getCartIdFromUser($user);
                    $cart = new StoreCheckout($cartId, true, $user);
                } else {
                    // Create new cart
                    $cart = new StoreCheckout(null, true, $user);
                }
            } else {
                // Is a guest
                $cart = new StoreCheckout(null);
            }
            /** @var \App\Store\Entity\Store $store2 */
            $store2 = $em->getRepository(Store::class)->findOneBy(array('url' => $formData['store_id']));
            $product2 = new StoreProduct($formData['product_id']);
            $cart->addToCart($store2, $product2, $formData['product_count']);

            $this->addFlash('product_added', 'Your product was added to the cart');
        }

        // Add a review
        $request = $this->getRequest();
        $addCommentForm->handleRequest($request);
        if ($addCommentForm->isSubmitted() && $addCommentForm->isValid()) {
            $formData = $addCommentForm->getData();
            StoreComments::addReview($formData['product_id'], USER_ID, $formData['comment'], $formData['rating']);
        }

        $comments = StoreComments::getCommentList($product->getVar('id'));
        foreach($comments as $index => $comment) {
            $comments[$index]['formatted_username'] = AccountHelper::formatUsername($comment['user_id']);
        }

        // Shopping cart widget
        if (LOGGED_IN) {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), false);
        }
        $cart = $rawCart->getCart($store->getId(), true, true);

        return $this->render('store/theme1/product.html.twig', array(
            'current_user'  => $currentUser,
            'current_store' => $store,
            'current_product'  => $product->productData,
            'comments' => $comments,
            'add_to_cart_form' => $addToCartForm->createView(),
            'add_comment_form' => $addCommentForm->createView(),
            'cart'             => $cart,
        ));
    }

    public function storeCheckoutAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        if (LOGGED_IN) {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), false);
        }
        $cart = $rawCart->getCart($store->getId(), true, false);

        $checkoutForm = $this->createForm(CheckoutType::class, null, array('user' => $currentUser));

        if($store->getVar('braintree_mode') == 'sandbox') {
            \Braintree_Configuration::environment('sandbox');
            \Braintree_Configuration::merchantId($store->getVar('braintree_sandbox_merchant_id'));
            \Braintree_Configuration::publicKey($store->getVar('braintree_sandbox_public_key'));
            \Braintree_Configuration::privateKey($store->getVar('braintree_sandbox_private_key'));
        } elseif($store->getVar('braintree_mode') == 'production') {
            \Braintree_Configuration::environment('production');
            \Braintree_Configuration::merchantId($store->getVar('braintree_production_merchant_id'));
            \Braintree_Configuration::publicKey($store->getVar('braintree_production_public_key'));
            \Braintree_Configuration::privateKey($store->getVar('braintree_production_private_key'));
        }

        $payment = array();
        $payment['client_token'] = \Braintree_ClientToken::generate();

        $checkoutForm->handleRequest($request);
        if ($checkoutForm->isSubmitted() && $checkoutForm->isValid()) {
            $formData = $checkoutForm->getData();

            $nonceFromTheClient = $request->request->get('payment_method_nonce');

            $productUnavailable = array();
            $newProductsStock = array();
            foreach ($cart as $key => $productInfo) {
                $product = new StoreProduct($productInfo['id']);

                if($product->getVar('stock_available') >= $productInfo['in_cart']) {
                    $newProductsStock[$product->getVar('id')] = $product->getVar('stock_available') - $productInfo['in_cart'];
                } else {
                    $product->productData['count'] = $productInfo['in_cart'];
                    $productUnavailable[$key] = $product->productData;
                }
            }

            if (count($productUnavailable) > 0) {
                foreach ($productUnavailable as $key => $item) {
                    $this->addFlash('products_unavailable', $item['name'].' has only '.$item['stock_available'].' left! You wanted '.$item['count']);
                }
            } else {

                $cart2 = $rawCart->getCart($store->getId(), true, true);
                $result = \Braintree_Transaction::sale([
                    'amount' => $cart2['system']['total_price'],
                    'paymentMethodNonce' => $nonceFromTheClient,
                    'options' => [
                        'submitForSettlement' => true,
                    ]
                ]);

                if ($result->success === true) {
                    $message = (new Swift_Message());
                    $imgDir1 = $message->embed(Swift_Image::fromPath(Kernel::getIntent()->getRootDir().'/web/assets/logo-long.png'));
                    $message->setSubject('Order confirmation')
                        ->setFrom(array($store->getOwner()->getEmail() => $store->getName()))
                        ->setTo(array(trim($formData['email']) => trim($formData['name'])))
                        ->setBcc(array($store->getOwner()->getEmail()))
                        ->setReplyTo(array($store->getOwner()->getEmail() => $store->getName()))
                        ->setBody($this->renderView('store/mail/order-confirmation.html.twig', array(
                            'current_store' => $store,
                            'order_form' => $formData,
                            'header_image' => $imgDir1,
                            'ordered_time' => time(),
                            'cart' => $cart,
                        )), 'text/html');

                    /** @var \Swift_Mailer $mailer */
                    $mailer = $this->get('mailer');
                    $mailSent = $mailer->send($message);

                    if($mailSent) {
                        $formData['delivery_type'] = @$request->request->get('shipping'); // TODO: Integrate this into the form
                        $rawCart->makeOrder($store->getId(), $formData);
                        $rawCart->clearCart();
                        $cart = $rawCart->getCart($store->getId(), true, true);

                        $this->addFlash('order_sent', 'We saved your order in our system, and sent you a confirmation. We will deliver you the products as soon as possible.');
                    } else {
                        $this->addFlash('order_not_sent', 'Your order was not sent. Try again!');
                    }
                } else {
                    $this->addFlash('order_not_sent', 'It looks like, we couldn\'t create a transaction with your given credit card information. Try again!');
                }
            }
        }

        return $this->render('store/theme1/checkout.html.twig', array(
            'current_user'  => $currentUser,
            'current_store' => $store,
            'checkout_form' => $checkoutForm->createView(),
            'cart' => $cart,
            'payment' => $payment,
        ));
    }

    public function storeDoCheckVoucherAction()
    {
        $em = $this->getEntityManager();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        $database = DatabaseContainer::getDatabase();
        $sql = $database->prepare('SELECT * FROM `store_voucher` WHERE `code`=:voucher AND `store_id`=:store_id LIMIT 1');
        $sql->execute(array(
            ':voucher' => $this->getRequest()->query->get('code'),
            ':store_id' => $store->getId(),
        ));

        if ($sql->rowCount() == 0) {
            $result = new \SimpleXMLElement('<root></root>');
            $result->addChild('result', 'invalid');
        } else {
            $voucherInfo = $sql->fetchAll(\PDO::FETCH_ASSOC);

            $result = new \SimpleXMLElement('<root></root>');
            $result->addChild('result', 'valid');
            $result->addChild('type', $voucherInfo[0]['type']);
            $result->addChild('amount', $voucherInfo[0]['amount']);
        }
        header('Content-Type: text/xml');
        return $result->asXML();
    }

    public function storeDoClearCartAction()
    {
        $em = $this->getEntityManager();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $cart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        $cart->clearCart();
        return '';
    }

    public function storeDoAddToCartAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        if (LOGGED_IN) {
            $checkout = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $checkout = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), false);
        }

        $product = !is_null($request->query->get('product')) ? $request->query->get('product') : (!is_null($request->request->get('product')) ? $request->request->get('product') : null);
        $count = !is_null($request->query->get('product_count')) ? $request->query->get('product_count') : (!is_null($request->request->get('product_count')) ? $request->request->get('product_count') : null);
        $responseType = !is_null($request->query->get('response')) ? $request->query->get('response') : (!is_null($request->request->get('response')) ? $request->request->get('response') : null);
        $checkout->addToCart($product, $count);

        $browser = !is_null($request->query->get('browser')) ? $request->query->get('browser') : (!is_null($request->request->get('browser')) ? $request->request->get('browser') : null);
        if (!is_null($responseType)) {
            if ($responseType == 'json') {
                return $this->json(array(
                    'result' => "true",
                ));
            }
        }
        if (is_null($browser) || (!is_null($browser) && $browser == true)) {
            return $this->redirect($request->server->get('HTTP_REFERER'), 302);
        } else {
            return '';
        }
    }

    public function storeDoRemoveFromCartAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        if (LOGGED_IN) {
            $checkout = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $checkout = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), false);
        }

        $product = !is_null($request->query->get('product')) ? $request->query->get('product') : (!is_null($request->request->get('product')) ? $request->request->get('product') : null);
        $count = !is_null($request->query->get('product_count')) ? $request->query->get('product_count') : (!is_null($request->request->get('product_count')) ? $request->request->get('product_count') : null);
        $responseType = !is_null($request->query->get('response')) ? $request->query->get('response') : (!is_null($request->request->get('response')) ? $request->request->get('response') : null);
        $checkout->removeFromCart($product, $count);

        $browser = !is_null($request->query->get('browser')) ? $request->query->get('browser') : (!is_null($request->request->get('browser')) ? $request->request->get('browser') : null);
        if (!is_null($responseType)) {
            if ($responseType == 'json') {
                return $this->json(array(
                    'result' => "true",
                ));
            }
        }
        if (is_null($browser) || (!is_null($browser) && $browser == true)) {
            return $this->redirect($request->server->get('HTTP_REFERER'), 302);
        } else {
            return '';
        }
    }

    public function storeAdminAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $params = array();
        $params['user_id']         = USER_ID;
        $params['current_user']    = $currentUser;
        $params['current_store']   = $store;
        $params['view_navigation'] = '';

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $request->getUri()));
        }
        if (USER_ID != (int)$store->getOwner()->getId()) {
            return $this->render('store/theme_admin1/no-permission.html.twig');
        }

        StoreAcp::includeLibs();

        $view = 'acp_not_found';

        foreach (StoreAcp::getAllMenus('root') as $sMenu => $aMenuInfo) {
            $selected                  = ($this->parameters['page'] === $aMenuInfo['href'] ? 'class="active"' : '');
            $params['view_navigation'] .= '<li><a href="'.$this->generateUrl('app_store_store_admin',
                    array(
                        'store' => $store->getUrl(),
                        'page'  => $aMenuInfo['href'],
                    )).'" '.$selected.'>'.$aMenuInfo['title'].'</a></li>';

            if (strlen($selected) > 0) {
                if (is_callable($aMenuInfo['screen'])) {
                    $view = $aMenuInfo['screen'];
                } else {
                    $view = 'acp_function_error';
                }
            }
        }

        foreach (StoreAcp::getAllGroups() as $sGroup => $aGroupInfo) {
            if (is_null($aGroupInfo['display']) || strlen($aGroupInfo['display']) == 0) {
                foreach (StoreAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                    $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? ' class="active"' : '');
                    if (strlen($selected) > 0) {
                        if (is_callable($aMenuInfo['screen'])) {
                            $view = $aMenuInfo['screen'];
                        } else {
                            $view = 'acp_function_error';
                        }
                    }
                }
                continue;
            }
            $params['view_navigation'] .= '<li><a href="#">'.$aGroupInfo['title'].'<span class="fa arrow"></span></a><ul class="nav nav-second-level collapse">';

            foreach (StoreAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                $selected                  = ($this->parameters['page'] === $aMenuInfo['href'] ? 'class="active"' : '');
                $params['view_navigation'] .= '<li><a href="'.$this->generateUrl('app_store_store_admin',
                        array(
                            'store' => $store->getUrl(),
                            'page'  => $aMenuInfo['href'],
                        )).'" '.$selected.'>'.$aMenuInfo['title'].'</a></li>';
                if (strlen($selected) > 0) {
                    if (is_callable($aMenuInfo['screen'])) {
                        $view = $aMenuInfo['screen'];
                    } else {
                        $view = 'acp_function_error';
                    }
                }
            }

            $params['view_navigation'] .= '</ul></li>';
        }


        $response = call_user_func($view, $this->container->get('twig'), $this);
        if (is_string($response)) {
            $params['view_body'] = $response;
        }

        return $this->render('store/theme_admin1/panel.html.twig', $params);
    }
}
