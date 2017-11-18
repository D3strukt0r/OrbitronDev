<?php

namespace App\Account\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * OAuthScope
 * @Entity
 * @Table(name="oauth_scopes")
 */
class OAuthScope
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
     * @Column(type="string", length=80, unique=true)
     */
    protected $scope;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":false})
     */
    protected $is_default = false;

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
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
     * Get is_default
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->is_default;
    }

    /**
     * Set is_default
     *
     * @param bool $is_default
     *
     * @return $this
     */
    public function setDefault($is_default)
    {
        $this->is_default = $is_default;

        return $this;
    }

    public function toArray()
    {
        return array(
            'id'         => $this->id,
            'scope'      => $this->scope,
            'name'       => $this->name,
            'is_default' => $this->is_default,
        );
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
