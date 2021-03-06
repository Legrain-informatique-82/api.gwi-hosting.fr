<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * NddRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NddRepository extends EntityRepository
{


    public function findByAgency(Agency $agency){
        $query = $this->createQueryBuilder('n')
            ->select('n')
            ->innerJoin('n.user','u')
            ->where('u.agency = :agency')
            ->setParameter('agency',$agency)
            ->getQuery()
            ->getResult();

        return $query;
    }
}
