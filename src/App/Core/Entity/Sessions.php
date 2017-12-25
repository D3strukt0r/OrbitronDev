<?php

namespace App\Core\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="app_sessions")
 */
class Sessions
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="string", length=128)
     */
    protected $sess_id;

    /**
     * @var resource
     * @Column(type="blob")
     */
    protected $sess_data;

    /**
     * @var int
     * @Column(type="integer", options={"unsigned":true})
     */
    protected $sess_time;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $sess_lifetime;
}
