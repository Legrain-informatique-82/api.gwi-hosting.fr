<?php

# Acme/ApiController/Controller/SoapLiteralController.php

namespace Legrain\ApiBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zend\Soap;
class SoapLiteralController extends Controller
{
    public function init()
    {
// No cache
        ini_set('soap.wsdl_cache_enable', 0);
        ini_set('soap.wsdl_cache_ttl', 0);
    }


    public function gandiAction()
    {
        if(isset($_GET['wsdl'])) {
            return $this->handleWSDL($this->generateUrl('legrain_api_soap_gandi', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'gandi_service');
        } else {
            return $this->handleSOAP($this->generateUrl('legrain_api_soap_gandi', array(), UrlGeneratorInterface::ABSOLUTE_URL), 'gandi_service');
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
        $autodiscover->setOperationBodyStyle(
            array('use' => 'literal',
//                'namespace' => 'http://framework.zend.com')
                'namespace' => 'http://schemas.xmlsoap.org/wsdl/soap12/wsdl11soap12.xsd')
        );

// Default is 'style' => 'rpc' and
// 'transport' => 'http://schemas.xmlsoap.org/soap/http'
        $autodiscover->setBindingStyle(
            array('style' => 'document',
//                'transport' => 'http://framework.zend.com')
                'transport' => 'http://schemas.xmlsoap.org/soap/http')
        );



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
                'uri' => $uri,
                'soap_version'=>SOAP_1_2
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