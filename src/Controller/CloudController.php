<?php

namespace Controller;

use App\Account\AccountHelper;
use App\Account\Entity\User;
use Controller;
use Symfony\Component\HttpFoundation\Response;

class CloudController extends Controller
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

        $fileDir = $this->get('kernel')->getRootDir().'/app/data/cloud/storage/'.$currentUser->getId().'/'.$this->parameters['file'];
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
}
