<?php

namespace AppBundle\Soap\Entity;


/**
 * Ndd
 */
class NextPaiement
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var \DateTime
     *
     */
    public $date;

    /**
     * @var string
     *
     */
    public $reference;

    /**
     * @var string
     *
     */
    public $name;

    /**
     * @var integer
     *
     */
    public $quantity;

    /**
     * @var float
     *
     */
    public $unitPriceHt;

    /**
     * @var float
     *
     */
    public $percentTax;

    /**
     * @var float
     *
     */
    public $totalHT;

    /**
     * @var float
     *
     */
    public $totalTax;

    /**
     * @var string
     *
     */
    public $features;


    /**
     * @var \AppBundle\Soap\Entity\Agency
     *
     */
    public $agency;


    /**
     * @var \AppBundle\Soap\Entity\Product
     *
     */
    public $product;



    /**
     * @var \AppBundle\Soap\Entity\User
     *
     */
    public $userFinal;

    /**
     * NextPaiement constructor.
     * @param int $id
     * @param string $date
     * @param string $reference
     * @param string $name
     * @param int $quantity
     * @param float $unitPriceHt
     * @param float $percentTax
     * @param float $totalHT
     * @param float $totalTax
     * @param string $features
     * @param $agency
     * @param $product
     * @param $userFinal
     */
    public function __construct($id=null, $date=null, $reference=null, $name=null, $quantity=null, $unitPriceHt=null, $percentTax=null, $totalHT=null, $totalTax=null, $features=null, $agency=null, $product=null, $userFinal=null)
    {
        $this->id = $id;
        $this->date = $date;
        $this->reference = $reference;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->unitPriceHt = $unitPriceHt;
        $this->percentTax = $percentTax;
        $this->totalHT = $totalHT;
        $this->totalTax = $totalTax;
        $this->features = $features;
        $this->agency = $agency;
        $this->product = $product;
        $this->userFinal = $userFinal;
    }


}
