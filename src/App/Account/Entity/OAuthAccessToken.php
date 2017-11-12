<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * OAuthAccessToken
 * @Entity(repositoryClass="App\Account\Repository\OAuthAccessTokenRepository")
 * @Table(name="oauth_access_tokens")
 */
class OAuthAccessToken
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
     * @Column(type="string", length=40, unique=true)
     */
    protected $token;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $client_id;

    /**
     * @var string
     * @Column(type="integer", nullable=true)
     */
    protected $user_id;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $expires;

    /**
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    protected $scope;

    /**
     * @var OAuthClient
     * @ManyToOne(targetEntity="OAuthClient")
     * @JoinColumn(name="client_id", referencedColumnName="client_identifier")
     */
    protected $client;

    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return OAuthAccessToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set client_id
     *
     * @param string $clientId
     *
     * @return OAuthAccessToken
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set user_id
     *
     * @param string $userId
     *
     * @return OAuthAccessToken
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_identifier
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     *
     * @return OAuthAccessToken
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set scope
     *
     * @param string $scope
     *
     * @return OAuthAccessToken
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set client
     *
     * @param OAuthClient $client
     *
     * @return OAuthAccessToken
     */
    public function setClient(OAuthClient $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return OAuthClient
     */
    public function getClient()
    {
        return $this->client;
    }

    public static function fromArray($params)
    {
        $token = new self();
        foreach ($params as $property => $value) {
            $token->$property = $value;
        }

        return $token;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return OAuthAccessToken
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function toArray()
    {
        return array(
            'token'     => $this->token,
            'client_id' => $this->client_id,
            'user_id'   => $this->user_id,
            'expires'   => $this->expires,
            'scope'     => $this->scope,
        );
    }
}