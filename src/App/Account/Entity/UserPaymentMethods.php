<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name=user_payment_methods)
 */
class UserPaymentMethods
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type=integer)
     */
    protected $id;

    /**
     * @ORM\Column(type=string)
     */
    protected $type;

    /**
     * @ORM\Column(type=json)
     */
    protected $data;
}