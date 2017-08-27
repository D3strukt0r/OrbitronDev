<?php

namespace App\Account;

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

    public static function get_img()
    {
        $request = Kernel::$kernel->getRequest();

        $userId = (int)$request->query->get('user_id');
        $selectedUser = new UserInfo($userId);

        $width = !is_null($request->query->get('width')) ? (int)$request->query->get('width') : 1000;
        $height = !is_null($request->query->get('height')) ? (int)$request->query->get('height') : 1000;

        $rootPictureDir = Kernel::$kernel->getRootDir() . '/web/app/account/profile_pictures/';

        if (AccountTools::idExists($userId)) {
            if (!is_null($selectedUser->getFromProfile('profile_picture')) && file_exists($filename = $rootPictureDir . $selectedUser->getFromProfile('profile_picture'))) {
                $oImage = new SimpleImage($filename);
                $oImage->resize($width, $height);
                $oImage->output();
            } else {
                $oImage = new SimpleImage(Kernel::$kernel->getRootDir() . '/web/assets/img/user.jpg');
                $oImage->resize($width, $height);
                $oImage->output();
            }
        } else {
            return self::__send_error_message('User not found');
        }

        return null;
    }

    public static function update_profile_pic($parameters)
    {
        $current_user = new UserInfo($parameters['user_id']);

        // Simple validation (max file size 2MB and only two allowed mime types)
        $validator = new \FileUpload\Validator\Simple('10M', array('image/png', 'image/jpg', 'image/jpeg'));

        $filenamegenerator = new \FileUpload\FileNameGenerator\Random();

        // Simple path resolver, where uploads will be put
        Account::buildPaths();
        $pathresolver = new \FileUpload\PathResolver\Simple(Account::$publicDir . '/profile_pictures');

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
            header($header . ': ' . $value);
        }

        if (isset($files[0]->error) && !is_string($files[0]->error)) {
            $current_user->updateProfilePicture($files[0]->getFileName());
        }

        //return array('files' => $files);
        return array('files' => array('name' => $files[0]->getFileName()));
    }

    public static function upload_progress()
    {
        // Assuming default values for session.upload_progress.prefix
        // and session.upload_progress.name:
        $s = $_SESSION['upload_progress_' . intval($_GET['PHP_SESSION_UPLOAD_PROGRESS'])];
        $progress = array(
            'lengthComputable' => true,
            'loaded'           => $s['bytes_processed'],
            'total'            => $s['content_length'],
        );
        return $progress;
    }

    // TODO: Is the function "panel_pages" still needed?
    public static function panel_pages()
    {
        $page = $_GET['p'];

        $view = new \App\Template\Template();

        $template = new \App\Template\TemplateLoad($page);
        $template->setHtml(function($page) {
            \App\Account\AccountAcp::includeLibs();
            $functionForPage = str_replace('-', '_', $page);
            $functionName = 'acp_html_' . $functionForPage;
            call_user_func($functionName);
        });

        $view->addTemplate($template);

        echo $view;
    }
}