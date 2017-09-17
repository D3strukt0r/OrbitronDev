<?php

use App\Store\StoreAcp;

StoreAcp::addMenu(array(
    'parent' => 'root',
    'id'     => 'home',
    'title'  => 'Dashboard',
    'href'   => 'home',
    'screen' => 'acp_html_home',
));

/**
 * @param \Twig_Environment $twig
 *
 * @return string
 * @throws Exception
 */
function acp_html_home($twig)
{
    return $twig->render('store/theme_admin1/home.html.twig');
}
