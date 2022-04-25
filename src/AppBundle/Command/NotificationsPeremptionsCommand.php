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

class NotificationsPeremptionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:notificationsPeremptions')
            ->setDescription('Envoie un email en fct des périodes');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $container =  $this->getApplication()->getKernel()->getContainer();

        $dans2mois = new \DateTime('now');
        $dans2mois->add(new \DateInterval('P60D'));

        $dans1mois = new \DateTime('now');
        $dans1mois->add(new \DateInterval('P30D'));

        $dans15jrs = new \DateTime('now');
        $dans15jrs->add(new \DateInterval('P15D'));

        $dans3jours = new \DateTime('now');
        $dans3jours->add(new \DateInterval('P3D'));

        $dans1jour = new \DateTime('now');
        $dans1jour->add(new \DateInterval('P1D'));

        /*
                // reboot
                $em->createQuery('UPDATE AppBundle:Instance i SET i.niveauNotification=0 WHERE i.dateEnd > :dateEnd AND i.niveauNotification > 0')->setParameter('dateEnd', $aujourdhui)->getResult();
                $em->createQuery('UPDATE AppBundle:Ndd n SET n.niveauNotification=0 WHERE n.expirationDate > :expirationDate AND n.niveauNotification > 0')->setParameter('expirationDate', $aujourdhui)->getResult();
                $em->createQuery('UPDATE AppBundle:EmailGandiPackPro e SET e.niveauNotification=0 WHERE e.dateEnding > :dateEnding AND e.niveauNotification > 0')->setParameter('dateEnding', $aujourdhui)->getResult();
        */
        // Remise à plat de tous les "niveauNotification" pour les entités Ndd/Instance/EmailGandiPackMail/hebergements qui ont une date de peremtion > 2 mois
        $em->createQuery('UPDATE AppBundle:Instance i SET i.niveauNotification=0 WHERE i.dateEnd > :dateEnd AND i.niveauNotification > 0')->setParameter('dateEnd', $dans2mois)->getResult();
        $em->createQuery('UPDATE AppBundle:Ndd n SET n.niveauNotification=0 WHERE n.expirationDate > :expirationDate AND n.niveauNotification > 0')->setParameter('expirationDate', $dans2mois)->getResult();

        $em->createQuery('UPDATE AppBundle:EmailGandiPackPro e SET e.niveauNotification=0 WHERE e.dateEnding > :dateEnding AND e.niveauNotification > 0')->setParameter('dateEnding', $dans2mois)->getResult();
        $em->createQuery('UPDATE AppBundle:Hosting e SET e.niveauNotification=0 WHERE e.dateEnding > :dateEnding AND e.niveauNotification > 0')->setParameter('dateEnding', $dans2mois)->getResult();


        $userRepository = $doctrine->getRepository('AppBundle:User');

        $users = $userRepository->findAll();

        foreach($users as $user){

            // abonnements périmés dans - de 2 mois
            $listeAbo =  $this->listeAbo( $em,$dans2mois,$user,(int)0);
            if(!empty($listeAbo))$this->send($dans2mois,$container,$listeAbo, $user);
            $em->flush();
            // abonnements périmés dans - de 1 mois
            $listeAbo =  $this->listeAbo( $em,$dans1mois,$user,(int)1);
            if(!empty($listeAbo))$this->send($dans1mois,$container,$listeAbo, $user);
            $em->flush();

            // abonnements périmés dans - de 15 jours
            $listeAbo =   $this->listeAbo( $em,$dans15jrs,$user,(int)2);
            if(!empty($listeAbo))$this->send($dans15jrs,$container,$listeAbo, $user);
            $em->flush();

            // abonnements périmés dans - de 3 jours
            $listeAbo =   $this->listeAbo( $em,$dans3jours,$user,(int)3);
            if(!empty($listeAbo))$this->send($dans3jours,$container,$listeAbo, $user);
            $em->flush();

            // abonnements périmés dans - de 1 jour
            $listeAbo =   $this->listeAbo( $em,$dans1jour,$user,(int)4);
            if(!empty($listeAbo))$this->send($dans1jour,$container,$listeAbo, $user);
            $em->flush();
        }
    }

    private function send($date,$container,$listeAbos,User $user)
    {


        // Si gestionnaire ou si l'agence ne facture pas
        if($user->getParent()==null||$user->getAgency()->getFacturationBylegrain()){
            // Pour les gestionnaire, on force l'agence à "legrain" ce qui permet d'afficher nos coordonnées dans l'e-mail
            $doctrine = $this->getContainer()->get('doctrine');
            $em = $doctrine->getManager();
            $agencyLegrainRepository = $em->getRepository('AppBundle:Agency');
            // 1 = id agence Legrain.
            $agencyLegrain = $agencyLegrainRepository->find(1);
            $agency = $agencyLegrain;
        }else{
            $agency = $user->getAgency();
        }
        if ($agency->getUrlApp() != null) {

            // Si legrain ou gestionnaire
            if( $agency->getId()==1||$user->getParent()==null){
                $emailTo = $user->getEmail();
            }else{
                $emailTo = 'notification@gwi-hosting.fr';
            }
            $message = \Swift_Message::newInstance()
                ->setSubject('['.$agency->getName().'] - Liste de vos abonnements se périmant avant le ' . $date->format('d/m/Y'))
                ->setFrom($container->getParameter('email_app'))
                //->setTo('notification@gwi-hosting.fr')
                ->setTo($emailTo )
                ->setBcc('notification@gwi-hosting.fr')
                ->setBody(
                    $container->get('templating')->render(
                    // app/Resources/views/Emails/registration.html.twig
                        'Email/abos_se_perimant_user.email.html.twig',
                        array('abos' => $listeAbos, 'user' => $user, 'date' => $date,'agency'=>$agency)
                    ),
                    'text/html'
                );
            $container->get('mailer')->send($message);
            //dump($message);
         // dump( $container->get('templating')->render('Email/abos_se_perimant_user.email.html.twig',array('abos' => $listeAbos, 'user' => $user, 'date' => $date,'agency'=>$agency)));
        }
    }

    private function selectEntitiesNdd( $em,\DateTime $date,User $user, $niveau){
        $query = $em->createQuery('SELECT n FROM AppBundle:Ndd n WHERE n.expirationDate <= :date  AND n.niveauNotification= :niveau AND n.user=:user')->setParameters(array('date'=> $date,'user'=>$user,'niveau'=>$niveau));
        return $query->getResult();
    }

    private function selectEntitiesInstances( $em,\DateTime $date,User $user, $niveau){
        $query = $em->createQuery('SELECT i FROM AppBundle:Instance i WHERE i.dateEnd <= :date  AND i.niveauNotification= :niveau  AND i.user=:user' )->setParameters(array('date'=> $date,'user'=>$user,'niveau'=>$niveau));
        return $query->getResult();
    }
    private function selectEntitiesHosting( $em,\DateTime $date,User $user, $niveau){
        $query = $em->createQuery('SELECT i FROM AppBundle:Hosting i WHERE i.dateEnding <= :date  AND i.niveauNotification= :niveau  AND i.user=:user' )->setParameters(array('date'=> $date,'user'=>$user,'niveau'=>$niveau));
        return $query->getResult();
    }

    private function selectEntitiesEmails( $em,\DateTime $date,User $user, $niveau){
        $query = $em->createQuery('SELECT e FROM AppBundle:EmailGandiPackPro e WHERE e.dateEnding <= :date  AND e.niveauNotification= :niveau AND e.user=:user')->setParameters(array('date'=> $date,'user'=>$user,'niveau'=>$niveau));
        return $query->getResult();
    }

    private function listeAbo( $em,\DateTime $date,User $user, $niveau){
        $ndds = $this->selectEntitiesNdd( $em,$date, $user,$niveau);
        $instances = $this->selectEntitiesInstances( $em,$date, $user,$niveau);
        $emails = $this->selectEntitiesEmails( $em,$date, $user,$niveau);
        $hostings = $this->selectEntitiesHosting( $em,$date, $user,$niveau);
        $return =array();
        foreach($ndds as $ndd){
            $tmp=array('type'=>'Nom de domaine','name'=>$ndd->getName(),'date'=>$ndd->getExpirationDate());
            $return[]=$tmp;
            $ndd->setNiveauNotification($ndd->getNiveauNotification()+1);
            $em->persist($ndd);
        }

        foreach($emails as $mailp){
            $tmp=array('type'=>'E-mails pro.','name'=>'mail pro pour le domaine : '.$mailp->getNdd()->getName(),'date'=>$mailp->getDateEnding());
            $return[]=$tmp;
            $mailp->setNiveauNotification($mailp->getNiveauNotification()+1);
            $em->persist($mailp);
        }

        foreach($instances as $instance){
            $tmp=array('type'=>'Serveur','name'=>$instance->getName(),'date'=>$instance->getDateEnd());
            $return[]=$tmp;
            $instance->setNiveauNotification($instance->getNiveauNotification()+1);
            $em->persist($instance);
        }
        foreach($hostings as $hosting){
            $tmp=array('type'=>'Hébergement mutualisé','name'=>$hosting->getVhost(),'date'=>$hosting->getDateEnding());
            $return[]=$tmp;
            $hosting->setNiveauNotification($hosting->getNiveauNotification()+1);
            $em->persist($hosting);
        }

        usort($return, function( $a, $b ) { return $a["date"]->getTimestamp() - $b["date"]->getTimestamp();});
        return $return;
    }

}




