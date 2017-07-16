<?php

use App\Account\AccountAcp;
use App\Account\UserInfo;
use App\Blog\Blog;
use App\Forum\Forum;
use App\Store\Store;
use Container\DatabaseContainer;

AccountAcp::addMenu(array(
    'parent' => 'root',
    'id'     => 'home',
    'title'  => _('Overview'),
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
    $database = DatabaseContainer::$database;
    if (is_null($database)) {
        throw new Exception('A database connection is required');
    }
    $currentUser = new UserInfo(USER_ID);

    return $twig->render('account/panel/home.html.twig', array(
        'current_user'         => $currentUser->aUser,
        'current_user_profile' => $currentUser->aProfile,
        'current_user_sub'     => $currentUser->aSubscription,
        'service_allowed'      => $currentUser->serviceAllowed() ? true : false,
        'blogs'                => Blog::getOwnerBlogList($currentUser->getFromUser('user_id')),
        'forums'               => Forum::getOwnerForumList($currentUser->getFromUser('user_id')),
        'stores'               => Store::getOwnerStoreList($currentUser->getFromUser('user_id')),
    ));
}
