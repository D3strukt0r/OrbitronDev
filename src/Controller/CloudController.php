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
    public function shareAction()
    {
        Account::updateSession();

        if (!LOGGED_IN) {
            return $this->redirectToRoute('app_account_login', array('redir' => $this->getRequest()->getUri()));
        }

        return $this->render('cloud/share.html.twig');
    }
}