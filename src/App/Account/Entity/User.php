<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name=users)
 */
class User
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
    protected $username;

    /**
     * @ORM\Column(type=string)
     */
    protected $password;

    /**
     * @ORM\Column(type=string)
     */
    protected $email;

    /**
     * @ORM\Column(type=boolean)
     */
    protected $emailVerified;

    /**
     * @ORM\Column(type=datetime)
     */
    protected $createdOn;

    /**
     * @ORM\Column(type=string)
     */
    protected $createdIp;

    /**
     * @ORM\Column(type=datetime)
     */
    protected $lastOnlineAt;

    /**
     * @ORM\Column(type=string)
     */
    protected $lastIp;

    /**
     * @ORM\Column(type=boolean)
     */
    protected $developerStatus;

    /**
     * @ORM\Column(type=integer)
     */
    protected $credits;

    /**
     * @ORM\Column(type=string)
     */
    protected $preferredPaymentMethod;

    /**
     * @ORM\Column(type=string)
     * @ORM\OneToMany(targetEntity="UserPaymentMethods", mappedBy="user")
     */
    protected $paymentMethods;

    /**
     * @ORM\OneToOne(targetEntity="UserProfiles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $profile;

    /**
     * @ORM\OneToOne(targetEntity="UserSubscription")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $subscription;

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
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    public function getCreatedIp()
    {
        return $this->createdIp;
    }

    public function setCreatedIp($createdIp)
    {
        $this->createdIp = $createdIp;
    }

    public function getLastOnlineAt()
    {
        return $this->lastOnlineAt;
    }

    public function setLastOnlineAt($lastOnlineAt)
    {
        $this->lastOnlineAt = $lastOnlineAt;
    }

    public function getLastIp()
    {
        return $this->lastIp;
    }

    public function setLastIp($lastIp)
    {
        $this->lastIp = $lastIp;
    }

    public function getDeveloperStatus()
    {
        return $this->developerStatus;
    }

    public function setDeveloperStatus($developerStatus)
    {
        $this->developerStatus = $developerStatus;
    }

    public function getCredits()
    {
        return $this->credits;
    }

    public function setCredits($credits)
    {
        $this->credits = $credits;
    }

    public function getPreferredPaymentMethod()
    {
        return $this->preferredPaymentMethod;
    }

    public function setPreferredPaymentMethod($preferredPaymentMethod)
    {
        $this->preferredPaymentMethod = $preferredPaymentMethod;
    }

    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods($paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }
}
