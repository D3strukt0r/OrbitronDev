<?php

namespace App\Forum\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Forum\Entity\Board $board */
        $board = $options['board'];

        $builder
            ->add('parent', HiddenType::class, array(
                'data' => $board->getId(),
            ))
            ->add('title', TextType::class, array(
                'label'       => 'Thread name',
                'attr'        => array(
                    'placeholder' => 'e.g. I need help',
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a title')),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a message')),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Create thread',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'board' => null,
        ));
    }
}
