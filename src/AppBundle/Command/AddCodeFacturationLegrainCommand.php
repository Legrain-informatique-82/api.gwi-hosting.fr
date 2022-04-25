<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use AppBundle\Entity\ListBugBugzilla;
use AppBundle\Entity\User;
use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCodeFacturationLegrainCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('legrain:codeFacturation')
            ->setDescription('Ajoute les codes facturations en fct du document .csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $container =  $this->getApplication()->getKernel()->getContainer();

        $filename = 'listecodesfacturation.csv';
        $path = $container->getParameter('kernel.root_dir').'/../web/';
//$output->writeln($path);
        //$emailContactPerDefault = 'legrain@legrain.biz';
        $userRepository = $doctrine->getRepository("AppBundle:User");
        $contactRepository = $doctrine->getRepository('AppBundle:Contact');
        if (($handle = fopen("$path"."$filename", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                list($email,$codeFacturation,$contactGandi) = $data;
                //$output->writeln($email);
                $user = $userRepository->findOneByEmail($email);
               // $contact = $contactRepository->findOneByCodeGandi($contactGandi);
             /*   if($contact && $contact->getEmail()!=$emailContactPerDefault){
                    $contact->setFakeEmail($contact->getEmail());
                    $contact->setEmail($emailContactPerDefault);
                }
             */
                if($user){
                    $user->setCodeClient($codeFacturation);
                  /*  if($contact ){
                        $contact->setUser($user);
                        $contact->setIsDefault(true);
                    }
                  */
                    $em->persist($user);
                    $output->writeln('Ajout du code de facturation : '.$codeFacturation.' pour l\'utilisateur : '.$email);

                }
               // if($contact)$em->persist($contact);

            }
            $em->flush();
            fclose($handle);
        }

    }



}




