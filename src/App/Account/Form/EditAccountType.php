<?php

namespace App\Account\Form;

use App\Account\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Account\Entity\User $user */
        $user = \Kernel::getIntent()->getEntityManager()->find(User::class, USER_ID);

        $builder
            ->add('new_username', TextType::class, array(
                'label'    => 'Username',
                'required' => false,
                'attr'     => array(
                    'placeholder' => 'Current username: '.$user->getUsername(),
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
                    'placeholder' => 'Current Email: '.$user->getEmail(),
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
            ));
    }
}
