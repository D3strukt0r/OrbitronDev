<?php

namespace App\Store\Entity;

/**
 * @Entity
 * @Table(name="store_product_rating")
 */
class ProductRating
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Product", inversedBy="ratings")
     * @JoinColumn(name="product_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $product;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @Column(type="integer")
     */
    protected $rating;

    /**
     * @Column(type="string")
     */
    protected $comment;

    /**
     * @Column(type="boolean")
     */
    protected $approved;

    /**
     * @Column(type="boolean")
     */
    protected $spam;

    /**
     * @Column(type="datetime")
     */
    protected $createdOn;

    /**
     * @Column(type="datetime")
     */
    protected $updatedOn;

    public function getId()
    {
        return $this->id;
    }

}
