<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name=user_subscriptions)
 */
class SubscriptionType
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
    protected $title;

    /**
     * @ORM\Column(type=float)
     */
    protected $price;

    /**
     * @ORM\Column(type=json)
     */
    protected $permissions;
}
