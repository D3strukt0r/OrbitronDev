<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Store\Entity\DeliveryType;
use App\Store\Entity\Product;
use App\Store\Entity\ProductRating;
use App\Store\Entity\Store;
use App\Store\Entity\StorePaymentMethods;
use App\Store\Entity\Voucher;
use App\Store\Form\AddCommentType;
use App\Store\Form\AddToCartType;
use App\Store\Form\CheckoutType;
use App\Store\Form\NewStoreType;
use App\Store\StoreAcp;
use App\Store\StoreCheckout;
use App\Store\StoreHelper;
use ReCaptcha\ReCaptcha;
use Swift_Image;
use Swift_Message;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends \Controller
{
    public function indexAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        /** @var \App\Store\Entity\Store[] $storeList */
        $storeList = $em->getRepository(Store::class)->findAll();

        return $this->render('store/list-stores.html.twig', array(
            'current_user' => $currentUser,
            'store_list'   => $storeList,
        ));
    }

    public function newStoreAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $createStoreForm = $this->createForm(NewStoreType::class);

        $createStoreForm->handleRequest($request);
        if ($createStoreForm->isValid()) {
            $errorMessages = array();
            $captcha = new ReCaptcha('6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll');
            $captchaResponse = $captcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
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
                        $createStoreForm->addError(new FormError('We could not create your store. ('.$e->getMessage().')'));
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

        $userLanguage = !is_null($request->query->get('lang')) ? $request->query->get('lang') : 'en';
        $userCurrency = !is_null($request->query->get('currency')) ? $request->query->get('currency') : 'USD';

        // Load list of all products
        /** @var \App\Store\Entity\Product[] $list */
        $list = $em->getRepository(Product::class)->findBy(array('store' => $store), array('last_edited' => 'DESC'));
        $productList = array();
        foreach ($list as $key => $product) {
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

        // Shopping cart widget
        if (LOGGED_IN) {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $rawCart = new StoreCheckout();
        }
        $cart = $rawCart->getCart($store, true, true);

        return $this->render('store/theme1/index.html.twig', array(
            'current_user'  => $currentUser,
            'current_store' => $store,
            'product_list'  => $productList,
            'cart'          => $cart,
            'language'      => $userLanguage,
            'currency'      => $userCurrency,
        ));
    }

    public function storeProductAction()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        //////////// TEST IF STORE EXISTS ////////////
        /** @var null|\App\Store\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(array('url' => $this->parameters['store']));
        if (is_null($store)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF STORE EXISTS ////////////

        //////////// TEST IF PRODUCT EXISTS ////////////
        /** @var null|\App\Store\Entity\Product $product */
        $product = $em->getRepository(Product::class)->findOneBy(array('id' => $this->parameters['product']));
        if (is_null($product)) {
            return $this->render('error/error404.html.twig');
        }
        //////////// END TEST IF PRODUCT EXISTS ////////////

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $userLanguage = !is_null($request->query->get('lang')) ? $request->query->get('lang') : 'en';
        $userCurrency = !is_null($request->query->get('currency')) ? $request->query->get('currency') : 'USD';

        // Add data
        $productData = $product->toArray();

        $names = $product->getNames();
        $productData['name'] = (isset($names[$userLanguage]) ? $names[$userLanguage] : (isset($names['en']) ? $names['en'] : null));

        $descriptions = $product->getDescriptions();
        $productData['description'] = (isset($descriptions[$userLanguage]) ? $descriptions[$userLanguage] : (isset($descriptions['en']) ? $descriptions['en'] : null));

        $prices = $product->getPrices();
        $productData['price'] = (isset($prices[$userCurrency]) ? $prices[$userCurrency] : (isset($prices['USD']) ? $prices['USD'] : null));

        $salePrices = $product->getSalePrices();
        $productData['price_sale'] = (isset($salePrices[$userCurrency]) ? $salePrices[$userCurrency] : (isset($salePrices['USD']) ? $salePrices['USD'] : null));

        $productData['in_sale'] = !is_null($productData['price_sale']) ? true : false;


        $addToCartForm = $this->createForm(AddToCartType::class, null, array('product' => $product));
        $addCommentForm = $this->createForm(AddCommentType::class, null, array('product' => $product));

        // Add product to cart
        $addToCartForm->handleRequest($request);
        if ($addToCartForm->isSubmitted() && $addToCartForm->isValid()) {
            $formData = $addToCartForm->getData();
            // TODO: As product_count is TextType, we have to convert it to a Int
            $formData['product_count'] = intval($formData['product_count']);

            if (LOGGED_IN) {
                // Is registered user
                if (StoreCheckout::cartExistsForUser($currentUser)) {
                    // Cart exists
                    $cart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
                } else {
                    // Create new cart
                    $cart = new StoreCheckout(null, true, $currentUser);
                }
            } else {
                // Is a guest
                $cart = new StoreCheckout();
            }
            $cart->addToCart($store, $product, $formData['product_count']);

            $this->addFlash('product_added', 'Your product was added to the cart');
        }

        // Add a review
        $request = $this->getRequest();
        $addCommentForm->handleRequest($request);
        if ($addCommentForm->isSubmitted() && $addCommentForm->isValid()) {
            $formData = $addCommentForm->getData();

            $stars = $formData['rating'];
            $comment = $formData['comment'];

            if ($stars > 5) {
                $stars = 5;
            }

            $rating = new ProductRating();
            $rating
                ->setProduct($product)
                ->setUser($currentUser)
                ->setRating($stars)
                ->setComment($comment)
                ->setCreatedOn(new \DateTime())
                ->setUpdatedOn(new \DateTime());
            $em->persist($rating);

            // Update rating count
            $rating = $product->getRatingCount();
            $rating = $rating + 1;
            $product->setRatingCount($rating);

            // Update stars
            $totalStars = 0;
            $count = 0;
            foreach ($product->getRatings() as $item) {
                $totalStars += $item->getRating();
                $count++;
            }
            $totalStars += $stars;
            $count++;

            $average = round(($totalStars / $count) * 2) / 2;
            $product->setRatingAverage($average);
            $em->flush();
        }

        /** @var \App\Store\Entity\ProductRating[] $comments */
        $comments = $em->getRepository(ProductRating::class)->findBy(array('product' => $product), array('updated_on' => 'DESC'));

        // Shopping cart widget
        if (LOGGED_IN) {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $rawCart = new StoreCheckout();
        }
        $cart = $rawCart->getCart($store, true, true);

        return $this->render('store/theme1/product.html.twig', array(
            'current_user'     => $currentUser,
            'current_store'    => $store,
            'current_product'  => $productData,
            'comments'         => $comments,
            'add_to_cart_form' => $addToCartForm->createView(),
            'add_comment_form' => $addCommentForm->createView(),
            'cart'             => $cart,
            'language'         => $userLanguage,
            'currency'         => $userCurrency,
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

        $userLanguage = !is_null($request->query->get('lang')) ? $request->query->get('lang') : 'en';
        $userCurrency = !is_null($request->query->get('currency')) ? $request->query->get('currency') : 'USD';

        if (LOGGED_IN) {
            $rawCart = new StoreCheckout(StoreCheckout::getCartIdFromUser($currentUser), true, $currentUser);
        } else {
            $rawCart = new StoreCheckout();
        }
        $cart = $rawCart->getCart($store, true, false);

        $checkoutForm = $this->createForm(CheckoutType::class, null, array('user' => $currentUser));

        /** @var \App\Store\Entity\StorePaymentMethods[] $paymentTypes */
        $paymentTypes = $em->getRepository(StorePaymentMethods::class)->findBy(array('store' => $store));

        $payment = null;
        if (count($paymentTypes) > 0) {
            foreach ($paymentTypes as $type) {
                if ($type->getId() != $store->getActivePaymentMethod()) {
                    continue;
                }
                $payment = array();
                $payment['method'] = $type;
                if ($type->getType() == StorePaymentMethods::TYPE_BRAINTREE_PRODUCTION) {
                    \Braintree_Configuration::environment('production');
                    \Braintree_Configuration::merchantId($type->getData()['merchant_id']);
                    \Braintree_Configuration::publicKey($type->getData()['public_key']);
                    \Braintree_Configuration::privateKey($type->getData()['private_key']);
                    $payment['client_token'] = \Braintree_ClientToken::generate();
                } elseif ($type->getType() == StorePaymentMethods::TYPE_BRAINTREE_SANDBOX) {
                    \Braintree_Configuration::environment('sandbox');
                    \Braintree_Configuration::merchantId($type->getData()['merchant_id']);
                    \Braintree_Configuration::publicKey($type->getData()['public_key']);
                    \Braintree_Configuration::privateKey($type->getData()['private_key']);
                    $payment['client_token'] = \Braintree_ClientToken::generate();
                }
            }
        }

        $checkoutForm->handleRequest($request);
        if ($checkoutForm->isSubmitted() && $checkoutForm->isValid()) {
            $formData = $checkoutForm->getData();

            $nonceFromTheClient = $request->request->get('payment_method_nonce');

            $productUnavailable = array();
            $newProductsStock = array();
            foreach ($cart as $key => $productInfo) {
                /** @var \App\Store\Entity\Product $product */
                $product = $em->getRepository(Product::class)->findOneBy(array('id' => $productInfo['id']));

                if ($product->getStock() >= $productInfo['in_cart']) {
                    $newProductsStock[$product->getId()] = $product->getStock() - $productInfo['in_cart'];
                } else {
                    $productData = $product->toArray();
                    $productData['count'] = $productInfo['in_cart'];
                    $productUnavailable[$key] = $productData;
                }
            }

            if (count($productUnavailable) > 0) {
                foreach ($productUnavailable as $key => $item) {
                    $this->addFlash('products_unavailable', $item['name'].' has only '.$item['stock_available'].' left! You wanted '.$item['count']);
                }
            } else {

                $cart2 = $rawCart->getCart($store, true, true);
                $result = \Braintree_Transaction::sale(array(
                    'amount'             => $cart2['system']['total_price'],
                    'paymentMethodNonce' => $nonceFromTheClient,
                    'options'            => array(
                        'submitForSettlement' => true,
                    ),
                ));

                if ($result->success === true) {
                    $message = (new Swift_Message());
                    $imgDir1 = $message->embed(Swift_Image::fromPath(\Kernel::getIntent()->getRootDir().'/web/img/logo-long.png'));
                    $message->setSubject('Order confirmation')
                        ->setFrom(array($store->getEmail() => $store->getName()))
                        ->setTo(array(trim($formData['email']) => trim($formData['name'])))
                        ->setBcc(array($store->getEmail()))
                        ->setReplyTo(array($store->getOwner()->getEmail() => $store->getName()))
                        ->setBody($this->renderView('store/mail/order-confirmation.html.twig', array(
                            'current_store' => $store,
                            'order_form'    => $formData,
                            'header_image'  => $imgDir1,
                            'ordered_time'  => time(),
                            'cart'          => $cart,
                        )), 'text/html');

                    /** @var \Swift_Mailer $mailer */
                    $mailer = $this->get('mailer');
                    $mailSent = $mailer->send($message);

                    if ($mailSent) {
                        // TODO: Integrate shipping method into the form
                        $formData['delivery_type'] = $request->request->get('shipping');
                        $rawCart->makeOrder($store->getId(), $formData);
                        $rawCart->clearCart();
                        $cart = $rawCart->getCart($store, true, true);

                        $this->addFlash('order_sent', 'We saved your order in our system, and sent you a confirmation. We will deliver you the products as soon as possible.');
                    } else {
                        $this->addFlash('order_not_sent', 'Your order was not sent. Try again!');
                    }
                } else {
                    $this->addFlash('order_not_sent', 'It looks like, we couldn\'t create a transaction with your given credit card information. Try again!');
                }
            }
        }

        /** @var \App\Store\Entity\DeliveryType[] $deliveryType */
        $deliveryType = $em->getRepository(DeliveryType::class)->findBy(array('store' => $store));


        return $this->render('store/theme1/checkout.html.twig', array(
            'current_user'   => $currentUser,
            'current_store'  => $store,
            'checkout_form'  => $checkoutForm->createView(),
            'delivery_types' => $deliveryType,
            'cart'           => $cart,
            'payment'        => $payment,
            'currency'       => $userCurrency,
            'language'       => $userLanguage,
        ));
    }

    public function storeDoCheckVoucherAction()
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

        /** @var \App\Store\Entity\Voucher $voucher */
        $voucher = $em->getRepository(Voucher::class)->findOneBy(array('store' => $store, 'code' => $request->query->get('code')));

        if (is_null($voucher)) {
            $result = new \SimpleXMLElement('<root></root>');
            $result->addChild('result', 'invalid');
        } else {
            $result = new \SimpleXMLElement('<root></root>');
            $result->addChild('result', 'valid');
            $result->addChild('type', $voucher->getType());
            $result->addChild('amount', $voucher->getAmount());
        }
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent($result->asXML());

        return $response;
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
            $checkout = new StoreCheckout();
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
            $checkout = new StoreCheckout();
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
        $params['user_id'] = USER_ID;
        $params['current_user'] = $currentUser;
        $params['current_store'] = $store;
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
            $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
            $url = $this->generateUrl('app_store_store_admin', array('store' => $store->getUrl(), 'page' => $aMenuInfo['href']));
            $params['view_navigation'] .= '<li class="nav-item '.$selected.'" data-toggle="tooltip" data-placement="right" title="'.$aMenuInfo['title'].'">
                    <a class="nav-link" href="'.$url.'">
                        <i class="'.$aMenuInfo['icon'].'"></i>
                        <span class="nav-link-text">'.$aMenuInfo['title'].'</span>
                    </a>
                </li>';

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
            $params['view_navigation'] .= '<li class="nav-item" data-toggle="tooltip" data-placement="right" title="Components">
                    <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapse_'.$aGroupInfo['id'].'" data-parent="#menu">
                        <i class="'.$aGroupInfo['icon'].'"></i>
                        <span class="nav-link-text">'.$aGroupInfo['title'].'</span>
                    </a>
                    <ul class="sidenav-second-level collapse" id="collapse_'.$aGroupInfo['id'].'">';

            foreach (StoreAcp::getAllMenus($aGroupInfo['id']) as $sMenu => $aMenuInfo) {
                $selected = ($this->parameters['page'] === $aMenuInfo['href'] ? 'active' : '');
                $url = $this->generateUrl('app_store_store_admin', array('store' => $store->getUrl(), 'page' => $aMenuInfo['href']));
                $params['view_navigation'] .= '<li class="nav-item '.$selected.'" data-toggle="tooltip" data-placement="right" title="'.strip_tags($aMenuInfo['title']).'">
                    <a class="nav-link" href="'.$url.'">
                        <i class="'.$aMenuInfo['icon'].'"></i>
                        <span class="nav-link-text">'.$aMenuInfo['title'].'</span>
                    </a>
                </li>';
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


        $response = call_user_func($view, $this);
        if (is_string($response)) {
            $params['view_body'] = $response;
        }

        return $this->render('store/theme_admin1/panel.html.twig', $params);
    }
}
