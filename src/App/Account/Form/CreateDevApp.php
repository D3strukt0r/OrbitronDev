<?php

namespace App\Account\Form;

use App\Account\AccountDeveloper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateDevApp extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scope_choices = array();
        foreach (AccountDeveloper::getAllScopes() as $scope) {
            $scope_choices[$scope['name']] = $scope['scope'];
        }

        $builder
            ->add('client_name', TextType::class, array(
                'label'       => 'Name',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter the Public client name')),
                ),
            ))
            ->add('redirect_uri', TextType::class, array(
                'label'       => 'Redirect URI',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a redirect URI')),
                ),
            ))
            ->add('scopes', ChoiceType::class, array(
                'choices'  => $scope_choices,
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Create new Oauth2 App',
            ));
    }
}
