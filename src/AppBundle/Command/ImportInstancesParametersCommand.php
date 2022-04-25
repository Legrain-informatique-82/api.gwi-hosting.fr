<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductAgency;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\PriceListLine;
use AppBundle\Entity\SizeInstance;
use AppBundle\Entity\TypeInstance;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportInstancesParametersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('import:instancesParameters')
            ->setDescription('Importe les paramètres relatifs aux instances');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        // Ajout de la catégorie de produit renewinstance et instance

        $productCategoryRepository = $doctrine->getRepository('AppBundle:ProductCategory');
        $productRepository = $doctrine->getRepository('AppBundle:Product');

        $productCategory = $productCategoryRepository->findOneByName('instance');
        if($productCategory==null){
            $productCategory = new ProductCategory();
            $productCategory->setName('instance');
            $productCategory->setIsVisible(false);
            $em->persist($productCategory);
        }
        $productCategoryRenew = $productCategoryRepository->findOneByName('renewinstance');
        if($productCategoryRenew==null){
            $productRenew = new ProductCategory();
            $productRenew->setName('renewinstance');
            $productRenew->setIsVisible(false);
            $em->persist($productRenew);
        }
        $em->flush();

        $products = array();
        $products[]=array('name'=>'Instance à 5.5 euros','reference'=>'instance5.5','codeLgr'=>'instance5.5','price'=>5.5,'shortDescription'=>'Instance à 5.5 euros','longDescription'=>'Instance à 5.5 euros (66 euros/an)','cat'=>$productCategory);
        $products[]=array('name'=>'Renouvellement Instance à 5.5 euros','reference'=>'renewinstance5.5','codeLgr'=>'renewInstance5.5','price'=>5.5,'shortDescription'=>'Renouvellement Instance à 5.5 euros','longDescription'=>'Renouvellement Instance à 5.5 euros (66 euros/an)','cat'=>$productCategoryRenew);


        $products[]=array('name'=>'Instance à 10 euros','reference'=>'instance10','codeLgr'=>'instance10','price'=>10,'shortDescription'=>'Instance à 10 euros','longDescription'=>'Instance à 10 euros (120 euros/an)','cat'=>$productCategory);
        $products[]=array('name'=>'Renouvellement Instance à 10 euros','reference'=>'renewinstance10','codeLgr'=>'renewInstance10','price'=>10,'shortDescription'=>'Renouvellement Instance à 10 euros','longDescription'=>'Renouvellement Instance à 10 euros (120 euros/an)','cat'=>$productCategoryRenew);

        $products[]=array('name'=>'Instance à 15 euros','reference'=>'instance15','codeLgr'=>'instance15','price'=>15,'shortDescription'=>'Instance à 15 euros','longDescription'=>'Instance à 15 euros (180 euros/an)','cat'=>$productCategory);
        $products[]=array('name'=>'Renouvellement Instance à 15 euros','reference'=>'renewinstance15','codeLgr'=>'renewInstance15','price'=>15,'shortDescription'=>'Renouvellement Instance à 15 euros','longDescription'=>'Renouvellement Instance à 15 euros (180 euros/an)','cat'=>$productCategoryRenew);


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
                $np->addCategory($p['cat']);
                $p['cat']->addProduct($np);
                $priceListLine->setProduct($np);
                $em->persist($np);

            }
        }
        $em->flush();




        // Ajout des types d'instances
        $typeInstanceRemository = $doctrine->getRepository('AppBundle:TypeInstance');
        $types = array("phpmysql", "phppgsql", "nodejspgsql", "nodejsmongodb", "phpmongodb", "nodejsmysql", "pythonmysql", "pythonpgsql", "pythonmongodb", "rubymysql", "rubypgsql", "rubymongodb");
        foreach($types as $t){
            if($typeInstanceRemository->findOneByName($t)==null) {
                $ty = new TypeInstance();
                $ty->setName($t);
                $em->persist($ty);
            }
        }

        // Ajout des sizes d'instances
        $sizeInstanceRemository = $doctrine->getRepository('AppBundle:SizeInstance');
        $sizes = array("s", "m", "l", "xl", "xxl");
        foreach($sizes as $t){
            if($sizeInstanceRemository->findOneByName($t)==null) {
                $ty = new SizeInstance();
                $ty->setName($t);
                $em->persist($ty);
            }
        }


        $em->flush();
    }
}