<?php

namespace GandiBundle\Controller;

use GandiBundle\Entity\AutorenewReturn;
use GandiBundle\Entity\BusinessUnit;
use GandiBundle\Entity\Contact;
use GandiBundle\Entity\Mailbox\ForwardReturn;
use GandiBundle\Entity\Mailbox\MailboxQuota;
use GandiBundle\Entity\Mailbox\MailboxResponder;
use GandiBundle\Entity\Mailbox\MailboxReturn;
use GandiBundle\Entity\PackMailReturn;
use GandiBundle\Entity\ShippingAddress;
use GandiBundle\Entity\ZoneReturn;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GandiBundle\ThirdParty\XmlRpcGandi;



use GandiBundle\Entity\Connect;
use GandiBundle\Entity\Domain;
use GandiBundle\Entity\InfosDomain;
use GandiBundle\Entity\GInstance;
use GandiBundle\Entity\Mailbox\MailboxListReturn;
/**
 * Class Soapi v1.0
 */
class GandiController extends Controller{



    public function connection($user,$password){
        $connect = new Connect($user, $password);
        $this->connect($connect);
        return $connect;
    }


    public function deleteDNSSubdomain($username,$password,$domain,$subdomain){


        $connect = $this->connection($username,$password);
        $myrecord = array('name'=> $subdomain, 'type'=> 'A');


        $infosDomain = $this->infosDomain($connect,$domain);


        $zoneId = $infosDomain->zoneId;

        $numVersion = $this->domainZoneVersionNew($connect,$zoneId);
        $this->domainZoneRecordDelete($connect,$zoneId,$numVersion,$myrecord);
        $this->domainZoneVersionSet($connect, $zoneId, $numVersion);
        $this->domainZoneSet($connect, $domain, $zoneId);
        return true;


    }


    public function registerDNSSubDomainAction($username,$password,$domain,$subdomain,$ip){
        $connect = $this->connection($username,$password);
        $myrecord = array('name'=> $subdomain, 'type'=> 'A','ttl'=>10800,'value'=>$ip);


        $infosDomain = $this->infosDomain($connect,$domain);


        $zoneId = $infosDomain->zoneId;

        $numVersion = $this->domainZoneVersionNew($connect,$zoneId);
        $this->domainZoneAddRecordAction($connect,$zoneId,$numVersion,$myrecord);
        $this->domainZoneVersionSet($connect, $zoneId, $numVersion);
        $this->domainZoneSet($connect, $domain, $zoneId);
        return true;
    }


    public function domainZoneVersionNew($connect,$zone_id/*,$version_id*/){

        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        return $gandi->request('domainZoneVersionNew',$zone_id);
    }
    public function domainZoneVersionSet($connect,$zone_id,$version_id){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        return $gandi->request('domainZoneVersionSet',$zone_id,$version_id);
    }


    public function domainZoneSet($connect,$domain,$version_id){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        return $gandi->request('domainZoneSet',$domain,$version_id);
    }

    public function domainZoneAddRecordAction($connect,$zone_id,$version_id,$records){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        return $gandi->request('domainZoneRecordAdd',$zone_id,$version_id,$records);


    }
    public function domainZoneSetRecordAction($connect,$zone_id,$version_id,$records){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        return $gandi->request('domainZoneRecordSet',$zone_id,$version_id,$records);


    }
    public function domainZoneRecordDelete($connect,$zone_id,$version_id,$record){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        return $gandi->request('domainZoneRecordDelete',$zone_id,$version_id,$record);
    }

    public function domainZoneVersionDelete($connect,$zone_id,$version_id){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        return $gandi->request('domainZoneVersionDelete',$zone_id,$version_id);
    }


    public function domainZoneInfoAction($connect,$zone_id){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $rqt = $gandi->request('domainZoneInfo',$zone_id);
        return new ZoneReturn($rqt['date_updated'],$rqt['domains'],$rqt['id'],$rqt['name'],$rqt['owner'],$rqt['public'],$rqt['version'],$rqt['versions']);

    }



    /************************************
     * *********************************
     * ********************************
     * Domaines
     * ******************************
     * *******************************
     ***********************************/

