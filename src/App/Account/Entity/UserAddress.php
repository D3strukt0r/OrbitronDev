<?php

namespace App\Account\Entity;

/**
 * @Entity
 * @Table(name="user_addresses")
 */
class UserAddress
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="user_id")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="UserProfiles", inversedBy="addresses")
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=FALSE)
     */
    protected $userProfile;

    /**
     * @Column(type="string")
     */
    protected $street;

    /**
     * @Column(type="string")
     */
    protected $houseNumber;

    /**
     * @Column(type="string")
     */
    protected $zipCode;

    /**
     * @Column(type="string")
     */
    protected $city;

    /**
     * @Column(type="string")
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
