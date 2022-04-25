<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 11/12/14
 * Time: 09:41
 */

namespace GandiBundle\ThirdParty;
//require 'XML/RPC2/Client.php';


class XmlRpcGandi{

    private $debug=false;

    private $keyProd='HgwfzRO2Dg5jgzuxMEAckzW9';
    private $urlProd='https://rpc.gandi.net/xmlrpc/';
    private $keyTest='UVcWoTCOuTtJvVDsZlRLuhnl';
    private $urlTest='https://rpc.ote.gandi.net/xmlrpc/';
    private $reseller ="GI47-GANDI" ;




    private $key;
    private $url;

    private $prefix;
    private $action;


    /**
     * @param String $prefix
     * @param String $method
     * @param Array $options
     * @return mixed
     * @throws SoapFault
     */
    public function request($method,$options=null,$options2=null,$options3=null,$options4=null){
        $this->selectParams($method);
//        $request = @\XML_RPC2_Client::create($this->urlProd,array( 'prefix' => $this->prefix, 'sslverify' => False));
        $request = @\XML_RPC2_Client::create($this->urlProd,array( 'prefix' => $this->prefix, 'sslverify' => False));
        //var_dump($options3);
        if(isset($options4)||is_array($options4)) {
            // echo 'aa';
            $result = $request->__call($this->action, array($this->key, $options, $options2,$options3,$options4));
        }elseif(isset($options3)||is_array($options3)) {
           // echo 'aa';
            $result = $request->__call($this->action, array($this->key, $options, $options2,$options3));
        }
        elseif(isset($options2)||is_array($options2)) {
            $result = $request->__call($this->action, array($this->key, $options, $options2));
        }
        elseif(isset($options)||is_array($options)) {
            $result = $request->__call($this->action, array($this->key, $options));
        }
        else {
            $result = $request->__call($this->action, array($this->key));
        }

        return $result;

    }