    public function domainAvaillable($connect,$domain){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        do {
            $result = $gandi->request('domainAvaillable', array($domain));
            usleep(700000);

        }while($result[$domain] == 'pending');
        //print_r($result);

        return $result[$domain]=='available'?true:false;

    }
    public function domainsAvaillable($connect,$domains){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();


            $result = $gandi->request('domainAvaillable', $domains);

        //print_r($result);

        return $result;

    }
    public function canAssociateDomain($connect,$contact,$domain){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        $options = array(
            'domain' =>$domain,
            'owner' => true,
        );


        return $gandi->request('canAssociateDomain',$contact,$options);

    }
    public function contactCreate($connect,$options){
        try{
            $this->connect($connect);
            $gandi = new XmlRpcGandi();


            return $gandi->request('contactCreate',$options);
        }catch(\SoapFault $e){
            throw new \SoapFault('error',$e->getMessage());
        }

    }
    public function contactUpdate($connect,$codeGandi,$options){
        try{
            $this->connect($connect);
            $gandi = new XmlRpcGandi();


            return $gandi->request('contactUpdate',$codeGandi,array('extra_parameters'=>$options));
        }catch(\SoapFault $e){
            throw new \SoapFault('error',$e->getMessage());
        }

    }

    public function catalogListPricesDomain($connect)
    {
        //api.catalog.list(apikey, {'product':{'type': 'domains'}})
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['product']=array('type'=>'domains'/*,'description'=> '.fr'*/);
        // $options['action']=array('name'=> 'create', 'duration'=> 2);


        return $gandi->request('catalogList',$options);

    }


    public function domainCreate($connect,$domain,$durationInYears,$ownerContact,$adminContact,$billContact,$techContact){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['duration'] = $durationInYears;
        $options['owner'] = $ownerContact;
        $options['admin'] = $adminContact;
        $options['bill'] = $billContact;
        $options['tech'] = $techContact;


        $res = $gandi->request('domainCreate',$domain,$options);

        return true;
    }


    public function createPackMail($connect,$domain,$durationInDays){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['storage_quota'] = 1; // The storage limit, in Go
        $options['duration'] = $durationInDays; // calcul de la période d'abonement en jours
        $options['forward_quota'] = 1000; // The limit to the number of forwards
        $options['mailbox_quota'] = 1000; // The limit to the number of mailboxes

        $res = $gandi->request('createPackMail',$domain,$options);

        return true;
    }


    public function paasUpdate(Connect $connect,$id_paas,$puissance){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['size'] = $puissance; // puissance (s,m,l,xl,xxl)
        // $options['upgrade'] = true; // met à jour l'instance

        $gandi->request('paasUpdate',$id_paas,$options);
        return true;
    }

    public function paasUpdateConsole(Connect $connect,$id_paas,$asset){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['console'] = $asset; // puissance (s,m,l,xl,xxl)
        // $options['upgrade'] = true; // met à jour l'instance

        $gandi->request('paasUpdate',$id_paas,$options);
        return true;
    }

    public function paasUpdateDisk(Connect $connect,$id_paas,$sizeDisk){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['quantity'] = (int)$sizeDisk; // quantité
        // $options['upgrade'] = true; // met à jour l'instance

        $gandi->request('paasUpdate',$id_paas,$options);
        return true;
    }

    public function paasUpdateSnapshotProfile(Connect $connect,$id_paas,$idSnapshotProfile){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['snapshot_profile'] = $idSnapshotProfile; // profil du snapshot
        // $options['upgrade'] = true; // met à jour l'instance

        $gandi->request('paasUpdate',$id_paas,$options);
        return true;
    }


    public function renewPackMail($connect,$domainName,$durationInDays){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['duration'] = $durationInDays; // calcul de la période d'abonement en jours

        $res = $gandi->request('renewPackMail',$domainName,$options);

        return true;

    }


    public function updatePackMail($connect,$domain,$sizePack){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['storage_quota'] = $sizePack;
        $res = $gandi->request('updatePackMail',$domain,$options);
        return true;
    }

    public function removePackMail($connect,$domain){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $res = $gandi->request('removePackMail',$domain);
        return true;
    }


    public function countDomains($connect){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
//        $options['reseller'] = Constant::RESELLER;

        $request = $gandi->request('countDomains');

        if(!filter_var($request,FILTER_VALIDATE_INT)){


            throw new \SoapFault('server','Erreur');
        }
        return $request;
    }



