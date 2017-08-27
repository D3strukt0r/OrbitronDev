<?php

namespace Controller;

use App\Account\Account;
use App\Account\UserInfo;
use App\Blog\Blog;
use App\Store\Store;
use Controller;
use Form\RecaptchaType;
use PDO;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class StoreController extends Controller
{

    public function indexAction()
    {
        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);

        $storeList = Store::getStoreList();
        foreach ($storeList as $key => $store) {
            $user                        = new UserInfo($store['owner_id']);
            $storeList[$key]['username'] = $user->getFromUser('username');
        }

        return $this->render('store/list-stores.html.twig', array(
            'current_user' => $currentUser->aUser,
            'store_list'  => $storeList,
        ));
    }

    public function newStoreAction()
    {
        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);

        $createStoreForm = $this->createFormBuilder()
            ->add('name', TextType::class, array(
                'label'       => 'Store name',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a name')),
                ),
            ))
            ->add('url', TextType::class, array(
                'label'       => 'Store url',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a url')),
                ),
            ))
            ->add('recaptcha', RecaptchaType::class, array(
                'private_key'    => '6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll',
                'public_key'     => '6Ldec_4SAAAAAJ_TnvICnltNqgNaBPCbXp-wN48B',
                'recaptcha_ajax' => false,
                'attr'           => array(
                    'options' => array(
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal',
                        'defer' => true,
                        'async' => true,
                    ),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Create',
            ))
            ->getForm();


        /** @var Request $request */
        $request = $this->get('kernel')->getRequest();
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
                } elseif (Store::urlExists($storeUrl)) {
                    $errorMessages[] = '';
                    $createStoreForm->get('url')->addError(new FormError('This url is already in use'));
                }

                if (!count($errorMessages)) {
                    /** @var \PDOStatement $addStore */
                    $addStore = $this->get('database')->prepare('INSERT INTO `stores`(`name`,`url`,`owner_id`) VALUES (:name,:url,:user_id)');
                    $storeAdded = $addStore->execute(array(
                        ':name'    => $storeName,
                        ':url'     => $storeUrl,
                        ':user_id' => USER_ID,
                    ));

                    if ($storeAdded) {
                        /** @var \PDOStatement $getStore */
                        $getStore = $this->get('database')->prepare('SELECT `url` FROM `stores` WHERE `url`=:url LIMIT 1');
                        $getStore->execute(array(
                            ':url' => $storeUrl,
                        ));
                        $storeData = $getStore->fetchAll(PDO::FETCH_ASSOC);

                        return $this->redirectToRoute('app_store_store_index', array('store' => $storeData[0]['url']));
                    } else {
                        $errorMessage = $addStore->errorInfo();
                        $createStoreForm->addError(new FormError('We could not create your forum. (ERROR: '.$errorMessage[2].')'));
                    }
                }
            }
        }

        return $this->render('store/create-new-store.html.twig', array(
            'current_user'      => $currentUser->aUser,
            'create_store_form' => $createStoreForm->createView(),
        ));
    }

    public function storeIndexAction()
    {
        // Does the store even exist?
        if (!Store::urlExists($this->parameters['store'])) {
            return $this->render('error/error404.html.twig');
        }

        Account::updateSession();
        $currentUser = new UserInfo(USER_ID);

        $storeId = Store::url2Id($this->parameters['store']);
        $store   = new Store($storeId);

        return $this->render('store/theme1/index.html.twig', array(
            'current_user'  => $currentUser->aUser,
            'current_store' => $store->storeData,
        ));
    }
}
