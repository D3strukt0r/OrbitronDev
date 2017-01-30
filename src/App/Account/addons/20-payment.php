<?php

use App\Account\AccountAcp;
use App\Account\UserInfo;

if(!isset($indirectly)) {
	AccountAcp::addGroup(array(
		'parent' => 'root',
		'id'     => 'payment',
		'title'  => _('Billing')
	));

	AccountAcp::addMenu(array(
		'parent' => 'payment',
		'id'     => 'buy_credits',
		'title'  => _('Buy credits'),
		'href'   => 'buy-credits',
		'screen' => 'acp_html_buy_credits',
	));

	AccountAcp::addMenu(array(
		'parent' => 'payment',
		'id'     => 'plans',
		'title'  => _('Plans'),
		'href'   => 'plans',
		'screen' => 'acp_html_plans',
	));

	AccountAcp::addMenu(array(
		'parent' => 'payment',
		'id'     => 'payment_methods',
		'title'  => _('Payment methods'),
		'href'   => 'payment',
		'screen' => 'acp_html_payment',
	));
}

/**
 * @param \Twig_Environment             $twig
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_buy_credits($twig, $controller)
{
	return $twig->render('account/panel/buy-credits.html.twig');
}

/**
 * @param \Twig_Environment             $twig
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_plans($twig, $controller)
{
    $currentUser = new UserInfo(USER_ID);
    return $twig->render('account/panel/plans.html.twig', array(
        'current_user_sub' => $currentUser->aSubscription,
    ));
}

function acp_html_payment()
{
	
}