    public function listDomains( $connect){
        $this->connect($connect);
        $totalPerPage=500;
        $gandi = new XmlRpcGandi();
        $total = $this->countDomains($connect);
        $totalPages = (Int)ceil($total/$totalPerPage);
        $domains=array();
        for($i=0;$i<$totalPages;$i++){
            $options = array();
//            $options['reseller'] = Constant::RESELLER;
            //$options['owner'] = $owner;
            $options['items_per_page'] = $totalPerPage;
            $options['page'] = $i;
            $request = $gandi->request('listDomains',$options);
            foreach($request as $elem){
                //var_dump($elem);
                $fqdn=array_key_exists('fqdn',$elem)?$elem['fqdn']:null;
                $tld=array_key_exists('tld',$elem)?$elem['tld']:null;
                $date_registry_creation=array_key_exists('date_registry_creation',$elem)?$elem['date_registry_creation']->timestamp:null;
                $date_registry_end=array_key_exists('date_registry_end',$elem)?$elem['date_registry_end']->timestamp:null;
                $date_updated=array_key_exists('date_updated',$elem)?$elem['date_updated']->timestamp:null;
                $authinfo=array_key_exists('authinfo',$elem)?$elem['authinfo']:null;
                $status=array_key_exists('status',$elem)?$elem['status']:null;
                $date_created=array_key_exists('date_created',$elem)?($elem['date_created']==null?null:$elem['date_created']->timestamp):null;
                $domains[]=new Domain($fqdn,$tld,$date_registry_creation,$date_registry_end,$date_updated,$authinfo,$status,$date_created);
            }
        }
        return $domains;
    }


    public function countInstances($connect){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();


        $request = $gandi->request('countInstances');

        if(!filter_var($request,FILTER_VALIDATE_INT)){


            throw new \SoapFault('server','Erreur');
        }
        return $request;
    }


    public function listInstances( $connect){
        $this->connect($connect);
        $options=array();

        $totalPerPage = 500;

        $gandi = new XmlRpcGandi();
        $total = $this->countInstances($connect);
        $totalPages = (Int)ceil($total/$totalPerPage);

        $instances = array();
        for($i=0;$i<$totalPages;$i++) {
            $options = array();

            $options['items_per_page'] = $totalPerPage;
            $options['page'] = $i;
            $res = $gandi->request('listInstances', $options);
            // return $res;

            foreach ($res as $a) {
                $instances[] = new GInstance($a['name'], $a['date_end']->timestamp, $a['id']);
            }
        }
        return $instances;



    }



    public function getInstance(Connect $connect,$idInstance){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('getInstance',$idInstance);
    }




    public function domainRenew(Connect $connect,$domain,$durationInYears){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $infosDomain = $this->infosDomain($connect,$domain);
        $endDate = $infosDomain->dateRegistryEnd;


        $options=array(
            'duration' =>$durationInYears,
            'current_year' =>(int)date('Y',$endDate)

        );
        return $gandi->request('domainRenew',$domain,$options);
        /*
        Retour :
        array(13) { 'errortype' => NULL 'date_updated' => class stdClass#8 (3) { public $scalar => string(17) "20141215T11:55:01" public $timestamp => int(1418640901) public $xmlrpc_type => string(8) "datetime" } 'last_error' => NULL 'date_start' => NULL 'session_id' => int(13454680) 'source' => string(10) "GI47-GANDI" 'step' => string(4) "BILL" 'eta' => int(-1863666) 'params' => array(11) { 'domain' => string(12) "aucept82.com" 'reseller' => string(10) "GI47-GANDI" 'remote_addr' => string(13) "92.149.96.145" 'domain_id' => int(2078125) 'session_id' => int(13454680) 'current_year' => int(2015) 'param_type' => string(6) "domain" 'tracker_id' => string(36) "ebed2822-5a53-485d-9c8e-11ba79661076" 'tld' => string(3) "com" 'duration' => int(1) 'auth_id' => int(13454680) } 'date_created' => class stdClass#7 (3) { public $scalar => string(17) "20141215T11:55:01" public $timestamp => int(1418640901) public $xmlrpc_type => string(8) "datetime" } 'infos' => array(7) { 'product_action' => string(5) "renew" 'product_type' => string(6) "domain" 'id' => string(0) "" 'extras' => array(0) { } 'label' => string(12) "aucept82.com" 'product_name' => string(3) "com" 'quantity' => string(0) "" } 'type' => string(12) "domain_renew" 'id' => int(23588128) }

        */

    }
    public function instanceRenew(Connect $connect,$id_paas,$durationInMonths){
        $this->connect($connect);

        $gandi = new XmlRpcGandi();


        $options=array(
            'duration' =>$durationInMonths, // Durée en ? mois ? ans ? Test sur /save
            'fixed' =>false // fixed ?

        );
        return $gandi->request('paasRenew',$id_paas,$options);

    }

