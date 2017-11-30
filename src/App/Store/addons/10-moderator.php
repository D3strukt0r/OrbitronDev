<?php

use App\Store\StoreAcp;

StoreAcp::addGroup(array(
    'parent' => 'root',
    'id'     => 'moderator',
    'title'  => 'Moderator',
));

StoreAcp::addMenu(array(
    'parent' => 'moderator',
    'id'     => 'advertisement',
    'title'  => 'Advertisement',
    'href'   => 'advertisement',
    'screen' => 'acp_html_advertisement',
));

StoreAcp::addMenu(array(
    'parent' => 'moderator',
    'id'     => 'mod_tools',
    'title'  => 'Mod tools',
    'href'   => 'mod-tools',
    'screen' => 'acp_html_mod_tools',
));

/**
 * @param \Controller $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_advertisement(Controller $controller)
{
    return $controller->renderView('store/theme_admin1/advertisement.html.twig');
}

/**
 * @param \Controller $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_mod_tools(Controller $controller)
{
    return $controller->renderView('store/theme_admin1/mod_tools.html.twig');
}
