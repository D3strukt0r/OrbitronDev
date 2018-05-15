<?php

use App\Account\AccountAcp;

AccountAcp::addGroup(array(
    'parent'  => 'root',
    'id'      => 'null',
    'title'   => null,
    'display' => null,
));

/**
 * @param \Controller $controller
 *
 * @return string
 */
function acp_not_found(Controller $controller)
{
    return $controller->renderView('admin/panel/not-found.html.twig');
}
