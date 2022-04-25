<?php

namespace AppBundle\Soap\Entity;


/**
 * City
 */
class City
{
    /**
     * @var integer
     *
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $codeInsee;










    /**
     * Constructor
     */
    public function __construct($id,$name,$codeInsee)
    {
        $this->id = $id;
        $this->name=$name;
        $this->codeInsee=$codeInsee;



    }


}
