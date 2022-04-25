<?php


// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Contact;
use AppBundle\Entity\ListBugBugzilla;
use Proxies\__CG__\AppBundle\Entity\Ndd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncBugCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:syncBugs')
            ->setDescription('Synchronise les bug et envoi un mail à l\'utilisateur si nouveau message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $container =  $this->getApplication()->getKernel()->getContainer();
        $curl =$container->get('tools.curlBugzilla');
        $mailer = $container->get('mailer');


        $nddRepository = $doctrine->getRepository('AppBundle:Ndd');
        $listBugBugzillaRepository =$doctrine->getRepository('AppBundle:ListBugBugzilla');
        $bugzillaRepository =$doctrine->getRepository('AppBundle:Bugzilla');



        // Liste des ndds de l'appli
        $ndds = $nddRepository->findAll();

$listComponents = $curl->listComponents('Sites%20Web%20Clients');

        foreach ($ndds as $ndd){
            $sendMail = false;
            $user = $ndd->getUser();

            // On regarde si le proprio du domaine a le tag : Sites Web Clients
            $tagBugzilla = $bugzillaRepository->findOneBy(array('user'=>$user,'tag'=>'Sites Web Clients'));

            if($tagBugzilla){
                // On continue pour ce domaine
                if(in_array($ndd->getName(), $listComponents)){
                    // On continue pour ce domaine
                    $listBugzilla = $curl->listBugs('Sites%20Web%20Clients',$ndd->getName());
                    foreach($listBugzilla as $l){
                        $bug = $listBugBugzillaRepository->findOneByIdBug($l->id);
                        if($bug==null){
                            $sendMail=true;
                            $bug = new ListBugBugzilla();
                            $bug->setIdBug($l->id);
                            $bug->setNdd($ndd);
                            $bug->setIsRead(false);
                            $bug->setDateLastUpdate(new \DateTime($l->last_change_time));
                        }else{
                            $lastDate = $bug->getDateLastUpdate();
                            $newDate = new \DateTime($l->last_change_time);
                            if($lastDate->format('YmdHis')!=$newDate->format('YmdHis')) {
                                $bug->setIsRead('false');
                                $sendMail=true;
                            }
                            $bug->setDateLastUpdate($newDate);
                        }
                        $em->persist($bug);

                        // Récupération des infos à afficher
                    }
                    $em->flush();

if($sendMail){
    //$output->writeln('Mail pour le domaine : '.$ndd->getName());

    $message = \Swift_Message::newInstance()
        ->setSubject('[Legrain Informatique] Notification intervention sur votre domaine : '.$ndd->getName())
        ->setFrom(        $container->getParameter('email_app')
)
        ->setTo($user->getEmail())
        ->setBody(
            '<p>Bonjour,</p>
            <p>Nous avons effectué une intervention sur votre site Internet. Vous en trouverez les détails sur GWI-Hosting</p>',
            'text/html'
        )
        /*
         * If you also want to include a plaintext version of the message
        ->addPart(
            $this->renderView(
                'Emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */
    ;
    $mailer->send($message);

}
                }

            }

        }



    }
}