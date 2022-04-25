<?php

namespace GandiBundle\Entity;

/**
 * Class ShippingAddress
 */
class ShippingAddress{


    /**
     * @var String
* @var string
     */
    public $city	 	;
    /**
     * @var String
* @var string
     */
    public $country	 	;
    /**
     * @var String
* @var string
     */
    public $family	 	;
    /**
     * @var String
* @var string
     */
    public $given	 	;
    /**
     * @var String
* @var string
     */
    public $orgname	 	;
    /**
     * @var String
* @var string
     */
    public $state	 	;
    /**
     * @var String
* @var string
     */
    public $streetaddr	 	;
    /**
     * @var String
* @var string
     */
    public $streetaddr2	 	;
    /**
     * @var String
* @var string
     */
    public $zip;

    /**
     * ShippingAddress constructor.
     * @param String $city
     * @param String $country
     * @param String $family
     * @param String $given
     * @param String $orgname
     * @param String $state
     * @param String $streetaddr
     * @param String $streetaddr2
     * @param String $zip
     */
    public function __construct($city, $country, $family, $given, $orgname, $state, $streetaddr, $streetaddr2, $zip)
    {
        $this->city = $city;
        $this->country = $country;
        $this->family = $family;
        $this->given = $given;
        $this->orgname = $orgname;
        $this->state = $state;
        $this->streetaddr = $streetaddr;
        $this->streetaddr2 = $streetaddr2;
        $this->zip = $zip;
    }


}

