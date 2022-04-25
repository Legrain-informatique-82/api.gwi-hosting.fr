<?php

namespace AppBundle\Soap\Security;
use AppBundle\Soap\Entity\City;
use AppBundle\Soap\Entity\ZipCode;


/**
 * User
 */
class UserSecurity
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
     *
     */
    public $password;

    /**
     * @var string
     *
     */
    public $registrationDate;

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
     * @var boolean
     */
    public $active;

    /**
     * @var mixed
     *
     */
    public $roles;

    /**
     * @var string
     *
     */
    public $codeClient;

    /**
     * @var string
     *
     */
    public $numTVA;

    /**
     * @var string
     *
     */
    public $companyName;

    /**
     * @var \AppBundle\Soap\Entity\TiersPourTVA
     */
    public $tiersPourTVA;
    /**
     * @var string
     *
     */
    public $urlApp;

    public function __construct($id,$name,$firstname,$email,$password,$registrationDate,$address1,$address2,$address3,City $city,ZipCode $zipCode,$phone,$cellPhone,$workPhone,$active,$agency,$roles, $codeTiers,$companyName,$numTVA,$tiersPourTva,$urlApp){
        $this->id=$id;
        $this->name = $name;
        $this->firstname=$firstname;
        $this->password=$password;
        $this->registrationDate = $registrationDate;
        $this->email=$email;
        $this->address1=$address1;
        $this->address2=$address2;
        $this->address3=$address3;
        $this->city=$city;
        $this->zipcode=$zipCode;
        $this->phone=$phone;
        $this->cellPhone=$cellPhone;
        $this->workPhone=$workPhone;
        $this->active=$active;
        $this->agency=$agency;
        $this->password=$password;
        $this->roles = $roles;
        $this->codeClient=$codeTiers;
        $this->numTVA=$numTVA;
        $this->companyName=$companyName;
        $this->tiersPourTVA=$tiersPourTva;
        $this->urlApp=$urlApp;


    }

}
