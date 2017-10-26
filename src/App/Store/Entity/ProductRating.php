<?php

namespace App\Store\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_product_rating")
 */
class ProductRating
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type=integer)
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="ratings")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type=integer)
     */
    protected $rating;

    /**
     * @ORM\Column(type=string)
     */
    protected $comment;

    /**
     * @ORM\Column(type=boolean)
     */
    protected $approved;

    /**
     * @ORM\Column(type=boolean)
     */
    protected $spam;

    /**
     * @ORM\Column(type=datetime)
     */
    protected $createdOn;

    /**
     * @ORM\Column(type=datetime)
     */
    protected $updatedOn;

    public function getId()
    {
        return $this->id;
    }

}
