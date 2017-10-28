<?php

namespace App\Account\Entity;

/**
 * @Entity
 * @Table(name="user_subscription_type")
 */
class SubscriptionType
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="UserSubscription", inversedBy="subscription")
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=FALSE)
     */
    protected $userSubscription;

    /**
     * @Column(type="string")
     */
    protected $title;

    /**
     * @Column(type="decimal")
     */
    protected $price;

    /**
     * @Column(type="json_array")
     */
    protected $permissions;

    public function getId()
    {
        return $this->id;
    }

    public function getUserSubscription()
    {
        return $this->userSubscription;
    }

    public function setUserSubscription(UserSubscription $userSubscription)
    {
        $this->userSubscription = $userSubscription;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }
}
