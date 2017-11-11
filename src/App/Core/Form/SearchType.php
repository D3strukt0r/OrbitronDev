<?php

namespace App\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('search', TextType::class, array(
                'label'    => 'Search',
                'required' => true,
                'attr'     => array(
                    'pattern'     => '.{1,}',
                    'placeholder' => 'Search',
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Go',
            ));
    }
}
