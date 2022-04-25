<?php

namespace AppBundle\Soap\Entity;


/**
 * ZipCode
 */
class ZipCode
{
    /**
     * @var integer
     **/
    public $id;

    /**
     * @var string
     */
    public $name;



    /**
     * Constructor
     */
    public function __construct($id=null,$name=null)
    {
        $this->id=$id;
        $this->name=$name;
    }


}
