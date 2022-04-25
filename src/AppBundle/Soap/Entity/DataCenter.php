<?php

namespace AppBundle\Soap\Entity;


/**
 * DataCenter
 *
 */
class DataCenter
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     *
     */
    public $country;

    /**
     * @var string
     *
     */
    public $dcCode;


    /**
     * @var string
     *
     */
    public $iso;

    /**
     * @var string
     *
     */
    public $name;

    /**
     * DataCenter constructor.
     * @param int $id
     * @param string $country
     * @param string $dcCode
     * @param string $iso
     * @param string $name
     */
    public function __construct($id, $country, $dcCode, $iso, $name)
    {
        $this->id = $id;
        $this->country = $country;
        $this->dcCode = $dcCode;
        $this->iso = $iso;
        $this->name = $name;
    }


}
