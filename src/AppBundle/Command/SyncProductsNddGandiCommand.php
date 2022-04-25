<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use AppBundle\Entity\DataCenter;
use AppBundle\Entity\Instance;
use AppBundle\Entity\PriceListLine;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\SnapshotProfileInstance;
use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncProductsNddGandiCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:syncProductsNddGandi')
            ->setDescription('Synchronise les prix des ndd gandi avec la liste par défaut. Et, créé les produits dans l\'application si l\'extension n\'existe pas');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $productRepository = $doctrine->getRepository('AppBundle:Product');
        $productCategorieRepository = $doctrine->getRepository('AppBundle:ProductCategory');
        $PriceListRepository = $doctrine->getRepository('AppBundle:PriceList');
        $PriceListLineRepository = $doctrine->getRepository('AppBundle:PriceListLine');
        $tvaRapeRepository = $doctrine->getRepository('AppBundle:TvaRate');

        $tvaRate = $tvaRapeRepository->find(1);
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

        $priceList = $PriceListRepository->findOneByIsApplicationDefault(true);

        $list = $gandiApi->catalogListPricesDomain($connect);
        /*
         * array:6 [
          0 => array:3 [
            "action" => array:2 [
              "name" => "renew"
              "param" => []
            ]
            "product" => array:2 [
              "type" => "domain"
              "description" => ".fr"
            ]
            "unit_price" => array:2 [
              0 => array:9 [
                "max_duration" => 9
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 6.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "E"
                "id" => 134216
              ]
              1 => array:9 [
                "max_duration" => 9
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 12.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "A"
                "id" => 85576
              ]
            ]
          ]
          1 => array:3 [
            "action" => array:2 [
              "name" => "restore"
              "param" => []
            ]
            "product" => array:2 [
              "type" => "domain"
              "description" => ".fr"
            ]
            "unit_price" => array:1 [
              0 => array:9 [
                "max_duration" => 1
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 12.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "A"
                "id" => 85616
              ]
            ]
          ]
          2 => array:3 [
            "action" => array:2 [
              "name" => "transfer"
              "param" => []
            ]
            "product" => array:2 [
              "type" => "domain"
              "description" => ".fr"
            ]
            "unit_price" => array:2 [
              0 => array:9 [
                "max_duration" => 1
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 6.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "E"
                "id" => 134220
              ]
              1 => array:9 [
                "max_duration" => 1
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 12.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "A"
                "id" => 85578
              ]
            ]
          ]
          3 => array:3 [
            "action" => array:2 [
              "name" => "transfer_reseller"
              "param" => []
            ]
            "product" => array:2 [
              "type" => "domain"
              "description" => ".fr"
            ]
            "unit_price" => array:1 [
              0 => array:9 [
                "max_duration" => 1
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 0.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "A"
                "id" => 814
              ]
            ]
          ]
          4 => array:3 [
            "action" => array:2 [
              "name" => "change_owner"
              "param" => []
            ]
            "product" => array:2 [
              "type" => "domain"
              "description" => ".fr"
            ]
            "unit_price" => array:1 [
              0 => array:9 [
                "max_duration" => 1
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 12.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "A"
                "id" => 85577
              ]
            ]
          ]
          5 => array:3 [
            "action" => array:2 [
              "name" => "create"
              "param" => array:1 [
                "tld_phase" => "golive"
              ]
            ]
            "product" => array:2 [
              "type" => "domain"
              "description" => ".fr"
            ]
            "unit_price" => array:2 [
              0 => array:9 [
                "max_duration" => 10
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 6.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "E"
                "id" => 134212
              ]
              1 => array:9 [
                "max_duration" => 10
                "special_op" => false
                "currency" => "EUR"
                "duration_unit" => "y"
                "price" => 12.0
                "min_duration" => 1
                "price_type" => null
                "grid" => "A"
                "id" => 85579
              ]
            ]
          ]
        ]

         */

        foreach($list as $l) {

            $price = 0;
            foreach ($l['unit_price'] as $unit_price) {
                if ($unit_price['grid'] == 'A') {
                    $price = round($unit_price['price'] / 12,2);
                }
            }

            // On charge le produit s'il existe. Ou, on le créé
            $p = $productRepository->findOneByReference($l['action']['name'] . 'ndd' . substr($l['product']['description'], 1));
            $save = true;
            $ok = true;
          //  $output->writeln('Type : '.$l['action']['name']);
            if($l['action']['name']=='create'){
                /*
                "action" => array:2 [
                      "name" => "create"
                      "param" => array:1 [
                        "tld_phase" => "golive"
                      ]
                */
                if($l['action']['param']['tld_phase']!='golive')$ok = false;

            }

            if($ok) {
                if ($p === null) {
                    //  $output->writeln($l['action']['name'].' '. $l['product']['description']);
                    switch ($l['action']['name']) {
                        case 'renew':
                            $name = 'Renouvellement nom de domaine en  ' . $l['product']['description'];
                            $short = 'Renouvellement nom de domaine en ' . $l['product']['description'];
                            $long = 'Renouvellement nom de domaine en ' . $l['product']['description'];
                            break;
                        case 'create':
                            $name = 'Creation nom de domaine en  ' . $l['product']['description'];
                            $short = 'Creation nom de domaine en ' . $l['product']['description'];
                            $long = 'Creation nom de domaine en ' . $l['product']['description'];

                            break;
                        case 'restore':
                            $name = 'Restauration nom de domaine en  ' . $l['product']['description'];
                            $short = 'Restauration nom de domaine en ' . $l['product']['description'];
                            $long = 'Restauration nom de domaine en ' . $l['product']['description'];
                            break;
                        case 'transfer':
                            $name = 'Transfert nom de domaine en  ' . $l['product']['description'];
                            $short = 'Transfert nom de domaine en ' . $l['product']['description'];
                            $long = 'Transfert nom de domaine en ' . $l['product']['description'];
                            break;
                        case 'transfer_reseller':
                            $name = 'Transfert revendeur nom de domaine en  ' . $l['product']['description'];
                            $short = 'Transfert revendeur nom de domaine en ' . $l['product']['description'];
                            $long = 'Transfert revendeur nom de domaine en ' . $l['product']['description'];
                            break;
                        case 'change_owner':
                            $name = 'Changement de propriètaire nom de domaine en  ' . $l['product']['description'];
                            $short = 'Changement de propriètaire nom de domaine en ' . $l['product']['description'];
                            $long = 'Changement de propriètaire nom de domaine en ' . $l['product']['description'];
                            break;
                        default:
                            $name = '';
                            $short = '';
                            $long = '';
                            $save = false;
                            break;
                    }
                    if ($save) {
                        $p = new Product();
                        $p->setActive(false);
                        $p->setReference($l['action']['name'] . 'ndd' . substr($l['product']['description'], 1));
                        // On regarde pour loader la catégorie
                        $cat = $productCategorieRepository->findOneByName($l['action']['name'] . 'ndd');
                        if ($cat === null) {
                            $cat = new ProductCategory();
                            $cat->setName($l['action']['name'] . 'ndd');
                            $cat->setIsVisible(false);
                            $em->persist($cat);
                            $em->flush();
                            $output->writeln('Ajout de la catégorie : ' . $cat->getName());
                        }
                        $p->setCodeLgr($l['action']['name'] . 'Ndd' . ucfirst(substr($l['product']['description'], 1)));
                        $p->addCategory($cat);
                        $p->setName($name);
                        $p->setMinPeriod(12);
                        $p->setShortDescription($short);
                        $p->setLongDescription($long);
                        $p->setSousProduit(false);
                        $p->setFeatures(array('tld' => $l['product']['description']));
                        $p->setActive(false);
                        $em->persist($p);

                        $em->flush();
                        $output->writeln('Ajout du produit : ' . $p->getName());


                    }
                }
                if ($save) {
                    $features = is_object($p->getFeatures()) ? $p->getFeatures() : new \stdClass();

                    $features->tld = $l['product']['description'];


                    //    $output->writeln($p->getName() . ' extention : ' . $l['product']['description']);
                    $p->setFeatures($features);
                    //   $output->writeln('Done');
                    $em->persist($p);
                }
                if ($p != null) {

                    // On charge la ligne de la grille par défaut de l'application
                    $priceLine = $PriceListLineRepository->findOneBy(array('priceList' => $priceList, 'product' => $p));
                    if ($priceLine == null) {
                        $priceLine = new PriceListLine();
                        $priceLine->setMinPrice(null);
                        $priceLine->setPrice($price);
                        $priceLine->setProduct($p);
                        $priceLine->setPriceList($priceList);
                        $priceLine->setTvaRate($tvaRate);
                    }
                //    $priceLine->setPrice(0);
                    if ($priceLine->getPrice() < $price) {
                        $priceLine->setPrice($price);
                        $output->writeln('Modification du prix pour le produit : ' . $p->getName() . ' prix : ' . $price);
                    }
                    $em->persist($priceLine);
                }
            }
        }
        $em->flush();
    }
}