    public function createInstance(Connect $connect,$idDatacenter,$durationInMonths,$name,$password,$size,$type,$quantityGoInOption,$snapshotprofile){
        $this->connect($connect);

        $gandi = new XmlRpcGandi();
        $options = array(
            'name'=> $name,
            'size'=> $size,//m
            'type'=> $type,// phpmysql
            'datacenter_id'=> $idDatacenter,
            'quantity'=> $quantityGoInOption,
            'duration'=> $durationInMonths.'m',
            'password'=> $password,

        );
        if($snapshotprofile!=null){
            $options['snapshot_profile']=$snapshotprofile;
        }
        return $gandi->request('paasCreate',$options);

    }

    public function instanceRestart(Connect $connect,$id_paas){
        $this->connect($connect);

        $gandi = new XmlRpcGandi();



        return $gandi->request('instanceRestart',$id_paas);
    }

    public function vhostsList(Connect $connect,$id_paas){
        $this->connect($connect);

        $gandi = new XmlRpcGandi();


        $options=array(
            'paas_id' =>$id_paas

        );
        return $gandi->request('vhostlist',$options);


    }

    public function  domainWebredirList(Connect $connect,$domain,$host=null){
        // type = http301; http302, cloak == (frame)
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        $options = array();
        if($host!=null){
            $options['~host']=$host;
        }
        $options['items_per_page']=500;
       try {
           return $gandi->request('domainWebredirList', $domain, $options);
       }catch (\Exception $e){
           throw new \SoapFault('e',$e->getMessage());
       }


    }

    public function  domainWebredirDelete(Connect $connect,$domain,$host){
        // type = http301; http302, cloak == (frame)
        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        $gandi->request('domainWebredirDelete',$domain,$host);
        return true;

    }

    public function  domainWebredirCreate(Connect $connect,$domain,$host,$url,$type){
        // type = http301; http302, cloak == (frame)
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array('url'=>$url
        ,'type'=>$type,
            'host'=>$host
        );
        $gandi->request('domainWebredirCreate',$domain,$options);
        return true;

    }

    public function  domainWebredirUpdate(Connect $connect,$domain,$host,$newhost,$newurl,$newtype){
        // type = http301; http302, cloak == (frame)
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array(
            'url'=>$newurl,
            'type'=>$newtype,
            'host'=>$newhost
        );


        $gandi->request('domainWebredirUpdate',$domain,$host,$options);
        return true;

    }
    public function vhostsCreate(Connect $connect,$paas_id,$vhost){
//        paas.vhost.create
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        //    try {
        $override = true;
        $zone_alter = true;
        $options = array(
            'paas_id'=>$paas_id,
            'vhost'=>$vhost,
            'override'=>$override,
            'zone_alter'=>$zone_alter
        );
        $res = $gandi->request('vhostsCreate', $options);
        return $res;
    }

    public function vhostDelete(Connect $connect,$vhost){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $gandi->request('vhostDelete',$vhost);
        return true;
    }




    public function infosDomain(Connect $connect,$domain){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        try {
            $res = $gandi->request('infosDomain', $domain);


            return new InfosDomain(
                $res['status'],
                $res['date_pending_delete_end']->timestamp,
                $res['zone_id'],
                $res['tags'],
                $res['date_updated']->timestamp,
                $res['date_delete']->timestamp,
                $res['date_hold_end']->timestamp,
                $res['fqdn'],
                $res['date_registry_end']->timestamp,
                $res['nameservers'],
                $res['authinfo'],
                $res['date_registry_creation']->timestamp,
                $res['date_renew_begin']->timestamp,
                $res['tld'],
                $res['services'],
                $res['date_created']->timestamp,
                $res['date_restore_end']->timestamp,
                $res['autorenew'],
                $res['contacts'],
                $res['id'],
                $res['date_hold_begin']->timestamp
            );
        }catch (\Exception $e){
            throw new \SoapFault('server','Erreurs de récupération des informations du nom de domaine : '.$domain);
        }

    }

