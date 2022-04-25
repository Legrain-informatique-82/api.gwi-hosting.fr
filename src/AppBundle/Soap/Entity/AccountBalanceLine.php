<?php

namespace AppBundle\Soap\Entity;



/**
 * AccountBalanceLine
 */
class AccountBalanceLine
{

 /**
 * @var integer
 */
    public $id;

    /**
     * @var \DateTime
     *     */
    public $date;

    /**
     * @var int
     */
    public $idTransaction;

    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $mouvement;

    /**
     * @var float
     */
    public $balance;

    /**
     * AccountBalanceLine constructor.
     * @param int $id
     * @param $date
     * @param $idTransaction
     * @param $description
     * @param $mouvement
     * @param $balance
     */
    public function __construct($id, $date, $idTransaction, $description, $mouvement, $balance)
    {
        $this->id = $id;
        $this->date = $date;
        $this->idTransaction = $idTransaction;
        $this->description = $description;
        $this->mouvement = $mouvement;
        $this->balance = $balance;
    }

}
