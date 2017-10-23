<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name=user_profiles)
 */
class UserProfiles
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
    protected $name;

    /**
     * @ORM\Column(type=string)
     */
    protected $surname;

    /**
     * @ORM\Column(type=smallint)
     */
    protected $gender;

    /**
     * @ORM\Column(type=date)
     */
    protected $birthday;

    /**
     * @ORM\Column(type=string)
     */
    protected $website;

    /**
     * @ORM\Column(type=string)
     */
    protected $picture;

    /**
     * @ORM\Column(type=integer)
     */
    protected $activeAddress;

    /**
     * @ORM\Column(type=string)
     * @ORM\OneToMany(targetEntity="UserAddress", mappedBy="id")
     */
    protected $addresses;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getBirthday()
    {
        return $this->birthday;
    }

    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    public function getActiveAddress()
    {
        return $this->activeAddress;
    }

    public function setActiveAddress($activeAddress)
    {
        $this->activeAddress = $activeAddress;
    }

    public function getAddresses()
    {
        return $this->addresses;
    }

    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
    }
}
