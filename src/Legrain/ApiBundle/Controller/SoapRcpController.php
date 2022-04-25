<?php

# Acme/ApiController/Controller/SoapRcpController.php

namespace Legrain\ApiBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zend\Soap;
class SoapRcpController extends Controller
{
    public function init()
    {
// No cache
        ini_set('soap.wsdl_cache_enable', 0);
        ini_set('soap.wsdl_cache_ttl', 0);
    }
    public function userSecurityAction()
    {
        if(isset($_GET['wsdl'])) {
            return $this->handleWSDL($this->generateUrl('legrain_api_soap_gwi_hosting_security', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'security_service');
        } else {
            return $this->handleSOAP($this->generateUrl('legrain_api_soap_gwi_hosting_security', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'security_service');
        }
    }
    public function gwiHostingAction()
    {
        if(isset($_GET['wsdl'])) {
            return $this->handleWSDL($this->generateUrl('legrain_api_soap_gwi_hosting', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'gwi_hosting_service');
        } else {
            return $this->handleSOAP($this->generateUrl('legrain_api_soap_gwi_hosting', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'gwi_hosting_service');
        }
    }



    public function checkAction()
    {
        if(isset($_GET['wsdl'])) {
            return $this->handleWSDL($this->generateUrl('legrain_api_soap_check', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'check_service');
        } else {
            return $this->handleSOAP($this->generateUrl('legrain_api_soap_check', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'check_service');
        }
    }

    /**
     * return the WSDL
     */
    public function handleWSDL($uri, $class){
// Soap auto discover
        $autodiscover = new Soap\AutoDiscover(new Soap\Wsdl\ComplexTypeStrategy\ArrayOfTypeComplex() );

        $autodiscover->setClass($this->get($class));
        $autodiscover->setUri($uri);




// Response
        $response = new Response();

        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');
        ob_start();
// Handle Soap
        $autodiscover->handle();
        $response->setContent(ob_get_clean());

        return $response;

    }

    /**
     * execute SOAP request
     */
    public function handleSOAP($uri, $class){
// Soap server

        $soap = new Soap\Server(null,
            array('location' => $uri,
                'uri' => $uri

            ));

        $soap->setClass($this->get($class));

// Response
        $response = new Response();

        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');

        ob_start();
// Handle Soap
        $soap->handle();
        $response->setContent(ob_get_clean());
        return $response;
    }
}