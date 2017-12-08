<?php

namespace App\Core\Form;

use Form\RecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label'       => 'Name',
                'attr'        => array(
                    'pattern' => '.{1,}', // min length
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your name')),
                    new Length(array('min' => 4, 'max' => 255)),
                ),
            ))
            ->add('email', EmailType::class, array(
                'label'       => 'E-mail',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email address')),
                    new Email(array('message' => 'Please enter a VALID email address')),
                    new Length(array('max' => 255)),
                ),
            ))
            ->add('subject', TextType::class, array(
                'label'       => 'Subject',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your subject')),
                    new Length(array('min' => 10, 'max' => 255)),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'label'       => 'Message',
                'attr'        => array(
                    'rows' => 7,
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your message')),
                    new Length(array('min' => 10, 'max' => 255)),
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
                /*
                'constraints' => array(
                    new RecaptchaConstraint(null, array(
                        'enabled' => true,
                        'privateKey' => '6LcFPwcUAAAAAP2vo5xPbUoVRAyq9VmyLEfXmazU',
                        'requestStack' => $requestStack,
                        'httpProxy' => array(
                            'host' => null,
                            'port' => null,
                            'auth' => null,
                        )
                    )),
                ),
                */
            ))
            ->add('send_to_own', CheckboxType::class, array(
                'label'    => 'Send a copy to my e-mail address',
                'required' => false,
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Send message',
            ));
    }
}
