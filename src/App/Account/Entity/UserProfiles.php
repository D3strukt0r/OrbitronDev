<?php

namespace App\Account\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="user_profiles")
 */
class UserProfiles
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="user_id")
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="User", inversedBy="profile")
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=false)
     */
    protected $user;

    /**
     * @Column(type="string", name="name", nullable=true)
     */
    protected $name;

    /**
     * @Column(type="string", name="surname", nullable=true)
     */
    protected $surname;

    /**
     * @Column(type="smallint", name="gender", nullable=true)
     */
    protected $gender;

    /**
     * @Column(type="date", name="birthday", nullable=true)
     */
    protected $birthday;

    /**
     * @Column(type="string", name="website", nullable=true)
     */
    protected $website;

    /**
     * @Column(type="string", name="picture", nullable=true)
     */
    protected $picture;

    /**
     * @Column(type="integer", name="active_address", nullable=true)
     */
    protected $activeAddress;

    /**
     * @OneToMany(targetEntity="UserAddress", mappedBy="userProfile", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $addresses;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    public function getBirthday()
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTime $birthday)
    {
        $this->birthday = $birthday;
        return $this;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    public function getActiveAddress()
    {
        return $this->activeAddress;
    }

    public function setActiveAddress($activeAddress)
    {
        $this->activeAddress = $activeAddress;
        return $this;
    }

    public function getAddresses()
    {
        return $this->addresses->toArray();
    }

    public function addAddress(UserAddress $address)
    {
        $this->addresses->add($address);
        $address->setUserProfile($this);

        return $this;
    }

    public function removeAddress(UserAddress $address)
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            $address->setUserProfile(null);
        }

        return $this;
    }
}
