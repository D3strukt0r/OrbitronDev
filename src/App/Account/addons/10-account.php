<?php

use App\Account\Account;
use App\Account\AccountAcp;
use App\Account\AccountTools;
use App\Account\UserInfo;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

if (!isset($indirectly)) {
    AccountAcp::addGroup(array(
        'parent' => 'root',
        'id'     => 'account',
        'title'  => _('Account'),
    ));

    AccountAcp::addMenu(array(
        'parent' => 'account',
        'id'     => 'account',
        'title'  => _('Account details'),
        'href'   => 'account',
        'screen' => 'acp_html_account',
    ));

    AccountAcp::addMenu(array(
        'parent' => 'account',
        'id'     => 'profile',
        'title'  => _('Personal information'),
        'href'   => 'profile',
        'screen' => 'acp_html_profile',
    ));
}

/**
 * @param \Twig_Environment             $twig
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_account($twig, $controller)
{
    $currentUser = new UserInfo(USER_ID);

    $editAccountForm = $controller->createFormBuilder()
        ->add('new_username', TextType::class, array(
            'label'    => 'Username',
            'required' => false,
            'attr'     => array(
                'placeholder' => 'Current username: ' . $currentUser->getFromUser('username'),
            ),
        ))
        ->add('new_password', PasswordType::class, array(
            'label'    => 'Password',
            'required' => false,
            'attr'     => array(
                'placeholder' => 'Enter your new password',
            ),
        ))
        ->add('new_password_verify', PasswordType::class, array(
            'label'    => 'Repeat Password',
            'required' => false,
            'attr'     => array(
                'placeholder' => 'Confirm new password',
            ),
        ))
        ->add('new_email', EmailType::class, array(
            'label'       => 'Email',
            'required'    => false,
            'attr'        => array(
                'placeholder' => 'Current Email: ' . $currentUser->getFromUser('email'),
            ),
            'constraints' => array(
                new Email(array('message' => 'Please enter a VALID email address')),
            ),
        ))
        ->add('password_verify', PasswordType::class, array(
            'label'       => 'Current Password',
            'attr'        => array(
                'placeholder' => 'Enter your current password',
            ),
            'constraints' => array(
                new NotBlank(array('message' => 'Please enter your password')),
            ),
        ))
        ->add('send', SubmitType::class, array(
            'label' => 'Submit',
        ))
        ->getForm();

    $request = Kernel::getIntent()->getRequest();
    $editAccountForm->handleRequest($request);
    if ($editAccountForm->isSubmitted() && $editAccountForm->isValid()) {
        if (strlen($newUsername = $editAccountForm->get('new_username')->getData()) > 0) {
            $changeUsername = true;
        } else {
            $changeUsername = false;
        }
        if (strlen($newPassword = $editAccountForm->get('new_password')->getData()) > 0) {
            $changePassword = true;
        } else {
            $changePassword = false;
        }
        if (strlen($newEmail = $editAccountForm->get('new_email')->getData()) > 0) {
            $changeEmail = true;
        } else {
            $changeEmail = false;
        }

        if (AccountTools::passwordMatches($currentUser, $editAccountForm->get('password_verify')->getData())) {
            $errorMessages = array();

            if ($changeUsername) {
                if (strlen($newUsername) == 0) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('You have to insert an username.');
                } elseif (strlen($newUsername) < 1 && strlen($newUsername) > 32) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('Your username must be between 1 and 32 letters/numbers etc.');
                } elseif (AccountTools::isNameTaken($newUsername)) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('This username is already in use.');
                } elseif (AccountTools::isNameBlocked($newUsername)) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('This username has been blocked by an staff.');
                } elseif (!AccountTools::isValidName($newUsername)) {
                    $errorMessages['new_username'] = $controller->get('translator')->trans('This username isn\'t valid.');
                }
            }
            if ($changePassword) {
                $verifyNewPassword = $editAccountForm->get('new_password_verify')->getData();
                if (strlen($newPassword) == 0) {
                    $aErrorMessages['new_password'] = $controller->get('translator')->trans('You have to insert a password.');
                } elseif (strlen($newPassword) < 7) {
                    $aErrorMessages['new_password'] = $controller->get('translator')->trans('Your password is too short. Min 7 characters.');
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
                    $currentUser->updateUsername($newUsername);
                }
                if ($changePassword) {
                    $hashedNewPassword = AccountTools::hash($newPassword);
                    $_SESSION['USER_PW'] = $hashedNewPassword;
                    $currentUser->updatePassword($hashedNewPassword);
                    if (isset($_COOKIE['account'])) {
                        Account::changeSession(null, $hashedNewPassword, true);
                    }
                    Account::changeSession(null, $hashedNewPassword, false);
                }
                if ($changeEmail) {
                    $_SESSION['USER_EM'] = $newEmail;
                    $currentUser->updateEmail($newEmail);
                    if (isset($_COOKIE['account'])) {
                        Account::changeSession($newEmail, null, true);
                    }
                    Account::changeSession($newEmail, null, false);
                }
            }
            // Else do nothing
        } else {
            $errorMessages['password_verify'] = $controller->get('translator')->trans('Your inserted password is not your current.');
        }
    }

    $currentUser = new UserInfo(USER_ID); // Update
    return $twig->render('account/panel/account.html.twig', array(
        'edit_account_form'    => $editAccountForm->createView(),
        'current_user'         => $currentUser->aUser,
        'current_user_profile' => $currentUser->aProfile,
        'current_user_sub'     => $currentUser->aSubscription,
    ));
}

/**
 * @param \Twig_Environment             $twig
 *
 * @param \Controller\AccountController $controller
 *
 * @return string
 */
