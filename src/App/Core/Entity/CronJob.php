<?php

namespace App\Core\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="app_cronjob")
 */
class CronJob
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var bool
     * @Column(type="boolean", options={"default":true})
     */
    protected $enabled;

    /**
     * @var int
     * @Column(type="smallint", options={"default":5})
     */
    protected $priority;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $script_file;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $last_exec;

    /**
     * @var int
     * @Column(type="bigint", options={"default":3600})
     */
    protected $exec_every;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getScriptFile()
    {
        return $this->script_file;
    }

    /**
     * @param string $script_file
     *
     * @return $this
     */
    public function setScriptFile(string $script_file)
    {
        $this->script_file = $script_file;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastExec()
    {
        return $this->last_exec;
    }

    /**
     * @param \DateTime $last_exec
     *
     * @return $this
     */
    public function setLastExec(\DateTime $last_exec)
    {
        $this->last_exec = $last_exec;

        return $this;
    }

    /**
     * @return int
     */
    public function getExecEvery()
    {
        return $this->exec_every;
    }

    /**
     * @param int $exec_every
     *
     * @return $this
     */
    public function setExecEvery(int $exec_every)
    {
        $this->exec_every = $exec_every;

        return $this;
    }
}
