<?php
/**
 * https://github.com/excelwebzone/EWZRecaptchaBundle/blob/master/Validator/Constraints/IsTrue.php
 */

namespace Form;

use Symfony\Component\Validator\Constraint;

class RecaptchaConstraint extends Constraint
{
    public $message = 'This value is not a valid captcha.';

    public $additional = null;

    public function __construct($options = null, $additional = null)
    {
        parent::__construct($options);
        $this->additional = $additional;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return __NAMESPACE__.'\RecaptchaValidator';
    }
}
