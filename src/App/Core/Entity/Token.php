<?php

namespace App\Core\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="app_token")
 */
class Token
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
    protected $token;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $job;

    /**
     * @var null|\DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $expires;

    /**
     * @var null|array
     * @Column(type="json_array", nullable=true)
     */
    protected $optional_info;

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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param string $job
     *
     * @return $this
     */
    public function setJob(string $job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param \DateTime|null $expires
     *
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getOptionalInfo()
    {
        return $this->optional_info;
    }

    /**
     * @param array|null $optional_info
     *
     * @return $this
     */
    public function setOptionalInfo($optional_info)
    {
        $this->optional_info = $optional_info;

        return $this;
    }
}