function acp_html_profile($twig, $controller)
{
    $currentUser = new UserInfo(USER_ID);

    if(!is_null($currentUser->getFromProfile('birthday'))) {
        $dtBirthday = \DateTime::createFromFormat('Y-m-d', $currentUser->getFromProfile('birthday'));
    } else {
        $dtBirthday = null;
    }

    $editProfileForm = $controller->createFormBuilder()
        ->add('first_name', TextType::class, array(
            'label' => 'First name',
            'attr'  => array(
                'placeholder' => $currentUser->getFromProfile('firstname'),
                'value'       => $currentUser->getFromProfile('firstname'),
            ),
        ))
        ->add('last_name', TextType::class, array(
            'label' => 'Last name',
            'attr'  => array(
                'placeholder' => $currentUser->getFromProfile('lastname'),
                'value'       => $currentUser->getFromProfile('lastname'),
            ),
        ))
        ->add('gender', ChoiceType::class, array(
            'label'   => 'Gender',
            'choices' => array(
                'None'   => 0,
                'Male'   => 1,
                'Female' => 2,
            ),
            'data'    => $currentUser->getFromProfile('gender'),
        ))
        ->add('birthday', TextType::class, array(
            'label' => 'Birthday',
            'attr'  => array(
                'value' => !is_null($dtBirthday) ? $dtBirthday->format('d.m.Y') : null,
            ),
        ))
        ->add('location_street', TextType::class, array(
            'label' => 'Location',
            'attr'  => array(
                'placeholder' => 'Street (' . $currentUser->getFromProfile('location_country') . ')',
                'value'       => $currentUser->getFromProfile('location_street'),
            ),
        ))
        ->add('location_street_number', TextType::class, array(
            'label' => 'Location',
            'attr'  => array(
                'placeholder' => 'Street Number (' . $currentUser->getFromProfile('location_country') . ')',
                'value'       => $currentUser->getFromProfile('location_street_number'),
            ),
        ))
        ->add('location_postal_code', TextType::class, array(
            'label' => 'Location',
            'attr'  => array(
                'placeholder' => 'Post Code (' . $currentUser->getFromProfile('location_country') . ')',
                'value'       => $currentUser->getFromProfile('location_zip'),
            ),
        ))
        ->add('location_city', TextType::class, array(
            'label' => 'Location',
            'attr'  => array(
                'placeholder' => 'City (' . $currentUser->getFromProfile('location_country') . ')',
                'value'       => $currentUser->getFromProfile('location_city'),
            ),
        ))
        ->add('location_country', TextType::class, array(
            'label' => 'Location',
            'attr'  => array(
                'placeholder' => 'Country (' . $currentUser->getFromProfile('location_country') . ')',
                'value'       => $currentUser->getFromProfile('location_country'),
            ),
        ))
        ->add('website', TextType::class, array(
            'label' => 'Website',
            'attr'  => array(
                'value' => $currentUser->getFromProfile('website'),
            ),
        ))
        ->add('usage', ChoiceType::class, array(
            'label'   => 'Primary Usage',
            'choices' => array(
                'None'   => 0,
                'Home'   => 1,
                'Work'   => 2,
                'School' => 3,
            ),
            'data'    => $currentUser->getFromProfile('usages'),
        ))
        ->add('password_verify', PasswordType::class, array(
            'label'       => 'Current Password',
            'attr'        => array(
                'placeholder' => 'Enter your current password',
            ),
            'constraints' => array(
                new NotBlank(array('message' => 'Please enter your password')),
            ),
        ))
        ->add('send', SubmitType::class, array(
            'label' => 'Submit',
        ))
        ->getForm();

    $request = Kernel::getIntent()->getRequest();
    $editProfileForm->handleRequest($request);
    if ($editProfileForm->isSubmitted()) {

        if (AccountTools::passwordMatches($currentUser, $editProfileForm->get('password_verify')->getData())) {
            if (strlen($newFirstName = $editProfileForm->get('first_name')->getData()) > 0) {
                $currentUser->updateFirstName($newFirstName);
            }
            if (strlen($newLastName = $editProfileForm->get('last_name')->getData()) > 0) {
                $currentUser->updateLastName($newLastName);
            }
            if ($newGender = $editProfileForm->get('gender')->getData() > 0) {
                $currentUser->updateGender($newGender);
            }
            if (strlen($newBirthday = $editProfileForm->get('birthday')->getData()) > 0) {
                $dt = \DateTime::createFromFormat('d.m.Y', $newBirthday);
                $currentUser->updateBirthday($dt->format('Y-m-d'));
            }
            if (strlen($newWebsite = $editProfileForm->get('website')->getData()) > 0) {
                $currentUser->updateWebsite($newWebsite);
            }
            if ($newUsage = $editProfileForm->get('usage')->getData() > 0) {
                $currentUser->updateUsage($newUsage);
            }
            if (strlen($newStreet = $editProfileForm->get('location_street')->getData()) > 0) {
                $currentUser->updateStreet($newStreet);
            }
            if (strlen($newStreetNumber = $editProfileForm->get('location_street_number')->getData()) > 0) {
                $currentUser->updateStreetNumber($newStreetNumber);
            }
            if (strlen($newPostalCode = $editProfileForm->get('location_postal_code')->getData()) > 0) {
                $currentUser->updatePostalCode($newPostalCode);
            }
            if (strlen($newCity = $editProfileForm->get('location_city')->getData()) > 0) {
                $currentUser->updateCity($newCity);
            }
            if (strlen($newCountry = $editProfileForm->get('location_country')->getData()) > 0) {
                $currentUser->updateCountry($newCountry);
            }

            return $controller->redirectToRoute('app_account_panel', array('page' => 'profile'));

        } else {
            $aErrorMessages['password_verify'] = $controller->get('translator')->trans('Your inserted password is not your current.');
        }
    }

    $currentUser = new UserInfo(USER_ID); // Update
    return $twig->render('account/panel/profile.html.twig', array(
        'edit_profile_form'    => $editProfileForm->createView(),
        'current_user'         => $currentUser->aUser,
        'current_user_profile' => $currentUser->aProfile,
        'current_user_sub'     => $currentUser->aSubscription,
    ));
}
