<?php

use App\Account\AccountAcp;
use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Account\Form\DeleteAccountType;

if (!isset($indirectly)) {
    AccountAcp::addGroup(array(
        'parent' => 'root',
        'id'     => 'security',
        'title'  => 'Security',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'security',
        'id'     => 'inactivity',
        'title'  => 'Inactivity',
        'href'   => 'inactivity',
        'screen' => 'acp_html_inactivity',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'security',
        'id'     => 'log',
        'title'  => 'Login log',
        'href'   => 'login-log',
        'screen' => 'acp_html_login_log',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'security',
        'id'     => 'delete',
        'title'  => sprintf('%sDelete Account%s', '<b><span class="text-danger">', '</span></b>'),
        'href'   => 'delete-account',
        'screen' => 'acp_html_delete_account',
    ));
}

function acp_html_inactivity()
{

}

function acp_html_login_log()
{

}

/**
 * @param \Twig_Environment             $twig
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_delete_account($twig, $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    $deleteAccountForm = $controller->createForm(DeleteAccountType::class);

    $request = $controller->getRequest();
    $deleteAccountForm->handleRequest($request);
    if ($deleteAccountForm->isSubmitted() && $deleteAccountForm->isValid()) {
        //AccountHelper::logout();
        AccountHelper::removeUser($currentUser);
    }
    return $twig->render('account/panel/delete-account.html.twig', array(
        'delete_account_form' => $deleteAccountForm->createView(),
    ));
}
