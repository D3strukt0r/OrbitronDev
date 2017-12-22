<?php

namespace App\Account;

use App\Account\Entity\User;
use Kernel;

class AccountApi
{
    /**
     * @param string $message
     *
     * @return array
     */
    private static function __send_error_message($message)
    {
        return array(
            'status'        => 'error',
            'error-message' => $message,
        );
    }

    public static function getImg()
    {
        $kernel = Kernel::getIntent();
        $request = $kernel->getRequest();
        $em = $kernel->getEntityManager();

        $userId = (int)$request->query->get('user_id');

        /** @var null|\App\Account\Entity\User $selectedUser */
        $selectedUser = $em->find(User::class, $userId);

        $width = !is_null($request->query->get('width')) ? (int)$request->query->get('width') : 1000;
        $height = !is_null($request->query->get('height')) ? (int)$request->query->get('height') : 1000;

        $rootPictureDir = $kernel->getRootDir().'/public/app/account/profile_pictures/';

        if (!is_null($selectedUser)) {
            if (!is_null($selectedUser->getProfile()->getPicture()) && file_exists($filename = $rootPictureDir.$selectedUser->getProfile()->getPicture())) {
                $oImage = new SimpleImage($filename);
                $oImage->resize($width, $height);
                $oImage->output();
                exit;
            } else {
                $oImage = new SimpleImage($kernel->getRootDir().'/public/img/user.jpg');
                $oImage->resize($width, $height);
                $oImage->output();
                exit;
            }
        } else {
            return self::__send_error_message('User not found');
        }
    }

    public static function updateProfilePic()
    {
        $kernel = Kernel::getIntent();
        $request = $kernel->getRequest();
        $em = $kernel->getEntityManager();

        /** @var \App\Account\Entity\User $current_user */
        $current_user = $em->find(User::class, $request->query->get('user_id'));

        // Simple validation (max file size 2MB and only two allowed mime types)
        $validator = new \FileUpload\Validator\Simple('10M', array('image/png', 'image/jpg', 'image/jpeg', 'image/gif'));

        $filenamegenerator = new \FileUpload\FileNameGenerator\Random();

        // Simple path resolver, where uploads will be put
        AccountHelper::buildPaths();
        if (!file_exists(AccountHelper::$publicDir.'/profile_pictures')) {
            mkdir(AccountHelper::$publicDir.'/profile_pictures', 0777, true);
        }
        $pathresolver = new \FileUpload\PathResolver\Simple(AccountHelper::$publicDir.'/profile_pictures');

        // The machine's filesystem
        $filesystem = new \FileUpload\FileSystem\Simple();

        // FileUploader itself
        $fileupload = new \FileUpload\FileUpload($_FILES['files'], $_SERVER);

        // Adding it all together. Note that you can use multiple validators or none at all
        $fileupload->setFileNameGenerator($filenamegenerator);
        $fileupload->setPathResolver($pathresolver);
        $fileupload->setFileSystem($filesystem);
        $fileupload->addValidator($validator);

        // Doing the deed
        /** @var \FileUpload\File[] $files */
        list($files, $headers) = $fileupload->processAll();

        // Outputting it, for example like this
        foreach ($headers as $header => $value) {
            header($header.': '.$value);
        }


        if (isset($files[0]->error) && !is_string($files[0]->error)) {
            // Remove old picture
            $oldPicture = realpath(AccountHelper::$publicDir.'/profile_pictures/'.$current_user->getProfile()->getPicture());
            if (is_writable($oldPicture)) {
                unlink($oldPicture);
            }

            // Update db with new picture
            $current_user->getProfile()->setPicture($files[0]->getFileName());
            $em->flush();
        }

        return array('files' => array('name' => $files[0]->getFileName()));
    }

    public static function uploadProgress()
    {
        // Assuming default values for session.upload_progress.prefix
        // and session.upload_progress.name:
        $request = Kernel::getIntent()->getRequest();
        $s = $request->getSession()->get('upload_progress_'.intval($request->query->get('PHP_SESSION_UPLOAD_PROGRESS')));
        $progress = array(
            'lengthComputable' => true,
            'loaded'           => $s['bytes_processed'],
            'total'            => $s['content_length'],
        );

        return $progress;
    }

    public static function panelPages($parameters, $controller)
    {
        $kernel = Kernel::getIntent();
        $request = $kernel->getRequest();
        AccountHelper::updateSession();
        $page = $request->query->get('p');

        AccountAcp::includeLibs();
        $functionForPage = str_replace('-', '_', $page);
        $functionName = 'acp_html_'.$functionForPage;
        $content = call_user_func($functionName, $controller);

        return $content;
    }
}
