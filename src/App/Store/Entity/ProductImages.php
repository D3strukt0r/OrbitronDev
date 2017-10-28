<?php

namespace App\Store\Entity;

/**
 * @Entity
 * @Table(name="store_product_images")
 */
class ProductImages
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Product", inversedBy="images")
     * @JoinColumn(name="product_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $product;

    /**
     * @Column(type="string")
     */
    protected $image;

    public function getId()
    {
        return $this->id;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
}
