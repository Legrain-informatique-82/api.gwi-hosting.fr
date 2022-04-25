<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Product;
use AppBundle\Entity\PriceListLine;
use AppBundle\Entity\ProductCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportNddProductAndLinksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('import:nddProducts')
            ->setDescription('Ajoute les ndds et fait la liaison avec les ndd déjà enregistrés');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        // On regarde si la catégorie renewndd existe
        $productCategoryRepository = $doctrine->getRepository('AppBundle:ProductCategory');
        $cat = $productCategoryRepository->findOneByName('renewndd');
        if(!$cat){
            $cat = new ProductCategory();
            $cat->setIsVisible(false);
            $cat->setName('renewndd');
            $em->persist($cat);
            $em->flush();
        }
        $products = array();
        $products[]=array('name'=>'Renouvellement nom de domaine en .fr','reference'=>'renewnddfr','codeLgr'=>'renewNddFr','price'=>1,'shortDescription'=>'Renouvellement nom de domaine en .fr','longDescription'=>'Renouvellement nom de domaine en .fr (12 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .biz','reference'=>'renewnddbiz','codeLgr'=>'renewNddBiz','price'=>1.133,'shortDescription'=>'Renouvellement nom de domaine en .biz','longDescription'=>'Renouvellement nom de domaine en .biz (13,60 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .com','reference'=>'renewnddcom','codeLgr'=>'renewNddCom','price'=>1.045,'shortDescription'=>'Renouvellement nom de domaine en .com','longDescription'=>'Renouvellement nom de domaine en .com (12,54 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .net','reference'=>'renewnddnet','codeLgr'=>'renewNddNet','price'=>1.167,'shortDescription'=>'Renouvellement nom de domaine en .net','longDescription'=>'Renouvellement nom de domaine en .net (14 euros/an)');

        $products[]=array('name'=>'Renouvellement nom de domaine en .eu','reference'=>'renewnddeu','codeLgr'=>'renewNddEu','price'=>1,'shortDescription'=>'Renouvellement nom de domaine en .eu','longDescription'=>'Renouvellement nom de domaine en .eu (12 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .info','reference'=>'renewnddinfo','codeLgr'=>'renewNddInfo','price'=>1.167,'shortDescription'=>'Renouvellement nom de domaine en .info','longDescription'=>'Renouvellement nom de domaine en .info (14 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .org','reference'=>'renewnddorg','codeLgr'=>'renewNddOrg','price'=>1.167,'shortDescription'=>'Renouvellement nom de domaine en .org','longDescription'=>'Renouvellement nom de domaine en .org (14 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .pro','reference'=>'renewnddpro','codeLgr'=>'renewNddPro','price'=>1.333,'shortDescription'=>'Renouvellement nom de domaine en .pro','longDescription'=>'Renouvellement nom de domaine en .pro (16 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .tel','reference'=>'renewnddtel','codeLgr'=>'renewNddTel','price'=>1.167,'shortDescription'=>'Renouvellement nom de domaine en .tel','longDescription'=>'Renouvellement nom de domaine en .tel (14 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .tv','reference'=>'renewnddtv','codeLgr'=>'renewNddTv','price'=>2.375,'shortDescription'=>'Renouvellement nom de domaine en .tv','longDescription'=>'Renouvellement nom de domaine en .tv (28.5 euros/an)');
        $products[]=array('name'=>'Renouvellement nom de domaine en .mobi','reference'=>'renewnddmobi','codeLgr'=>'renewNddMobi','price'=>1.583,'shortDescription'=>'Renouvellement nom de domaine en .mobi','longDescription'=>'Renouvellement nom de domaine en .mobi (19 euros HT/an)');


        // Ajout des produits

        $productRepository = $doctrine->getRepository('AppBundle:Product');

        $priceListRepository = $doctrine->getRepository('AppBundle:PriceList');
        $tvaRepository = $doctrine->getRepository('AppBundle:TvaRate');
        foreach($products as $p){
            // On essaye de loader le produits.
            $exist = $productRepository->findOneByReference($p['reference']);
            if(!$exist){
                $np = new Product();
                $np->setName($p['name']);
                $np->setReference($p['reference']);
                $np->setCodeLgr($p['codeLgr']);
                $np->setShortDescription($p['shortDescription']);
                $np->setLongDescription($p['longDescription']);
                $np->setMinPeriod(12);
                $priceListLine= new PriceListLine();
                $priceListLine->setPrice($p['price']);
                $priceListLine->setPriceList($priceListRepository->find(1));
                $priceListLine->setTvaRate($tvaRepository->find(1));
                $np->addPriceListLine($priceListLine);
                $np->addCategory($cat);
                $cat->addProduct($np);
                $priceListLine->setProduct($np);
                $em->persist($np);

            }
        }
        $em->flush();

        $nddRepository = $doctrine->getRepository('AppBundle:Ndd');
        // On boucle sur les NDD et on ajoute le bon produit.
        $nddProducts = $nddRepository->findAll();

        foreach($nddProducts as $ndd){
            // On récupere l'extention
            $arrayNdd = explode('.',$ndd->getName());
            $ext = $arrayNdd[count($arrayNdd)-1];


            $product = $productRepository->findOneByReference('renewndd'.$ext);
//            switch($ext){
//                case 'fr':
//                    $product = $productRepository->findOneByReference('renewnddfr');
//                    break;
//                case 'biz':
//                    $product = $productRepository->findOneByReference('renewnddbiz');
//                    break;
//                case 'com':
//                    $product = $productRepository->findOneByReference('renewnddcom');
//                    break;
//                case 'net':
//                    $product = $productRepository->findOneByReference('renewnddnet');
//                    break;
//                default:
//                    $product =false;
//                    break;
//            }
            if($product){
                $ndd->setProduct($product);
                $em->persist($ndd);
            }
         //   echo $ndd->getName();
        }

        $em->flush();


    }
}