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
        $request = Kernel::getIntent()->getRequest();

        $userId = (int)$request->query->get('user_id');

        /** @var null|\App\Account\Entity\User $selectedUser */
        $selectedUser = Kernel::getIntent()->getEntityManager()->find(User::class, $userId);

        $width = !is_null($request->query->get('width')) ? (int)$request->query->get('width') : 1000;
        $height = !is_null($request->query->get('height')) ? (int)$request->query->get('height') : 1000;

        $rootPictureDir = Kernel::getIntent()->getRootDir().'/public/app/account/profile_pictures/';

        if (!is_null($selectedUser)) {
            if (!is_null($selectedUser->getProfile()->getPicture()) && file_exists($filename = $rootPictureDir.$selectedUser->getProfile()->getPicture())) {
                $oImage = new SimpleImage($filename);
                $oImage->resize($width, $height);
                $oImage->output();
                exit;
            } else {
                $oImage = new SimpleImage(Kernel::getIntent()->getRootDir().'/public/assets/img/user.jpg');
                $oImage->resize($width, $height);
                $oImage->output();
                exit;
            }
        } else {
            return self::__send_error_message('User not found');
        }

        return null;
    }

    public static function updateProfilePic()
    {
        $request = Kernel::getIntent()->getRequest();

        /** @var \App\Account\Entity\User $current_user */
        $current_user = Kernel::getIntent()->getEntityManager()->find(User::class, $request->query->get('user_id'));

        // Simple validation (max file size 2MB and only two allowed mime types)
        $validator = new \FileUpload\Validator\Simple('10M', array('image/png', 'image/jpg', 'image/jpeg'));

        $filenamegenerator = new \FileUpload\FileNameGenerator\Random();

        // Simple path resolver, where uploads will be put
        AccountHelper::buildPaths();
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
        list($files, $headers) = $fileupload->processAll();

        // Outputting it, for example like this
        foreach ($headers as $header => $value) {
            header($header.': '.$value);
        }


        if (isset($files[0]->error) && !is_string($files[0]->error)) {
            $current_user->getProfile()->setPicture($files[0]->getFileName());
            Kernel::getIntent()->getEntityManager()->flush();
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

    // TODO: Is the function "panel_pages" still needed?
    public static function panelPages()
    {
        $request = Kernel::getIntent()->getRequest();
        $page = $request->query->get('p');

        $view = new \App\Template\Template();

        $template = new \App\Template\TemplateLoad($page);

        \App\Account\AccountAcp::includeLibs();
        $functionForPage = str_replace('-', '_', $page);
        $functionName = 'acp_html_'.$functionForPage;
        $content = call_user_func($functionName);
        $template->setHtml($content);

        $view->addTemplate($template);

        echo $view;
    }
}
