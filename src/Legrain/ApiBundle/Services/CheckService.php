<?php

# Acme/ApiBundle/Services/CheckService.php
namespace Legrain\ApiBundle\Services;


use AppBundle\AppBundle;
use AppBundle\Soap\Type\User;
use Doctrine\ORM\EntityManager;

class CheckService
{


    protected $em;
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

/**
* Check soap service, display name when called
* @param string $name
* @return mixed
*/
public function check($name)
{
return 'Hellosss '.$name;
}

    /**
     * Méthode test
     * @param int $a
     * @return mixed
     */
    public function test($a){
        return $a;
    }

    /**
     * @param string $a
     * @return \Legrain\ApiBundle\Services\Tag
     */
    public function test3($a){
            return new Tag('a','aa','aaa');
    }
}

class Tag{
    /**
     * @var string $a
     */
    public $a;
    /**
     * @var string $aa
     */
    public $aa;
    /**
     * @var string $aaa
     */
    public $aaa;

    /**
     * Tag constructor.
     * @param string $a
     * @param string $aa
     * @param string $aaa
     */
    public function __construct($a, $aa, $aaa)
    {
        $this->a = $a;
        $this->aa = $aa;
        $this->aaa = $aaa;
    }


}