<?php

namespace GandiBundle\Entity;

/**
 * Class Domain
 */
class Domain{


    /**
     * @var String $domain
     * @var string
     */
    public $domain;
    /**
     * @var String $tld
     */
    public $tld;
    /**
     * @var Int

     */
    public $dateUpdated;
    /**
     * @var Int

     */
    public $dateRegistryCreation;
    /**
     * @var Int

     */
    public $dateRegistryEnd;
    /**
     * @var String

     */
    public $authInfos;

    /**
     * @var mixed $status
     */
    public $status;
    /**
     * @var mixed
     */
    public $date_created;

    /**
     * @param String $domain
     * @param String $tld
     * @param Int $dateRegistryCreation
     * @param Int $dateRegistryEnd
     * @param Int $dateUpdated
     * @param String $authInfos
     * @param mixed $status
     * @param Int $date_created
     */
    public function __construct($domain=null,$tld=null,$dateRegistryCreation=null,$dateRegistryEnd=null,$dateUpdated=null,$authInfos=null,$status=null,$date_created=null){
        $this->domain=$domain;
        $this->tld=$tld;
        $this->dateRegistryCreation=$dateRegistryCreation;
        $this->dateRegistryEnd=$dateRegistryEnd;
        $this->dateUpdated=$dateUpdated;
        $this->authInfos= $authInfos;
        $this->status= $status;
        $this->date_created=$date_created;


    }



}

