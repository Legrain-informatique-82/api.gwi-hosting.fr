<?php

namespace AppBundle\Soap\Entity;


/**
 * SnapshopProfile
 */
class SnapshotProfile
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     *
     */
    public $name;


    /**
     * @var float
     *
     */
    public $priceHtPerMonth;

    /**
     * @var int
     */
    public $idProduct;
    /**
     * SizeInstance constructor.
     * @param int $id
     * @param string $name
     * @param float $priceHtPerMonth
     * @param int $idProduct
     */
    public function __construct($id, $name,$priceHtPerMonth,$idProduct)
    {
        $this->id = $id;
        $this->idProduct = $idProduct;
        $this->name = $name;
        $this->priceHtPerMonth = $priceHtPerMonth;
    }


}
