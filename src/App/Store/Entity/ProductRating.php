<?php

namespace App\Store\Entity;

use App\Account\Entity\User;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="store_product_rating")
 */
class ProductRating
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Store\Entity\Product
     * @ManyToOne(targetEntity="Product", inversedBy="ratings")
     * @JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    protected $product;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $rating;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $comment;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $approved;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $spam;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $created_on;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $updated_on;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Store\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param \App\Store\Entity\Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return \App\Account\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \App\Account\Entity\User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     *
     * @return $this
     */
    public function setRating(int $rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment(string $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     *
     * @return $this
     */
    public function setApproved(bool $approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSpam()
    {
        return $this->spam;
    }

    /**
     * @param bool $spam
     *
     * @return $this
     */
    public function setSpam(bool $spam)
    {
        $this->spam = $spam;

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
     * @return \DateTime
     */
    public function getUpdatedOn()
    {
        return $this->updated_on;
    }

    /**
     * @param \DateTime $updated_on
     *
     * @return $this
     */
    public function setUpdatedOn(\DateTime $updated_on)
    {
        $this->updated_on = $updated_on;

        return $this;
    }
}
