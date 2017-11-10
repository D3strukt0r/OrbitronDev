<?php

namespace App\Account\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * OAuthClient
 * @Entity(repositoryClass="App\Account\Repository\OAuthClientRepository")
 * @Table(name="oauth_clients")
 */
class OAuthClient extends EncryptableFieldEntity
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
     * @Column(type="string", length=50, unique=true)
     */
    protected $client_identifier;

    /**
     * @var string
     * @Column(type="string", length=20)
     */
    protected $client_secret;

    /**
     * @var string
     * @Column(type="string", length=255, options={"default":""})
     */
    protected $redirect_uri = '';

    /**
     * @var array
     * @Column(type="string", options={"default":""})
     */
    protected $scopes = '';

    /**
     * @var array
     * @Column(type="string", options={"default":""})
     */
    protected $users = '';

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->scopes = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * Set client_identifier
     *
     * @param string $clientIdentifier
     * @return OAuthClient
     */
    public function setClientIdentifier($clientIdentifier)
    {
        $this->client_identifier = $clientIdentifier;
        return $this;
    }

    /**
     * Get client_identifier
     *
     * @return string
     */
    public function getClientIdentifier()
    {
        return $this->client_identifier;
    }

    /**
     * Set client_secret
     *
     * @param string $clientSecret
     * @param bool   $encrypt
     *
     * @return \App\Account\Entity\OAuthClient
     */
    public function setClientSecret($clientSecret, $encrypt = false)
    {
        if ($encrypt) {
            $this->client_secret = $this->encryptField($clientSecret);
        } else {
            $this->client_secret = $clientSecret;
        }
        return $this;
    }

    /**
     * Get client_secret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * Verify client's secret
     *
     * @param string $clientSecret
     * @return boolean
     */
    public function verifyClientSecret($clientSecret)
    {
        return $this->verifyEncryptedFieldValue($this->getClientSecret(), $clientSecret);
    }

    /**
     * Set redirect_uri
     *
     * @param string $redirectUri
     * @return OAuthClient
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirect_uri = $redirectUri;
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
     * Set scopes
     *
     * @param array $scopes
     * @return OAuthClient
     */
    public function setScopes($scopes)
    {
        $this->scopes = implode(' ', $scopes);
        return $this;
    }

    /**
     * Get scopes
     *
     * @return array
     */
    public function getScopes()
    {
        $scopes = explode(' ', $this->scopes);
        return $scopes;
    }

    public function addScope($scope)
    {
        $scopes = $this->getScopes();
        $scopes[] = $scope;
        $this->setScopes($scopes);
        return $this;
    }

    public function removeScope($scope)
    {
        $scopes = $this->getScopes();
        if (in_array($scope, $scopes)) {
            $key = array_search($scope, $scopes);
            unset($scopes[$key]);
        }
        return $this;
    }

    /**
     * Set users (in charge)
     *
     * @param array $users
     * @return OAuthClient
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * Get users (in charge)
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function toArray()
    {
        return [
            'client_id' => $this->client_identifier,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri,
            'scopes' => $this->scopes
        ];
    }
}
