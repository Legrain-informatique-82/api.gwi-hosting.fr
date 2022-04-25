<?php

namespace AppBundle\Soap\Entity;

use \AppBundle\Soap\Entity;

/**
 * User
 */
class User
{


    public function __construct($id, $name, $firstname, $email, $address1, $address2, $address3,  $city=null,  $zipCode=null, $phone, $active,  $agency=null, $isGestionnaire=null, $cellPhone=null, $workPhone=null, $companyName=null, $codeClient=null, $numTVA=null, $tiersPourTVA=null){
        $this->id=$id;
        $this->name = $name;
        $this->firstname=$firstname;
        $this->email=$email;
        $this->address1=$address1;
        $this->address2=$address2;
        $this->address3=$address3;
        $this->city=$city;
        $this->zipcode=$zipCode;
        $this->phone=$phone;
        $this->active=$active;
        $this->agency=$agency;
        $this->isGestionnaire=$isGestionnaire;
        $this->cellPhone = $cellPhone;
        $this->workPhone = $workPhone;
        $this->companyName = $companyName;
        $this->codeClient = $codeClient;
        $this->numTVA = $numTVA;
        $this->tiersPourTVA = $tiersPourTVA;

    }



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
     * @var string
     *
     */
    public $firstname;

    /**
     * @var string
     *
     */
    public $email;


    /**
     * @var string

     */
    public $address1;

    /**
     * @var string

     */
    public $address2;

    /**
     * @var string

     */
    public $address3;


    /**
     * @var \AppBundle\Soap\Entity\City
     */
    public $city;

    /**
     * @var \AppBundle\Soap\Entity\ZipCode
     */
    public $zipcode;

    /**
     * @var \AppBundle\Soap\Entity\Agency
     */
    public $agency;

    /**
     * @var string
     *
     */
    public $phone;

    /**
     * @var string
     *
     */
    public $cellPhone;

    /**
     * @var string
     *
     */
    public $workPhone;

    /**
     *  @var string
     *
     */
    public $companyName;
    /**
     *  @var string
     *
     */
    public $codeClient;
    /**
     *  @var string
     *
     */
    public $numTVA;

    /**
     * @var \AppBundle\Soap\Entity\TiersPourTVA
     */
    public $tiersPourTVA;



    /**
     * @var string
     *
     */
    public $active;

    /**
     * @var boolean
     *
     */
    public $isGestionnaire;



}
