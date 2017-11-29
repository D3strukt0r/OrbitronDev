<?php

use App\Forum\ForumAcp;

ForumAcp::addMenu(array(
    'parent' => 'root',
    'id'     => 'home',
    'title'  => 'Dashboard',
    'href'   => 'home',
    'screen' => 'acp_html_home',
));

/**
 * @param \Controller\ForumController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_home($controller)
{
    return $controller->renderView('forum/theme_admin1/home.html.twig');
}
