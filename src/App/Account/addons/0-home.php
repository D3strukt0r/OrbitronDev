<?php

use App\Account\AccountAcp;
use App\Account\Entity\User;
use App\Blog\BlogHelper;
use App\Forum\Forum;
use App\Store\Store;

AccountAcp::addMenu(array(
    'parent' => 'root',
    'id'     => 'home',
    'title'  => 'Overview',
    'href'   => 'home',
    'screen' => 'acp_html_home',
));

/**
 * @param \Twig_Environment             $twig
 * @param \Controller\AccountController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_home($twig, $controller)
{
    /** @var \App\Account\Entity\User $user */
    $user = $controller->getEntityManager()->find(User::class, USER_ID);

    return $twig->render('account/panel/home.html.twig', array(
        'current_user'    => $user,
        'service_allowed' => in_array('web_service', $user->getSubscription()->getSubscription()->getPermissions()) ? true : false,
        'blogs'           => BlogHelper::getOwnerBlogList($user),
        'forums'          => Forum::getOwnerForumList($user->getId()),
        'stores'          => Store::getOwnerStoreList($user->getId()),
    ));
}
