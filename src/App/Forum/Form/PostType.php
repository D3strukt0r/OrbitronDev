<?php

namespace App\Forum\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Forum\Entity\Thread $thread */
        $thread = $options['thread'];

        $builder
            ->add('title', TextType::class, array(
                'label'       => 'Post title',
                'attr'        => array(
                    'placeholder' => 'RE: '.$thread->getTopic(),
                ),
                'data'        => 'RE: '.$thread->getTopic(),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a title')),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a url')),
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Post reply',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'thread' => null,
        ));
    }
}
