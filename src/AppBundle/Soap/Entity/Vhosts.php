<?php

namespace AppBundle\Soap\Entity;


/**
 * Vhosts
 *
 * @Soap\Alias("Vhosts")
 */
class Vhosts
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
     *
     */
    public $state;

    /**
     * @var bool
     *
     */
    public $inMaintenance;


    /**
     * Vhosts constructor.
     * @param int $id
     * @param string $dateCrea
     * @param string $name
     * @param string $state
     * @param boolean $inMaintenance
     */
    public function __construct($id, $dateCrea, $name, $state,$inMaintenance=null)
    {
        $this->id = $id;
        $this->dateCrea = $dateCrea;
        $this->name = $name;
        $this->state = $state;
        $this->inMaintenance = $inMaintenance;
    }


}
