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

class SwitchcInstancesCommand extends ContainerAwareCommand
{
    protected function configure(){
        $this
            ->setName('sync:switchInstance')
            ->setDescription('Change les types de serveurs (instance5.5, instance15, instance10');
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $instanceRepository = $em->getRepository('AppBundle:Instance');

        $productRepository = $em->getRepository('AppBundle:Product');
        $numberVhostsInstanceRepository = $em->getRepository('AppBundle:NumberVhostsInstance');


        $instances = $instanceRepository->findAll();



        $newProduct = $productRepository->findOneByReference('instance');
        $newProductRenew = $productRepository->findOneByReference('renewinstance');


        foreach ($instances as $instance){
            $product = $instance->getProduct();
            if($product->getReference()=='instance5.5'||$product->getReference()=='instance10'||$product->getReference()=='instance15'){
                $instance->setProduct($newProduct);
                $instance->setProductRenew($newProductRenew);
                // On change aussi les produits en fct des produits spécifiques


                switch ($product->getReference()){
                    case 'instance5.5':
                        $numberMaxVhost = $numberVhostsInstanceRepository->findOneByValue(5);
                        break;
                    case 'instance10':
                        $numberMaxVhost = $numberVhostsInstanceRepository->findOneByValue(15);
                        break;
                    case 'instance15':
                        $numberMaxVhost = $numberVhostsInstanceRepository->findOneByValue(70);
                        break;
                }
                $instance->setNumberMaxVhosts($numberMaxVhost);
                $em->persist($instance);
            }

        }
        $em->flush();

    }
}