<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 23/06/15
 * Time: 15:10
 */
namespace GandiBundle\Entity;

/**
 * Class AutorenewReturn
 */
class ZoneRecordReturn{


    /**
* @var int
     */
    public $id;
    /**
* @var string
     */
    public $name;
    /**
* @var string
     */
    public $ttl;
    /**
* @var string
     */
    public $type;
    /**
* @var string
     */
    public $value;

    /**
     * ZoneRecordReturn constructor.
     * @param Int $id
     * @param String $name
     * @param String $ttl
     * @param String $type
     * @param String $value
     */
    public function __construct($id, $name, $ttl, $type, $value)
    {
        $this->id = $id;
        $this->name = $name;
        $this->ttl = $ttl;
        $this->type = $type;
        $this->value = $value;
    }


}