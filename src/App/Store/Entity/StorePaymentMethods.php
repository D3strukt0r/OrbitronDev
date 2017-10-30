<?php

namespace App\Store\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="store_payment_methods")
 */
class StorePaymentMethods
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Store", inversedBy="paymentMethods")
     * @JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @Column(type="string", name="payment_type")
     */
    protected $type;

    /**
     * @Column(type="json_array", name="data")
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
