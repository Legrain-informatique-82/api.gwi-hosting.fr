<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 12/08/15
 * Time: 15:30
 */


namespace Legrain\ToolsBundle\Service;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;



class CurlBugzilla extends Controller
{

    /**
     * @var Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    protected $container; // <- Add this

    public function __construct(Registry $doctrine,ContainerInterface $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }


    public function listComponents($product){
        //URL of targeted site
        // url :  http://bugs.legrain.fr/rest/product/Sites%20Web%20Clients?api_key=EBOZdqweChj2bldktO2XDGGYcOUXapdfR25T5v3a
        $initurl = $this->container->getParameter('bugzilla.url');
        $api_key = $this->container->getParameter('bugzilla.api_key');
        $url=$initurl.'/rest/product/'.$product.'?api_key='.$api_key;
        $output = $this->curl($url);
        $output = json_decode($output);
        $res = array();
        foreach( $output->result->products[0]->components as $c){
            $res[]=$c->name;
        }
        return $res;
    }

    public function listBugs($product,$component){
        //http://bugs.legrain.fr/rest/bug?product=Sites%20Web%20Clients&component=codeaf.net&api_key=EBOZdqweChj2bldktO2XDGGYcOUXapdfR25T5v3a
        $initurl = $this->container->getParameter('bugzilla.url');
        $api_key = $this->container->getParameter('bugzilla.api_key');
        $url=$initurl.'/rest/bug?product='.$product.'&component='.$component.'&api_key='.$api_key;
        $output = $this->curl($url);
        $output = json_decode($output);
        return $output->result->bugs;
    }

    public function getCommentsBugs($idbug){
        http://bugs.legrain.fr/rest/bug/26/comment?api_key=EBOZdqweChj2bldktO2XDGGYcOUXapdfR25T5v3a
        $initurl = $this->container->getParameter('bugzilla.url');
        $api_key = $this->container->getParameter('bugzilla.api_key');
        $url=$initurl.'/rest/bug/'.$idbug.'/comment?api_key='.$api_key;
        $output = $this->curl($url);
        $output = json_decode($output);
        return $output->result->bugs->$idbug->comments;
    }

    private function curl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

}

}