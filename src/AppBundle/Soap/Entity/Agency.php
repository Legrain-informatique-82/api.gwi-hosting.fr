<?php

namespace AppBundle\Soap\Entity;


/**
 * Agency
 */
class Agency
{
    /**
     * @var integer
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
    public $siret;

    /**
     * @var string
     *
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
     * @var \AppBundle\Soap\Entity\ZipCode
     */
    public $zipCode;
    /**
     * @var \AppBundle\Soap\Entity\City
     */
    public $city;



    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $website;

    /**
     * @var boolean
     */
    public $facturationBylegrain;

    /**
     * @var boolean
     */
    public $infosCheque;

    /**
     * @var boolean
     */
    public $infosVirement;
    /**
     * @var boolean
     */
    public $useTva;

    /**
     * @var string
     */
    public $descriptionHtml;


    public function __construct($id,$name,$siret,$address1,$address2,$address3, $city, $zipcode,$phone,$email,$website,$facturationBylegrain,$infosCheque,$infosVirement,$useTva,$descriptionHtml){
        $this->id=$id;
        $this->name=$name;
        $this->siret=$siret;
        $this->address1=$address1;
        $this->address2=$address2;
        $this->address3=$address3;
        $this->city=$city;
        $this->zipCode=$zipcode;
        $this->phone=$phone;
        $this->email=$email;
        $this->website=$website;
        $this->facturationBylegrain = $facturationBylegrain;
        $this->infosCheque = $infosCheque;
        $this->infosVirement = $infosVirement;
        $this->useTva = $useTva;
        $this->descriptionHtml = $descriptionHtml;
    }
}

