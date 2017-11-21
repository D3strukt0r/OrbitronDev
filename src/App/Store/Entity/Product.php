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
 * @Table(name="store_products")
 */
class Product
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Store\Entity\Store
     * @ManyToOne(targetEntity="Store", inversedBy="products")
     * @JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var array
     * @Column(type="json_array")
     */
    protected $name;

    /**
     * @var array
     * @Column(type="json_array")
     */
    protected $description;

    /**
     * @var array
     * @Column(type="json_array")
     */
    protected $price;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="ProductImages", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $images;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":false})
     */
    protected $downloadable = false;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="ProductFile", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $stock;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $last_edited;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $rating_count;

    /**
     * @var float
     * @Column(type="decimal")
     */
    protected $rating_average;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="ProductRating", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $ratings;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Store\Entity\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param \App\Store\Entity\Store $store
     *
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return \App\Account\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \App\Account\Entity\User $owner
     *
     * @return $this
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @param string $language
     *
     * @return $this
     */
    public function setName(string $name, string $language)
    {
        if (!is_array($this->name)) {
            $this->name = array();
        }

        $array = new ArrayCollection($this->name);
        $array->set($language, $name);
        $this->name = $array->toArray();

        return $this;
    }

    /**
     * @param string $language
     *
     * @return $this
     */
    public function removeName(string $language)
    {
        if (!is_array($this->name)) {
            $this->name = array();
        }

        $array = new ArrayCollection($this->name);
        if ($array->containsKey($language)) {
            $array->remove($language);
            $this->name = $array->toArray();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getDescriptions()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @param string $language
     *
     * @return $this
     */
    public function setDescription(string $description, string $language)
    {
        if (!is_array($this->description)) {
            $this->description = array();
        }

        $array = new ArrayCollection($this->description);
        $array->set($language, $description);
        $this->description = $array->toArray();

        return $this;
    }

    /**
     * @param string $language
     *
     * @return $this
     */
    public function removeDescription(string $language)
    {
        if (!is_array($this->description)) {
            $this->description = array();
        }

        $array = new ArrayCollection($this->description);
        if ($array->containsKey($language)) {
            $array->remove($language);
            $this->description = $array->toArray();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPrices()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @param string $currency
     *
     * @return $this
     */
    public function setPrice(float $price, string $currency)
    {
        if (!is_array($this->price)) {
            $this->price = array();
        }

        $array = new ArrayCollection($this->price);
        $array->set($currency, $price);
        $this->price = $array->toArray();

        return $this;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function removePrice(string $currency)
    {
        if (!is_array($this->price)) {
            $this->price = array();
        }

        $array = new ArrayCollection($this->price);
        if ($array->containsKey($currency)) {
            $array->remove($currency);
            $this->price = $array->toArray();
        }

        return $this;
    }

    /**
     * @return \App\Store\Entity\ProductImages[]
     */
    public function getImages()
    {
        return $this->images->toArray();
    }

    /**
     * @param \App\Store\Entity\ProductImages $image
     *
     * @return $this
     */
    public function addImage(ProductImages $image)
    {
        $this->images->add($image);
        $image->setProduct($this);

        return $this;
    }

    /**
     * @param \App\Store\Entity\ProductImages $image
     *
     * @return $this
     */
    public function removeImage(ProductImages $image)
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * @param bool $downloadable
     *
     * @return $this
     */
    public function setDownloadable(bool $downloadable)
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    /**
     * @return \App\Store\Entity\ProductFile
     */
    public function getFiles()
    {
        return $this->files->toArray();
    }

    /**
     * @param \App\Store\Entity\ProductFile $file
     *
     * @return $this
     */
    public function addFile(ProductFile $file)
    {
        $this->images->add($file);
        $file->setProduct($this);

        return $this;
    }

    /**
     * @param \App\Store\Entity\ProductFile $file
     *
     * @return $this
     */
    public function removeFile(ProductFile $file)
    {
        if ($this->images->contains($file)) {
            $this->images->removeElement($file);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     *
     * @return $this
     */
    public function setStock(int $stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastEdited()
    {
        return $this->last_edited;
    }

    /**
     * @param \DateTime $lastEdited
     *
     * @return $this
     */
    public function setLastEdited(\DateTime $lastEdited)
    {
        $this->last_edited = $lastEdited;

        return $this;
    }

    /**
     * @return int
     */
    public function getRatingCount()
    {
        return $this->rating_count;
    }

    /**
     * @param $ratingCount
     *
     * @return $this
     */
    public function setRatingCount(int $ratingCount)
    {
        $this->rating_count = $ratingCount;

        return $this;
    }

    /**
     * @return float
     */
    public function getRatingAverage()
    {
        return $this->rating_average;
    }

    /**
     * @param float $ratingAverage
     *
     * @return $this
     */
    public function setRatingAverage(float $ratingAverage)
    {
        $this->rating_average = $ratingAverage;

        return $this;
    }

    /**
     * @return \App\Store\Entity\ProductRating[]
     */
    public function getRatings()
    {
        return $this->ratings->toArray();
    }

    /**
     * @param \App\Store\Entity\ProductRating $rating
     *
     * @return $this
     */
    public function addRating(ProductRating $rating)
    {
        $this->images->add($rating);
        $rating->setProduct($this);

        return $this;
    }

    /**
     * @param \App\Store\Entity\ProductRating $rating
     *
     * @return $this
     */
    public function removeRating(ProductRating $rating)
    {
        if ($this->images->contains($rating)) {
            $this->images->removeElement($rating);
        }

        return $this;
    }
}
