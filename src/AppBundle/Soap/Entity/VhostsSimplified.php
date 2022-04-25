<?php

namespace AppBundle\Soap\Entity;


/**
 * Vhosts
 *
 */
class VhostsSimplified
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var \DateTime
     *
     */
    public $dateCrea;


    /**
     * @var string
     *
     */
    public $name;
    /**
     * @var string
     */
public $serverName;


    /**
     * @var int
     */
    public $serverId;

    /**
     * @var bool
     *
     */
    public $inMaintenance;

    public $mutualise;

    /**
     * Vhosts constructor.
     * @param int $id
     * @param string $dateCrea
     * @param string $name
     */
    public function __construct($id, $dateCrea, $name, $serverName,$serverId,$inMaintenance=null,$mutualise=false)
    {
        $this->id = $id;
        $this->dateCrea = $dateCrea;
        $this->name = $name;
        $this->serverId = $serverId;
        $this->serverName = $serverName;
        $this->inMaintenance = $inMaintenance;
        $this->mutualise = $mutualise;
    }


}
