<?php

namespace GandiBundle\Entity;

/**
 * Class Contact
 */
class Contact{


    /**
     * @var string
     */
    public $brand_number;

    /**
     * @var \GandiBundle\Entity\BusinessUnit
     */
    public $bu;
    /**
     * @var string
     */
    public $city;
    /**
     * @var bool
     */
    public $community	;
//country	struct
    /**
     * @var int
     */
    public $data_obfuscated	;
    /**
     * @var string
     */
    public $email	;
//extra_parameters	struct
    /**
     * @var string
     */
    public $family	;
    /**
     * @var string
     */
    public $fax	;
    /**
     * @var string
     */
    public $given	;
    /**
     * @var string
     */
    public $handle	;
    /**
     * @var int
     */
    public $id	;
    /**
     * @var bool
     */
    public $is_corporate	;


    /**
     * @var string
     */
    public $lang	;
    /**
     * @var int
     */
    public $mail_obfuscated	;
    /**
     * @var string
     */
    public $mobile	;
    /**
     * @var int
     */
    public $newsletter	;
    /**
     * @var string
     */
    public $orgname	;
    /**
     * @var string
     */
    public $phone	;
    /**
     * @var string
     */
    public $reachability	;
    /**
     * @var string
     */
    public $security_question_answer	;
    /**
     * @var int
     */
    public $security_question_num	;
    /**
     * @var \GandiBundle\Entity\ShippingAddress
     */
    public $shippingaddress;
    /**
     * @var string
     */
    public $siren	;
    /**
     * @var string
     */
    public $state	;
    /**
     * @var string
     */
    public $streetaddr	;
    /**
     * @var int
     */
    public $third_part_resell	;
    /**
     * @var int
     */
    public $type;
    /**
     * @var string
     */
    public $validation;
    /**
     * @var string
     */
    public $vat_number	;
    /**
     * @var string
     */
    public $zip	;

    /**
     * Contact constructor.
     * @param $jo_publication_date
     * @param $brand_number
     * @param $bu
     * @param $city
     * @param $community
     * @param $data_obfuscated
     * @param $email
     * @param $family
     * @param $fax
     * @param $given
     * @param $handle
     * @param $id
     * @param $is_corporate
     * @param $jo_announce_number
     * @param $jo_announce_page
     * @param $jo_declaration_date
     * @param $lang
     * @param $mail_obfuscated
     * @param $mobile
     * @param $newsletter
     * @param $orgname
     * @param $phone
     * @param $reachability
     * @param $security_question_answer
     * @param $security_question_num
     * @param $shippingaddress
     * @param $siren
     * @param $state
     * @param $streetaddr
     * @param $third_part_resell
     * @param $type
     * @param $validation
     * @param $vat_number
     * @param $zip
     */
    public function __construct( $brand_number=null, $bu, $city, $community, $data_obfuscated, $email, $family, $fax, $given, $handle, $id, $is_corporate, $lang, $mail_obfuscated, $mobile, $newsletter, $orgname, $phone, $reachability, $security_question_answer, $security_question_num, $shippingaddress, $siren, $state, $streetaddr, $third_part_resell, $type, $validation, $vat_number, $zip)
    {
        $this->brand_number = $brand_number;
        $this->bu = $bu;
        $this->city = $city;
        $this->community = $community;
        $this->data_obfuscated = $data_obfuscated;
        $this->email = $email;
        $this->family = $family;
        $this->fax = $fax;
        $this->given = $given;
        $this->handle = $handle;
        $this->id = $id;
        $this->is_corporate = $is_corporate;
        $this->lang = $lang;
        $this->mail_obfuscated = $mail_obfuscated;
        $this->mobile = $mobile;
        $this->newsletter = $newsletter;
        $this->orgname = $orgname;
        $this->phone = $phone;
        $this->reachability = $reachability;
        $this->security_question_answer = $security_question_answer;
        $this->security_question_num = $security_question_num;
        $this->shippingaddress = $shippingaddress;
        $this->siren = $siren;
        $this->state = $state;
        $this->streetaddr = $streetaddr;
        $this->third_part_resell = $third_part_resell;
        $this->type = $type;
        $this->validation = $validation;
        $this->vat_number = $vat_number;
        $this->zip = $zip;
    }


}

