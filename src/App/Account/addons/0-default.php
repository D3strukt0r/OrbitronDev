<?php

use App\Account\AccountAcp;

AccountAcp::addGroup(array(
    'parent'  => 'root',
    'id'      => 'null',
    'title'   => null,
    'display' => null,
));

/**
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_not_found($controller)
{
    return $controller->renderView('account/panel/not-found.html.twig');
}
