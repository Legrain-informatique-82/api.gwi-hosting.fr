<?php


namespace AppBundle\Soap\Entity;


/**
 * Class InstanceSnapshot
 */
class InstanceSnapshot{


    /**
     * @var \DateTime
     */
    public $date_created;
    /**
     * @var string
     */
    public $name;
    /**
     * @var int
     */
    public $size;
    /**
     * @var string
     */
    public $type;

    /**
     * InstanceSnapshot constructor.
     * @param $date_created
     * @param $name
     * @param $size
     * @param $type
     */
    public function __construct($date_created, $name, $size, $type)
    {
        $this->date_created = $date_created;
        $this->name = $name;
        $this->size = $size;
        $this->type = $type;
    }


}
