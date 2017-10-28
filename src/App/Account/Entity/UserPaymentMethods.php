<?php

namespace App\Account\Entity;

/**
 * @Entity
 * @Table(name="user_payment_methods")
 */
class UserPaymentMethods
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="paymentMethods")
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=FALSE)
     */
    protected $user;

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
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
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
