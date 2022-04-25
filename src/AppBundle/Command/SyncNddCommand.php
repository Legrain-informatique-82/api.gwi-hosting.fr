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

class SyncNddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:syncNdds')
            ->setDescription('Synchronise les ndds de l\'appli avec les données chez Gandi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $container =  $this->getApplication()->getKernel()->getContainer();
        // Liste des ndds de l'appli qui ont un id Gandi.




        $query = $em->createQuery('SELECT n FROM AppBundle:Ndd n WHERE n.idGandi != :idGandi')->setParameter('idGandi','');
        $ndds = $query->getResult();
        //
        // Appel de l'api Gandi

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';


        $contactRepository = $doctrine->getRepository('AppBundle:Contact');
        $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);
        //  var_dump($ndds);
        $emailperDefault = $container->getParameter('email_gandi_per_default');
        foreach ($ndds as $ndd) {

            // Récupèration des infos
           

                $infos = $gandiApi->infosDomain($connect, $ndd->getName());

                //   $output->writeln($infos->contacts['owner']['handle']);

                // On charge le contact et, s'il nexiste pas on l'enregistre.
                $contact = $contactRepository->findOneByIdGandi($infos->contacts['owner']['id']);
                if(!$contact){
                    $contact= new Contact();
                    $contact->setCodeGandi($infos->contacts['owner']['handle']);
                    $contact->setCode(str_replace('GANDI','GWI',$infos->contacts['owner']['handle']));
                    $contact->setIdGandi($infos->contacts['owner']['id']);
                    // On récupère le mail associé.
                    $infoContact = $gandiApi->getContact($connect,$infos->contacts['owner']['handle']);
                    $contact->setFakeEmail($infoContact->email);
                    $contact->setEmail($infoContact->email);


                    $em->persist($contact);

                }

                // On regarde si le contact possède déjà le ndd
                if(!   $contact->getNdds()->contains($ndd)){
                    $output->writeln('Ajout du contact '.$contact->getName().' pour le ndd : '.$ndd->getName());
                    $contact->addNdd($ndd);
                    $ndd->setContact($contact);
                    $contact->setUser($ndd->getUser());
                    //   $em->persist($ndd);
                    $em->persist($contact);
                }
                // synchronisation (On ajoutera les autres infos quand on en aura besoin)
                $ndd->setExpirationDate(new \DateTime(date('Y-m-d',$infos->dateRegistryEnd) ));
                $ndd->setServices($infos->services );

                //$output->writeln($ndd->getName().' '.implode('||',$infos->services));
                $em->persist($ndd);
                $em->flush();


        }



        $em->flush();
    }
}

