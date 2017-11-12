<?php

use App\Account\AccountAcp;
use App\Account\AccountHelper;
use App\Account\Entity\User;
use App\Account\Entity\UserAddress;
use App\Account\Form\AddAddressType;
use App\Account\Form\EditAccountType;
use App\Account\Form\EditProfileType;

if (!isset($indirectly)) {
    AccountAcp::addGroup(array(
        'parent' => 'root',
        'id'     => 'account',
        'title'  => 'Account',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'account',
        'id'     => 'account',
        'title'  => 'Account details',
        'href'   => 'account',
        'screen' => 'acp_html_account',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'account',
        'id'     => 'profile',
        'title'  => 'Personal information',
        'href'   => 'profile',
        'screen' => 'acp_html_profile',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'account',
        'id'     => 'add_address',
        'title'  => 'Add new address',
        'href'   => 'add-address',
        'screen' => 'acp_html_add_address',
    ));
}

/**
 * @param \Twig_Environment             $twig
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_account($twig, $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    $editAccountForm = $controller->createForm(EditAccountType::class);

    $request = $controller->getRequest();
    $editAccountForm->handleRequest($request);
    if ($editAccountForm->isSubmitted() && $editAccountForm->isValid()) {
        $formData = $editAccountForm->getData();
        if (strlen($newUsername = $formData['new_username']) > 0) {
            $changeUsername = true;
        } else {
            $changeUsername = false;
        }
        if (strlen($newPassword = $formData['new_password']) > 0) {
            $changePassword = true;
        } else {
            $changePassword = false;
        }
        if (strlen($newEmail = $formData['new_email']) > 0) {
            $changeEmail = true;
        } else {
            $changeEmail = false;
        }

        if (AccountHelper::passwordMatches($currentUser, $formData['password_verify'])) {
            $errorMessages = array();

            if ($changeUsername) {
                if (strlen($newUsername) == 0) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('You have to insert an username.');
                } elseif (strlen($newUsername) < AccountHelper::$settings['username']['min_length']) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('Your username must be between '.AccountHelper::$settings['username']['min_length'].' and '.AccountHelper::$settings['username']['max_length'].' letters/numbers etc.');
                } elseif (strlen($newUsername) > AccountHelper::$settings['username']['max_length']) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('Your username must be between '.AccountHelper::$settings['username']['min_length'].' and '.AccountHelper::$settings['username']['max_length'].' letters/numbers etc.');
                } elseif (AccountHelper::usernameExists($newUsername)) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('This username is already in use.');
                } elseif (AccountHelper::usernameBlocked($newUsername)) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('This username has been blocked by an staff.');
                } elseif (!AccountHelper::usernameValid($newUsername)) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('This username isn\'t valid.');
                }
            }
            if ($changePassword) {
                $verifyNewPassword = $formData['new_password_verify'];
                if (strlen($newPassword) == 0) {
                    $aErrorMessages['new_password'] = $controller->get('translator')->trans('You have to insert a password.');
                } elseif (strlen($newPassword) < AccountHelper::$settings['password']['min_length']) {
                    $aErrorMessages['new_password'] = $controller->get('translator')->trans('Your password is too short. Min '.AccountHelper::$settings['password']['min_length'].' characters.');
                } elseif ($newPassword == $verifyNewPassword) {
                    $aErrorMessages['new_password_verify'] = $controller->get('translator')->trans('Your inserted password do not match the password verifier.');
                }
            }
            if ($changeEmail) {
                if (strlen($newEmail) == 0) {
                    $aErrorMessages['new_email'] = $controller->get('translator')->trans('You have to insert an email.');
                }
            }

            if (count($errorMessages) == 0) {
                if ($changeUsername) {
                    $currentUser->setUsername($newUsername);
                }
                if ($changePassword) {
                    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
                    $session = $controller->get('session');
                    $session->set(AccountHelper::$settings['login']['session_password'], $newPassword); // TODO: Password is published FIX THAT

                    $currentUser->setPassword($newPassword);

                    AccountHelper::changeSession(null, $newPassword);
                    if ($request->cookies->has(AccountHelper::$settings['login']['cookie_name'])) {
                        setcookie(
                            AccountHelper::$settings['login']['cookie_name'],
                            base64_encode(json_encode(array(
                                'email'    => $session->get(AccountHelper::$settings['login']['session_email']),
                                'password' => $session->get(AccountHelper::$settings['login']['session_password']),
                            ))),
                            strtotime(AccountHelper::$settings['login']['cookie_expire']),
                            AccountHelper::$settings['login']['cookie_path'],
                            AccountHelper::$settings['login']['cookie_domain']
                        );
                    }
                }
                if ($changeEmail) {
                    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
                    $session = $controller->get('session');
                    $session->set(AccountHelper::$settings['login']['session_email'], $newEmail);

                    $currentUser->setEmail($newEmail);
                    $currentUser->setEmailVerified(false);

                    AccountHelper::changeSession($newEmail, null);
                    if ($request->cookies->has(AccountHelper::$settings['login']['cookie_name'])) {
                        setcookie(
                            AccountHelper::$settings['login']['cookie_name'],
                            base64_encode(json_encode(array(
                                'email'    => $session->get(AccountHelper::$settings['login']['session_email']),
                                'password' => $session->get(AccountHelper::$settings['login']['session_password']),
                            ))),
                            strtotime(AccountHelper::$settings['login']['cookie_expire']),
                            AccountHelper::$settings['login']['cookie_path'],
                            AccountHelper::$settings['login']['cookie_domain']
                        );
                    }
                }
                $controller->getEntityManager()->flush();
            }
            // Else do nothing
        } else {
            $errorMessages['password_verify'] = $controller->get('translator')->trans('Your inserted password is not your current.');
        }
    }

    return $twig->render('account/panel/account.html.twig', array(
        'edit_account_form' => $editAccountForm->createView(),
        'current_user'      => $currentUser,
    ));
}

/**
 * @param \Twig_Environment             $twig
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_profile($twig, $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    $editProfileForm = $controller->createForm(EditProfileType::class);

    $request = $controller->getRequest();
    $editProfileForm->handleRequest($request);
    if ($editProfileForm->isSubmitted()) {
        $formData = $editProfileForm->getData();

        if (AccountHelper::passwordMatches($currentUser, $formData['password_verify'])) {
            if (strlen($newFirstName = $formData['first_name']) > 0) {
                $currentUser->getProfile()->setName($newFirstName);
            }
            if (strlen($newLastName = $formData['last_name']) > 0) {
                $currentUser->getProfile()->setSurname($newLastName);
            }
            if ($newGender = $formData['gender'] > 0) {
                $currentUser->getProfile()->setGender($newGender);
            }
            if (strlen($newBirthday = $formData['birthday']) > 0) {
                $currentUser->getProfile()->setBirthday(\DateTime::createFromFormat('d.m.Y', $newBirthday));
            }
            if (strlen($newWebsite = $formData['website']) > 0) {
                $currentUser->getProfile()->setWebsite($newWebsite);
            }

            $controller->getEntityManager()->flush();

            return $controller->redirectToRoute('app_account_panel', array('page' => 'profile'));

        } else {
            $aErrorMessages['password_verify'] = $controller->get('translator')->trans('Your inserted password is not your current.');
        }
    }

    return $twig->render('account/panel/profile.html.twig', array(
        'edit_profile_form' => $editProfileForm->createView(),
        'current_user'      => $currentUser,
    ));
}

/**
 * @param \Twig_Environment             $twig
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_add_address($twig, $controller)
{
    /** @var \App\Account\Entity\User $currentUser */
    $currentUser = $controller->getEntityManager()->find(User::class, USER_ID);

    $addAddressForm = $controller->createForm(AddAddressType::class);

    $request = $controller->getRequest();
    $addAddressForm->handleRequest($request);
    if ($addAddressForm->isSubmitted()) {
        $formData = $addAddressForm->getData();

        if (AccountHelper::passwordMatches($currentUser, $formData['password_verify'])) {

            $newAddress = new UserAddress();

            if (strlen($newStreet = $formData['location_street']) > 0) {
                $newAddress->setStreet($newStreet);
            }
            if (strlen($newStreetNumber = $formData['location_street_number']) > 0) {
                $newAddress->setHouseNumber($newStreetNumber);
            }
            if (strlen($newPostalCode = $formData['location_postal_code']) > 0) {
                $newAddress->setZipCode($newPostalCode);
            }
            if (strlen($newCity = $formData['location_city']) > 0) {
                $newAddress->setCity($newCity);
            }
            if (strlen($newCountry = $formData['location_country']) > 0) {
                $newAddress->setCountry($newCountry);
            }

            $currentUser->getProfile()->addAddress($newAddress);

            $controller->getEntityManager()->flush();

            return $controller->redirectToRoute('app_account_panel', array('page' => 'profile'));

        } else {
            $aErrorMessages['password_verify'] = $controller->get('translator')->trans('Your inserted password is not your current.');
        }
    }

    return $twig->render('account/panel/add-address.html.twig', array(
        'add_address_form' => $addAddressForm->createView(),
        'current_user'     => $currentUser,
    ));
}
