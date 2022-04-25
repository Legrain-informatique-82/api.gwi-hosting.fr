<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 13/08/15
 * Time: 14:15
 */

namespace AppBundle\Soap\Entity;

use \AppBundle\Soap\Entity;

/**
 * Cart
 *
 */
class PotentialPayer extends User{

    /**
     * @var float
     *
     */
    public $solde;

    public function __construct($id,$name,$firstname,$email,$address1,$address2,$address3,City $city,ZipCode $zipCode,$phone,$active,Agency $agency,$solde){
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
        $this->solde=$solde;


    }


}