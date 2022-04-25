<?php

namespace AppBundle\Soap\Entity;


/**
 * Cart
 *

 */
class Cart
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var float
     */
    public $totalHt;

    /**
     * @var float
     */
    public $totalTax;

    /**
     * @var float
     */
    public $totalTTC;

    /**
     * @var bool
     */
    public $isPaid;

    /**
     * @var \DateTime
     */
    public $dateIsPaid;



    /**
     * @var \AppBundle\Soap\Entity\AccountBalanceLine
     */
    public $accountBalanceLine;


    /**
     *  @var \AppBundle\Soap\Entity\CartLine
     */
    public $cartLines;

    /**
     *  @var \AppBundle\Soap\Entity\PotentialPayer
     */
    public $potentialPayer;

    /**
     *  @var \AppBundle\Soap\Entity\CGU
     */
    public $cgus;

    /**
     * Cart constructor.
     * @param int $id
     * @param $totalHt
     * @param $totalTax
     * @param $isPaid
     * @param $dateIsPaid
     * @param $accountBalanceLine
     * @param $cartLines
     * @param $cgus
     */
    public function __construct($id, $totalHt, $totalTax, $isPaid, $dateIsPaid, $accountBalanceLine, $cartLines,$potentialPayer,$cgus)
    {
        $this->id = $id;
        $this->totalHt = $totalHt;
        $this->totalTax = $totalTax;
        $this->totalTTC = $totalHt+$totalTax;
        $this->isPaid = $isPaid;
        $this->dateIsPaid = $dateIsPaid;
        $this->accountBalanceLine = $accountBalanceLine;
        $this->cartLines = $cartLines;
        $this->potentialPayer= $potentialPayer;
        $this->cgus = $cgus;
    }


}
