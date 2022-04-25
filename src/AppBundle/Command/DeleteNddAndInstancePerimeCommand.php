<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 04/11/15
 * Time: 09:12
 */

// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteNddAndInstancePerimeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:deleteNddsInstances')
            ->setDescription('Supprime de la bdd les instances et les ndds qui ne sont plus chez Gandi.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();


        $nddRepository = $doctrine->getRepository('AppBundle:Ndd');
        $instanceRepository = $doctrine->getRepository('AppBundle:Instance');

        // Appel de l'api Gandi

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = $gandiApi->connection($usrGandi,$passwordGandi);

        // Liste des ndds du site
        $dNdds = $nddRepository->findAll();
        // Liste des ndds gandi.
        $gNdds = $gandiApi->listDomains($connect);
        $nddsPresentsChezGandi = array();
        foreach($gNdds as $gNdd){
            //  $output->writeln($gNdd->domain);
            $nddsPresentsChezGandi[]=$gNdd->domain;
        }
        foreach($dNdds as $dNdd){
            //  $output->writeln($dNdd->getName());
            if(!in_array($dNdd->getName(),$nddsPresentsChezGandi)){
                $output->writeln('Ndd supprimé : '.$dNdd->getName());
                $em->remove($dNdd);
            }
        }

        // Idem instances

        $dInstances = $instanceRepository->findAll();
        $gInstances = $gandiApi->listInstances($connect);

        $idsInstancesSurGandi = array();
        foreach($gInstances as $gi){
            $idsInstancesSurGandi[]=$gi->id_g;
        }
        foreach($dInstances as $di){
            if(!in_array($di->getIdGandi(),$idsInstancesSurGandi)){
                $output->writeln('Instance supprimée : '.$di->getName());
                $em->remove($di);
            }
        }
        $em->flush();
    }
}