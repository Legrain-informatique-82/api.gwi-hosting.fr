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

class SyncInstancesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:syncInstances')
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


        $productRepository = $doctrine->getRepository('AppBundle:Product');
        $productPartHdd = $productRepository->findOneByReference('parthdd');

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


                // On loade le datacenter s'il existe, ou, on le sauve
                $dataCenterRepository = $doctrine->getRepository('AppBundle:DataCenter');
                $dataCenter = $dataCenterRepository->findOneByIdGandi($gi['datacenter']['id']);
                if ($dataCenter == null) {
                    $dataCenter = new DataCenter();
                    $dataCenter->setName($gi['datacenter']['name']);
                    $dataCenter->setCountry($gi['datacenter']['iso']);
                    $dataCenter->setIso($gi['datacenter']['name']);
                    $dataCenter->setIdGandi($gi['datacenter']['id']);
                    $dataCenter->setDcCode($gi['datacenter']['dc_code']);
                    $em->persist($dataCenter);
                    $em->flush();
                }

                $snapshotProfileRepository = $doctrine->getRepository('AppBundle:SnapshotProfileInstance');
                if($gi['snapshot_profile']==null){
                    // Id en dur du profil null
                    $snapshotProfile = $snapshotProfileRepository->find(2);
                }else {
                    $snapshotProfile = $snapshotProfileRepository->findOneByIdGandi($gi['snapshot_profile']['id']);
                    if ($snapshotProfile == null) {
                        $snapshotProfile = new SnapshotProfileInstance();
                        $snapshotProfile->setName($gi['snapshot_profile']['name']);
                        $snapshotProfile->setIdGandi($gi['snapshot_profile']['id']);
                        $em->persist($snapshotProfile);
                        $em->flush();
                    }
                }
                $sizeInstanceRepository = $doctrine->getRepository('AppBundle:SizeInstance');
                $sizeInstance = $sizeInstanceRepository->findOneByName($gi['size']);
                if ($sizeInstance == null) throw new \SoapFault('Error', 'Taille inconnue');

                $typeInstanceRepository = $doctrine->getRepository('AppBundle:TypeInstance');
                $typeInstance = $typeInstanceRepository->findOneByName($gi['type']);
                if ($typeInstance == null) throw new \SoapFault('Error', 'Type d\'instance inconnue');

                $instance->setName($gi['name']);

                $instance->setCatalogName($gi['catalog_name']);
                $instance->setConsole($gi['console']);
                $instance->setDataCenter($dataCenter);
                $instance->setDataDiskAdditionalSize($gi['data_disk_additional_size']);
                $instance->setActive(true);
                $instance->setDataDiskTotalSize($gi['datadisk_total_size']);
                $instance->setUserFtp($gi['user']);
                $instance->setIdGandi($gi['id']);
                $instance->setSizeInstance($sizeInstance);
                $instance->setFtpServer($gi['ftp_server']);
                $instance->setGitServer($gi['git_server']);
                $instance->setNeedUpgrade($gi['need_upgrade']);
                $instance->setSnapshopProfileInstance($snapshotProfile);
                $instance->setTypeInstance($typeInstance);
                $dateEnd = new \DateTime();
                $dateEndCommitment = new \DateTime();
                $dateStart = new \DateTime();

                $instance->setDateEnd($gi['date_end'] == null ? null : $dateEnd->setTimestamp($gi['date_end']->timestamp));
                $instance->setDateEndCommitment($gi['date_end_commitment'] == null ? null : $dateEndCommitment->setTimestamp($gi['date_end_commitment']->timestamp));
                $instance->setDateStart($gi['date_start'] == null ? null : $dateStart->setTimestamp($gi['date_start']->timestamp));

                $instance->setFreeDisk($diskInfos['free']);
                $instance->setUsedDisk($diskInfos['used']);
                $instance->setProductPartHdd($productPartHdd);

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