<?php

namespace App\Forum\Entity;

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
 * @Table(name="forum_boards")
 */
class Board
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Forum\Entity\Forum
     * @ManyToOne(targetEntity="Forum", inversedBy="boards")
     * @JoinColumn(name="forum_id", referencedColumnName="id", nullable=false)
     */
    protected $forum;

    /**
     * @var null|\App\Forum\Entity\Board
     * @ManyToOne(targetEntity="Board", inversedBy="boards")
     * @JoinColumn(name="parent_board_id", referencedColumnName="id")
     */
    protected $parent_board;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $title;

    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var int
     * @Column(type="smallint")
     */
    protected $type;

    const TYPE_BOARD = 1;
    const TYPE_CATEGORY = 2;

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
     * @var int
     * @Column(type="integer", options={"default":0})
     */
    protected $thread_count = 0;

    /**
     * @var int
     * @Column(type="integer", options={"default":0})
     */
    protected $post_count = 0;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="Board", mappedBy="parent_board", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $boards;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="Thread", mappedBy="board", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $threads;

    public function __construct()
    {
        $this->boards = new ArrayCollection();
        $this->threads = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Forum\Entity\Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * @param \App\Forum\Entity\Forum $forum
     *
     * @return $this
     */
    public function setForum(Forum $forum)
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return \App\Forum\Entity\Board|null
     */
    public function getParentBoard()
    {
        return $this->parent_board;
    }

    /**
     * @param \App\Forum\Entity\Board|null $parent_board
     *
     * @return $this
     */
    public function setParentBoard($parent_board)
    {
        $this->parent_board = $parent_board;

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
    public function setTitle(string $title)
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
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType(int $type)
    {
        $this->type = $type;

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
     * @return int
     */
    public function getThreadCount()
    {
        return $this->thread_count;
    }

    /**
     * @param int $threads
     *
     * @return $this
     */
    public function setThreadCount(int $threads)
    {
        $this->thread_count = $threads;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostCount()
    {
        return $this->post_count;
    }

    /**
     * @param int $posts
     *
     * @return $this
     */
    public function setPostCount(int $posts)
    {
        $this->post_count = $posts;

        return $this;
    }

    /**
     * @return \App\Forum\Entity\Thread[]
     */
    public function getThreads()
    {
        return $this->threads->toArray();
    }

    /**
     * @param \App\Forum\Entity\Thread $thread
     *
     * @return $this
     */
    public function addThread(Thread $thread)
    {
        $this->threads->add($thread);
        $thread->setBoard($this);

        return $this;
    }

    /**
     * @param \App\Forum\Entity\Thread $thread
     *
     * @return $this
     */
    public function removeThread(Thread $thread)
    {
        if ($this->threads->contains($thread)) {
            $this->threads->removeElement($thread);
        }

        return $this;
    }

    /**
     * @return \App\Forum\Entity\Board[]
     */
    public function getBoards()
    {
        return $this->boards->toArray();
    }

    /**
     * @param \App\Forum\Entity\Board $board
     *
     * @return $this
     */
    public function addBoard(Board $board)
    {
        $this->boards->add($board);
        $board->setParentBoard($this);

        return $this;
    }

    /**
     * @param \App\Forum\Entity\Board $board
     *
     * @return $this
     */
    public function removeBoard(Board $board)
    {
        if ($this->boards->contains($board)) {
            $this->boards->removeElement($board);
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
            'forum'          => $this->forum,
            'parent_board'   => $this->parent_board,
            'title'          => $this->title,
            'description'    => $this->description,
            'type'           => $this->type,
            'last_post_user' => $this->last_post_user,
            'last_post_time' => $this->last_post_time,
            'threads'        => $this->thread_count,
            'posts'          => $this->post_count,
        );
    }
}
