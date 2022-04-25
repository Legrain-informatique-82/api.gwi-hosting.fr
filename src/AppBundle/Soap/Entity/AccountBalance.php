<?php

namespace AppBundle\Soap\Entity;


/**
 * AccountBalance
 */
class AccountBalance
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var float
     */
    public $amount;


    /**
     * @var \AppBundle\Soap\Entity\AccountBalanceLine
     */
    public $lines;




    /**
     * Constructor
     */
    public function __construct($id,$amount,$lines){
        $this->id=$id;
        $this->amount=$amount;
        $this->lines=$lines;
    }

}
