<?php

namespace AppBundle\Soap\Entity;


/**
 * PriceListLine
 *
 */
class PriceListLine
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var integer
     */
    public $idProduct;

    /**
     * @var string
     *
     */
    public $productName;



    /**
     * @var float
     *
     */
    public $price;



    /**
     * @var float
     *
     */
    public $minPrice;

    /**
     * PriceListLine constructor.
     * @param int $id
     * @param int $idProduct
     * @param string $productName
     * @param float $price
     * @param float $minPrice
     */
    public function __construct($id,$idProduct, $productName, $price, $minPrice)
    {
        $this->id = $id;
        $this->idProduct = $idProduct;
        $this->productName = $productName;
        $this->price = $price;
        $this->minPrice = $minPrice;
    }


}
