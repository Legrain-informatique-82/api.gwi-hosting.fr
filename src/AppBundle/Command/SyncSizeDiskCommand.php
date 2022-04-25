<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use AppBundle\Entity\DataCenter;
use AppBundle\Entity\Instance;
use AppBundle\Entity\SnapshotProfileInstance;
use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncSizeDiskCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:syncSizeDiskInstances')
            ->setDescription('Synchronise les instances de l\'appli avec les données chez Gandi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        // Liste des ndds de l'appli qui ont un id Gandi.

        $query = $em->createQuery('SELECT n FROM AppBundle:Instance n WHERE n.idGandi != :idGandi')->setParameter('idGandi','');
        $instances = $query->getResult();
        //
        // Appel de l'api Gandi

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';



//        $contactRepository = $doctrine->getRepository('AppBundle:Contact');
        $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);
        //  var_dump($ndds);
        foreach ($instances as $instance) {
            try {
                $gi = $gandiApi->getInstance($connect, $instance->getIdGandi());


                $infosDiskServer = $gandiApi->getSizeHddSimpleHosting($connect,$instance->getIdGandi());
                $diskInfos = array();
                foreach ($infosDiskServer as $info){
                    $diskInfos[$info['size'][0]]=(float)$info['points'][0]['value'];

                }

                $instance->setFreeDisk($diskInfos['free']);
                $instance->setUsedDisk($diskInfos['used']);


                // On sauve
                $em->persist($instance);
            }catch(\Exception $e){
                if($e->getMessage() =='Error on object : OBJECT_PAAS (CAUSE_NOTFOUND) [Paas \''.$instance->getIdGandi().'\' doesn\'t exist.]' ) {
                    $em->remove($instance);
                }
            }

        }
        $em->flush();
    }
}