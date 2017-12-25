<?php

namespace Container;

use Symfony\Component\Validator\Validation;
use Validator\ConstraintValidatorFactory;

class ValidatorContainer
{
    /**
     * RoutingContainer constructor.
     *
     * @param \Kernel $kernel
     */
    public function __construct(\Kernel $kernel)
    {

        $instance = Validation::createValidatorBuilder();
        $instance->setConstraintValidatorFactory(new ConstraintValidatorFactory($kernel, array(
            'validator.expression'                                                              => 'validator.expression',
            'Symfony\\Component\\Validator\\Constraints\\ExpressionValidator'                   => 'validator.expression',
            'Symfony\\Component\\Validator\\Constraints\\EmailValidator'                        => 'validator.email',
            'security.validator.user_password'                                                  => 'security.validator.user_password',
            'Symfony\\Component\\Security\\Core\\Validator\\Constraints\\UserPasswordValidator' => 'security.validator.user_password',
            'doctrine.orm.validator.unique'                                                     => 'doctrine.orm.validator.unique',
            'Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator'          => 'doctrine.orm.validator.unique',
            'Form\\RecaptchaValidator'                                                          => 'recaptcha.validator.true',
        )));
        $instance->setTranslator($this->$kernel('translator'));
        $instance->setTranslationDomain('validators');
        $instance->addXmlMappings(array(0 => ($kernel->getRootDir().'/vendor/symfony/form/Resources/config/validation.xml')));
        //$instance->enableAnnotationMapping($this->get('annotation_reader')); // TODO: No annotation available
        $instance->addMethodMapping('loadValidatorMetadata');
        //$instance->addObjectInitializers(array(0 => $this->get('doctrine.orm.validator_initializer'))); // TODO: Doctrine not installed
        $kernel->set('validator.builder', $instance);

        $kernel->set('validator', $kernel->get('validator.builder')->getValidator());
    }
}