    public function getContact(Connect $connect,$handle){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $res = $gandi->request('getContact',$handle);

//var_dump($res);
        $bu = $res['bu'];
        $shippingaddress = $res['shippingaddress'];

        $shippingaddressCity = array_key_exists('city',$shippingaddress)?$shippingaddress['city']:null;
        $shippingaddressCountry = array_key_exists('country',$shippingaddress)?$shippingaddress['country']:null;
        $shippingaddressFamily = array_key_exists('family',$shippingaddress)?$shippingaddress['family']:null;
        $shippingaddressGiven = array_key_exists('given',$shippingaddress)?$shippingaddress['given']:null;
        $shippingaddressOrgname = array_key_exists('orgname',$shippingaddress)?$shippingaddress['orgname']:null;
        $shippingaddressState = array_key_exists('state',$shippingaddress)?$shippingaddress['state']:null;
        $shippingaddressStreetAddr = array_key_exists('streetAddr',$shippingaddress)?$shippingaddress['streetAddr']:null;
        $shippingaddressStreetAddr2 = array_key_exists('streetAddr2',$shippingaddress)?$shippingaddress['streetAddr2']:null;
        $shippingaddressZip = array_key_exists('zip',$shippingaddress)?$shippingaddress['zip']:null;


        $brandNumber = array_key_exists('brand_number',$res)?$res['brand_number']:null;
        $orgname = array_key_exists('orgname',$res)?$res['orgname']:null;
        $siren = array_key_exists('siren',$res)?$res['siren']:null;



        return new Contact(
            $brandNumber,

            new BusinessUnit($bu['forbidden_tlds'],$bu['id'],$bu['name'])
            ,$res['city'],$res['community'],$res['data_obfuscated'],$res['email'],$res['family'],$res['fax'],$res['given'],$res['handle'],$res['id'],
            $res['is_corporate'],$res['lang'],$res['mail_obfuscated'],$res['mobile'],
            $res['newsletter'],$orgname,$res['phone'],$res['reachability'],$res['security_question_answer'],$res['security_question_num'],
            new ShippingAddress($shippingaddressCity,$shippingaddressCountry,$shippingaddressFamily,$shippingaddressGiven,$shippingaddressOrgname,$shippingaddressState,$shippingaddressStreetAddr,$shippingaddressStreetAddr2,$shippingaddressZip)
            ,$siren,$res['state'],$res['streetaddr'],
            $res['third_part_resell'],$res['type'],$res['validation'],$res['vat_number'],$res['zip']);


        // var_dump($res);

    }
    /***
     * Mailbox
     */

    public function getMailbox(Connect $connect,$domain,$login){
        $this->connect($connect);


        $gandi = new XmlRpcGandi();
        $res = $gandi->request('getMailbox',$domain,$login);


        // var_dump($res);


        return new MailboxReturn($res['aliases'],$res['fallback_email'],$res['login'],
            new MailboxQuota($res['quota']['granted'],$res['quota']['used']),
            new MailboxResponder($res['responder']['active'],$res['responder']['text']),
            false,false
        );


    }


    public function listMailbox(Connect $connect,$domain){
        $this->connect($connect);


        $totalPerPage=500;
        $gandi = new XmlRpcGandi();
        $total = $this->countMailbox($connect,$domain);

        $totalPages = (Int)ceil($total/$totalPerPage);
        $return =array();

        for($i=0;$i<$totalPages;$i++){
            $options = array();
            $options['items_per_page'] = $totalPerPage;
            $options['page'] = $i;

            $gandi = new XmlRpcGandi();
            $res = $gandi->request('listMailbox',$domain,$options);


            foreach ($res as $elem){


                $emailApiExist=false;
                // On regarde si un compte email pour la mailbox en cours qui a un role ROLE_COMPTE_EMAIL

                $return[] = new MailboxListReturn($elem['login'],
                    new MailboxQuota($elem['quota']['granted'],$elem['quota']['used']),
                    new MailboxResponder($elem['responder']['active']),
                    $emailApiExist
                );
            }


        }

        return $return;
    }


    public function disableResponder(Connect $connect,$emailAddress,$dateEnd=null){
        $this->connect($connect);
        $tmp = explode("@",$emailAddress);
        $loginEmail=$tmp[0];
        $domain=$tmp[1];
        $gandi = new XmlRpcGandi();
        $options = array();

        if($dateEnd!=null)$options['date']=$dateEnd;

        $res = $gandi->request('disableResponder',$domain,$loginEmail,$options);
        return true;
    }


