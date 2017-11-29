<?php
/**
 * https://github.com/excelwebzone/EWZRecaptchaBundle/blob/master/Form/Type/EWZRecaptchaType.php
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecaptchaType extends AbstractType
{
    /**
     * The reCAPTCHA server URL's
     */
    const RECAPTCHA_API_SERVER = 'https://www.google.com/recaptcha/api.js';
    const RECAPTCHA_API_JS_SERVER = '//www.google.com/recaptcha/api/js/recaptcha_ajax.js';

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['recaptcha_enabled'] = true;

        if (!isset($options['language'])) {
            $options['language'] = $this->resolveLocale();
        }

        if (!$options['recaptcha_ajax']) {
            $view->vars = array_replace($view->vars, array(
                'recaptcha_ajax' => false,
                'url_challenge'  => sprintf('%s?hl=%s', self::RECAPTCHA_API_SERVER, $options['language']),
                'public_key'     => $options['public_key'],
            ));
        } else {
            $view->vars = array_replace($view->vars, array(
                'recaptcha_ajax' => true,
                'url_api'        => self::RECAPTCHA_API_JS_SERVER,
                'public_key'     => $options['public_key'],
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound'          => false,
            'language'          => $this->resolveLocale(),
            'recaptcha_enabled' => true,
            'recaptcha_ajax'    => false,
            'public_key'        => null,
            'private_key'       => null,
            'url_challenge'     => null,
            'url_noscript'      => null,
            'attr'              => array(
                'options' => array(
                    'theme'           => 'light',
                    'type'            => 'image',
                    'size'            => 'normal',
                    'expiredCallback' => null,
                    'defer'           => false,
                    'async'           => false,
                ),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'recaptcha';
    }

    public function resolveLocale()
    {
        return (defined('TEMPLATE_LANGUAGE') && strlen(TEMPLATE_LANGUAGE) > 0) ? TEMPLATE_LANGUAGE : 'en';
    }
}
