<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use AppBundle\Entity\DataCenter;
use AppBundle\Entity\SnapshotProfileInstance;
use AppBundle\Entity\Vhosts;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncVhostsCommand extends ContainerAwareCommand
{
    protected function configure(){
        $this->setName('cron:syncVhosts')
            ->setDescription('Synchronise les vhosts de l\'appli avec les données simpleHosting de chez Gandi');
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $gandiApi = new \GandiBundle\Controller\GandiController();
        $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';
        $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

        $instanceRepository = $doctrine->getRepository('AppBundle:Instance');
        $vhostsRepository = $doctrine->getRepository('AppBundle:Vhosts');
        // On charge toutes les instances de l'appli.
        $instances = $instanceRepository->findAll();
        // On boucle sur chaque instance
        $vhostspresents = array();
        foreach($instances as $ins){
            // Synchronise les instances..
            $gi =  $gandiApi->getInstance($connect,$ins->getIdGandi());
            $dataCenterRepository = $doctrine->getRepository('AppBundle:DataCenter');
            $dataCenter = $dataCenterRepository->findOneByIdGandi($gi['datacenter']['id']);
            if($dataCenter==null){
                $dataCenter = new DataCenter();
                $dataCenter->setName($gi['datacenter']['name']);
                $dataCenter->setCountry($gi['datacenter']['iso']);
                $dataCenter->setIso($gi['datacenter']['name']);
                $dataCenter->setIdGandi($gi['datacenter']['id']);
                $dataCenter->setDcCode($gi['datacenter']['dc_code']);
                $em->persist($dataCenter);
                $em->flush();
            }

            $snapshotProfineRepository =$doctrine->getRepository('AppBundle:SnapshotProfileInstance');
            if($gi['snapshot_profile']['id']==null){
                $id = (int)0;
            }else{
                $id=$gi['snapshot_profile']['id'];
            }
            $snapshotProfine = $snapshotProfineRepository->findOneByIdGandi($id);
            if($snapshotProfine==null){
                $snapshotProfine = new SnapshotProfileInstance();
                $snapshotProfine->setName($gi['snapshot_profile']['name']);
                $snapshotProfine->setIdGandi($gi['snapshot_profile']['id']);
                $em->persist($snapshotProfine);
                $em->flush();
            }

            $sizeInstanceRepository = $doctrine->getRepository('AppBundle:SizeInstance');
            $sizeInstance = $sizeInstanceRepository->findOneByName($gi['size']);
            if($sizeInstance==null) throw new \SoapFault('Error','Taille inconnue');

            $typeInstanceRepository = $doctrine->getRepository('AppBundle:TypeInstance');
            $typeInstance = $typeInstanceRepository->findOneByName($gi['type']);
            if($typeInstance==null) throw new \SoapFault('Error','Type d\'instance inconnue');
            // On la sauve.
            $ins->setName($gi['name']);
            // On fixe l'instance au produit 5.5
            $ins->setCatalogName($gi['catalog_name']);
            $ins->setConsole($gi['console']);
            $ins->setDataCenter($dataCenter);
            $ins->setDataDiskAdditionalSize($gi['data_disk_additional_size']);
            $ins->setActive(true);
            $ins->setDataDiskTotalSize($gi['datadisk_total_size']);
            $ins->setUserFtp($gi['user']);
            $ins->setIdGandi($gi['id']);
            $ins->setSizeInstance($sizeInstance);
            $ins->setFtpServer($gi['ftp_server']);
            $ins->setGitServer($gi['git_server']);
            $ins->setNeedUpgrade($gi['need_upgrade']);
            $ins->setSnapshopProfileInstance($snapshotProfine);
            $ins->setTypeInstance($typeInstance);
            $dateEnd = new \DateTime();
            $dateEndCommitment = new \DateTime();
            $dateStart = new \DateTime();

            $ins->setDateEnd($gi['date_end']==null?null:$dateEnd->setTimestamp($gi['date_end']->timestamp));
            $ins->setDateEndCommitment($gi['date_end_commitment']==null?null:$dateEndCommitment->setTimestamp($gi['date_end_commitment']->timestamp));
            $ins->setDateStart($gi['date_start']==null?null:$dateStart->setTimestamp($gi['date_start']->timestamp));


            // liste des "hosting" déjà sauvé pour l'instance
            $nbEmptyHost = 0;
            $nbHost = 0;
            $hostingRepository = $doctrine->getRepository('AppBundle:Hosting');


            foreach($hostingRepository->findByInstance($ins) as $h){
                $nbHost++;
                if($h->getVhost() ==null){
                    $nbEmptyHost++;
                }
            }

            $ins->setTotalHerberMutu($nbHost);
            $ins->setNbEmptyHerberMutu($nbEmptyHost);
            $nddRepository = $doctrine->getRepository('AppBundle:Ndd');


            // Vhosts
            $vhostsGandi = $gandiApi->vhostsList($connect,$ins->getIdGandi());
            $ins->setNbVhosts(count($vhostsGandi));
            // On sauve l'instance
            $em->persist($ins);

            // 3 cas : -vhosts trouvé dans appli et chez gandi : On update
            //         - vhosts chez gandi pas dans appli : On ajoute
            //         - vhosts dans appli pas chez gandi : On supprime le vhost dans appli (rqt : On cherche tous les vhosts pour cette instance qui ne sont pas dans la liste (liste de chez gandi)).
            foreach($vhostsGandi as $vh) {
                $dateCrea = date('Y-m-d H:i:s', $vh['date_creation']->timestamp);
                // On loade le vhost (s'il existe), ou, on l'ajoute. On passe aussi le nom du vhosts dans un array
                $vhostspresents[] = $vh['name'];
                $dvhost = $vhostsRepository->findOneBy(array('instance' => $ins, 'name' => $vh['name']));
                // On récupère ne NDD correspondant au vhost
                // Ou sous domaine ( www.domain.tld, domain.tld, a.domain.tld)
                $ndd=null;
                $aNdd = explode('.',$vh['name']);
                // On prend les 2 derniers éléments qui doivent correspondre au ndd
                $countNdd = count($aNdd)-1;
                $nameNdd =$aNdd[$countNdd-1].'.'.$aNdd[$countNdd];
                $ndd=$nddRepository->findOneByName($nameNdd);



                // cas 2
                if ($dvhost == null) {
                    $dvhost = new Vhosts();
                    $dvhost->setInstance($ins);
                    $dvhost->setInMaintenance(false);
                }
                // cas
                if($ndd){
                $dvhost->setUser($ndd->getUser());
                    $dvhost->setNdd($ndd);

                }
           // 1 et 2
                $dvhost->setName($vh['name']);
                $dvhost->setDateCrea(new \DateTime($dateCrea));
                $dvhost->setIdGandi($vh['id']);
                $dvhost->setState($vh["state"]);
                $em->persist($dvhost);
            }
            // cas 3.
            $vhostsToDelete= $vhostsRepository->loadOtherVhostsForInstance($ins,$vhostspresents);
            foreach($vhostsToDelete as $v){
                $em->remove($v);
            }
        }
        $em->flush();

    }
}