    /**
     * @param String $methodName
     */
    private function selectParams($methodName)
    {

        switch ($methodName) {
            case 'listDomains':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='list';
                $this->prefix='domain.';
                break;
            case 'domainZoneVersionList':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='list';
                $this->prefix='domain.zone.version.';
                break;
            case 'domainZoneClone':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='clone';
                $this->prefix='domain.zone.';
                break;
            
            case 'domainZoneRecordList':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='list';
                $this->prefix='domain.zone.record.';
                break;
            case 'paasCreate':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='create';
                $this->prefix='paas.';
                break;
            case 'domainZoneVersionNew':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='new';
                $this->prefix='domain.zone.version.';
                break;
            case 'domainZoneRecordDelete':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='delete';
                $this->prefix='domain.zone.record.';
                break;
            case 'domainZoneVersionDelete':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='delete';
                $this->prefix='domain.zone.version.';
                break;
            case 'domainZoneVersionSet':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='set';
                $this->prefix='domain.zone.version.';
                break;
            case 'domainZoneSet':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='set';
                $this->prefix='domain.zone.';
                break;
            case 'domainZoneRecordAdd':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='add';
                $this->prefix='domain.zone.record.';
                break;
            case 'domainZoneRecordSet':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='set';
                $this->prefix='domain.zone.record.';
                break;
            case 'domainZoneInfo':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='info';
                $this->prefix='domain.zone.';
                break;
            case 'domainAvaillable':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='available';
                $this->prefix='domain.';
                break;
            case 'infosDomain':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='info';
                $this->prefix='domain.';
                break;
            case 'countDomains':
                $this->key=$this->debug?$this->keyProd:$this->keyProd;
                $this->url=$this->debug?$this->urlProd:$this->urlProd;
                $this->action='count';
                $this->prefix='domain.';
                break;
            case 'domainRenew':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='renew';
                $this->prefix='domain.';
                break;
            case 'listInstances':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='paas.';
                break;
            case 'getInstance':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='info';
                $this->prefix='paas.';
                break;
            case 'listMailbox':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='domain.mailbox.';
                break;
            case 'deleteMailbox':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='delete';
                $this->prefix='domain.mailbox.';
                break;

            case 'countMailbox':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='count';
                $this->prefix='domain.mailbox.';
                break;

            case 'updateMailbox':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='update';
                $this->prefix='domain.mailbox.';
                break;
            case 'createMailbox':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='create';
                $this->prefix='domain.mailbox.';
                break;
            case 'getMailbox':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='info';
                $this->prefix='domain.mailbox.';
                break;
            case 'mailboxSetAliases':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='set';
                $this->prefix='domain.mailbox.alias.';
                break;
            case 'packmailinfo':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='info';
                $this->prefix='domain.packmail.';
                break;

            case 'listMailForward':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='domain.forward.';
                break;
            case 'countMailForward':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='count';
                $this->prefix='domain.forward.';
                break;
            case 'createMailForward':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='create';
                $this->prefix='domain.forward.';
                break;
            case 'updateMailForward':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='update';
                $this->prefix='domain.forward.';
                break;
            case 'deleteMailForward':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='delete';
                $this->prefix='domain.forward.';
                break;
            case 'disableResponder':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='deactivate';
                $this->prefix='domain.mailbox.responder.';
                break;
            case 'activateResponder':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='activate';
                $this->prefix='domain.mailbox.responder.';
                break;
            case 'updatePackMail':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='update';
                $this->prefix='domain.packmail.';
                break;
            case 'removePackMail':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='delete';
                $this->prefix='domain.packmail.';
                break;
            case 'createPackMail':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='create';
                $this->prefix='domain.packmail.';
                break;
            case 'renewPackMail':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='renew';
                $this->prefix='domain.packmail.';
                break;
            case 'getContact':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='info';
                $this->prefix='contact.';
                break;
            case 'countContacts':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='count';
                $this->prefix='contact.';
                break;
            case 'countInstances':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='count';
                $this->prefix='paas.';
                break;
            case 'paasRenew':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='renew';
                $this->prefix='paas.';
                break;
            case 'vhostlist':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='paas.vhost.';
                break;
            case 'vhostsCreate':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='create';
                $this->prefix='paas.vhost.';
                break;
            case 'vhostDelete':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='delete';
                $this->prefix='paas.vhost.';
                break;
            case 'domainWebredirCreate':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='create';
                $this->prefix='domain.webredir.';
                break;
            case 'domainWebredirUpdate':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='update';
                $this->prefix='domain.webredir.';
                break;
            case 'domainWebredirDelete':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='delete';
                $this->prefix='domain.webredir.';
                break;
            case 'domainWebredirList':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='domain.webredir.';
                break;
            case 'instanceRestart':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='restart';
                $this->prefix='paas.';
                break;
            case 'paasSnapshotList':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='paas.snapshot.';
                break;
            case 'paasUpdate':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='update';
                $this->prefix='paas.';
                break;
            case 'catalogList':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='catalog.';
                break;
            case 'contactList':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='contact.';
                break;
            case 'canAssociateDomain':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='can_associate_domain';
                $this->prefix='contact.';
                break;
            case 'contactCreate':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='create';
                $this->prefix='contact.';
                break;
            case 'domainCreate':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='create';
                $this->prefix='domain.';
                break;
            case 'contactUpdate':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='update';
                $this->prefix='contact.';
                break;
            case 'vmList':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='list';
                $this->prefix='hosting.vm.';
                break;

            case 'vmStart':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='start';
                $this->prefix='hosting.vm.';
                break;

            case 'vmStop':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='stop';
                $this->prefix='hosting.vm.';
                break;
            case 'hostingMetricQuery':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='query';
                $this->prefix='hosting.metric.';
                break;
            case 'setNameservers':
                $this->key=$this->debug?$this->keyTest:$this->keyProd;
                $this->url=$this->debug?$this->urlTest:$this->urlProd;
                $this->action='set';
                $this->prefix='domain.nameservers.';
                break;
            default:
                throw new \SoapFault('server', 'methode inconnue');
                break;

        }


    }
}