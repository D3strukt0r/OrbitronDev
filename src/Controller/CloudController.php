<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\Entity\User;
use elFinder;
use elFinderConnector;
use Symfony\Component\HttpFoundation\Response;

class CloudController extends \Controller
{
    public function indexAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }

        return $this->redirectToRoute('app_cloud_files');
    }

    // TODO: Add possibility to publish a website by crating a "public_html" folder in the root
    public function filesAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        return $this->render('cloud/files.html.twig', array(
            'current_user' => $currentUser,
        ));
    }

    public function showRawFileAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $extensionToMime = array(
            'pdf'  => 'application/pdf',
            'zip'  => 'application/zip',
            'gif'  => 'image/gif',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'css'  => 'text/css',
            'html' => 'text/html',
            'js'   => 'text/javascript',
            'txt'  => 'text/plain',
            'xml'  => 'text/xml',
        );

        $fileDir = $this->get('kernel')->getRootDir().'/data/cloud/storage/'.$currentUser->getId().'/'.$this->parameters['file'];
        $fileInfo = pathinfo($fileDir);

        $response = new Response();
        $response->setContent(file_get_contents($fileDir));
        $response->headers->set('Content-Type', $extensionToMime[$fileInfo['extension']]);

        return $response;
    }

    public function shareAction()
    {
        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }

        return $this->render('cloud/share.html.twig');
    }

    public function connectorAction()
    {
        /** @var \Kernel $kernel */
        $kernel = $this->get('kernel');

        if (is_null(AccountHelper::updateSession()) || !LOGGED_IN) {
            header('Content-Type: application/json');
            echo '{}';
            exit;
        }
        if (USER_ID != $_GET['user_id']) {
            header('Content-Type: application/json');
            echo '{}';
            exit;
        }

        $functionsFile = $kernel->getRootDir().'/src/App/Cloud/functions.php';
        if (file_exists($functionsFile)) {
            include_once $functionsFile;
        }

        // ===============================================

        // Enable FTP connector netmount
        // elFinder::$netDrivers['ftp'] = 'FTP';

        // ===============================================

        // // Required for Dropbox network mount
        // // Installation by composer
        // // `composer require kunalvarma05/dropbox-php-sdk`
        // // Enable network mount
        // elFinder::$netDrivers['dropbox2'] = 'Dropbox2';
        // // Dropbox2 Netmount driver need next two settings. You can get at https://www.dropbox.com/developers/apps
        // // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=dropbox2&host=1"
        // define('ELFINDER_DROPBOX_APPKEY',    '');
        // define('ELFINDER_DROPBOX_APPSECRET', '');
        // ===============================================

        // // Required for Google Drive network mount
        // // Installation by composer
        // // `composer require google/apiclient:^2.0`
        // // Enable network mount
        // elFinder::$netDrivers['googledrive'] = 'GoogleDrive';
        // // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
        // // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
        // define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
        // define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
        // // Required case of without composer
        // define('ELFINDER_GOOGLEDRIVE_GOOGLEAPICLIENT', '/path/to/google-api-php-client/vendor/autoload.php');
        // ===============================================

        // // Required for Google Drive network mount with Flysystem
        // // Installation by composer
        // // `composer require nao-pon/flysystem-google-drive:~1.1 nao-pon/elfinder-flysystem-driver-ext`
        // // Enable network mount
        // elFinder::$netDrivers['googledrive'] = 'FlysystemGoogleDriveNetmount';
        // // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
        // // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
        // define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
        // define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
        // ===============================================

        // // Required for One Drive network mount
        // //  * cURL PHP extension required
        // //  * HTTP server PATH_INFO supports required
        // // Enable network mount
        // elFinder::$netDrivers['onedrive'] = 'OneDrive';
        // // GoogleDrive Netmount driver need next two settings. You can get at https://dev.onedrive.com
        // // AND reuire regist redirect url to "YOUR_CONNECTOR_URL/netmount/onedrive/1"
        // define('ELFINDER_ONEDRIVE_CLIENTID',     '');
        // define('ELFINDER_ONEDRIVE_CLIENTSECRET', '');
        // ===============================================

        // // Required for Box network mount
        // //  * cURL PHP extension required
        // // Enable network mount
        // elFinder::$netDrivers['box'] = 'Box';
        // // Box Netmount driver need next two settings. You can get at https://developer.box.com
        // // AND reuire regist redirect url to "YOUR_CONNECTOR_URL"
        // define('ELFINDER_BOX_CLIENTID',     '');
        // define('ELFINDER_BOX_CLIENTSECRET', '');
        // ===============================================

        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        $opts = array(
            // 'debug' => true,
            'roots' => array(

                // Items volume
                array(
                    'alias'         => 'Home',
                    'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path'          => realpath($kernel->getRootDir().'/data/cloud/storage/'.$_GET['user_id']), // path to files (REQUIRED)
                    'URL'           => '/files/',                   // URL to files (REQUIRED)
                    'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                    'uploadAllow'   => array('image', 'text/plain'),// Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
                ),

                // Trash volume
                array(
                    'id'            => '1',
                    'driver'        => 'Trash',
                    'path'          => realpath($kernel->getRootDir().'/data/cloud/storage/'.$_GET['user_id'].'/.trash'),
                    'tmbURL'        => '../data/cloud/storage/'.$_GET['user_id'].'/.trash/.tmb',
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => array('all'),                // Recommend the same settings as the original volume that uses the trash
                    'uploadAllow'   => array('image', 'text/plain'),// Same as above
                    'uploadOrder'   => array('deny', 'allow'),      // Same as above
                    'accessControl' => 'access',                    // Same as above
                )
            )
        );

        // Create directories
        if (!file_exists($kernel->getRootDir().'/data/cloud/storage/'.$_GET['user_id'])) {
            mkdir($kernel->getRootDir().'/data/cloud/storage/'.$_GET['user_id'], 0777, true);
        }
        if (!file_exists($kernel->getRootDir().'/data/cloud/storage/'.$_GET['user_id'].'/.trash/')) {
            mkdir($kernel->getRootDir().'/data/cloud/storage/'.$_GET['user_id'].'/.trash/', 0777, true);
        }

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
        exit;
    }
}
