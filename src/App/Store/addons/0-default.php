<?php

use App\Store\StoreAcp;

StoreAcp::addGroup(array(
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
    return $twig->render('store/theme_admin1/not-found.html.twig');
}
