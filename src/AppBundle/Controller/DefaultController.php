<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AccountBalance;
use AppBundle\Entity\AccountBalanceLine;
use AppBundle\Entity\City;
use AppBundle\Entity\Log;
use AppBundle\Entity\ServiceProvider;
use AppBundle\Entity\TypeInstance;
use AppBundle\Entity\ZipCode;
use AppBundle\Entity\User;
use GandiBundle\ThirdParty\XmlRpcGandi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;


class DefaultController extends Controller
{



    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(){
/*
        $gandiApi = new \GandiBundle\Controller\GandiController();
        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $res = $gandiApi->listMailbox($connect,'legrain.fr');
        var_dump($res);
*/
        /*
        $gandiApi = new \GandiBundle\Controller\GandiController();
        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $res = $gandiApi->getInstance($connect,180784);
        dump($res);
        */
      /*

         $em = $this->getDoctrine()->getManager();
        $t = $em->getRepository('AppBundle:TypeInstance');

        $new = new TypeInstance();
        $new->setName('php7mysql');
        $em->persist($new);
        $em->flush();
      */
/*
        $gandiApi = new \GandiBundle\Controller\GandiController();
        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $domains=array('tartanpion.fr','tartanpion.com','tartanpion.net','tartanpion.info');
        $res = $gandiApi->domainsAvaillable($connect, $domains);

        dump($res);
*/
/*

        $domain='promethee.biz';

        $infosDomain = $gandiApi->infosDomain($connect,$domain);
        $zoneId = $infosDomain->zoneId;

       // $list = $gandiApi->listDomainZoneVersion($connect, $zoneId);
        $list = $gandiApi->domainZoneInfo($connect,$zoneId);

//        $test = $gandiApi->domainZoneRecordList($connect,$zoneId,34);
        dump($list);
        if($list['public']){
            $new = ($gandiApi->domainZoneClone($connect,$zoneId));
            dump($gandiApi->domainZoneSet($connect,$domain,$new['id']));
        }*/

/*
        $nameservers=array('dns1','dns2.ovh.net','dns3.ovh.net');
        dump($gandiApi -> setNameServers($connect,$domain,$nameservers));*/

/*
        $instance = $gandiApi->getInstance($connect,166340);
        dump($instance);*/
//        $infosServer = $gandiApi->getSizeHddSimpleHosting($connect,23293);
//        dump($infosServer);

      /*  $returns = array();
        foreach ($infosServer as $info){
            $returns[$info['size'][0]]=(float)$info['points'][0]['value']/1073741824;

        }
dump($returns);*/

/*
        $gandiApi = new \GandiBundle\Controller\GandiController();
        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $infosServer = $gandiApi->getInstance($connect,23293);
        dump($infosServer);
*/
/*
        $userRepository=  $this->getDoctrine()->getRepository('AppBundle:User');
        $nddRepository=  $this->getDoctrine()->getRepository('AppBundle:Ndd');
        $user = $userRepository->find(1);
        $list = $nddRepository->findByAgency($user->getAgency());

        dump($list);
*/
        /*
                $userRepository = $this->getDoctrine()->getRepository('AppBundle:User');
                $roleRepository=  $this->getDoctrine()->getRepository('AppBundle:Roles');

                $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');


                $userRepository->userDontExist('newservice3@legrain.fr',$roleCompteEmail);

        */

        /* $mydomain = 'legrain.work';

 // Enregistrement à ajouter
         $myrecord = array('name'=> 'monserveur', 'type'=> 'A','ttl'=>10800,'value'=>'192.254.100.11');

         $gandiApi = new \GandiBundle\Controller\GandiController();
         $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
         $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

         $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

         $infosDomain = $gandiApi->infosDomain($connect,$mydomain);



 //   # On cree une nouvelle version de la zone
 //        version = api.domain.zone.version.new(apikey, zone_id)
 //        # Mise a jour (suppression puis création de l'enregistrement)
 //        api.domain.zone.record.delete(apikey, zone_id, version, myrecord)
 //        myrecord['value'] = currentip
 //        myrecord['ttl'] = myttl
 //        api.domain.zone.record.add(apikey, zone_id, version, myrecord)
 //        # On valide les modifications sur la zone
 //        api.domain.zone.version.set(apikey, zone_id, version)
 //        api.domain.zone.set(apikey, mydomain, zone_id)
 //
         $zoneId = $infosDomain->zoneId;

         $numVersion = $gandiApi->domainZoneVersionNew($connect,$zoneId);
         $gandiApi->domainZoneAddRecordAction($connect,$zoneId,$numVersion,$myrecord);
         $gandiApi->domainZoneVersionSet($connect, $zoneId, $numVersion);
         $gandiApi->domainZoneSet($connect, $mydomain, $zoneId);
         echo 'done';
        */


        /*
        $productCategoryRepository = $this->getDoctrine()->getRepository('AppBundle:ProductCategory');
        $productRepository = $this->getDoctrine()->getRepository('AppBundle:Product');


        dump(
          $productRepository->findOneByTldAndCategory('com',$productCategoryRepository->findOneByName('createndd'))
        );
     */

        /* $gandiApi = new \GandiBundle\Controller\GandiController();

         $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
         $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

         $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
 */
        /*
        $cartLineRepository = $this->getDoctrine()->getRepository('AppBundle:CartLine');
        $cartRepository = $this->getDoctrine()->getRepository('AppBundle:Cart');
        $productCategoryRepository = $this->getDoctrine()->getRepository('AppBundle:ProductCategory');

        $productRepository =  $this->getDoctrine()->getRepository('AppBundle:Product');
         dump($cartLineRepository->findLinesPerProductCategoryAndOptionValidIsTrue($cartRepository->find(91),$productCategoryRepository->findOneByName('createndd')));
        */

        /*
                $cartLineRepository = $this->getDoctrine()->getRepository('AppBundle:CartLine');
                $cartRepository = $this->getDoctrine()->getRepository('AppBundle:Cart');
                $productCategoryRepository = $this->getDoctrine()->getRepository('AppBundle:ProductCategory');

                $productRepository =  $this->getDoctrine()->getRepository('AppBundle:Product');
              //  dump($cartLineRepository->findLinesPerProductCategory($cartRepository->find(91),$productCategoryRepository->findOneByName('createndd')));
        */


        /*
          SELECT p FROM Post p
        JOIN p.sports s
           JOIN s.sportUser sp WITH sp.user = :user
         */



        /*    $urlApp='dev.gwi-hosting.fr';
             $agencyRepository = $this->getDoctrine()->getRepository('AppBundle:Agency');

             $agency = $agencyRepository->findOneByUrlApp($urlApp);
             // Si pas trouvé, on charge la premiere ( legrain)
             if(!$agency)$agency = $agencyRepository->find(1);


             dump($agency);
     */
        /*
        $productCategoryRepository = $this->getDoctrine()->getRepository('AppBundle:ProductCategory');
        $nddCat = $productCategoryRepository->findOneByName('createndd');
        $productRepository = $this->getDoctrine()->getRepository('AppBundle:Product');
        //$tlds = $productRepository->findBy(array('categories'=>$nddCat,'active'=>true));
        $tlds = $productRepository->findByCategoryAndActive($nddCat,true);
*/
        //dump($tlds);
        /*
                $gandiApi = new \GandiBundle\Controller\GandiController();

                $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
                $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

                $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);


                $res = $gandiApi->domainAvaillable($connect,'julien-ver.net');

                dump($res);
        */
        /*
                $cartRepository = $this->getDoctrine()->getRepository('AppBundle:Cart');

                $cart = $cartRepository->find(12);

                $message = \Swift_Message::newInstance()
                    ->setSubject('Détail de la commande relative au panier : '.$cart->getId())
                    ->setFrom($this->getParameter('email_app'))
                    ->setTo('paiement@gwi-hosting.fr')
                    ->setBody(
                        $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                            'Email/cart.email.html.twig',
                            array('cart' => $cart)
                        ),
                        'text/html'
                    )

                ;
                $this->get('mailer')->send($message);
        */


        /*
                $gandiApi = new \GandiBundle\Controller\GandiController();

                $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
                $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

                $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);


                $list = $gandiApi->catalogListPricesDomain($connect);

                dump($list);
        */
// Ajout des produits.
        // $test = new GwiHostingController();


        //  dump($test->privateListWebRedir('legrain.fr'));
        /*
                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);
        */
        //   $list = $gandiApi->paasUpdateDisk($connect,145763,0);
        //$list = $gandiApi->paasUpdate($connect,145763,'s');

        //var_dump($list);

        /*
                $productPackMailRepository = $this->getDoctrine()->getRepository('AppBundle:Product');
                $packMailRepository = $this->getDoctrine()->getRepository('AppBundle:EmailGandiPackPro');
                $packMail = $packMailRepository->find(13);

                $ndd = $packMail->getNdd();
                $dateFinDomaine = $ndd->getExpirationDate();
                $options = new \stdClass();
                $options->period=12;

                $mathService = $this->container->get('tools.math');
                $pricePerMonth = 1.167;// prixUnitaire
                // $newTimestamp = strtotime('+2 years', $timestamp);
                //date('d/m/Y',strtotime('2015-11-11 +2 year'));
                $dateEnding= date('Y-m-d',strtotime($dateFinDomaine->format('Y-m-d').'+'.$options->period.' months'));// date fin domaine
                $dateBegin= $dateFinDomaine->format('Y-m-d');// date fin domaine


                echo 'date Deb : '.$dateBegin.'<br>';
                echo 'date fin : '.$dateEnding.'<br>';
                $quantity = $packMail->getSize();// nbre de Go dans le pack actuel.
                $prixAuProrata = $mathService->calculPrixAuProrata($dateEnding, $pricePerMonth, $quantity,$dateBegin);





                echo 'prix proratat : '.$prixAuProrata.'<br>';


                //echo date('d/m/Y',strtotime('2015-11-11 +2 year'));
                $gandiApi = new \GandiBundle\Controller\GandiController();

                $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
                $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

                $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

              //  $gandiApi->domainWebredirDelete($connect,'chasselas.fr','');
               // $gandiApi->vhostDelete($connect,'delete.chasselas.fr');
         //       $listSnapshot = $gandiApi->paasSnapshotList($connect,'113960');
        //dump($listSnapshot);
                $ACCOUNTBALANCEREPOSITORY = $this->getDoctrine()->getRepository('AppBundle:AccountBalance');
        $userRepository = $this->getDoctrine()->getRepository('AppBundle:User');
        */


        //    $curl = $this->get('tools.curlBugzilla');

        // $result = $curl->listComponents('Sites%20Web%20Clients');
        //  $result = $curl->listBugs('Sites%20Web%20Clients','davril-formation-securite.com');
//        $result = $curl->getCommentsBugs(26);
//        dump($result);
        /*
                $em = $this->getDoctrine()->getManager();
                $instanceRepository = $this->getDoctrine()->getRepository('AppBundle:Instance');

                $instance = $instanceRepository->findOneByIdGandi(23293);

                $lastDate = $instance->getDateEnd();
                var_dump($lastDate);
                echo '<hr>';
                $newDate = clone $lastDate;
                $newDate=$newDate->add(new \DateInterval('P1Y'));
                $instance->setDateEnd($newDate);
                $instance->setFtpServer('sftp.dc0.gpaas.net');
                $em->persist($instance);
                $em->flush();


                dump($instance);
        */
        /*
                $vhostsRepository = $this->getDoctrine()->getRepository('AppBundle:Vhosts');
                $instanceRepository = $this->getDoctrine()->getRepository('AppBundle:Instance');



                $instance = $instanceRepository->findOneByIdGandi(113960);
        */

        // dump($vhosts= $vhostsRepository->loadOtherVhostsForInstance($instance,array('awmtc.fr','www.awmtc.fr')) );
        // renouvelement d'une instance

        /*  $gandiApi = new \GandiBundle\Controller\GandiController();

          $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
          $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

          $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

          //$res = $gandiApi-> vhostsCreate( $connect,23293,'testdepuisinstance.legrain.fr');

         $res =  $gandiApi-> domainWebredirCreate( $connect,'chasselas.fr','','http://www.chasselas.fr','http301');
          dump($res);*/
        /*  $gandiApi = new \GandiBundle\Controller\GandiController();

                $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
                $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

                $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

                //$res = $gandiApi-> vhostsCreate( $connect,23293,'testdepuisinstance.legrain.fr');

               $res =  $gandiApi-> domainWebredirCreate( $connect,'chasselas.fr','','http://www.chasselas.fr','http301');
                dump($res);*/

//
//        $durationInMonths='1m';
//        $id_paas=113960;// instance awmtc :
        //$gandiApi->instanceRenew($connect,$id_paas,$durationInMonths);

        //  dump($gandiApi->vhostsList($connect,$id_paas));



        /*
                $userRepository = $this->getDoctrine()->getRepository('AppBundle:User');

                $user = $userRepository->find(1);


                $log = new Log($user,'test');

                $em = $this->getDoctrine()->getManager();
                $em->persist($log);

                $em->flush();

                echo $log->getId();

        */

//        $domain = "legrain.fr";
        //$domain = "echapeebio.fr";
        //$domain = "a2l-concept.com";
        //$gandi = new XmlRpcGandi();
        //$res = $gandi->request('updateMailbox',$domain,$login,$options);
        //var_dump($domain,$login,$options);

        // $res = $gandi->request('infosDomain',$domain);
        //  var_dump($res);
        /*   $gandiApi = new \GandiBundle\Controller\GandiController();

           $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
           $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

           $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);
           // instance joseph
           $id = 60900;
           $a = $gandiApi->getInstance($connect,$id);
          // $a = $gandiApi->listInstances($connect);

           echo '<pre>';
           var_dump($a);
           echo '</pre>';
           // $infoDomain = $gandiApi->getContact($connect,'GI47-GANDI');

   //var_dump($infoDomain);
           //   echo '<hr>';
           //   if(in_array ( "mail" , $infoDomain->services )) echo 'OUI';else echo 'Non';


           /* $domains=array();
                $options = array();
          //      $options['reseller'] = Constant::RESELLER;
                //$options['owner'] = $owner;
                $options['items_per_page'] = 100;
                $options['page'] = 1;
                $request = $gandi->request('listDomains',$options);

            var_dump($request);
    */

        // $responderEmailEntity = $this->getDoctrine()->getRepository('AppBundle:ResponderEmail');
        // $ndd = $this->getDoctrine()->getRepository('AppBundle:Ndd');
        /*
                     //  Payement...
                    \Stripe\Stripe::setApiKey('sk_test_gSTXDQxFFZiiBdKOGlrcLZWh');
                    $myCard = array('number' => '4242424242424242', 'exp_month' => 5, 'exp_year' => 2018,'cvc'=>100,'name'=>'Harry Covert');
                    // Amount en centime
                    $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => 20000, 'currency' => 'eur'));
                    echo $charge;

        */
        //$mathService = $this->container->get('tools.math');
        //   echo $mathService->nombreDeJours('2014-12-31','2013-12-31');

//            $dateFinDomaine=new \DateTime('2016-12-31');
//            $mathService = $this->container->get('tools.math');
//            $quantity=1;
//            $pricePerMonth=1;// prixUnitaire
//            $dateEnding=$dateFinDomaine->format('Y-m-d');// date fin domaine
//            $prixAuProrata = $mathService->calculPrixAuProrata(date('Y-m-d'),$dateEnding,$pricePerMonth,$quantity);
//            echo 'test : '.$prixAuProrata.'<hr>';

        /*
         * Calcul prix au prorata

                $dateBegin = '2015-01-01';
                $dateEnding = '2015-12-31';
                $priceGbPerMonth =1;
                $priceAboPerMonth=1;


                $totalGb = 10;

                $priceTotal = 0;

                // calcul du 1er mois ( nbre de jour restant dans le mois)

                $finDumois = date("Y-m-t", strtotime($dateBegin));
                $nbJoursDansLemois = date("t", strtotime($dateBegin));

                $datetime1 = new \DateTime($dateBegin);
                $datetime2 = new \DateTime($finDumois);
                $interval = $datetime1->diff($datetime2);
                // +1 car on compte le jour en cours
                $nbday= (int)$interval->format('%d')+1;
                $tmp=(($priceAboPerMonth+($priceGbPerMonth*$totalGb))/$nbJoursDansLemois)*$nbday;
                echo '$nbday'.$nbday.'<br>';
                $priceTotal+=$tmp;
                echo $tmp.'<br>';

                // Nombre de mois entre 2 dates
                $datetime1 = new \DateTime($dateBegin);
                $datetime2 = new \DateTime($dateEnding);
                $interval = $datetime1->diff($datetime2);
                $nbMonth= $interval->format('%m'); //Retourne le nombre de mois

                $tmp=($priceAboPerMonth+($priceGbPerMonth*$totalGb))*$nbMonth;
                $priceTotal+=$tmp;
                echo '+'.$tmp.'<br>';
                // Mois en cours
                $nbDUtilises=date('d',strtotime($dateEnding));
                $nbJoursDansLemois = date("t", strtotime($dateEnding));
                if( $nbJoursDansLemois != $nbDUtilises ) {
                    $tmp = (($priceAboPerMonth + ($priceGbPerMonth * $totalGb)) / $nbJoursDansLemois) * $nbDUtilises;
                    $priceTotal += $tmp;
                    echo $tmp . '<br>= <br/>';

                }
                echo '<hr>=' . round($priceTotal, 2) . '<hr>';
                // Affiche le dernier jour du mois passé en parametre
                //  $a_date = "2015-02-10";
                // echo date("Y-m-t", strtotime($a_date));
        */
        /*



                        $paypalService = $this->container->get('paypal.sdk');

                   $sandbox =  $this->getParameter('sandbox');

                  //  var_dump($sandbox);
                        $payement =  $paypalService->pay('mastercard','5574619727537302','12','2018','John','Doe',110,'Rechargement compte pré payé',$sandbox);
                       // $payement =  $paypalService->pay('visa','4446283280247004','11','2018','Joe','Shopper',30,'Rechargement compte pré payé',$sandbox);

                  //  echo $payement->state;
                //   print_r($payement->id);

        */

        $gandiApi = new \GandiBundle\Controller\GandiController();
        $user='hohloobeen1quaez7eis8eiBaiNgeita';
        $password='cooBeeNgeijaerie9aibae0ohxootee5';

        /*
                $accountBalanceRepository = $this->getDoctrine()->getRepository('AppBundle:AccountBalance');
                $accountBalance = $accountBalanceRepository->find(3);
                $line = new AccountBalanceLine();
                $line->setDescription('test programme');
                $line->setMouvement(-15);

               $accountBalance->addLine( $line);

                $em = $this->getDoctrine()->getManager();
                $em->persist($line);
                $em->flush();
        */
        //$connect = new \GandiBundle\Entity\Connect($user,$password);
        //  var_dump($gandiApi->infosDomain($connect,'legrain.fr'));
        //  var_dump($gandiApi->updateMailForward($connect,'legrain.fr','adressearediriger',array('aa@aa.fr','julien@legrain.fr','aaa@aa.fr')));

        // var_dump($gandiApi->listMailbox($connect,'legrain.fr'));

        /*


                $gandiApi = new \GandiBundle\Controller\GandiController();
                $user='hohloobeen1quaez7eis8eiBaiNgeita';
                $password='cooBeeNgeijaerie9aibae0ohxootee5';

                $connect = new \GandiBundle\Entity\Connect($user,$password);
                 var_dump($gandiApi->packMailInfo($connect,'pageweb.fr'));
        */
        // var_dump($gandiApi->listMailbox($connect,'legrain.fr'));

        /*
        $em = $this->getDoctrine()->getManager();
        $userRepository =  $this->getDoctrine()->getRepository('AppBundle:User');

        $user=$userRepository->find(1);
*/
        // echo $user->getRegistrationDate()->getTimestamp();

        /*
                $em = $this->getDoctrine()->getManager();
                $userRepository =  $this->getDoctrine()->getRepository('AppBundle:User');

                $user=$userRepository->find(1);
                $password = '1234';
              if($userRepository->checkLogIn($user,$password)){
                  echo 'Connecté';
              }else{
                  echo ' ou pas';
              }
        */
//        $userRepository->verifLogIn();
        /*
                $city = new City();
                $city->setName('Montech');
                $em = $this->getDoctrine()->getManager();
                $em->persist($city);
                $em->flush();
        */
        /*
        $em = $this->getDoctrine()->getManager();
        $repositoryCity =  $this->getDoctrine()->getRepository('AppBundle:City');

        $city= $repositoryCity->findOneById(11);
        $city->setName('Montech');
        $em->persist($city);
        $em->flush();
        */

//        $repository =  $this->getDoctrine()->getRepository('AppBundle:City');
//        $city = $repository->findOneBy(array('name'=>'Toulouse'));
//        var_dump($city);

        /*
                $zipCode = $this->getDoctrine()->getRepository('AppBundle:ZipCode')->find(1) ;

                $city = $this->getDoctrine()->getRepository('AppBundle:City')->find(1) ;

                echo $city->getName();
                echo '<br>';
                echo $zipCode->getName();

                $city->addZipCode($zipCode);

                $em = $this->getDoctrine()->getManager();
                $em->persist($city);
                $em->flush();
        */
        /*
                $zipCode = $this->getDoctrine()->getRepository('AppBundle:ZipCode')->find(2) ;

                $city = $this->getDoctrine()->getRepository('AppBundle:City')->find(1) ;

                echo $city->getName();
                echo '<br>';
                echo $zipCode->getName();

                $zipCode->addCity($city);

                $em = $this->getDoctrine()->getManager();
                $em->persist($zipCode);
                $em->flush();

        */
        return $this->render('default/index.html.twig');
    }


}
