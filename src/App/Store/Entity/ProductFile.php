<?php

namespace App\Store\Entity;

/**
 * @Entity
 * @Table(name="store_product_files")
 */
class ProductFile
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
    protected $file;

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

    public function getImages()
    {
        return $this->file;
    }

    public function setImages($file)
    {
        $this->file = $file;
        return $this;
    }
}
