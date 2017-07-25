<?php

use App\Forum\ForumAcp;

ForumAcp::addGroup(array(
    'parent'  => 'root',
    'id'      => 'null',
    'title'   => 'THIS SHOULD NOT BE HERE',
    'display' => false,
));

/**
 * @param \Twig_Environment $twig
 *
 * @return string
 */
function acp_not_found($twig)
{
    return $twig->render('forum/theme_admin1/not-found.html.twig');
}
