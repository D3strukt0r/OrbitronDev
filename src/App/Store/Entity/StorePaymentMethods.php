<?php

namespace App\Store\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_payment_methods")
 */
class StorePaymentMethods
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