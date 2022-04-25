<?php

namespace AppBundle\Soap\Entity;


/**
 * WebRedir
 */
class NumberVhostsInstance
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
     * @var int
     *
     */
    public $value;

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
     * @param int $value
     * @param float $priceHtPerMonth
     * @param int $idProduct
     */
    public function __construct($id, $name, $value,$priceHtPerMonth,$idProduct)
    {
        $this->id = $id;
        $this->idProduct = $idProduct;
        $this->name = $name;
        $this->value = $value;
        $this->priceHtPerMonth = $priceHtPerMonth;
    }


}
