<?php


namespace AppBundle\Command;

use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StopVmCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stopVm')
            ->setDescription('Stoppe les vms');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Appel de l'api Gandi
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        // id du serveur servpprod
        $gandiApi->vmStop($connect,241568);
        // id du serveur serveur02 (dev)
        $gandiApi->vmStop($connect,131636);
    }
}