<?php

namespace App\Account\Form;

use Kernel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $request = Kernel::getIntent()->getRequest();
        /** @var \Symfony\Component\Routing\Generator\UrlGenerator $router */
        $router = Kernel::getIntent()->get('router');

        $builder
            ->add('redirect', HiddenType::class, array(
                'data' => strlen($request->query->get('redir')) > 0 ? $request->query->get('redir') : $router->generate('app_account_panel', array('page' => 'home')),
            ))
            ->add('email', EmailType::class, array(
                'label'       => 'E-mail',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email address')),
                    new Email(array('message' => 'Please enter a VALID email address')),
                ),
            ))
            ->add('password', PasswordType::class, array(
                'label'       => 'Password',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your password')),
                ),
            ))
            ->add('remember', CheckboxType::class, array(
                'label'    => 'Keep me logged in',
                'required' => false,
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Log in',
            ));
    }
}
