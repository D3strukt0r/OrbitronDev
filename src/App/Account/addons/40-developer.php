<?php

use App\Account\AccountAcp;
use App\Account\AccountDeveloper;
use App\Account\UserInfo;
use App\Core\Token;
use Container\DatabaseContainer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

if (!isset($indirectly)) {
    $oUserInfo = new UserInfo(USER_ID);

    if ((int)$oUserInfo->getFromUser('developer') == 1) {
        AccountAcp::addGroup(array(
            'parent' => 'root',
            'id'     => 'developer',
            'title'  => _('Developer'),
        ));

        AccountAcp::addMenu(array(
            'parent' => 'developer',
            'id'     => 'developer_create_application',
            'title'  => _('Create new application'),
            'href'   => 'developer-create-application',
            'screen' => 'acp_html_developer_create_application',
        ));

        AccountAcp::addMenu(array(
            'parent' => 'developer',
            'id'     => 'developer_applications',
            'title'  => _('Your applications'),
            'href'   => 'developer-applications',
            'screen' => 'acp_html_developer_applications',
        ));

        AccountAcp::addMenu(array(
            'parent' => 'null',
            'id'     => 'developer_show_application',
            'title'  => _('Show application'),
            'href'   => 'developer-show-application',
            'screen' => 'acp_html_developer_show_applications',
        ));

    } else {
        AccountAcp::addMenu(array(
            'parent' => 'root',
            'id'     => 'create_developer_account',
            'title'  => _('Create developer account'),
            'href'   => 'developer-register',
            'screen' => 'acp_html_developer_register',
        ));
    }
}

/**
 * @param \Twig_Environment             $twig
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_developer_create_application($twig, $controller)
{
    $currentUser = new UserInfo(USER_ID);

    $scope_choices = array();
    foreach (AccountDeveloper::getAllScopes() as $scope) {
        $scope_choices[$scope['name']] = $scope['scope'];
    }
    $createAppForm = $controller->createFormBuilder()
        ->add('client_name', TextType::class, array(
            'label'       => 'Name',
            'constraints' => array(
                new NotBlank(array('message' => 'Please enter the Public client name')),
            ),
        ))
        ->add('redirect_uri', TextType::class, array(
            'label'       => 'Redirect URI',
            'constraints' => array(
                new NotBlank(array('message' => 'Please enter a redirect URI')),
            ),
        ))
        ->add('scopes', ChoiceType::class, array(
            'choices'  => $scope_choices,
            'expanded' => true,
            'multiple' => true,
        ))
        ->add('send', SubmitType::class, array(
            'label' => 'Create new Oauth2 App',
        ))
        ->getForm();

    $request = Kernel::$kernel->getRequest();
    $createAppForm->handleRequest($request);
    if ($createAppForm->isValid()) {

        AccountDeveloper::addApp(
            hash('md5', time()),
            $createAppForm->get('client_name')->getData(),
            Token::createRandomToken(array('use_openssl' => false)),
            $createAppForm->get('redirect_uri')->getData(),
            $createAppForm->get('scopes')->getData(),
            USER_ID
        );

        header('Location: ' . $controller->generateUrl('app_account_panel', array('page' => 'developer-applications')));
        exit;
    }

    return $twig->render('account/panel/developer-create-applications.html.twig', array(
        'create_app_form' => $createAppForm->createView(),
        'current_user'    => $currentUser->aUser,
    ));
}

/**
 * @param \Twig_Environment             $twig
 *
 * @return string
 */
function acp_html_developer_applications($twig)
{
    return $twig->render('account/panel/developer-list-applications.html.twig', array(
        'current_user_dev_apps' => AccountDeveloper::getApps(USER_ID),
    ));
}

/**
 * @param \Twig_Environment             $twig
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_developer_show_applications($twig, $controller)
{
    // TODO: Send message when user is not a developer
    if (!isset($_GET['app'])) {
        header('Location: ' . $controller->generateUrl('app_account_panel', array('p' => 'developer-applications')));
        exit;
    }
    $database = DatabaseContainer::getDatabase();
    $fAppId = $_GET['app'];
    $oApplicationData = $database->prepare('SELECT * FROM `oauth_clients` WHERE `client_id`=:id');
    $oApplicationData->execute(array(
        ':id' => $fAppId,
    ));
    if ($oApplicationData->rowCount() > 0) {
        $appData = $oApplicationData->fetchAll(\PDO::FETCH_ASSOC);
        return $twig->render('account/panel/developer-show-app.html.twig', array(
            'app' => $appData[0],
        ));
    }
    return 'App not found'; // TODO: Create better page
}

/**
 * @param \Twig_Environment             $twig
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_developer_register($twig, $controller)
{
    $developerForm = $controller->createFormBuilder()
        ->add('send', SubmitType::class, array(
            'label' => 'Register developer account',
        ))
        ->getForm();

    $currentUser = new UserInfo(USER_ID);

    $request = Kernel::$kernel->getRequest();
    $developerForm->handleRequest($request);
    if ($developerForm->isSubmitted()) {
        $currentUser->updateUserDeveloper(true);
        header('Location: ' . $controller->generateUrl('app_account_panel', array('page' => 'developer-applications')));
        exit;
    }

    return $twig->render('account/panel/developer-register.html.twig', array(
        'developer_form' => $developerForm->createView(),
        'current_user'   => $currentUser->aUser,
    ));
}
