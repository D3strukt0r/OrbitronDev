<?php

namespace App\Blog\Entity;

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
 * @Table(name="blog_comments")
 */
class Comment
{
    /**
     * @var integer
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Blog\Entity\Post
     * @ManyToOne(targetEntity="Post", inversedBy="comments")
     * @JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
     */
    protected $post;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     * @Column(type="text")
     */
    protected $comment;

    /**
     * @var \App\Blog\Entity\Comment
     * @OneToMany(targetEntity="Comment", mappedBy="parent")
     */
    protected $responses;

    /**
     * @var null|\App\Blog\Entity\Comment
     * @ManyToOne(targetEntity="Comment", inversedBy="responses")
     * @JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $date;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Blog\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param \App\Blog\Entity\Post $post
     *
     * @return $this
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

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
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return array
     */
    public function getResponses()
    {
        return $this->responses->toArray();
    }

    /**
     * @param \App\Blog\Entity\Comment $comment
     *
     * @return $this
     */
    public function addResponse(Comment $comment)
    {
        $this->responses->add($comment);
        $comment->setPost($this->post);
        $comment->setParent($this);

        return $this;
    }

    /**
     * @param \App\Blog\Entity\Comment $comment
     *
     * @return $this
     */
    public function removeResponse(Comment $comment)
    {
        if ($this->responses->contains($comment)) {
            $this->responses->removeElement($comment);
            $comment->setPost(null);
            $comment->setParent(null);
        }

        return $this;
    }

    /**
     * @return null|\App\Blog\Entity\Comment
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param null|\App\Blog\Entity\Comment $parent
     *
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }
}
