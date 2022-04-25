<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddProductsInstanceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('products:add')
            ->setDescription('Active et désactive les répondeurs chez Gandi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $categoryRepository = $doctrine->getRepository('AppBundle:ProductCategory');

        $puissanceInstance = $categoryRepository->findOneByName('puissanceInstance');
        if($puissanceInstance===null) {
            $puissanceInstance = new ProductCategory();
            $puissanceInstance->setName('puissanceInstance');
            $puissanceInstance->setIsVisible(false);
            $em->persist($puissanceInstance);
            $em->flush();
        }
        $optionInstance = $categoryRepository->findOneByName('optionInstance');
        if($optionInstance===null) {
            $optionInstance = new ProductCategory();
            $optionInstance->setName('optionInstance');
            $optionInstance->setIsVisible(false);
            $em->persist($optionInstance);
            $em->flush();
        }

        $optionPartHdd = $categoryRepository->findOneByName('optionPartHddInstance');
        if($optionPartHdd===null) {
            $optionPartHdd = new ProductCategory();
            $optionPartHdd->setName('optionPartHddInstance');
            $optionPartHdd->setIsVisible(false);
            $em->persist($optionPartHdd);
            $em->flush();
        }

        $catInstance = $categoryRepository->findOneByName('instance');
        $catRenewInstance = $categoryRepository->findOneByName('renewinstance');

        $catPuissance = $categoryRepository->findOneByName('puissanceInstance');
        $categories = array($catPuissance);

        // Ajout des produits.

        $products = array(
            // dédié escalimmo
            array('name'=>'Serveur dédié E','codeLgr'=>'serveur dedié E','features'=>(array('tailleDisque'=>60,'puissance'=>'m')),'longDescription'=>'Serveur dédié E','minPeriod'=>'12','reference'=>'instanceimmoe','shortDescription'=>'Serveur dédié E','categories'=>array($catInstance),'sousProduit'=>false),
            array('name'=>'Renouvellement Serveur dédié E','codeLgr'=>'Renouvellement serveur dedié E','features'=>(array('tailleDisque'=>60,'puissance'=>'m')),'longDescription'=>'Renouvellement Serveur dédié E','minPeriod'=>'12','reference'=>'renewinstanceimmoe','shortDescription'=>'Renouvellement Serveur dédié E','categories'=>array($catRenewInstance),'sousProduit'=>false),

            array('name'=>'Serveur immo','codeLgr'=>'serveur immo','features'=>(array('tailleDisque'=>50,'puissance'=>'m')),'longDescription'=>'Serveur immo','minPeriod'=>'12','reference'=>'instanceimmo','shortDescription'=>'Serveur immo','categories'=>array($catInstance),'sousProduit'=>false),
            array('name'=>'Renouvellement Serveur immo','codeLgr'=>'Renouvellement serveur immo','features'=>(array('tailleDisque'=>50,'puissance'=>'m')),'longDescription'=>'Renouvellement Serveur immo','minPeriod'=>'12','reference'=>'renewinstanceimmo','shortDescription'=>'Renouvellement Serveur immo','categories'=>array($catRenewInstance),'sousProduit'=>false),


            //puissances
            array('name'=>'Puissance S','codeLgr'=>'puissance S','features'=>(array('size'=>'S')),'longDescription'=>'Puissance S','minPeriod'=>'12','reference'=>'puissances','shortDescription'=>'Puissance S','categories'=>$categories,'sousProduit'=>true),
            array('name'=>'Puissance M','codeLgr'=>'puissance M','features'=>(array('size'=>'M')),'longDescription'=>'Puissance M','minPeriod'=>'12','reference'=>'puissancem','shortDescription'=>'Puissance M','categories'=>$categories,'sousProduit'=>true),
            array('name'=>'Puissance L','codeLgr'=>'puissance L','features'=>(array('size'=>'L')),'longDescription'=>'Puissance L','minPeriod'=>'12','reference'=>'puissancel','shortDescription'=>'Puissance L','categories'=>$categories,'sousProduit'=>true),
            array('name'=>'Puissance XL','codeLgr'=>'puissance XL','features'=>(array('size'=>'XL')),'longDescription'=>'Puissance XL','minPeriod'=>'12','reference'=>'puissancexl','shortDescription'=>'Puissance XL','categories'=>$categories,'sousProduit'=>true),
            array('name'=>'Puissance XXL','codeLgr'=>'puissance XXL','features'=>(array('size'=>'XXL')),'longDescription'=>'Puissance XXL','minPeriod'=>'12','reference'=>'puissancexxl','shortDescription'=>'Puissance XXL','categories'=>$categories,'sousProduit'=>true),


            // tranche disque
            array('name'=>'Espace disque en option','codeLgr'=>'partHdd','features'=>(array('part'=>'5')),'longDescription'=>'Part hébergement 5Go','minPeriod'=>'12','reference'=>'parthdd','shortDescription'=>'Part hébergement 5Go','categories'=>array($optionPartHdd),'sousProduit'=>true),

            // SSL
//            array('name'=>'option ssl','codeLgr'=>'ssl','features'=>(array()),'longDescription'=>'Activer le SSL','minPeriod'=>'12','reference'=>'simplehostingssl','shortDescription'=>'option ssl','categories'=>array($cat,$cat2,$optionInstance),'sousProduit'=>true), // SSL
            // Maintenance technique
            array('name'=>'maintenance technique','codeLgr'=>'mainteancetechnique','features'=>(array()),'longDescription'=>'maintenance technique','minPeriod'=>'12','reference'=>'simplehostingmaintenance','shortDescription'=>'maintenance technique','categories'=>array($optionInstance),'sousProduit'=>true),

        );

        $productRepository = $doctrine->getRepository('AppBundle:Product');
        foreach($products as $p){

            $tmp= $productRepository->findOneByReference($p['reference']);
            if($tmp===null) {
                $produit = new Product();
                $produit->setName($p['name']);
                $produit->setCodeLgr($p['codeLgr']);
                $produit->setFeatures($p['features']);
                $produit->setLongDescription($p['longDescription']);
                $produit->setMinPeriod($p['minPeriod']);
                $produit->setReference($p['reference']);
                $produit->setShortDescription($p['shortDescription']);
                $produit->setSousProduit($p['sousProduit']);
                $produit->setActive(true);
                foreach($p['categories'] as $c) {
                    $produit->addCategory($c);
                }

                $em->persist($produit);
            }
        }


        $em->flush();
    }
}