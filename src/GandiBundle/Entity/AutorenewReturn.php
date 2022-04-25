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
class AutorenewReturn{
    /**
     * @Svar boolean
     * autorenew status. 1 autorenew is active. 0 deactivate
     */
    public $active;
    /**
     * @var string
     * handle of the contact that activate/deactivate the autorenew
     */
    public $contact;
    /**
     * @var int
     */
    public $duration;
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $product_id;
    /**
     * @var int
     */
    public $product_type_id;

    function __construct($active, $contact, $duration, $id, $product_id, $product_type_id)
    {
        $this->active = $active;
        $this->contact = $contact;
        $this->duration = $duration;
        $this->id = $id;
        $this->product_id = $product_id;
        $this->product_type_id = $product_type_id;
    }


}