<?php

namespace App\Store\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Forum\ForumThread $store */
        $store = $options['store'];

        /** @var \App\Forum\ForumThread $product */
        $product = $options['product'];

        $builder
            ->add('store_id', HiddenType::class, array(
                'data' => $store->getVar('id'),
            ))
            ->add('product_id', HiddenType::class, array(
                'data' => $product->getVar('id'),
            ))
            // TODO: This should be IntegerType
            ->add('product_count', TextType::class, array(
                'label'       => 'Amount',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter a number')),
                    //new Type(array('type' => 'int', 'message' => 'The value {{ value }} is not a valid {{ type }}.'))
                ),
                'disabled'    => $product->getVar('stock_available') == 0 ? true : false,
            ))
            ->add('send', SubmitType::class, array(
                'label'    => 'Add to shopping cart',
                'disabled' => $product->getVar('stock_available') == 0 ? true : false,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'store'   => null,
            'product' => null,
        ));
    }
}
