<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name=user_addresses)
 */
class UserAddress
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type=integer)
     */
    protected $id;

    /**
     * @ORM\Column(type=string)
     */
    protected $street;

    /**
     * @ORM\Column(type=string)
     */
    protected $houseNumber;

    /**
     * @ORM\Column(type=string)
     */
    protected $zipCode;

    /**
     * @ORM\Column(type=string)
     */
    protected $city;

    /**
     * @ORM\Column(type=string)
     */
    protected $country;

    public function getId()
    {
        return $this->id;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }
}
