<?php

namespace AppBundle\Soap\Entity;


/**
 * Product
 *

 */
class ProductSimplified
{
    /**
     * @var integer
     *
     */
    public $id;

    /**
     * @var string
     * @Soap\ComplexType("string")
     */
    public $name;


    /**
     * Product constructor.
     * @param int $id
     * @param string $name


     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;

    }


}
