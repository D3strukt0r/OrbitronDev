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
 * OAuthAuthorizationCode
 * @Entity(repositoryClass="App\Account\Repository\OAuthAuthorizationCodeRepository")
 * @Table(name="oauth_authorization_codes")
 */
class OAuthAuthorizationCode
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
     * @Column(type="string", length=40, unique=true)
     */
    protected $code;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $client_id;

    /**
     * @var int
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
     * @Column(type="string", length=200)
     */
    protected $redirect_uri;

    /**
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    protected $scope;

    /**
     * @var \App\Account\Entity\OAuthClient
     * @ManyToOne(targetEntity="OAuthClient")
     * @JoinColumn(name="client_id", referencedColumnName="client_identifier")
     */
    protected $client;

    /**
     * @var \App\Account\Entity\User
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * Set client_id
     *
     * @param string $clientId
     *
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get user_identifier
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_id
     *
     * @param int $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

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
     * Set expires
     *
     * @param \DateTime $expires
     *
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get redirect_uri
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    /**
     * Set redirect_uri
     *
     * @param string $redirectUri
     *
     * @return $this
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirect_uri = $redirectUri;

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
     * Set scope
     *
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get client
     *
     * @return \App\Account\Entity\OAuthClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set client
     *
     * @param \App\Account\Entity\OAuthClient $client
     *
     * @return $this
     */
    public function setClient(OAuthClient $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Account\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param \App\Account\Entity\User $user
     *
     * @return $this
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'code'      => $this->code,
            'client_id' => $this->client_id,
            'user_id'   => $this->user_id,
            'expires'   => $this->expires,
            'scope'     => $this->scope,
        );
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray($params)
    {
        $code = new self();
        foreach ($params as $property => $value) {
            $code->$property = $value;
        }

        return $code;
    }
}
