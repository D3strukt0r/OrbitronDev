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
 * @Entity
 * @Table(name="oauth_clients")
 */
class Oauth2Clients
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="client_id")
     */
    protected $id;

    /**
     * @Column(type="string", name="client_name", nullable=true)
     */
    protected $name;

    /**
     * @Column(type="string", name="client_secret", nullable=true)
     */
    protected $secret;

    /**
     * @Column(type="string", name="redirect_uri")
     */
    protected $redirectUri;

    /**
     * @Column(type="string", name="grant_types", nullable=true)
     */
    protected $grantTypes;

    /**
     * @Column(type="string", name="scope", nullable=true)
     */
    protected $scope;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="user_id", nullable=true)
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    public function setGrantTypes($grantTypes)
    {
        $this->grantTypes = $grantTypes;
        return $this;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }
}
