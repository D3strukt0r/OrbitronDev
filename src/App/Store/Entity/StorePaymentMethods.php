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
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="paymentMethods")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $store;

    /**
     * @ORM\Column(type=string)
     */
    protected $type;

    /**
     * @ORM\Column(type=json)
     */
    protected $data;

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->store;
    }

    public function setUser(Store $user)
    {
        $this->store = $user;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}