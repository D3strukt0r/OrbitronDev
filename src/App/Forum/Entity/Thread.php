<?php

namespace App\Forum\Entity;

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
 * @Table(name="forum_threads")
 */
class Thread
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var \App\Forum\Entity\Board
     * @ManyToOne(targetEntity="Board", inversedBy="threads")
     * @JoinColumn(name="board_id", referencedColumnName="id", nullable=false)
     */
    protected $board;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $topic;

    /**
     * @var int
     * @Column(type="integer", options={"default":0})
     */
    protected $views = 0;

    /**
     * @var int
     * @Column(type="integer", options={"default":0})
     */
    protected $replies = 0;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":false})
     */
    protected $sticky = false;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":false})
     */
    protected $closed = false;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $created_on;

    /**
     * @var null|\App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="last_post_user_id", referencedColumnName="id")
     */
    protected $last_post_user;

    /**
     * @var \DateTime|null
     * @Column(type="datetime", nullable=true)
     */
    protected $last_post_time;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="Post", mappedBy="thread", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return \App\Forum\Entity\Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @param \App\Forum\Entity\Board $board
     *
     * @return $this
     */
    public function setBoard(Board $board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     *
     * @return $this
     */
    public function setTopic(string $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     *
     * @return $this
     */
    public function setViews(int $views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * @return int
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * @param int $replies
     *
     * @return $this
     */
    public function setReplies(int $replies)
    {
        $this->replies = $replies;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSticky()
    {
        return $this->sticky;
    }

    /**
     * @param bool $sticky
     *
     * @return $this
     */
    public function setSticky(bool $sticky)
    {
        $this->sticky = $sticky;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     *
     * @return $this
     */
    public function setClosed(bool $closed)
    {
        $this->closed = $closed;

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
     * @param \DateTime $created_on
     *
     * @return $this
     */
    public function setCreatedOn(\DateTime $created_on)
    {
        $this->created_on = $created_on;

        return $this;
    }

    /**
     * @return \App\Account\Entity\User|null
     */
    public function getLastPostUser()
    {
        return $this->last_post_user;
    }

    /**
     * @param \App\Account\Entity\User|null $last_post_user
     *
     * @return $this
     */
    public function setLastPostUser($last_post_user)
    {
        $this->last_post_user = $last_post_user;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastPostTime()
    {
        return $this->last_post_time;
    }

    /**
     * @param \DateTime|null $last_post_time
     *
     * @return $this
     */
    public function setLastPostTime($last_post_time)
    {
        $this->last_post_time = $last_post_time;

        return $this;
    }

    /**
     * @return \App\Forum\Entity\Post[]
     */
    public function getPosts()
    {
        return $this->posts->toArray();
    }

    /**
     * @param \App\Forum\Entity\Post $post
     *
     * @return $this
     */
    public function addPost(Post $post)
    {
        $this->posts->add($post);
        $post->setThread($this);

        return $this;
    }

    /**
     * @param \App\Forum\Entity\Post $post
     *
     * @return $this
     */
    public function removePost(Post $post)
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id'             => $this->id,
            'user'           => $this->user,
            'board'          => $this->board,
            'topic'          => $this->topic,
            'views'          => $this->views,
            'replies'        => $this->replies,
            'sticky'         => $this->sticky,
            'closed'         => $this->closed,
            'created_on'     => $this->created_on,
            'last_post_user' => $this->last_post_user,
            'last_post_time' => $this->last_post_time,
        );
    }
}
