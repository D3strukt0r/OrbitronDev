<?php

use App\Account\AccountAcp;

if (!isset($indirectly)) {
    AccountAcp::addGroup(array(
        'parent' => 'root',
        'id'     => 'security',
        'title'  => _('Security'),
    ));

    AccountAcp::addMenu(array(
        'parent' => 'security',
        'id'     => 'inactivity',
        'title'  => _('Inactivity'),
        'href'   => 'inactivity',
        'screen' => 'acp_html_inactivity',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'security',
        'id'     => 'log',
        'title'  => _('Login log'),
        'href'   => 'login-log',
        'screen' => 'acp_html_login_log',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'security',
        'id'     => 'delete',
        'title'  => sprintf(_('%sDelete Account%s'), '<b><span class="text-danger">', '</span></b>'),
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
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_delete_account($twig, $controller)
{
    return $twig->render('account/panel/delete-account.html.twig');
}