<?php

namespace App\Forum\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateBoardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label'    => 'Board name',
                'required' => true,
            ))
            ->add('description', TextType::class, array(
                'label'    => 'Description',
                'required' => false,
            ))
            ->add('parent', ChoiceType::class, array(
                'label'    => 'Parent',
                'required' => true,
                'choices'  => $options['board_list'],
                'expanded' => false, // select tag
                'multiple' => false,
            ))
            ->add('type', ChoiceType::class, array(
                'label'       => 'Type',
                'required'    => false,
                'choices'     => array(
                    'Board'    => 1,
                    'Category' => 2,
                ),
                'placeholder' => false,
                'expanded'    => true, // radio buttons
                'multiple'    => false,

            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Submit',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'board_list' => null,
        ));
    }
}
