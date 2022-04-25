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

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\CreditCard;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payment;

class Math extends Controller
{

    /**
     * @var Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param $dateEnding string
     * @param $pricePerMonth float
     * @param $quantity int
     * @param $dateEnding string| null
     * @return float
     */
    public function calculPrixAuProrata($dateEnding,$pricePerMonth,$quantity,$dateBegin=null){
        if($dateBegin==null)
            $dateBegin=date('Y-m-d');
        // Nombre de jours entre 2 dates
        $datetime1 = new \DateTime($dateBegin);
        $datetime2 = new \DateTime($dateEnding);
        $interval = $datetime1->diff($datetime2);
        $totalJours= $interval->format('%a'); //Retourne le nbre de jours entre 2 dates
        // Nombre de jours par mois
        $nbJrsParMois = 30.4167;
        $priceTotal=($pricePerMonth*$quantity)*($totalJours/$nbJrsParMois);
        return round($priceTotal, 2) ;
    }

    public function nombreDeJours($date1,$date2){
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        // affiche la difference en jour
        $total  = (int)$interval->format('%a');
        return $total==0?1:$total;
    }
}