<?php

use App\Account\AccountAcp;

AccountAcp::addGroup(array(
    'parent'  => 'root',
    'id'      => 'null',
    'title'   => null,
    'display' => null,
));

/**
 * @param \Twig_Environment $twig
 *
 * @return string
 */
function acp_not_found($twig)
{
    return $twig->render('account/panel/not-found.html.twig');
}
