<?php

namespace App\Account\Form;

use App\Account\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = \Kernel::getIntent()->getEntityManager()->find(User::class, USER_ID);

        if (!is_null($currentUser->getProfile()->getBirthday())) {
            $dtBirthday = $currentUser->getProfile()->getBirthday()->format('d.m.Y');
        } else {
            $dtBirthday = null;
        }

        $builder
            ->add('first_name', TextType::class, array(
                'label' => 'First name',
                'attr'  => array(
                    'placeholder' => $currentUser->getProfile()->getName(),
                    'value'       => $currentUser->getProfile()->getName(),
                ),
            ))
            ->add('last_name', TextType::class, array(
                'label' => 'Last name',
                'attr'  => array(
                    'placeholder' => $currentUser->getProfile()->getSurname(),
                    'value'       => $currentUser->getProfile()->getSurname(),
                ),
            ))
            ->add('gender', ChoiceType::class, array(
                'label'   => 'Gender',
                'choices' => array(
                    'None'   => 0,
                    'Male'   => 1,
                    'Female' => 2,
                ),
                'data'    => $currentUser->getProfile()->getGender(),
            ))
            ->add('birthday', TextType::class, array(
                'label' => 'Birthday',
                'attr'  => array(
                    'value' => $dtBirthday,
                ),
            ))
            ->add('website', TextType::class, array(
                'label' => 'Website',
                'attr'  => array(
                    'value' => $currentUser->getProfile()->getWebsite(),
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
