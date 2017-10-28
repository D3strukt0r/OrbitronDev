<?php

namespace App\Store\Entity;

use App\Account\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="store_products")
 */
class Product
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string")
     * @ManyToOne(targetEntity="Store")
     * @JoinColumn(name="store_id", referencedColumnName="id")
     */
    protected $store;

    /**
     * @Column(type="string")
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @Column(type="json_array")
     */
    protected $name;

    /**
     * @Column(type="json_array")
     */
    protected $description;
    /**
     * @Column(type="json_array")
     */
    protected $price;

    /**
     * @OneToMany(targetEntity="ProductImages", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $images;

    /**
     * @Column(type="boolean")
     */
    protected $downloadable;

    /**
     * @OneToMany(targetEntity="ProductFiles", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $files;

    /**
     * @Column(type="integer")
     */
    protected $stock;

    /**
     * @Column(type="datetime")
     */
    protected $lastEdited;

    /**
     * @Column(type="integer")
     */
    protected $ratingCount;

    /**
     * @Column(type="decimal")
     */
    protected $ratingAverage;

    /**
     * @OneToMany(targetEntity="ProductRating", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $ratings;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStore()
    {
        return $this->store;
    }

    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor(User $author)
    {
        $this->author = $author;
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

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function getImages()
    {
        return $this->images->toArray();
    }

    public function addImage(ProductImages $image)
    {
        $this->images->add($image);
        $image->setProduct($this);
        return $this;
    }

    public function removeImage(ProductImages $image)
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            $image->setProduct(null);
        }
        return $this;
    }

    public function getDownloadable()
    {
        return $this->downloadable;
    }

    public function setDownloadable($downloadable)
    {
        $this->downloadable = $downloadable;
        return $this;
    }

    public function getFiles()
    {
        return $this->files->toArray();
    }

    public function addFile(ProductFile $file)
    {
        $this->images->add($file);
        $file->setProduct($this);
        return $this;
    }

    public function removeFile(ProductFile $file)
    {
        if ($this->images->contains($file)) {
            $this->images->removeElement($file);
            $file->setProduct(null);
        }
        return $this;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function setStock($stock)
    {
        $this->stock = $stock;
        return $this;
    }

    public function getLastEdited()
    {
        return $this->lastEdited;
    }

    public function setLastEdited($lastEdited)
    {
        $this->lastEdited = $lastEdited;
        return $this;
    }

    public function getRatingCount()
    {
        return $this->ratingCount;
    }

    public function setRatingCount($ratingCount)
    {
        $this->ratingCount = $ratingCount;
        return $this;
    }

    public function getRatingAverage()
    {
        return $this->ratingAverage;
    }

    public function setRatingAverage($ratingAverage)
    {
        $this->ratingAverage = $ratingAverage;
        return $this;
    }

    public function getRatings()
    {
        return $this->ratings->toArray();
    }

    public function addRating(ProductRating $rating)
    {
        $this->images->add($rating);
        $rating->setProduct($this);
        return $this;
    }

    public function removeRating(ProductRating $rating)
    {
        if ($this->images->contains($rating)) {
            $this->images->removeElement($rating);
            $rating->setProduct(null);
        }
        return $this;
    }
}
