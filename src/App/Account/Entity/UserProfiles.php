<?php

namespace App\Account\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=FALSE)
     */
    protected $user;

    /**
     * @Column(type="string")
     */
    protected $name = null;

    /**
     * @Column(type="string")
     */
    protected $surname = null;

    /**
     * @Column(type="smallint")
     */
    protected $gender = null;

    /**
     * @Column(type="date")
     */
    protected $birthday = null;

    /**
     * @Column(type="string")
     */
    protected $website = null;

    /**
     * @Column(type="string")
     */
    protected $picture = null;

    /**
     * @Column(type="integer")
     */
    protected $activeAddress = null;

    /**
     * @OneToMany(targetEntity="UserAddress", mappedBy="userProfile", cascade={"persist", "remove"}, orphanRemoval=TRUE)
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
