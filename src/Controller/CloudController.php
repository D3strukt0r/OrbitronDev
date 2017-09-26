<?php

namespace Controller;

use App\Account\Account;
use App\Account\UserInfo;
use Controller;

class CloudController extends Controller
{
    public function indexAction()
    {
        Account::updateSession();

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }

        return $this->redirectToRoute('app_cloud_files');
    }

    // TODO: Add possibility to publish a website by crating a "public_html" folder in the root
    public function filesAction()
    {
        Account::updateSession();

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }
        $currentUser = new UserInfo(USER_ID);

        return $this->render('cloud/files.html.twig', array(
            'current_user'      => $currentUser->aUser,
        ));
    }

    public function showRawFileAction()
    {
        Account::updateSession();

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }
        $currentUser = new UserInfo(USER_ID);

        $extenstionToMime = array(
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'css' => 'text/css',
            'html' => 'text/html',
            'js' => 'text/javascript',
            'txt' => 'text/plain',
            'xml' => 'text/xml',
        );

        $fileDir = $this->get('kernel')->getRootDir().'/app/data/cloud/storage/'.$currentUser->getFromUser('user_id').'/'.$this->parameters['file'];
        $fileInfo = pathinfo($fileDir);
        header('Content-Type: '.$extenstionToMime[$fileInfo['extension']]);
        echo file_get_contents($fileDir);
        exit;
    }

    public function shareAction()
    {
        Account::updateSession();

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }

        return $this->render('cloud/share.html.twig');
    }
}
