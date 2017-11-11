<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="user_addresses")
 */
class UserAddress
{
    /**
     * @var integer
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="UserProfiles", inversedBy="addresses")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $userProfile;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $street;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $house_number;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $zip_code;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $country;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Account\Entity\UserProfiles
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }

    /**
     * @param \App\Account\Entity\UserProfiles $userProfile
     *
     * @return $this
     */
    public function setUserProfile(UserProfiles $userProfile)
    {
        $this->userProfile = $userProfile;

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
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getHouseNumber()
    {
        return $this->house_number;
    }

    /**
     * @param string $houseNumber
     *
     * @return $this
     */
    public function setHouseNumber($houseNumber)
    {
        $this->house_number = $houseNumber;

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
     * @param string $zipCode
     *
     * @return $this
     */
    public function setZipCode($zipCode)
    {
        $this->zip_code = $zipCode;

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
    public function setCity($city)
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
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }
}
