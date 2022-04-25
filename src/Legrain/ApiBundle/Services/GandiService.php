<?php

# Acme/ApiBundle/Services/CheckService.php
namespace Legrain\ApiBundle\Services;


use AppBundle\AppBundle;
use AppBundle\Soap\Type\User;
use Doctrine\ORM\EntityManager;
use GandiBundle\Controller\GandiController;

class GandiService
{


    protected $em;
    protected $gandi;
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->gandi = new GandiController();
    }


    /**
     * Supprime les DNS pour un sous domaine donné
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $subdomain
     * @return bool
     */
    public function deleteDNSSubdomain($username,$password,$domain,$subdomain){


        $connect = $this->gandi->connection($username,$password);
        $myrecord = array('name'=> $subdomain, 'type'=> 'A');


        $infosDomain = $this->gandi->infosDomain($connect,$domain);


        $zoneId = $infosDomain->zoneId;

        $numVersion = $this->gandi->domainZoneVersionNew($connect,$zoneId);
        $this->gandi->domainZoneRecordDelete($connect,$zoneId,$numVersion,$myrecord);
        $this->gandi->domainZoneVersionSet($connect, $zoneId, $numVersion);
        $this->gandi->domainZoneSet($connect, $domain, $zoneId);
        return true;


    }

    
    /**
     * Ajoute une ligne de DNS pour un sous domaine donné
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $subdomain
     * @param string $ip
     * @return bool
     */
    public function registerDNSSubDomain($username,$password,$domain,$subdomain,$ip){
        $connect = $this->gandi->connection($username,$password);
        $myrecord = array('name'=> $subdomain, 'type'=> 'A','ttl'=>10800,'value'=>$ip);


        $infosDomain = $this->gandi->infosDomain($connect,$domain);


        $zoneId = $infosDomain->zoneId;

        $numVersion = $this->gandi->domainZoneVersionNew($connect,$zoneId);
        $this->gandi->domainZoneAddRecordAction($connect,$zoneId,$numVersion,$myrecord);
        $this->gandi->domainZoneVersionSet($connect, $zoneId, $numVersion);
        $this->gandi->domainZoneSet($connect, $domain, $zoneId);
        return true;
    }


}