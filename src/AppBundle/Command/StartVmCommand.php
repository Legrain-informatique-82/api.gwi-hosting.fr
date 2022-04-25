<?php


namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartVmCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:startVm')
            ->setDescription('start les vms');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
        // id du serveur servpprod
        $gandiApi->vmStart($connect,241568);
        // id du serveur serveur02 (dev)
        $gandiApi->vmStart($connect,131636);
    }
}