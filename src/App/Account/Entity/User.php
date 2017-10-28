<?php

namespace App\Account\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="users")
 */
class User
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="user_id")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $username;

    /**
     * @Column(type="string")
     */
    protected $password;

    /**
     * @Column(type="string")
     */
    protected $email;

    /**
     * @Column(type="boolean")
     */
    protected $emailVerified = false;

    /**
     * @Column(type="datetime")
     */
    protected $createdOn;

    /**
     * @Column(type="string")
     */
    protected $createdIp;

    /**
     * @Column(type="datetime")
     */
    protected $lastOnlineAt;

    /**
     * @Column(type="string")
     */
    protected $lastIp;

    /**
     * @Column(type="boolean")
     */
    protected $developerStatus = false;

    /**
     * @Column(type="integer")
     */
    protected $credits = 0;

    /**
     * @Column(type="string")
     */
    protected $preferredPaymentMethod;

    /**
     * @OneToMany(targetEntity="UserPaymentMethods", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $paymentMethods;

    /**
     * @OneToOne(targetEntity="UserProfiles", mappedBy="user")
     * @JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $profile;

    /**
     * @OneToOne(targetEntity="UserSubscription", mappedBy="user")
     * @JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $subscription;

    public function __construct()
    {
        $this->paymentMethods = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
        return $this;
    }

    public function getCreatedIp()
    {
        return $this->createdIp;
    }

    public function setCreatedIp($createdIp)
    {
        $this->createdIp = $createdIp;
        return $this;
    }

    public function getLastOnlineAt()
    {
        return $this->lastOnlineAt;
    }

    public function setLastOnlineAt(\DateTime $lastOnlineAt)
    {
        $this->lastOnlineAt = $lastOnlineAt;
        return $this;
    }

    public function getLastIp()
    {
        return $this->lastIp;
    }

    public function setLastIp($lastIp)
    {
        $this->lastIp = $lastIp;
        return $this;
    }

    public function getDeveloperStatus()
    {
        return $this->developerStatus;
    }

    public function setDeveloperStatus($developerStatus)
    {
        $this->developerStatus = $developerStatus;
        return $this;
    }

    public function getCredits()
    {
        return $this->credits;
    }

    public function setCredits($credits)
    {
        $this->credits = $credits;
        return $this;
    }

    public function getPreferredPaymentMethod()
    {
        return $this->preferredPaymentMethod;
    }

    public function setPreferredPaymentMethod($preferredPaymentMethod)
    {
        $this->preferredPaymentMethod = $preferredPaymentMethod;
        return $this;
    }

    public function getPaymentMethods()
    {
        return $this->paymentMethods->toArray();
    }

    public function addPaymentMethod(UserPaymentMethods $paymentMethod)
    {
        $this->paymentMethods->add($paymentMethod);
        $paymentMethod->setUser($this);
        return $this;
    }

    public function removePaymentMethod(UserPaymentMethods $paymentMethod)
    {
        if ($this->paymentMethods->contains($paymentMethod)) {
            $this->paymentMethods->removeElement($paymentMethod);
            $paymentMethod->setUser(null);
        }
        return $this;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile(UserProfiles $profile)
    {
        $this->profile = $profile;
        return $this;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }

    public function setSubscription(UserSubscription $subscription)
    {
        $this->subscription = $subscription;
        return $this;
    }
}
