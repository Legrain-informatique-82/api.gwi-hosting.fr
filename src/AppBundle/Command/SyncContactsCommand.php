<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:syncContacts')
            ->setDescription('Synchronise les contacts de l\'appli avec les données chez Gandi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = false;
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $container =  $this->getApplication()->getKernel()->getContainer();
        // Liste des ndds de l'appli qui ont un id Gandi.



        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';


        $contactRepository = $doctrine->getRepository('AppBundle:Contact');


        $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

        $gContacts = $gandiApi->listContacts($connect);
//dump($gcontacts[0]);
        /*
        array:33 [
  "family" => "turfplac sarl"
  "community" => false
  "vat_number" => null
  "newsletter" => 0
  "id" => 2990992
  "is_corporate" => false
  "city" => "Verdun-sur-Garonne"
  "given" => "turfplac sarl"
  "zip" => "82600"
  "extra_parameters" => array:4 [
    "birth_date" => ""
    "birth_department" => ""
    "birth_city" => ""
    "birth_country" => ""
  ]
  "orgname" => "turfplac sarl"
  "state" => null
  "security_question_answer" => ""
  "type" => 1
  "email" => "legrain@wanadoo.fr"
  "is_resel" => true
  "fax" => null
  "handle" => "TTS56-GANDI"
  "third_part_resell" => 0
  "siren" => "389313628"
  "data_obfuscated" => 0
  "phone" => "+33.563026914"
  "lang" => "en"
  "shippingaddress" => []
  "streetaddr" => "domaine de pescay Mas Grenier"
  "bu" => array:3 [
    "forbidden_tlds" => []
    "id" => 1
    "name" => "Gandi SAS"
  ]
  "mobile" => null
  "country" => "FR"
  "mail_obfuscated" => 0
  "brand_number" => null
  "reachability" => "pending"
  "security_question_num" => 0
  "validation" => "none"
]

         */
        // à la fin, il faudra supprimer de l'application tous les contacts qui ne sont plus chez Gandi
        $contactsInGandi=array();
        foreach($gContacts as $gContact){

            $contactsInGandi[]=$gContact['handle'];
            $dContact = $contactRepository->findOneByCodeGandi($gContact['handle']);
            if($dContact==null){
                $dContact = new Contact();
                $dContact->setFakeEmail($gContact['email']);
            }

           // if($gContact['handle']=='TTS56-GANDI'){
             //   dump($gContact);
           // }
            $dContact->setCodeGandi($gContact['handle']);
            $dContact->setCode(str_replace('GANDI','GWI',$gContact['handle']));
            $dContact->setIdGandi($gContact['id']);
            // On récupère le mail associé.
            if($force)$dContact->setFakeEmail($gContact['email']);
            $dContact->setEmail($gContact['email']);
            $dContact->setName($gContact['family']);
            $dContact->setFirstname($gContact['given']);
            $em->persist($dContact);
        }

        // On supprime tous les contacts Qui ne SONT PAS chez Gandi
        $em->createQuery('DELETE FROM AppBundle:Contact c WHERE c.codeGandi NOT IN (:codes)')->setParameter('codes',$contactsInGandi)->getResult();

        $em->flush();
    }
}