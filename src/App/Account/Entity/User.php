<?php

namespace App\Account\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="App\Account\Repository\UserRepository")
 * @Table(name="users")
 */
class User extends EncryptableFieldEntity
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $password;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $email;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":0})
     */
    protected $email_verified = false;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $created_on;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $created_ip;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $last_online_at;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $last_ip;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":false})
     */
    protected $developer_status = false;

    /**
     * @var int
     * @Column(type="integer", options={"default":0})
     */
    protected $credits = 0;

    /**
     * @var null|int
     * @Column(type="integer", nullable=true)
     */
    protected $preferred_payment_method;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="UserPaymentMethods", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $paymentMethods;

    /**
     * @var \App\Account\Entity\UserProfiles
     * @OneToOne(targetEntity="UserProfiles", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @JoinColumn(name="profile_id", referencedColumnName="id")
     */
    protected $profile;

    /**
     * @var \App\Account\Entity\UserSubscription
     * @OneToOne(targetEntity="UserSubscription", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @JoinColumn(name="subscription_id", referencedColumnName="id")
     */
    protected $subscription;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ManyToMany(targetEntity="User", mappedBy="myFriends")
     */
    private $friendsWithMe;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ManyToMany(targetEntity="User", inversedBy="friendsWithMe")
     * @JoinTable(name="user_friends",
     *     joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="friend_user_id", referencedColumnName="id")}
     * )
     */
    private $myFriends;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":false})
     */
    private $online = false;

    public function __construct()
    {
        $this->paymentMethods = new ArrayCollection();
        $this->friendsWithMe = new ArrayCollection();
        $this->myFriends = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     * @throws \Exception
     */
    public function setPassword($password)
    {
        $newPassword = $this->encryptField($password);

        if ($newPassword === false) {
            throw new \Exception('[Account] A hashed password could not be generated');
        }

        $this->password = $newPassword;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function verifyPassword($password)
    {
        return $this->verifyEncryptedFieldValue($this->getPassword(), $password);
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
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        return $this->email_verified;
    }

    /**
     * @param bool $emailVerified
     *
     * @return $this
     */
    public function setEmailVerified($emailVerified)
    {
        $this->email_verified = $emailVerified;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->created_on;
    }

    /**
     * @param \DateTime $createdOn
     *
     * @return $this
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->created_on = $createdOn;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedIp()
    {
        return $this->created_ip;
    }

    /**
     * @param string $createdIp
     *
     * @return $this
     */
    public function setCreatedIp($createdIp)
    {
        $this->created_ip = $createdIp;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastOnlineAt()
    {
        return $this->last_online_at;
    }

    /**
     * @param \DateTime $lastOnlineAt
     *
     * @return $this
     */
    public function setLastOnlineAt(\DateTime $lastOnlineAt)
    {
        $this->last_online_at = $lastOnlineAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastIp()
    {
        return $this->last_ip;
    }

    /**
     * @param string $lastIp
     *
     * @return $this
     */
    public function setLastIp($lastIp)
    {
        $this->last_ip = $lastIp;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDeveloperStatus()
    {
        return $this->developer_status;
    }

    /**
     * @param bool $developerStatus
     *
     * @return $this
     */
    public function setDeveloperStatus($developerStatus)
    {
        $this->developer_status = $developerStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * @param int $credits
     *
     * @return $this
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * @param int $credits
     *
     * @return \App\Account\Entity\User
     */
    public function giveCredits($credits)
    {
        return $this->setCredits($this->getCredits() + $credits);
    }

    /**
     * @param int $credits
     *
     * @return $this
     */
    public function takeCredits($credits)
    {
        return $this->setCredits($this->getCredits() - $credits);
    }

    /**
     * @return null|int
     */
    public function getPreferredPaymentMethod()
    {
        return $this->preferred_payment_method;
    }

    /**
     * @param null|int $preferredPaymentMethod
     *
     * @return $this
     */
    public function setPreferredPaymentMethod($preferredPaymentMethod)
    {
        $this->preferred_payment_method = $preferredPaymentMethod;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->paymentMethods->toArray();
    }

    /**
     * @param \App\Account\Entity\UserPaymentMethods $paymentMethod
     *
     * @return $this
     */
    public function addPaymentMethod(UserPaymentMethods $paymentMethod)
    {
        $this->paymentMethods->add($paymentMethod);
        $paymentMethod->setUser($this);

        return $this;
    }

    /**
     * @param \App\Account\Entity\UserPaymentMethods $paymentMethod
     *
     * @return $this
     */
    public function removePaymentMethod(UserPaymentMethods $paymentMethod)
    {
        if ($this->paymentMethods->contains($paymentMethod)) {
            $this->paymentMethods->removeElement($paymentMethod);
        }

        return $this;
    }

    /**
     * @return \App\Account\Entity\UserProfiles
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param \App\Account\Entity\UserProfiles $profile
     *
     * @return $this
     */
    public function setProfile(UserProfiles $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return \App\Account\Entity\UserSubscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param \App\Account\Entity\UserSubscription $subscription
     *
     * @return $this
     */
    public function setSubscription(UserSubscription $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * @return \App\Account\Entity\User[]
     */
    public function getFriends()
    {
        return $this->myFriends->toArray();
    }

    /**
     * @param \App\Account\Entity\User $user
     */
    public function addFriend(User $user)
    {
        if ($this->myFriends->contains($user)) {
            return;
        }
        $this->myFriends->add($user);
        $user->addFriend($this);
    }

    /**
     * @param \App\Account\Entity\User $user
     */
    public function removeFriend(User $user)
    {
        if (!$this->myFriends->contains($user)) {
            return;
        }
        $this->myFriends->removeElement($user);
        $user->removeFriend($this);
    }

    /**
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * @param bool $online
     *
     * @return $this
     */
    public function setOnline(bool $online)
    {
        $this->online = $online;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'user_id'                  => $this->id,
            'username'                 => $this->username,
            'password'                 => $this->password,
            'email'                    => $this->email,
            'email_verified'           => $this->email_verified,
            'created_on'               => $this->created_on,
            'created_ip'               => $this->created_ip,
            'last_online_at'           => $this->last_online_at,
            'last_ip'                  => $this->last_ip,
            'developer_status'         => $this->developer_status,
            'credits'                  => $this->credits,
            'preferred_payment_method' => $this->preferred_payment_method,
            'payment_methods'          => $this->paymentMethods->toArray(),
            'profile'                  => $this->profile->toArray(),
            'subscription'             => $this->subscription->toArray(),
            'scope'                    => null,
        );
    }
}
