<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="user_subscriptions")
 */
class UserSubscription
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="user_id")
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="User", inversedBy="subscription")
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=false)
     */
    protected $user;

    /**
     * @OneToOne(targetEntity="SubscriptionType")
     * @JoinColumn(name="subscription_id", referencedColumnName="id")
     */
    protected $subscription;

    /**
     * @Column(type="datetime", name="activated_at")
     */
    protected $activatedAt;

    /**
     * @Column(type="datetime", name="expires_at")
     */
    protected $expiresAt;

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }

    public function setSubscription(SubscriptionType $subscription)
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(\DateTime $activatedAt)
    {
        $this->activatedAt = $activatedAt;
        return $this;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }
}
