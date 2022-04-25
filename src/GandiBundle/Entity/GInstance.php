<?php

namespace GandiBundle\Entity;


/**
 * Class Instance
 */
class GInstance {
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $date_end;
    /**
     * @var int
     */
    public $id_g;

    /**
     * @param string $name
     * @param int $date_end
     * @param int $id_g
     */
    public function __construct($name,$date_end,$id_g){
        $this->name=$name;
        $this->date_end=$date_end;
        $this->id_g=$id_g;
    }
}