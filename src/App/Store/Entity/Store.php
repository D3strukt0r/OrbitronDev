<?php

namespace App\Store\Entity;

use App\Account\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="stores")
 */
class Store
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
    protected $url;

    /**
     * @ORM\Column(type=json)
     */
    protected $keywords;

    /**
     * @ORM\Column(type=string)
     */
    protected $description;

    /**
     * @ORM\Column(type=string)
     */
    protected $googleAnalyticsId;

    /**
     * @ORM\Column(type=string)
     */
    protected $googleWebDeveloper;

    /**
     * @ORM\Column(type=string)
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @ORM\Column(type=string)
     */
    protected $activePaymentMethod;

    /**
     * @ORM\OneToMany(targetEntity="StorePaymentMethods", mappedBy="store", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $paymentMethods;

    public function __construct()
    {
        $this->paymentMethods = new ArrayCollection();
    }

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
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getGoogleAnalyticsId()
    {
        return $this->googleAnalyticsId;
    }

    public function setGoogleAnalyticsId($googleAnalyticsId)
    {
        $this->googleAnalyticsId = $googleAnalyticsId;
        return $this;
    }

    public function getGoogleWebDeveloper()
    {
        return $this->googleWebDeveloper;
    }

    public function setGoogleWebDeveloper($googleWebDeveloper)
    {
        $this->googleWebDeveloper = $googleWebDeveloper;
        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(User $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    public function getActivePaymentMethod()
    {
        return $this->activePaymentMethod;
    }

    public function setActivePaymentMethod($activePaymentMethod)
    {
        $this->activePaymentMethod = $activePaymentMethod;
        return $this;
    }

    public function getPaymentMethods()
    {
        return $this->paymentMethods->toArray();
    }

    public function addPaymentMethod(StorePaymentMethods $paymentMethod)
    {
        $this->paymentMethods->add($paymentMethod);
        $paymentMethod->setUser($this);
        return $this;
    }

    public function removePaymentMethod(StorePaymentMethods $paymentMethod)
    {
        if ($this->paymentMethods->contains($paymentMethod)) {
            $this->paymentMethods->removeElement($paymentMethod);
            $paymentMethod->setUser(null);
        }
        return $this;
    }
}
