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
     * @ORM\OneToMany(targetEntity="UserSubscription", inversedBy="subscription")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $userSubscription;

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
