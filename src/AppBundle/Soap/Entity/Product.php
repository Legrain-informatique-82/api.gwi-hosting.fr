<?php

namespace AppBundle\Soap\Entity;


/**
 * Product
 *
 */
class Product
{
    /**
     * @var integer
     *
     */
    public $id;

    /**
     * @var string
     *
     */
    public $name;

    /**
     * @var string
     *
     */
    public $reference;

    /**
     * @var string
     *
     */
    public $codeLgr;

    /**
     * @var string
     *
     */
    public $shortDescription;

    /**
     * @var string
     *
     */
    public $longDescription;

    /**
     * @var string ( 0 si illimité)
     *
     */
    public $minPeriod;




    /**
     * @var string
     *     */
    public $codeFacturationAgence;
    /**
     * @var float
     */
    public $priceHT;

    /**
     * @var float
     */
    public $minPriceHT;


    /**
     * @var float
     */
    public $percentTax;

    /**
     * @var \AppBundle\Soap\Entity\Category[]
     */
    public $categories;


    /**
     * @var \AppBundle\Soap\Entity\ProductSimplified[]
     */
    public $dependancies;


    /**
     * @var \AppBundle\Soap\Entity\ProductSimplified[]
     */
    public $produitsComposes;

    /**
     * @var \AppBundle\Soap\Entity\Category[]
     */
    public $dependanciesPerCategories;

    /**
     * @var \AppBundle\Soap\Entity\Feature[]
     */
    public $features;

    /**
     * @var \AppBundle\Soap\Entity\CGU[]
     */
    public $cgus;

    /**
     * @var bool
     */
    public $active;

  




    /**
     * Product constructor.
     * @param int $id
     * @param string $name
     * @param string $reference
     * @param string $codeLgr
     * @param string $shortDescription
     * @param string $longDescription
     * @param int $minPeriod
     * @param $codeFacturationAgence
     * @param $priceHT
     * @param $minPriceHT
     * @param $percentTax
     * @param $dependancies
     * @param $produitsComposes
     * @param $dependanciesPerCategories
     * @param $features
     * @param $active
     *
     */
    public function __construct($id, $name, $reference, $codeLgr, $shortDescription, $longDescription, $minPeriod, $codeFacturationAgence, $priceHT, $minPriceHT, $percentTax,$categories=null,$dependancies=null,$produitsComposes=null,$dependanciesPerCategories=null,$features=null,$active=null,$cgus=null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->reference = $reference;
        $this->codeLgr = $codeLgr;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
        $this->minPeriod = $minPeriod;
        $this->codeFacturationAgence = $codeFacturationAgence;
        $this->priceHT = $priceHT;
        $this->minPriceHT = $minPriceHT;
        $this->percentTax = $percentTax;
        $this->categories=$categories;
        $this->dependancies=$dependancies;
        $this->produitsComposes=$produitsComposes;
        $this->dependanciesPerCategories = $dependanciesPerCategories;
        $this->features=$features;
        $this->active = $active;
        $this->cgus = $cgus;
    }


}
