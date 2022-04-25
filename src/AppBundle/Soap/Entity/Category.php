<?php

namespace AppBundle\Soap\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 */
class Category
{
    /**
     * @var integer
     *
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $visible;










    /**
     * Constructor
     */
    public function __construct($id,$name,$visible)
    {
        $this->id = $id;
        $this->name=$name;
        $this->visible=$visible;



    }


}
