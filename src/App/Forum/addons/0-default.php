<?php

use App\Forum\ForumAcp;

ForumAcp::addGroup(array(
    'parent'  => 'root',
    'id'      => 'null',
    'title'   => 'THIS SHOULD NOT BE HERE',
    'display' => false,
));

/**
 * @param \Controller $controller
 *
 * @return string
 */
function acp_not_found(Controller $controller)
{
    return $controller->renderView('forum/theme_admin1/not-found.html.twig');
}