    public function activateResponder(Connect $connect,$emailAddress,$dateInit,$message){
        $this->connect($connect);
        $tmp = explode("@",$emailAddress);
        $loginEmail=$tmp[0];
        $domain=$tmp[1];
        $gandi = new XmlRpcGandi();
        $options = array('date'=>$dateInit,'content'=>$message);
        $res = $gandi->request('activateResponder',$domain,$loginEmail,$options);
//        $options = array('date'=>$dateEnd);
//        $res = $gandi->request('disableResponder',$domain,$loginEmail,$options);

        return true;
    }


    public function getMailForward(Connect $connect,$domain,$source){


        $this->connect($connect);



        $return =array();


        $options = array();

        $options['source'] = $source;


        $gandi = new XmlRpcGandi();
        $res = $gandi->request('listMailForward',$domain,$options);
//            var_dump($res);
        foreach ($res as $elem){

            return new ForwardReturn($elem['destinations'], $elem['source']
            );
        }



    }

    public function listMailForward(Connect $connect,$domain){

        $this->connect($connect);

        $totalPerPage=500;
        $gandi = new XmlRpcGandi();
        $total = $this->countMailForward($connect,$domain);

        $totalPages = (Int)ceil($total/$totalPerPage);
        $return =array();

        for($i=0;$i<$totalPages;$i++){
            $options = array();
            $options['items_per_page'] = $totalPerPage;
            $options['page'] = $i;

            $gandi = new XmlRpcGandi();
            $res = $gandi->request('listMailForward',$domain,$options);

//            var_dump($res);
            foreach ($res as $elem){

                $return[] = new ForwardReturn($elem['destinations'], $elem['source']
                );
            }


        }

        return $return;

    }

    public function countMailForward(Connect $connect,$domain){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('countMailForward',$domain);
    }


    public function deleteMailForward(Connect $connect,$domain,$source){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('deleteMailForward',$domain,$source);
    }


    public function createMailForward(Connect $connect,$domain,$source,$destinations){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['destinations'] = $destinations;
        $res= $gandi->request('createMailForward',$domain,$source,$options);
        return new ForwardReturn($res['destinations'], $res['source']);
    }

    public function updateMailForward(Connect $connect,$domain,$source,$destinations){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array();
        $options['destinations'] = $destinations;
        $res= $gandi->request('updateMailForward',$domain,$source,$options);
        return new ForwardReturn($res['destinations'], $res['source']);
    }


    public function countMailbox(Connect $connect,$domain){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('countMailbox',$domain);
    }


    public function mailboxAddAliasAction(Connect $connect,$emailAddress,$aliases){
        $this->connect($connect);

        $tmp=explode('@', $emailAddress);
        $login=$tmp[0];
        $domain=$tmp[1];

        try {
            // On récupère la liste d'alias pour la boite email
            $mailbox = $this->getMailbox($connect,$domain,$login);
            $actualAliases = is_array($mailbox->aliases)?$mailbox->aliases:array($mailbox->aliases);

            // array merge
            $newAliases = array_merge($actualAliases,$aliases);


            $gandi = new XmlRpcGandi();
            $res = $gandi->request('mailboxSetAliases', $domain, $login, $newAliases);
            // Verif upd bien déroulé.
            return true;
        }catch (\XML_RPC2_FaultException $e){
            throw new \SoapFault('error',$e->getMessage());
        }
    }


    public function mailboxRemoveAliasAction(Connect $connect,$emailAddress,$aliases){
        $this->connect($connect);

        $tmp=explode('@', $emailAddress);
        $login=$tmp[0];
        $domain=$tmp[1];

        try {
            // On récupère la liste d'alias pour la boite email
            $mailbox = $this->getMailbox($connect,$domain,$login);
            $actualAliases = is_array($mailbox->aliases)?$mailbox->aliases:array($mailbox->aliases);
            // array merge
            $newAliases = array();
            foreach($actualAliases as $a){
                if(!in_array($a,$aliases))$newAliases[]=$a;
            }
            $gandi = new XmlRpcGandi();

            $res = $gandi->request('mailboxSetAliases', $domain, $login, $newAliases);
            // Verif upd bien déroulé.
            return true;
        }catch (\XML_RPC2_FaultException $e){
            throw new \SoapFault('error',$e->getMessage());
        }
    }


