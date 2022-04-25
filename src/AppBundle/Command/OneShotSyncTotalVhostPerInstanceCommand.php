<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 07/04/16
 * Time: 10:10
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class OneShotSyncTotalVhostPerInstanceCommand extends ContainerAwareCommand
{
    protected function configure(){
        $this
            ->setName('oneShot:syncTotalVhosts')
            ->setDescription('Mets à jour le nombre max de vhosts (et, plus tard le profil de sauvegarde)');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $container =  $this->getApplication()->getKernel()->getContainer();


        $instanceRepository = $em->getRepository('AppBundle:Instance');
        $numberVhostsInstanceRepository = $em->getRepository('AppBundle:NumberVhostsInstance');

        $listInstances = $instanceRepository->findAll();

        foreach ($listInstances as $instance){
            $nbreVhosts = $instance->getProduct()->getFeaturesAsArray()['nombreVhostsMax'];

            $objNbreVhosts = $numberVhostsInstanceRepository->findOneByValue($nbreVhosts);
            if($objNbreVhosts!=null){
                $instance->setNumberMaxVhosts($objNbreVhosts);
                $em->persist($instance);
            }else{
                $output->writeln('Pas de correspondance pour le serveur : '.$instance->getName());
            }

        }

        $em->flush();
        $output->writeln('Done');
    }
}