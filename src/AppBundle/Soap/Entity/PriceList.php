<?php

namespace AppBundle\Soap\Entity;


/**
 * PriceList
 *
 */
class PriceList
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     *
     */
    public $name;


    /**
     * @var string
     *
     */
    public $isDefault;

    /**
     * PriceList constructor.
     * @param int $id
     * @param string $name
     * @param string $isDefault
     */
    public function __construct($id=null, $name=null, $isDefault=null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isDefault = $isDefault;
    }


}
