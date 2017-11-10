<?php

namespace App\Account\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, array(
                'label'       => 'Password',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
                ),
            ))
            ->add('password_verify', PasswordType::class, array(
                'label'       => 'Repeat Password',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Change password',
            ));
    }
}
