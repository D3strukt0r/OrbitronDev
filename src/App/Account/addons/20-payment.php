<?php

use App\Account\AccountAcp;
use App\Account\Entity\User;

if (!isset($indirectly)) {
    AccountAcp::addGroup(array(
        'parent' => 'root',
        'id'     => 'payment',
        'title'  => 'Billing',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'payment',
        'id'     => 'buy_credits',
        'title'  => 'Buy credits',
        'href'   => 'buy-credits',
        'screen' => 'acp_html_buy_credits',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'payment',
        'id'     => 'plans',
        'title'  => 'Plans',
        'href'   => 'plans',
        'screen' => 'acp_html_plans',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'payment',
        'id'     => 'payment_methods',
        'title'  => 'Payment methods',
        'href'   => 'payment',
        'screen' => 'acp_html_payment',
    ));
}

/**
 * @param \Controller $controller
 *
 * @return string
 */
function acp_html_buy_credits(Controller $controller)
{
    return $controller->renderView('account/panel/buy-credits.html.twig');
}

/**
 * @param \Controller $controller
 *
 * @return string
 */
function acp_html_plans(Controller $controller)
{
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    return $controller->renderView('account/panel/plans.html.twig', array(
        'current_user' => $currentUser,
    ));
}

function acp_html_payment()
{
    return '';
}
