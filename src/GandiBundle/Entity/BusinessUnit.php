<?php

namespace GandiBundle\Entity;

/**
 * Class BusinessUnit
 */
class BusinessUnit{


    /**
     * @var string[]
     */
    public $forbidden_tlds;
    /**
     * @var Int
     */
    public $id	;
    /**
     * @var string
     */
    public $name;

    /**
     * BusinessUnit constructor.
     * @param Int $forbidden_tlds
     * @param Int $id
     * @param Int $name
     */
    public function __construct($forbidden_tlds, $id, $name)
    {
        $this->forbidden_tlds = $forbidden_tlds;
        $this->id = $id;
        $this->name = $name;
    }


}

