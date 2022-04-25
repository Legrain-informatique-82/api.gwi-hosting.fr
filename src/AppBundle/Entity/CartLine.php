<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CartLine
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CartLineRepository")
 */
class CartLine
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
     * @var string
     *
     * @ORM\Column(name="productReference", type="string", length=255)
     */
    private $productReference;

    /**
     * @var string
     *
     * @ORM\Column(name="productName", type="text")
     */
    private $productName;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="unitPrice", type="float", scale=2)
     */
    private $unitPrice;

    /**
     * @var float
     * @ORM\Column(name="percentTax", type="float", scale=2)
     */
    private $percentTax;

    /**
     * @var float
     *
     * @ORM\Column(name="totalHt", type="float", scale=2)
     */
    private $totalHt;

    /**
     * @var float
     *
     * @ORM\Column(name="totalTax", type="float", scale=2)
     */
    private $totalTax;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="cartLines", cascade={"detach"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    protected $cart;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $utilisateurPourLequelEstLeProduit;


    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="cartLines", cascade={"detach"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;


    /**
     * @ORM\Column(name="options", type="text")

     */
    private $options;
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
     * Set productReference
     *
     * @param string $productReference
     * @return CartLine
     */
    public function setProductReference($productReference)
    {
        $this->productReference = $productReference;

        return $this;
    }

    /**
     * Get productReference
     *
     * @return string 
     */
    public function getProductReference()
    {
        return $this->productReference;
    }

    /**
     * Set productName
     *
     * @param string $productName
     * @return CartLine
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName
     *
     * @return string 
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return CartLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set unitPrice
     *
     * @param float $unitPrice
     * @return CartLine
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * Get unitPrice
     *
     * @return float 
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * Set totalHt
     *
     * @param float $totalHt
     * @return CartLine
     */
    public function setTotalHt($totalHt)
    {
        $this->totalHt = $totalHt;

        return $this;
    }

    /**
     * Get totalHt
     *
     * @return float 
     */
    public function getTotalHt()
    {
        return $this->totalHt;
    }

    /**
     * Set totalTax
     *
     * @param float $totalTax
     * @return CartLine
     */
    public function setTotalTax($totalTax)
    {
        $this->totalTax = $totalTax;

        return $this;
    }

    /**
     * Get totalTax
     *
     * @return float 
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * Get total TTC
     * @return float
     */
    public function getTotalTTC(){
        return $this->totalTax+$this->totalHt;
    }

    /**
     * Set percentTax
     *
     * @param float $percentTax
     * @return CartLine
     */
    public function setPercentTax($percentTax)
    {
        $this->percentTax = $percentTax;

        return $this;
    }

    /**
     * Get percentTax
     *
     * @return float 
     */
    public function getPercentTax()
    {
        return $this->percentTax;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return CartLine
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
     * @return CartLine
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
     * @return CartLine
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
     * @return CartLine
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
     * Set cart
     *
     * @param \AppBundle\Entity\Cart $cart
     * @return CartLine
     */
    public function setCart(\AppBundle\Entity\Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \AppBundle\Entity\Cart 
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     * @return CartLine
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
     * Set options
     *
     * @param string $options
     * @return CartLine
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return string 
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set utilisateurPourLequelEstLeProduit
     *
     * @param \AppBundle\Entity\User $utilisateurPourLequelEstLeProduit
     * @return CartLine
     */
    public function setUtilisateurPourLequelEstLeProduit(\AppBundle\Entity\User $utilisateurPourLequelEstLeProduit = null)
    {
        $this->utilisateurPourLequelEstLeProduit = $utilisateurPourLequelEstLeProduit;

        return $this;
    }

    /**
     * Get utilisateurPourLequelEstLeProduit
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUtilisateurPourLequelEstLeProduit()
    {
        return $this->utilisateurPourLequelEstLeProduit;
    }
}
