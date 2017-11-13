<?php

namespace App\Blog\Entity;

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
 * @Table(name="blog_posts")
 */
class Post
{
    /**
     * @var integer
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Blog\Entity\Blog
     * @ManyToOne(targetEntity="Blog", inversedBy="posts")
     * @JoinColumn(name="blog_id", referencedColumnName="id")
     */
    protected $blog;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $title;

    /**
     * @var null|string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $published_on;

    protected $categories;

    protected $tags;

    /**
     * @var null|string
     * @Column(type="string", nullable=true)
     */
    protected $header_image;

    /**
     * @var null|string
     * @Column(type="text", nullable=true)
     */
    protected $story;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Blog\Entity\Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @param \App\Blog\Entity\Blog $blog
     *
     * @return $this
     */
    public function setBlog(Blog $blog)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * @return \App\Account\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param \App\Account\Entity\User $author
     *
     * @return $this
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
