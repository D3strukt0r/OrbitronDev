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
 * @Table(name="forums")
 */
class Forum
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $name;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $url;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="\App\Account\Entity\User")
     * @JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    protected $owner;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":false})
     */
    protected $closed = false;

    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $closed_message;

    /**
     * @var null|array
     * @Column(type="json_array", nullable=true)
     */
    protected $keywords;

    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $google_analytics_id;

    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $google_web_developer;

    /**
     * @var array|null
     * @Column(type="json_array", nullable=true)
     */
    protected $links;

    /**
     * @var string|null
     * @Column(type="string", nullable=true, options={"default":"en-US"})
     */
    protected $language;

    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $copyright;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @OneToMany(targetEntity="Board", mappedBy="forum", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $boards;

    public function __construct()
    {
        $this->boards = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return \App\Account\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \App\Account\Entity\User $owner
     *
     * @return $this
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

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
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClosedMessage()
    {
        return $this->closed_message;
    }

    /**
     * @param string|null $closed_message
     *
     * @return $this
     */
    public function setClosedMessage($closed_message)
    {
        $this->closed_message = $closed_message;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keyword
     *
     * @return $this
     */
    public function addKeyword($keyword)
    {
        if (!is_array($this->keywords)) {
            $this->keywords = array();
        }

        $array = new ArrayCollection($this->keywords);
        $array->add($keyword);
        $this->keywords = $array->toArray();

        return $this;
    }

    /**
     * @param string $keyword
     *
     * @return $this
     */
    public function removeKeyword($keyword)
    {
        if (!is_array($this->keywords)) {
            $this->keywords = array();
        }

        $array = new ArrayCollection($this->keywords);
        if ($array->contains($keyword)) {
            $array->removeElement($keyword);
            $this->keywords = $array->toArray();
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGoogleAnalyticsId()
    {
        return $this->google_analytics_id;
    }

    /**
     * @param string|null $id
     *
     * @return $this
     */
    public function setGoogleAnalyticsId($id)
    {
        $this->google_analytics_id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGoogleWebDeveloper()
    {
        return $this->google_web_developer;
    }

    /**
     * @param string|null $google_web_dev
     *
     * @return $this
     */
    public function setGoogleWebDeveloper($google_web_dev)
    {
        $this->google_web_developer = $google_web_dev;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param array|null $links
     *
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string|null $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @param string|null $copyright
     *
     * @return $this
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return $this
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

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
        $board->setForum($this);

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
            'id'                   => $this->id,
            'name'                 => $this->name,
            'url'                  => $this->url,
            'owner'                => $this->owner,
            'closed'               => $this->closed,
            'closed_message'       => $this->closed_message,
            'keywords'             => $this->keywords,
            'description'          => $this->description,
            'google_analytics_id'  => $this->google_analytics_id,
            'google_web_developer' => $this->google_web_developer,
            'links'                => $this->links,
            'language'             => $this->language,
            'copyright'            => $this->copyright,
            'created'              => $this->created,
        );
    }
}
