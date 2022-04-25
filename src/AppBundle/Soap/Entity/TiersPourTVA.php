<?php

namespace AppBundle\Soap\Entity;



class TiersPourTVA
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     *
     */
    public $name;

    /**
     * TiersPourTVA constructor.
     * @param int $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }


}
