<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use AppBundle\Entity\TmpRenewPackMail;
use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Stdlib\DateTime;

class RenewPackMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:renewPacksMails')
            ->setDescription('Renouvelle les packs mails qui le doivent');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mathService = $this->getContainer()->get('tools.math');
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $gandiApi = new \GandiBundle\Controller\GandiController();
        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';
        $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);


        $tmpRenewPackMailRepository = $doctrine->getRepository('AppBundle:TmpRenewPackMail');


        $packs = $tmpRenewPackMailRepository->findAll();


        foreach($packs as $packToRenew){
            //$packToRenew = new TmpRenewPackMail();
            $pack = $packToRenew->getPackMail();
            $ndd  = $packToRenew->getNdd();
            $dateShouldBeTheDomain = $packToRenew->getDateShouldBeTheDomain();
            // On récupère les infos du ndd chez Gandi
            $gndd = $gandiApi->infosDomain($connect,$ndd->getName());

        $timestampGndd = $gndd->dateRegistryEnd;
            // Si la date de chez gandi = date que devrait avoir le domaine, on lance le renew chez gandi avec la difference de jour.
            $dateGndd = date('Ymd',$timestampGndd);
            if($dateGndd == $dateShouldBeTheDomain->format('Ymd')){
                $output->writeln('Renouvellement du pack mail : '.$ndd->getName());
                // Nombre de jours ebtre la fin du domaine chez Gandi et la date du packmail.
                $durationInDays = $mathService->nombreDeJours($pack->getDateEnding()->format('Y-m-d'), $dateShouldBeTheDomain->format('Y-m-d'));
               if( $gandiApi->renewPackMail($connect,$ndd->getName(),$durationInDays)){
                   // On met à jour la date de fin du pack
                   $pack->setDateEnding($dateShouldBeTheDomain);
                   $em->persist($pack);
                   $em->remove($packToRenew);
               }
            }
        }
        $em->flush();
    }
}