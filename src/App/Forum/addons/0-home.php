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
 * @param \Controller $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_home(Controller $controller)
{
    return $controller->renderView('forum/theme_admin1/home.html.twig');
}
