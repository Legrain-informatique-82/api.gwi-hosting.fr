<?php

namespace AppBundle\Soap\Entity;


/**
 * CartLine
 *

 */
class CartLine
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     *
     */
    public $productReference;

    /**
     * @var string
     *
     * */
    public $productName;

    /**
     * @var integer
     *
     */
    public $quantity;

    /**
     * @var float
     *
     */
    public $unitPrice;

    /**
     * @var float
     */
    public $percentTax;

    /**
     * @var float
     *
     */
    public $totalHt;

    /**
     * @var float
     *
     */
    public $totalTax;


    /**
     * @var float
     *
     */
    public $totalTTC;

    /**
     * @Soap\ComplexType("int")
     */
    public $productId;

    /**
     * CartLine constructor.
     * @param int $id
     * @param string $productReference
     * @param string $productName
     * @param int $quantity
     * @param float $unitPrice
     * @param float $percentTax
     * @param float $totalHt
     * @param float $totalTax
     * @param float $totalTTC
     * @param $productId
     */
    public function __construct($id, $productReference, $productName, $quantity, $unitPrice, $percentTax, $totalHt, $totalTax, $productId)
    {
        $this->id = $id;
        $this->productReference = $productReference;
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->percentTax = $percentTax;
        $this->totalHt = $totalHt;
        $this->totalTax = $totalTax;
        $this->totalTTC = $this->totalHt+$this->totalTax;
        $this->productId = $productId;
    }


}
