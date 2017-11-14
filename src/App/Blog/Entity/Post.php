<?php

namespace App\Blog\Entity;

use App\Account\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
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
     * @JoinColumn(name="blog_id", referencedColumnName="id", nullable=false)
     */
    protected $blog;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
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

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\App\Blog\Entity\Category[]
     * @ManyToMany(targetEntity="Category", inversedBy="posts")
     * @JoinTable(name="blog_m2m_post_categories",
     *      joinColumns={@JoinColumn(name="post_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     */
    protected $categories;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\App\Blog\Entity\Post[]
     * @ManyToMany(targetEntity="Tag", inversedBy="posts")
     * @JoinTable(name="blog_m2m_post_tags",
     *      joinColumns={@JoinColumn(name="post_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     */
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
     * @var \Doctrine\Common\Collections\ArrayCollection|\App\Blog\Entity\Comment[]
     * @OneToMany(targetEntity="Comment", mappedBy="post")
     */
    protected $comments;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

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

    /**
     * @return \DateTime
     */
    public function getPublishedOn()
    {
        return $this->published_on;
    }

    /**
     * @param \DateTime $published_on
     *
     * @return $this
     */
    public function setPublishedOn(\DateTime $published_on)
    {
        $this->published_on = $published_on;

        return $this;
    }

    /**
     * @param \App\Blog\Entity\Category $category
     */
    public function addCategory(Category $category)
    {
        if ($this->categories->contains($category)) {
            return;
        }
        $this->categories->add($category);
        $category->addPost($this);
    }

    /**
     * @param \App\Blog\Entity\Category $category
     */
    public function removeCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            return;
        }
        $this->categories->removeElement($category);
        $category->removePost($this);
    }

    /**
     * @param \App\Blog\Entity\Tag $tag
     */
    public function addTag(Tag $tag)
    {
        if ($this->tags->contains($tag)) {
            return;
        }
        $this->tags->add($tag);
        $tag->addPost($this);
    }

    /**
     * @param \App\Blog\Entity\Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            return;
        }
        $this->tags->removeElement($tag);
        $tag->removePost($this);
    }

    /**
     * @return null|string
     */
    public function getHeaderImage()
    {
        return $this->header_image;
    }

    /**
     * @param null|string $header_image
     *
     * @return $this
     */
    public function setHeaderImage($header_image)
    {
        $this->header_image = $header_image;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStory()
    {
        return $this->story;
    }

    /**
     * @param null|string $story
     *
     * @return $this
     */
    public function setStory($story)
    {
        $this->story = $story;

        return $this;
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->comments->toArray();
    }

    /**
     * @param \App\Blog\Entity\Comment $comment
     *
     * @return $this
     */
    public function addComment(Comment $comment)
    {
        $this->comments->add($comment);
        $comment->setPost($this);

        return $this;
    }

    /**
     * @param \App\Blog\Entity\Comment $comment
     *
     * @return $this
     */
    public function removeComment(Comment $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            $comment->setPost(null);
        }

        return $this;
    }
}
