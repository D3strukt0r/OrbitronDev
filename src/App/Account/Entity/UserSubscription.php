<?php

namespace App\Account\Entity;

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
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=FALSE)
     */
    protected $user;

    /**
     * @OneToMany(targetEntity="SubscriptionType", mappedBy="userSubscription")
     * @JoinColumn(name="subscription_id", referencedColumnName="id")
     */
    protected $subscription;

    /**
     * @Column(type="datetime")
     */
    protected $activatedAt;

    /**
     * @Column(type="datetime")
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
