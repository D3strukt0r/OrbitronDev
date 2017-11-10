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
 * OAuthRefreshToken
 * @Entity(repositoryClass="App\Account\Repository\OAuthRefreshTokenRepository")
 * @Table(name="oauth_refresh_tokens")
 */
class OAuthRefreshToken
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
    protected $refresh_token;

    /**
     * @var string
     * @Column(type="integer")
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
     * @JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * @var OAuthUser
     * @ManyToOne(targetEntity="OAuthUser")
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
     * Set refresh_token
     *
     * @param string $refresh_token
     * @return OAuthRefreshToken
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

    /**
     * Get refresh_token
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * Set client_id
     *
     * @param string $clientId
     * @return OAuthRefreshToken
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
     * @return OAuthRefreshToken
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
     * @return OAuthRefreshToken
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
     * @return OAuthRefreshToken
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
     * @return OAuthRefreshToken
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

    /**
     * Set user
     *
     * @param OAuthUser $user
     * @return OAuthRefreshToken
     */
    public function setUser(OAuthUser $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return OAuthUser
     */
    public function getUser()
    {
        return $this->user;
    }

    public function toArray()
    {
        return [
            'refresh_token' => $this->refresh_token,
            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
            'expires' => $this->expires,
            'scope' => $this->scope,
        ];
    }

    public static function fromArray($params)
    {
        $token = new self();
        foreach ($params as $property => $value) {
            $token->$property = $value;
        }
        return $token;
    }
}
