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
     * @ORM\ManyToOne(targetEntity="UserProfiles", inversedBy="addresses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $userProfile;

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

    public function getUserProfile()
    {
        return $this->userProfile;
    }

    public function setUserProfile(UserProfiles $userProfile)
    {
        $this->userProfile = $userProfile;
        return $this;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;
        return $this;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }
}