    public function packMailInfo(Connect $connect,$domain){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        //$res = $gandi->request('updateMailbox',$domain,$login,$options);
        //var_dump($domain,$login,$options);
        try {
            $res = $gandi->request('packmailinfo', $domain );
            // array(10) { 'status' => string(4) "free" 'domain' => string(15) "pro-boutique.fr" 'mailbox_quota' => int(5) 'date_end' => NULL 'storage_quota' => int(1) 'id' => int(3744448) 'autorenew' => NULL 'forward_quota' => int(1000) 'date_created' => class stdClass#656 (3) { public $scalar => string(17) "20150122T15:49:40" public $timestamp => int(1421938180) public $xmlrpc_type => string(8) "datetime" } 'domain_id' => int(4721496) } API GWI-hosting.
            $date_created =($res['date_created']!=null)?$res['date_created']->timestamp:null;
            $date_end =($res['date_end']!=null)?$res['date_end']->timestamp:null;

            if($res['autorenew']==null){
                $autorenew=null;
            }else{
                $autorenew = new AutorenewReturn($res['autorenew']['active'], $res['autorenew']['contact'], $res['autorenew']['duration'], $res['autorenew']['id'], $res['autorenew']['product_id'], $res['autorenew']['product_type_id']);
            }

            return new PackMailReturn($autorenew,$date_created,$date_end,$res['domain'],$res['domain_id'],$res['forward_quota'],$res['id'],$res['mailbox_quota'],$res['status'],$res['storage_quota']);
        }catch (\XML_RPC2_FaultException $e){

        }

    }

    public function updateMailBox(Connect $connect,$addressEmail,$password,$quota,$fallback_email){
        $this->connect($connect);

        $tmp=explode('@', $addressEmail);
        $login=$tmp[0];
        $domain=$tmp[1];
        $options = array();
        if($password){
            if(strlen($password)<8) throw new \SoapFault('PASSWORD_TOO_SMALL','Le mot de passe saisi doit posséder au minimum 8 caractères');
            $options["password"]=$password;
        }
        $options["fallback_email"]=$fallback_email;
        if($quota!=null)
            $options['quota']=(int)($quota==''?0:$quota);

        //  if($password==null&&$quota==null)return true;


        $gandi = new XmlRpcGandi();
        //$res = $gandi->request('updateMailbox',$domain,$login,$options);
        //var_dump($domain,$login,$options);
        try {
            $res = $gandi->request('updateMailbox', $domain, $login, $options);
            // Verif upd bien déroulé.
            return true;
        }catch (\XML_RPC2_FaultException $e){
            switch($e->getFaultCode()){
                case '500137' :
                    //  var_dump($e->getMessage());

                    throw new \SoapFault('PASSWORD_BASED_ON_DICTIONARY_WORD','Le mot de passe ne doit pas être basé sur un mot du dictionnaire.');

                    break;
                default:
                    throw new \SoapFault('UNKNOW_ERROR','Erreur inconnue, si le problème persiste, contactez Legrain au 05.63.30.31.44.');
                    break;
            }
            //  var_dump($e);
        }
    }


    public function createMailBox(Connect $connect,$addressEmail,$password,$quota,$fallback_email){
        $this->connect($connect);

        $tmp=explode('@', $addressEmail);
        $login=$tmp[0];
        $domain=$tmp[1];
        $options = array();
        if($password){
            if(strlen($password)<8) throw new \SoapFault('PASSWORD_TOO_SMALL','Le mot de passe saisi doit posséder au minimum 8 caractères');
            $options["password"]=$password;
        }
        $options["fallback_email"]=$fallback_email;
        $options['quota']=(int)($quota==''?0:$quota);

        if($password==null&&$quota==null)return true;
        $gandi = new XmlRpcGandi();
        //$res = $gandi->request('updateMailbox',$domain,$login,$options);
        //var_dump($domain,$login,$options);
        try {
            $res = $gandi->request('createMailbox', $domain, $login, $options);
            // Verif upd bien déroulé.
            return true;
        }catch (\XML_RPC2_FaultException $e){
            switch($e->getFaultCode()){
                case '500137' :
                    //  var_dump($e->getMessage());

                    throw new \SoapFault('PASSWORD_BASED_ON_DICTIONARY_WORD','Le mot de passe ne doit pas être basé sur un mot du dictionnaire.');

                    break;
                case '510341':
                    throw new \SoapFault('MAILBOX_EXIST','Cette boite e-mail existe déjà');
                    break;
                case '510535':
                    throw new \SoapFault('MAILBOX_EXIST','Vous possèdez déjà 5 boites e-mails');
                    // throw new \SoapFault('MAILBOX_EXIST',$e->getMessage());
                    break;
                default:
                    throw new \SoapFault('UNKNOW_ERROR','Erreur inconnue, si le problème persiste, contactez Legrain au 05.63.30.31.44.');
//                    throw new \SoapFault('UNKNOW_ERROR',$e->getFaultCode());
                    break;
            }
            //  var_dump($e);
        }
    }



