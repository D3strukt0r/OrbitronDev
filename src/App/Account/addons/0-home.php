<?php

use App\Account\AccountAcp;
use App\Account\Entity\User;

AccountAcp::addMenu(array(
    'parent' => 'root',
    'id'     => 'home',
    'title'  => 'Overview',
    'href'   => 'home',
    'icon'   => 'fa fa-fw fa-home',
    'screen' => 'acp_html_home',
));

/**
 * @param \Controller $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_home(Controller $controller)
{
    /** @var \App\Account\Entity\User $user */
    $user = $controller->getEntityManager()->find(User::class, USER_ID);

    return $controller->renderView('admin/panel/home.html.twig', array(
        'current_user'    => $user,
        //'service_allowed' => in_array('web_service', $user->getSubscription()->getSubscription()->getPermissions()) ? true : false,
    ));
}
