<?php

namespace GandiBundle\Entity;



/**
 * Class InfosDomain
 */
class InfosDomain{

    /**
     * @var mixed
     */
    public $status;

    /**
     * @var Int
     */
    public $datePendingDeleteEnd;

    /**
     * @var Int
     */
    public $zoneId;

    /**
     * @var mixed
     */
    public $tags;

    /**
     * @var Int

     */
    public $dateUpdated;

    /**
     * @var Int
     */
    public $dateDelete;

    /**
     * @var Int
     */
    public $dateHoldEnd;

    /**
     * @var String
     */
    public $fqdn;

    /**
     * @var Int
     */
    public $dateRegistryEnd;

    /**
     * @var mixed
     */
    public $nameservers;

    /**
     * @var String
     */
    public $authinfo;

    /**
     * @var Int
     */
    public $dateRegistryCreation;

    /**
     * @var Int
     */
    public $dateRenewBegin;

    /**
     * @var String
     */
    public $tld;

    /**
     * @var mixed
     */
    public $services;

    /**
     * @var Int
     */
    public $dateCreated;

    /**
     * @var Int
     */
    public $dateRestoreEnd;

    /**
     * @var String
     */
    public $autorenew;

    /**
     * @var mixed
     */
    public $contacts;

    /**
     * @var Int
     */
    public $id;

    /**
     * @var Int
     */
    public $dateHoldBegin;


    /**
     * @param mixed $status
     * @param Int $datePendingDeleteEnd
     * @param Int $zoneId
     * @param mixed $tags
     * @param Int $dateUpdated
     * @param Int $dateDelete
     * @param Int $dateHoldEnd
     * @param String $fqdn
     * @param Int $dateRegistryEnd
     * @param mixed $nameservers
     * @param String $authinfo
     * @param Int $dateRegistryCreation
     * @param Int $dateRenewBegin
     * @param String $tld
     * @param mixed $services
     * @param Int $dateCreated
     * @param Int $dateRestoreEnd
     * @param String $autorenew
     * @param mixed $contacts
     * @param Int $id
     * @param Int $dateHoldBegin
     */
    public function __construct($status,$datePendingDeleteEnd,$zoneId,$tags,$dateUpdated,$dateDelete,$dateHoldEnd,$fqdn,$dateRegistryEnd,$nameservers,$authinfo,$dateRegistryCreation,$dateRenewBegin,$tld,$services,$dateCreated,$dateRestoreEnd,$autorenew,$contacts,$id,$dateHoldBegin){
        $this->status = $status;
        $this->datePendingDeleteEnd = $datePendingDeleteEnd;
        $this->zoneId = $zoneId;
        $this->tags = $tags;
        $this->dateUpdated = $dateUpdated;
        $this->dateDelete = $dateDelete;
        $this->dateHoldEnd = $dateHoldEnd;
        $this->fqdn = $fqdn;
        $this->dateRegistryEnd = $dateRegistryEnd;
        $this->nameservers = $nameservers;
        $this->authinfo = $authinfo;
        $this->dateRegistryCreation = $dateRegistryCreation;
        $this->dateRenewBegin = $dateRenewBegin;
        $this->tld = $tld;
        $this->services = $services;
        $this->dateCreated = $dateCreated;
        $this->dateRestoreEnd = $dateRestoreEnd;
        $this->autorenew = $autorenew;
        $this->contacts = $contacts;
        $this->id= $id;
        $this->dateHoldBegin = $dateHoldBegin;
    }




}