<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name=oauth_clients)
 */
class Oauth2Clients
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type=integer)
     */
    protected $id;

    /**
     * @ORM\Column(type=string)
     */
    protected $name;

    /**
     * @ORM\Column(type=string)
     */
    protected $secret;

    /**
     * @ORM\Column(type=string)
     */
    protected $redirectUri;

    /**
     * @ORM\Column(type=string)
     */
    protected $grantTypes;

    /**
     * @ORM\Column(type=string)
     */
    protected $scope;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    public function setGrantTypes($grantTypes)
    {
        $this->grantTypes = $grantTypes;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }
}
