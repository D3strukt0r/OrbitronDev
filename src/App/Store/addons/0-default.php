<?php

use App\Store\StoreAcp;

StoreAcp::addGroup(array(
    'parent'  => 'root',
    'id'      => 'null',
    'title'   => 'THIS SHOULD NOT BE HERE',
    'display' => false,
));

/**
 * @param \Controller\StoreController $controller
 *
 * @return string
 */
function acp_not_found($controller)
{
    return $controller->renderView('store/theme_admin1/not-found.html.twig');
}
