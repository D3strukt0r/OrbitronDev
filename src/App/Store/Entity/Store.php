<?php

namespace App\Store\Entity;

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
     * @ORM\Column(type=json_array)
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
     * @ORM\Column(type=string)
     * @ORM\OneToMany(targetEntity="StorePaymentMethods", mappedBy="store")
     */
    protected $savedPaymentMethods;

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

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getGoogleAnalyticsId()
    {
        return $this->googleAnalyticsId;
    }

    public function setGoogleAnalyticsId($googleAnalyticsId)
    {
        $this->googleAnalyticsId = $googleAnalyticsId;
    }

    public function getGoogleWebDeveloper()
    {
        return $this->googleWebDeveloper;
    }

    public function setGoogleWebDeveloper($googleWebDeveloper)
    {
        $this->googleWebDeveloper = $googleWebDeveloper;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getActivePaymentMethod()
    {
        return $this->activePaymentMethod;
    }

    public function setActivePaymentMethod($activePaymentMethod)
    {
        $this->activePaymentMethod = $activePaymentMethod;
    }

    public function getSavedPaymentMethods()
    {
        return $this->savedPaymentMethods;
    }

    public function setSavedPaymentMethods($savedPaymentMethods)
    {
        $this->savedPaymentMethods = $savedPaymentMethods;
    }
}
