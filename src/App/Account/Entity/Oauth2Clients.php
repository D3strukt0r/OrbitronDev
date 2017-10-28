<?php

namespace App\Account\Entity;

/**
 * @Entity
 * @Table(name="oauth_clients")
 */
class Oauth2Clients
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name = null;

    /**
     * @Column(type="string")
     */
    protected $secret = null;

    /**
     * @Column(type="string")
     */
    protected $redirectUri;

    /**
     * @Column(type="string")
     */
    protected $grantTypes = null;

    /**
     * @Column(type="string")
     */
    protected $scope = null;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $user = null;

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
