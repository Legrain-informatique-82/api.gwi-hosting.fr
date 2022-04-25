<?php

namespace AppBundle\Soap\Entity;

/**
 * ProductHosting
 */
class ProductHosting
{
    /**
     * @var int
     *
     */
    public $id;

    /**
     * @var string
     */
    public $name;
    /**
     * @var float
     *
     */
    public $priceHt;



    /**
     * @var bool
     **/
    public $bookableByCustomer;

    /**
     * @var bool
     **/
    public $renewByCustomer;

    /**
     * @var string
     **/
    public $detail;

    /**
     * @var array
     **/
    public $features;


    /**
     *  @var \AppBundle\Soap\Entity\Product
     */
    public $product;

    /**
     * @var \AppBundle\Soap\Entity\Instance
     */
    public $instance;


    /**
     * @var \AppBundle\Soap\Entity\Agency
     */
    public $agency;

    /**
     * ProductHosting constructor.
     * @param int $id
     * @param string $name
     * @param float $priceHt
     * @param bool $bookableByCustomer
     * @param bool $renewByCustomer
     * @param string $detail
     * @param array $features
     * @param $product
     * @param Instance $instance
     * @param $agency
     */
    public function __construct($id, $name,$priceHt, $bookableByCustomer, $renewByCustomer, $detail, array $features, $product, Instance $instance, $agency)
    {
        $this->id = $id;
        $this->name = $name;
        $this->priceHt = $priceHt;
        $this->bookableByCustomer = $bookableByCustomer;
        $this->renewByCustomer = $renewByCustomer;
        $this->detail = $detail;
        $this->features = $features;
        $this->product = $product;
        $this->instance = $instance;
        $this->agency = $agency;
    }


}
