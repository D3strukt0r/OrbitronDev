<?php

namespace App\Account\Form;

use Form\RecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'label'       => 'Username',
                'attr' => array(
                    'placeholder' => 'JohnDoe',
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your username')),
                ),
            ))
            ->add('email', EmailType::class, array(
                'label'       => 'E-mail',
                'attr' => array(
                    'placeholder' => 'johndoe@gmail.com',
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email address')),
                    new Email(array('message' => 'Please enter a VALID email address')),
                ),
            ))
            ->add('password', PasswordType::class, array(
                'label'       => 'Password',
                'attr' => array(
                    'placeholder' => 'Password',
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
                ),
            ))
            ->add('password_verify', PasswordType::class, array(
                'label'       => 'Repeat Password',
                'attr' => array(
                    'placeholder' => 'Password',
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
                ),
            ))
            ->add('recaptcha', RecaptchaType::class, array(
                'private_key'    => '6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll',
                'public_key'     => '6Ldec_4SAAAAAJ_TnvICnltNqgNaBPCbXp-wN48B',
                'recaptcha_ajax' => false,
                'attr'           => array(
                    'options' => array(
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal',
                        'defer' => true,
                        'async' => true,
                    ),
                ),
                'mapped'         => false,
            ))
            ->add('terms', CheckboxType::class, array(
                'label'    => 'I accept the %link%Terms and Conditions%/link%',
                'required' => true,
                'constraints' => array(
                    new NotBlank(array('message' => 'Please accept the terms')),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Register',
            ));
    }
}
