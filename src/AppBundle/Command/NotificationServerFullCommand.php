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

class NotificationServerFullCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:notificationServerFull')
            ->setDescription('Notification sur les serveurs "trop pleins"');
    }

    protected function execute(InputInterface $input, OutputInterface $output){





        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $container =  $this->getApplication()->getKernel()->getContainer();

        $instanceRepository = $em->getRepository('AppBundle:Instance');
        $agencyRepository = $em->getRepository('AppBundle:Agency');


        $instances = $instanceRepository->findAll();

        $percentAlert = 90;

        foreach ($instances as $instance){


            $percent = round(($instance->getUsedDisk()/($instance->getUsedDisk()+$instance->getFreeDisk()))*100);
            if($percent >= $percentAlert) {
               // dump($percent);
                $user = $instance->getUser();
                // Si gestionnaire ou si l'agence ne facture pas
                if($user->getParent()==null||$user->getAgency()->getFacturationBylegrain()){
                    // Pour les gestionnaire, on force l'agence à "legrain" ce qui permet d'afficher nos coordonnées dans l'e-mail
                    // 1 = id agence Legrain.
                    $agency = $agencyRepository->find(1);
                }else{
                    $agency = $user->getAgency();
                }


                $message = \Swift_Message::newInstance()
                    ->setSubject('['.$agency->getName().'] '.$percent.'% de la limite de stockage pour le serveur '.$instance->getName())
                    ->setFrom($container->getParameter('email_app'))
                    ->setTo($instance->getUser()->getEmail())
//                    ->setTo('gweb@legrain.biz')
                    ->setBody(
                        $container->get('templating')->render(
                        // app/Resources/views/Emails/registration.html.twig
                            'Email/taille_limite_serveur.email.html.twig',
                            array('instance' => $instance,'percent'=>$percent,'agency'=>$agency)
                        ),
                        'text/html'
                    )

                ;
                $container->get('mailer')->send($message);

            }

        }




    }



}




