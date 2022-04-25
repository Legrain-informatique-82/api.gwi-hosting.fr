<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * VhostsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VhostsRepository extends EntityRepository{

    public function loadOtherVhostsForInstance(Instance $instance,$vhostsExluded){
        $query =  $this->createQueryBuilder('v')
            ->where('v.instance = :instance')
            ->andWhere('v.name NOT IN (:vhostsExcluded)')
            ->setParameters(array('instance'=>$instance,'vhostsExcluded'=>$vhostsExluded  ))

            ->getQuery();
        return $query->getResult();


    }

}