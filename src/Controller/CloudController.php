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
            return $this->redirectToRoute('app_account_login', ['redir' => $this->getRequest()->getUri()]);
        }

        return $this->redirectToRoute('app_cloud_files');
    }

    public function filesAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', ['redir' => $this->getRequest()->getUri()]);
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        return $this->render('cloud/files.html.twig', [
            'current_user' => $currentUser,
        ]);
    }

    public function showRawFileAction()
    {
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession())) {
            return $this->redirectToRoute('app_account_logout');
        }
        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', ['redir' => $this->getRequest()->getUri()]);
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        $extensionToMime = [
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
        ];

        $fileDir = $this->get('kernel')->getRootDir().'/data/cloud/storage/'.$currentUser->getId().'/'.$this->parameters['file'];
        $fileInfo = pathinfo($fileDir);

        $response = new Response();
        $response->setContent(file_get_contents($fileDir));
        $response->headers->set('Content-Type', $extensionToMime[$fileInfo['extension']]);

        return $response;
    }

    public function connectorAction()
    {
        /** @var \Kernel $kernel */
        $kernel = $this->get('kernel');
        $em = $this->getEntityManager();

        if (is_null(AccountHelper::updateSession()) || !LOGGED_IN) {
            return $this->json([]);
        }
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $em->find(User::class, USER_ID);

        // ===============================================
        // Enable FTP connector netmount
        elFinder::$netDrivers['ftp'] = 'FTP';
        // ===============================================

        // Create directories
        if (!file_exists($kernel->getRootDir().'/data/cloud/storage/'.$currentUser->getId())) {
            mkdir($kernel->getRootDir().'/data/cloud/storage/'.$currentUser->getId(), 0777, true);
        }
        if (!file_exists($kernel->getRootDir().'/data/cloud/storage/'.$currentUser->getId().'/.trash/')) {
            mkdir($kernel->getRootDir().'/data/cloud/storage/'.$currentUser->getId().'/.trash/', 0777, true);
        }

        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        /**
         * Simple function to demonstrate how to control file access using "accessControl" callback.
         * This method will disable accessing files/folders starting from '.' (dot)
         *
         * @param  string    $attr    attribute name (read|write|locked|hidden)
         * @param  string    $path    absolute file path
         * @param  string    $data    value of volume option `accessControlData`
         * @param  object    $volume  elFinder volume driver object
         * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
         * @param  string    $relpath file path relative to volume root directory started with directory separator
         * @return bool|null
         **/
        $accessFunction = function ($attr, $path, $data, $volume, $isDir, $relpath) {
            $basename = basename($path);
            return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
                   && strlen($relpath) !== 1           // but with out volume root
                ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
                :  null;                                 // else elFinder decide it itself
        };
        $opts = [
            // 'debug' => true,
            'roots' => [

                // Items volume
                [
                    'alias'         => 'Home',
                    'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path'          => realpath($kernel->getRootDir().'/data/cloud/storage/'.$currentUser->getId()), // path to files (REQUIRED)
                    'URL'           => '/files/',                   // URL to files (REQUIRED)
                    'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => ['all'],                     // All Mimetypes not allowed to upload
                    'uploadAllow'   => ['image', 'text/plain'],     // Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder'   => ['deny', 'allow'],           // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => $accessFunction,             // disable and hide dot starting files (OPTIONAL)
                ],

                // Trash volume
                [
                    'id'            => '1',
                    'driver'        => 'Trash',
                    'path'          => realpath($kernel->getRootDir().'/data/cloud/storage/'.$currentUser->getId().'/.trash'),
                    'tmbURL'        => '../data/cloud/storage/'.$currentUser->getId().'/.trash/.tmb',
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => ['all'],                     // Recommend the same settings as the original volume that uses the trash
                    'uploadAllow'   => ['image', 'text/plain'],     // Same as above
                    'uploadOrder'   => ['deny', 'allow'],           // Same as above
                    'accessControl' => $accessFunction,             // Same as above
                ]
            ]
        ];

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }
}
