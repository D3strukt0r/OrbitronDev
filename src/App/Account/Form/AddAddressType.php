<?php

namespace App\Account\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('location_street', TextType::class, array(
                'label' => 'Location',
                'attr'  => array(
                    'placeholder' => 'Street',
                ),
            ))
            ->add('location_street_number', TextType::class, array(
                'label' => 'Location',
                'attr'  => array(
                    'placeholder' => 'Street Number',
                ),
            ))
            ->add('location_postal_code', TextType::class, array(
                'label' => 'Location',
                'attr'  => array(
                    'placeholder' => 'Post Code',
                ),
            ))
            ->add('location_city', TextType::class, array(
                'label' => 'Location',
                'attr'  => array(
                    'placeholder' => 'City',
                ),
            ))
            ->add('location_country', TextType::class, array(
                'label' => 'Location',
                'attr'  => array(
                    'placeholder' => 'Country',
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
                'label' => 'Add address',
            ));
    }
}
