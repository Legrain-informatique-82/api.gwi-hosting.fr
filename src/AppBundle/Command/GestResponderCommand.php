<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GestResponderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:responder')
            ->setDescription('Active et désactive les répondeurs chez Gandi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

        // Activation des répondeur
        $query = $em->createQuery(
            'SELECT r
    FROM AppBundle:ResponderEmail r
    WHERE r.activeGandi = false
    AND r.initDate <= :initDate
   '
        )->setParameter('initDate', new \DateTime());

        $responders = $query->getResult();
        foreach($responders as $responder){

            $gandiApi->activateResponder($connect,$responder->getEmail(),date('Y-m-d'),$responder->getMessage());
            $responder->setActiveGandi(true);
            $em->persist($responder);
        }
        $em->flush();
        // désactivation des répondeurs et suppression de la ligne

        $query = $em->createQuery(
            'SELECT r
    FROM AppBundle:ResponderEmail r
    WHERE r.activeGandi = true
    AND r.endDate <= :endDate
   '
        )->setParameter('endDate', new \DateTime());

        $responders = $query->getResult();
        foreach($responders as $responder){
            $gandiApi->disableResponder($connect,$responder->getEmail(),date('Y-m-d'));
            $em->remove($responder);

        }
        $em->flush();
    }
}