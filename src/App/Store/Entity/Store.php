<?php

namespace App\Store\Entity;

use App\Account\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="stores")
 */
class Store
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="store_id")
     */
    protected $id;

    /**
     * @Column(type="string", name="name")
     */
    protected $name;

    /**
     * @Column(type="string", name="url")
     */
    protected $url;

    /**
     * @Column(type="json_array", name="keywords", nullable=true)
     */
    protected $keywords;

    /**
     * @Column(type="string", name="description", nullable=true)
     */
    protected $description;

    /**
     * @Column(type="string", name="google_analytics_id", nullable=true)
     */
    protected $googleAnalyticsId;

    /**
     * @Column(type="string", name="google_web_developer", nullable=true)
     */
    protected $googleWebDeveloper;

    /**
     * @Column(type="string")
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @Column(type="string", options={"default":0})
     */
    protected $activePaymentMethod;

    /**
     * @OneToMany(targetEntity="StorePaymentMethods", mappedBy="store", cascade={"persist", "remove"}, orphanRemoval=true)
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
