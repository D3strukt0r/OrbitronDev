<?php

namespace App\Store\Entity;

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
     * @JoinColumn(name="store_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $store;

    /**
     * @Column(type="string")
     */
    protected $type;

    /**
     * @Column(type="json_array")
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