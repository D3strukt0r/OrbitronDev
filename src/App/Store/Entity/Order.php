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
 * @Table(name="store_orders")
 */
class Order
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Store\Entity\Store
     * @ManyToOne(targetEntity="Store")
     * @JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $email;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $street;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $zip_code;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $city;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $country;

    /**
     * @var \App\Store\Entity\DeliveryType
     * @ManyToOne(targetEntity="DeliveryType")
     * @JoinColumn(name="delivery_type_id", referencedColumnName="id", nullable=false)
     */
    protected $delivery_type;

    /**
     * @var array
     * @Column(type="json_array")
     */
    protected $product_list;

    /**
     * @var int
     * @Column(type="smallint", options={"default":0})
     */
    protected $status = 0;

    const STATUS_NOT_PROCESSED = 0;
    const STATUS_IN_PRODUCTION = 1;
    const STATUS_SENT = 2;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Store\Entity\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param \App\Store\Entity\Store $store
     *
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return $this
     */
    public function setStreet(string $street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zip_code;
    }

    /**
     * @param string $zip_code
     *
     * @return $this
     */
    public function setZipCode(string $zip_code)
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity(string $city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry(string $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return \App\Store\Entity\DeliveryType
     */
    public function getDeliveryType()
    {
        return $this->delivery_type;
    }

    /**
     * @param \App\Store\Entity\DeliveryType $delivery_type
     *
     * @return $this
     */
    public function setDeliveryType(DeliveryType $delivery_type)
    {
        $this->delivery_type = $delivery_type;

        return $this;
    }

    /**
     * @return array
     */
    public function getProductList()
    {
        return $this->product_list;
    }

    /**
     * @param array $product_list
     *
     * @return $this
     */
    public function setProductList(array $product_list)
    {
        $this->product_list = $product_list;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus(int $status)
    {
        $this->status = $status;

        return $this;
    }
}
