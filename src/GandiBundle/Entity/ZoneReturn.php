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
class ZoneReturn{


    public $date_updated;
    /**
* @var int
     */
    public $domains;
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
    public $owner;
    /**
* @var bool
     */
    public $public;
    /**
* @var int
     */
    public $numVersionActive;
    /**
* @var string[]
     */
    public $versions;

    /**
     * ZoneReturn constructor.
     * @param $date_updated
     * @param $domains
     * @param $id
     * @param $name
     * @param $owner
     * @param $public
     * @param $numVersionActive
     * @param $versions
     */
    public function __construct($date_updated, $domains, $id, $name, $owner, $public, $numVersionActive, $versions)
    {
        $this->date_updated = $date_updated;
        $this->domains = $domains;
        $this->id = $id;
        $this->name = $name;
        $this->owner = $owner;
        $this->public = $public;
        $this->numVersionActive = $numVersionActive;
        $this->versions = $versions;
    }


}