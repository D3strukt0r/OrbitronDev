<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

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
     * @Column(type="string", name="title")
     */
    protected $title;

    /**
     * @Column(type="decimal", name="price")
     */
    protected $price;

    /**
     * @Column(type="json_array", name="permissions")
     */
    protected $permissions;

    public function getId()
    {
        return $this->id;
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

    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }
}
