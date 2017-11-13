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
 * @Table(name="blogs")
 */
class Blog
{
    /**
     * @var integer
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
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var boolean
     * @Column(type="boolean", options={"default":false})
     */
    protected $closed;

    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $closed_message;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Column(type="json_array")
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
     * @var Post|ArrayCollection
     * @OneToMany(targetEntity="Post", mappedBy="blog", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $posts;

    public function __construct()
    {
        $this->keywords = new ArrayCollection();
    }

    /**
     * @return integer
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
     * @return boolean
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param boolean $closed
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
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords->toArray();
    }

    /**
     * @param string $keyword
     *
     * @return $this
     */
    public function addKeyword($keyword)
    {
        $this->keywords->add($keyword);

        return $this;
    }

    /**
     * @param string $keyword
     *
     * @return $this
     */
    public function removePaymentMethod($keyword)
    {
        if ($this->keywords->contains($keyword)) {
            $this->keywords->removeElement($keyword);
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
     * @return array
     */
    public function getPosts()
    {
        return $this->posts->toArray();
    }

    /**
     * @param \App\Blog\Entity\Post $post
     *
     * @return $this
     */
    public function addPost(Post $post)
    {
        $this->posts->add($post);
        $post->setBlog($this);

        return $this;
    }

    /**
     * @param \App\Blog\Entity\Post $post
     *
     * @return $this
     */
    public function removePost(Post $post)
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            $post->setBlog(null);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
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
        ];
    }
}
