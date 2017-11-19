<?php

namespace App\Forum\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Account\Entity\User $currentUser */
        $currentUser = $options['user'];

        $activeAddress = $currentUser->getProfile()->getActiveAddress();
        if (!is_null($activeAddress)) {
            $mainAddress = array(
                'street'       => USER_ID != -1 ? $currentUser->getProfile()->getAddresses()[$activeAddress]->getStreet() : '',
                'house_number' => USER_ID != -1 ? $currentUser->getProfile()->getAddresses()[$activeAddress]->getHouseNumber() : '',
                'zip_code'     => USER_ID != -1 ? $currentUser->getProfile()->getAddresses()[$activeAddress]->getZipCode() : '',
                'city'         => USER_ID != -1 ? $currentUser->getProfile()->getAddresses()[$activeAddress]->getCity() : '',
                'country'      => USER_ID != -1 ? $currentUser->getProfile()->getAddresses()[$activeAddress]->getCountry() : '',
            );
        } else {
            $mainAddress = array(
                'street'       => '',
                'house_number' => '',
                'zip_code'     => '',
                'city'         => '',
                'country'      => '',
            );
        }

        $builder
            ->add('name', TextType::class, array(
                'label'       => 'Full name',
                'attr'        => array(
                    'value' => (USER_ID != -1 ? $currentUser->getProfile()->getName() : '').' '.(!empty($currentUser->aProfile) ? $currentUser->getProfile()->getSurname() : ''),
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your full name')),
                ),
            ))
            ->add('email', TextType::class, array(
                'label'       => 'Email',
                'attr'        => array(
                    'value' => (USER_ID != -1 ? $currentUser->getEmail() : ''),
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email')),
                ),
            ))
            ->add('phone', TextType::class, array(
                'label'       => 'Phone Nr.',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your phone number')),
                ),
            ))
            ->add('location_street', TextType::class, array(
                'label'       => 'Street',
                'attr'        => array(
                    'value' => $mainAddress['street'],
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your street')),
                ),
            ))
            ->add('location_street_number', TextType::class, array(
                'label'       => 'House Nr.',
                'attr'        => array(
                    'value' => $mainAddress['house_number'],
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your house nr.')),
                ),
            ))
            ->add('location_postal_code', TextType::class, array(
                'label'       => 'Postal code',
                'attr'        => array(
                    'value' => $mainAddress['zip_code'],
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your zip code')),
                ),
            ))
            ->add('location_city', TextType::class, array(
                'label'       => 'City',
                'attr'        => array(
                    'value' => $mainAddress['city'],
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your city')),
                ),
            ))
            ->add('location_country', TextType::class, array(
                'label'       => 'Country',
                'attr'        => array(
                    'value' => $mainAddress['country'],
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your country')),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Order',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'user' => null,
        ));
    }
}
