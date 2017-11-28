<?php

namespace App\Forum\Entity;

use App\Account\Entity\User;
use App\Forum\ForumHelper;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="forum_posts")
 */
class Post
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Forum\Entity\Thread
     * @ManyToOne(targetEntity="Thread", inversedBy="posts")
     * @JoinColumn(name="thread_id", referencedColumnName="id", nullable=false)
     */
    protected $thread;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $post_number;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $subject;

    /**
     * @var string
     * @Column(type="text")
     */
    protected $message;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $created_on;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Forum\Entity\Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param \App\Forum\Entity\Thread $thread
     *
     * @return $this
     */
    public function setThread(Thread $thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostNumber()
    {
        return $this->post_number;
    }

    /**
     * @param int $post_number
     *
     * @return $this
     */
    public function setPostNumber(int $post_number)
    {
        $this->post_number = $post_number;

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
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function getFormattedMessage()
    {
        return ForumHelper::formatBbCode($this->message);
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

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
     * @return array
     */
    public function toArray()
    {
        return array(
            'id'          => $this->id,
            'thread'      => $this->thread,
            'post_number' => $this->post_number,
            'user'        => $this->user,
            'subject'     => $this->subject,
            'message'     => $this->message,
            'created_on'  => $this->created_on,
        );
    }
}
