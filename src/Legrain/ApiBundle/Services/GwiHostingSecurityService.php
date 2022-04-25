<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 15/02/16
 * Time: 12:27
 */

namespace Legrain\ApiBundle\Services;



use AppBundle\Soap\Entity\City;
use Doctrine\ORM\EntityManager;
use AppBundle\Soap\Entity\Agency;
use AppBundle\Soap\Entity\TiersPourTVA;
use AppBundle\Soap\Entity\ZipCode;
use AppBundle\Soap\Security\UserSecurity;
use Monolog\Logger;

class GwiHostingSecurityService{

    protected $em;
    protected $logger;
    public function __construct(EntityManager $em,Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

   
    /**
     * @param string $email
     * @param string $password
     * @return \AppBundle\Soap\Security\UserSecurity
     * @throws \SoapFault
     */
    public function login($email,$password){
        $str = 'aiPaishaiy9Eidohm7oobeec7Ci2izaebaegho8moukaewaetahzau2ha8Iedahthai1liNai2aM3olielie8uVae9hee1Iev4c';
      /*
        $this->logger->info('email : '.$email);
        $this->logger->info('str : aiPaishaiy9Eidohm7oobeec7Ci2izaebaegho8moukaewaetahzau2ha8Iedahthai1liNai2aM3olielie8uVae9hee1Iev4c');
        $this->logger->info('mdp : '.$password);
       */

        if($password!=$str)
            throw new \SoapFault('server','Mot de passe système incorrect');
        // récupèration de l'objet user


        try {
            $userRepository = $this->em->getRepository('AppBundle:User');

            $user = $userRepository->findOneByEmail($email);
        }catch(\Exception $e){
            throw new \SoapFault('e',$e->getMessage());
        }
        if(null == $user ) throw new \SoapFault('BadCredentialsException','L\'utilisateur n\'est pas présent dans notre base de donnée.');

        $zc = $user->getZipCode();
        $c = $user->getCity();
        $ag=$user->getAgency();

        $agCity = $ag->getCity()==null?null:new City($ag->getCity()->getId(),$ag->getCity()->getName(),$ag->getCity()->getCodeInsee());
        $agZipCode= $ag->getZipCode()==null?null:new ZipCode($ag->getZipCode()->getId(),$ag->getZipCode()->getName());
        //$id,$name,$firstname,$email,$password,$registrationDate,$address1,$address2,$address3,City $city,ZipCode $zipCode,$phone,$active,$agency
        $roles=array();
        foreach($user->getRoles() as $r) {
            $roles[]=$r->getName();
        }

        $soapUser = new UserSecurity(
            $user->getId(),
            $user->getName(),
            $user->getFirstname(),
            $user->getEmail(),
            $user->getPassword(),

            $user->getRegistrationDate()->getTimestamp(),
            $user->getAddress1(),
            $user->getAddress2(),
            $user->getAddress3(),
            $c==null?null:new City($c->getId(),$c->getName(),$c->getCodeInsee()),
            $zc==null?null:new ZipCode($zc->getId(),$zc->getName()),
            $user->getPhone(),
            $user->getCellPhone(),
            $user->getWorkPhone(),
            $user->getActive(),
            new Agency($ag->getId(),$ag->getName(),$ag->getSiret(),$ag->getAddress1(),$ag->getAddress2(),$ag->getAddress3(),$agCity,$agZipCode,$ag->getPhone(),$ag->getEmail(),$ag->getWebsite(),( $ag->getFacturationBylegrainIsDefined()?$ag->getFacturationBylegrain():null),$ag->getInfosCheque(),$ag->getInfosVirement(),   $ag->getUseTva(), $ag->getDescriptionHtml()),
            $roles,
            $user->getCodeClient(),
            $user->getCompanyName(),
            $user->getCompanyName(),
            $user->getTiersPourTVA()==null?null:new TiersPourTVA($user->getTiersPourTVA()->getId(),$user->getTiersPourTVA()->getName()),
            $ag->getUrlApp()

        );
        return $soapUser;


    }
}