<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PriceListLine
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PriceListLineRepository")
 */
class PriceListLine
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;


    /**
     * @var float
     *
     * @ORM\Column(name="minPrice",nullable=true, type="float")
     */
    private $minPrice;


    /**
     * @ORM\ManyToOne(targetEntity="TvaRate", cascade={"detach"})
     * @ORM\JoinColumn(name="tva_rate_id", referencedColumnName="id")
     */
    protected $tvaRate;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="priceListLines",cascade={"detach"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="PriceList", inversedBy="priceListLines", cascade={"detach"})
     * @ORM\JoinColumn(name="price_list_id", referencedColumnName="id")
     */
    private $priceList;


    /**
     * @var integer
     *
     * @ORM\Column(name="whocreate", type="integer")
     */
    private $whoCreate;
    /**
     * @var integer
     *
     * @ORM\Column(name="whencreate", type="datetime")
     */
    private $whenCreate;
    /**
     * @var integer
     *
     * @ORM\Column(name="whoupdate", type="integer")
     */
    private $whoUpdate;
    /**
     * @var integer
     *
     * @ORM\Column(name="whenupdate", type="datetime")
     */
    private $whenUpdate;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return PriceListLine
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set minPrice
     *
     * @param float $minPrice
     * @return PriceListLine
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    /**
     * Get minPrice
     *
     * @return float 
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * Set tvaRate
     *
     * @param \AppBundle\Entity\TvaRate $tvaRate
     * @return PriceListLine
     */
    public function setTvaRate(\AppBundle\Entity\TvaRate $tvaRate = null)
    {
        $this->tvaRate = $tvaRate;

        return $this;
    }

    /**
     * Get tvaRate
     *
     * @return \AppBundle\Entity\TvaRate 
     */
    public function getTvaRate()
    {
        return $this->tvaRate;
    }

    /**
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     * @return PriceListLine
     */
    public function setProduct(\AppBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \AppBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return PriceListLine
     */
    public function setWhoCreate($whoCreate)
    {
        $this->whoCreate = $whoCreate;

        return $this;
    }

    /**
     * Get whoCreate
     *
     * @return integer 
     */
    public function getWhoCreate()
    {
        return $this->whoCreate;
    }

    /**
     * Set whenCreate
     *
     * @param \DateTime $whenCreate
     * @return PriceListLine
     */
    public function setWhenCreate($whenCreate)
    {
        $this->whenCreate = $whenCreate;

        return $this;
    }

    /**
     * Get whenCreate
     *
     * @return \DateTime 
     */
    public function getWhenCreate()
    {
        return $this->whenCreate;
    }

    /**
     * Set whoUpdate
     *
     * @param integer $whoUpdate
     * @return PriceListLine
     */
    public function setWhoUpdate($whoUpdate)
    {
        $this->whoUpdate = $whoUpdate;

        return $this;
    }

    /**
     * Get whoUpdate
     *
     * @return integer 
     */
    public function getWhoUpdate()
    {
        return $this->whoUpdate;
    }

    /**
     * Set whenUpdate
     *
     * @param \DateTime $whenUpdate
     * @return PriceListLine
     */
    public function setWhenUpdate($whenUpdate)
    {
        $this->whenUpdate = $whenUpdate;

        return $this;
    }

    /**
     * Get whenUpdate
     *
     * @return \DateTime 
     */
    public function getWhenUpdate()
    {
        return $this->whenUpdate;
    }

    /**
     * Set priceList
     *
     * @param \AppBundle\Entity\PriceList $priceList
     * @return PriceListLine
     */
    public function setPriceList(\AppBundle\Entity\PriceList $priceList = null)
    {
        $this->priceList = $priceList;

        return $this;
    }

    /**
     * Get priceList
     *
     * @return \AppBundle\Entity\PriceList 
     */
    public function getPriceList()
    {
        return $this->priceList;
    }
}
