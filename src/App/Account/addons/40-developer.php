<?php

use App\Account\AccountAcp;
use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Account\Form\CreateDevAccount;
use App\Account\Form\CreateDevApp;
use App\Core\Token;

if (!isset($indirectly)) {
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = Kernel::getIntent()->getEntityManager()->find(User::class, USER_ID);

    if ((int)$currentUser->getDeveloperStatus() == 1) {
        AccountAcp::addGroup(array(
            'parent' => 'root',
            'id'     => 'developer',
            'title'  => 'Developer',
            'icon'   => 'fa fa-fw fa-code'
        ));

        AccountAcp::addMenu(array(
            'parent' => 'developer',
            'id'     => 'developer_create_application',
            'title'  => 'Create new application',
            'href'   => 'developer-create-application',
            'screen' => 'acp_html_developer_create_application',
        ));

        AccountAcp::addMenu(array(
            'parent' => 'developer',
            'id'     => 'developer_applications',
            'title'  => 'Your applications',
            'href'   => 'developer-applications',
            'screen' => 'acp_html_developer_applications',
        ));

        AccountAcp::addMenu(array(
            'parent' => 'null',
            'id'     => 'developer_show_application',
            'title'  => 'Show application',
            'href'   => 'developer-show-application',
            'screen' => 'acp_html_developer_show_applications',
        ));

    } else {
        AccountAcp::addMenu(array(
            'parent' => 'root',
            'id'     => 'create_developer_account',
            'title'  => 'Create developer account',
            'href'   => 'developer-register',
            'screen' => 'acp_html_developer_register',
        ));
    }
}

/**
 * @param \Controller $controller
 *
 * @return string
 */
function acp_html_developer_create_application(Controller $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    if ((int)$currentUser->getDeveloperStatus() != 1) {
        header('Location: '.$controller->generateUrl('app_account_panel', array('p' => 'developer-register')));
        exit;
    }

    $createAppForm = $controller->createForm(CreateDevApp::class);

    $request = $controller->getRequest();
    $createAppForm->handleRequest($request);
    if ($createAppForm->isSubmitted() && $createAppForm->isValid()) {

        AccountHelper::addApp(
            $createAppForm->get('client_name')->getData(),
            Token::createRandomToken(array('use_openssl' => false)),
            $createAppForm->get('redirect_uri')->getData(),
            $createAppForm->get('scopes')->getData(),
            $currentUser->getId()
        );

        header('Location: '.$controller->generateUrl('app_account_panel', array('page' => 'developer-applications')));
        exit;
    }

    return $controller->renderView('account/panel/developer-create-applications.html.twig', array(
        'create_app_form' => $createAppForm->createView(),
        'current_user'    => $currentUser,
    ));
}

/**
 * @param \Controller $controller
 *
 * @return string
 */
function acp_html_developer_applications(Controller $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    if ((int)$currentUser->getDeveloperStatus() != 1) {
        header('Location: '.$controller->generateUrl('app_account_panel', array('p' => 'developer-register')));
        exit;
    }

    return $controller->renderView('account/panel/developer-list-applications.html.twig', array(
        'current_user_dev_apps' => AccountHelper::getDeveloperApps(USER_ID),
    ));
}

/**
 * @param \Controller $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_developer_show_applications(Controller $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    if ((int)$currentUser->getDeveloperStatus() != 1) {
        header('Location: '.$controller->generateUrl('app_account_panel', array('p' => 'developer-register')));
        exit;
    }

    if (!$controller->getRequest()->query->has('app')) {
        header('Location: '.$controller->generateUrl('app_account_panel', array('p' => 'developer-applications')));
        exit;
    }
    $appId = $controller->getRequest()->query->get('app');
    $appData = AccountHelper::getAppInformation($appId);

    if (is_null($appData)) {
        return $controller->renderView('account/panel/developer-app-not-found.html.twig');
    }

    return $controller->renderView('account/panel/developer-show-app.html.twig', array(
        'app' => $appData,
    ));
}

/**
 * @param \Controller $controller
 *
 * @return string
 */
function acp_html_developer_register(Controller $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    if ((int)$currentUser->getDeveloperStatus() == 1) {
        header('Location: '.$controller->generateUrl('app_account_panel', array('p' => 'developer-applications')));
        exit;
    }

    $developerForm = $controller->createForm(CreateDevAccount::class);

    $request = $controller->getRequest();
    $developerForm->handleRequest($request);
    if ($developerForm->isSubmitted()) {
        $currentUser->setDeveloperStatus(true);
        $controller->getEntityManager()->flush();
        header('Location: '.$controller->generateUrl('app_account_panel', array('page' => 'developer-applications')));
        exit;
    }

    return $controller->renderView('account/panel/developer-register.html.twig', array(
        'developer_form' => $developerForm->createView(),
        'current_user'   => $currentUser,
    ));
}
