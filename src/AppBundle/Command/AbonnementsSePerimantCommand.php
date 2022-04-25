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

class AbonnementsSePerimantCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:listAboPerimant')
            ->setDescription('Liste tous les abonnements se prérimant dans les 45 jours');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $container =  $this->getApplication()->getKernel()->getContainer();

        $dateCheck = new \DateTime('now');
        $dateCheck->add(new \DateInterval('P45D'));

        $output->writeln('Date du jour + 45jours : '.$dateCheck->format('d/m/Y'));

        // Liste de tous les hébergements mutualisés dont la date de fin < $dateCheck
        $query = $em->createQuery(
            'SELECT h
    FROM AppBundle:Hosting h
    WHERE h.dateEnding < :dateEnding

   '
        )->setParameter('dateEnding', $dateCheck);
        $hostings = $query->getResult();
        // Liste de tous les NDD dont la date de fin < $dateCheck
        $query = $em->createQuery(
            'SELECT n
    FROM AppBundle:Ndd n
    WHERE n.expirationDate < :expirationDate

   '
        )->setParameter('expirationDate', $dateCheck);

        $ndds = $query->getResult();
        // Liste de tous les comptes mail pro dont la date de fin < $dateCheck
        $query = $em->createQuery(
            'SELECT m
    FROM AppBundle:EmailGandiPackPro m
    WHERE m.dateEnding < :dateEnding

   '
        )->setParameter('dateEnding', $dateCheck);

        $mailsPro = $query->getResult();
        // Liste de toute les instances dont la date de fin < $dateCheck
        $query = $em->createQuery(
            'SELECT i
    FROM AppBundle:Instance i
    WHERE i.dateEnd < :dateEnd

   '
        )->setParameter('dateEnd', $dateCheck);

        $instances = $query->getResult();


        $return =array();
        foreach($ndds as $ndd){
            $tmp=array('name'=>$ndd->getName(),'date'=>$ndd->getExpirationDate(),'userEmail'=>$ndd->getUser()->getEmail());
            $return[]=$tmp;
        }
        foreach($hostings as $h){
            if($h->getProductHosting()->getRenewByCustomer()) {
                $tmp = array('name' => $h->getProduchHosting()->getName() . '(' . ($h->getHost() == null ? 'NC' : $h->getHost()) . ')', 'date' => $h->getDateEnding(), 'userEmail' => $h->getUser()->getEmail());
                $return[] = $tmp;
            }
        }

        foreach($mailsPro as $mailp){
            $tmp=array('name'=>'mail pro pour le domaine : '.$mailp->getNdd()->getName(),'date'=>$mailp->getDateEnding(),'userEmail'=>$mailp->getNdd()->getUser()->getEmail());
            $return[]=$tmp;
        }

        foreach($instances as $instance){
            $tmp=array('name'=>$instance->getName(),'date'=>$instance->getDateEnd(),'userEmail'=>$instance->getUser()->getEmail());
            $return[]=$tmp;
        }

        usort($return, function( $a, $b ) {
            return $a["userEmail"] > $b["userEmail"];
        });

       // Mail à écrire
         // Le foreach sera fait dans le template.
       /*
          foreach($return as $r){
            $output->writeln($r['userEmail'].' '.$r['name'].' date : '.$r['date']->format('d/m/Y'));
        }
*/
        // Email


        $message = \Swift_Message::newInstance()
        ->setSubject('Abonnements se périmant avant le '.$dateCheck->format('d/m/Y'))
        ->setFrom($container->getParameter('email_app'))
        ->setTo('notification@gwi-hosting.fr')
        ->setBody(
            $container->get('templating')->render(
            // app/Resources/views/Emails/registration.html.twig
        'Email/abos_se_perimant.email.html.twig',
        array('abos' => $return)
        ),
        'text/html'
        )

        ;
       $container->get('mailer')->send($message);


    }



}