    public function deleteMailbox(Connect $connect,$domain,$login){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('deleteMailbox',$domain,$login);
    }


    public function paasSnapshotList($connect,$id_paas){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options =array('paas_id'=>(int)$id_paas);
        return $gandi->request('paasSnapshotList',$options);

    }
    public function countContacts(Connect $connect)
    {

        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('countContacts');
    }

    public function listContacts(Connect $connect){

        $this->connect($connect);
        $gandi = new XmlRpcGandi();

        $totalPerPage=500;

        $total = $this->countContacts($connect);
        $totalPages = (Int)ceil($total/$totalPerPage);
        $contacts=array();
        for($i=0;$i<$totalPages;$i++){
            $options = array();
//            $options['reseller'] = Constant::RESELLER;
            //$options['owner'] = $owner;
            $options['items_per_page'] = $totalPerPage;
            $options['page'] = $i;
            $request = $gandi->request('contactList',$options);
            $contacts = array_merge($contacts,$request);
        }
        return $contacts;
    }



    public function vmList(Connect $connect)
    {

        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('vmList');
    }
    public function vmStart(Connect $connect,$idVm)
    {

        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('vmStart',$idVm);
    }
    public function vmStop(Connect $connect,$idVm)
    {

        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        return $gandi->request('vmStop',$idVm);
    }


    public function getSizeHddSimpleHosting(Connect $connect, $idSimpleHosting){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array(
            'resource_type'=> 'paas',
            'resource_id'=> $idSimpleHosting,
            'query'=> 'vfs.df.bytes.all',
            'start'=> date('Y-m-d 00:00:00'),
            'end'=> date('Y-m-d 12:00:00'),

            'sampler'=> array('unit'=> 'hours', 'value'=> 12, 'function'=> 'avg')
        );

        return $gandi->request('hostingMetricQuery',$options);
    }

    /**
     * @param Connect $connect
     * @param String $domain
     * @param array $nameservers
     */
    public function setNameServers(Connect $connect,$domain,$nameservers){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        $options = array(
            'override'=>true
        );

        return $gandi->request('setNameservers',$domain,$nameservers,$options);
    }
    /**
     * @param Connect $connect
     * @param int $zoneId
     */
    public function listDomainZoneVersion(Connect $connect,$zoneId){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        

        return $gandi->request('domainZoneVersionList',$zoneId);
    } 
    
    /**
     * @param Connect $connect
     * @param int $zoneId
     */
    public function domainZoneClone(Connect $connect,$zoneId){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();
        

        return $gandi->request('domainZoneClone',$zoneId);
    }

    /**
     * @param Connect $connect
     * @param int $zoneId
     */
    public function domainZoneInfo(Connect $connect,$zoneId){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();


        return $gandi->request('domainZoneInfo',$zoneId);
    }



    /**
     * @param Connect $connect
     * @param int $zoneId
     * @param int $versionId
     */
    public function domainZoneRecordList(Connect $connect,$zoneId,$versionId){
        $this->connect($connect);
        $gandi = new XmlRpcGandi();


        return $gandi->request('domainZoneRecordList',$zoneId,$versionId);
    }
// private methods
    /**
     * @param $connect
     * @throws \SoapFault
     * @ignore
     */
    private function connect( $connect)
    {
        if($connect->user!='hohloobeen1quaez7eis8eiBaiNgeita')
            throw new \SoapFault('server','identifiant inconnu');
        if($connect->password!='cooBeeNgeijaerie9aibae0ohxootee5')
            throw new \SoapFault('server','mdp incorrect');

    }



}

