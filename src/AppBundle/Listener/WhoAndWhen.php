<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 05/06/15
 * Time: 11:12
 */

namespace AppBundle\Listener;

use AppBundle\Entity\AccountBalanceLine;
use AppBundle\Entity\CartLine;
use Doctrine\ORM\Event\LifecycleEventArgs;
//use AppBundle\Entity;

class WhoAndWhen {

    public function postPersist(LifecycleEventArgs $args){
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
    }
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();


        $entity->setWhenCreate(new \DateTime());
        $entity->setWhenUpdate(new \DateTime());
        $entity->setWhoCreate(0);
        $entity->setWhoUpdate(0);

        // Si l'entité est une instance de la classe AccountBalanceLine, on doit réaffecter le nouveau "amount" à AccountBalance
        if ($entity instanceof AccountBalanceLine) {
            $header = $entity->getHeader();
            // On calcule la nouvelle balance ( ancien solde + mouvement)
            $entity->setBalance( $header->getAmount() + $entity->getMouvement());
            // On fixe le nouveau solde
            $header->setAmount($entity->getBalance());
        }

        // Si l'entité est une instance de la classe cartLine, on doit réaffecter les nouveaux totaux HT et TVA à Cart
        if ($entity instanceof CartLine) {
            $header = $entity->getCart();
            $header->setTotalHt( $header->getTotalHt() + $entity->getTotalHt());
            $header->setTotalTax( $header->getTotalTax() + $entity->getTotalTax());
        }

    }
    public function preRemove(LifecycleEventArgs $args){
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        if ($entity instanceof CartLine) {

            $header = $entity->getCart();
            $header->setTotalHt( $header->getTotalHt() - $entity->getTotalHt());
            $header->setTotalTax( $header->getTotalTax() - $entity->getTotalTax());
        }
    }
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entity->setWhenUpdate(new \DateTime());
    }

}
