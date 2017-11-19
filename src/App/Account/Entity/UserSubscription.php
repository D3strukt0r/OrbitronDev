<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="user_subscriptions")
 */
class UserSubscription
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Account\Entity\User
     * @OneToOne(targetEntity="User", inversedBy="subscription")
     * @JoinColumn(name="id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var \App\Account\Entity\SubscriptionType
     * @ManyToOne(targetEntity="SubscriptionType")
     * @JoinColumn(name="subscription_id", referencedColumnName="id", nullable=false)
     */
    protected $subscription;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $activated_at;

    /**
     * @var null|\DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $expires_at;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Account\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \App\Account\Entity\User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \App\Account\Entity\SubscriptionType
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param \App\Account\Entity\SubscriptionType $subscription
     *
     * @return $this
     */
    public function setSubscription(SubscriptionType $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activated_at;
    }

    /**
     * @param \DateTime $activatedAt
     *
     * @return $this
     */
    public function setActivatedAt(\DateTime $activatedAt)
    {
        $this->activated_at = $activatedAt;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    /**
     * @param null|\DateTime $expiresAt
     *
     * @return $this
     */
    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->expires_at = $expiresAt;

        return $this;
    }

    /**
     * @return float|int|null
     */
    public function getRemainingDays()
    {
        if (is_null($this->getExpiresAt())) {
            return null;
        }

        $difference = $this->getExpiresAt()->getTimestamp() - time();
        if ($difference <= 0) {
            return 0;
        }

        return ceil($difference / 86400);
    }

    /**
     * @return bool
     */
    public function hasSubscription()
    {
        if (is_null($days = $this->getRemainingDays()) || $days > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'user_id'      => $this->id,
            'subscription' => $this->subscription->toArray(),
            'activated_at' => $this->activated_at,
            'expires_at'   => $this->expires_at,
        );
    }
}
