<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name=user_subscriptions)
 */
class UserSubscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type=integer)
     */
    protected $id;

    /**
     * Many Users have One Address.
     * @ORM\ManyToOne(targetEntity="SubscriptionType")
     * @ORM\JoinColumn(name="subscription_id", referencedColumnName="id")
     */
    protected $subscription;

    /**
     * @ORM\Column(type=datetime)
     */
    protected $activatedAt;

    /**
     * @ORM\Column(type=datetime)
     */
    protected $expiresAt;
}
