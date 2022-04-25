<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NextPaiement
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\NextPaiementRepository")
 */
class NextPaiement
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="unitPriceHt", type="float")
     */
    private $unitPriceHt;

    /**
     * @var float
     *
     * @ORM\Column(name="percentTax", type="float")
     */
    private $percentTax;

    /**
     * @var float
     *
     * @ORM\Column(name="totalHT", type="float")
     */
    private $totalHT;

    /**
     * @var float
     *
     * @ORM\Column(name="totalTax", type="float")
     */
    private $totalTax;

    /**
     * @var string
     *
     * @ORM\Column(name="features", type="string", length=1000)
     */
    private $features;


    /**
     * @ORM\ManyToOne(targetEntity="Agency", inversedBy="nextPayements", cascade={"detach"})
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;


    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="nextPayements", cascade={"detach"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", cascade={"detach"})
     * @ORM\JoinColumn(name="client_cart_id", referencedColumnName="id")
     */
    protected $clientCart;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", cascade={"detach"})
     * @ORM\JoinColumn(name="agency_cart_id", referencedColumnName="id")
     */
    protected $agencyCart;


    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"detach"})
     * @ORM\JoinColumn(name="user_final_id", referencedColumnName="id")
     */
    protected $userFinal;

    /**
     * @var integer
     *
     * @ORM\Column(name="isArchived", type="boolean")
     */
    private $isArchived;


    /**
     * @var integer
     *
     * @ORM\Column(name="inCart", type="boolean")
     */
    private $inCart;
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
     * @ORM\Column(name="whenupdate", type="datetime")
     */
    private $whenUpdate;

    public function __construct(){
        $this->inCart=false;
    }

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
     * Set date
     *
     * @param \DateTime $date
     * @return NextPaiement
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;

    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set reference
     *
     * @param string $reference
     * @return NextPaiement
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return NextPaiement
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return NextPaiement
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
     * Set unitPriceHt
     *
     * @param float $unitPriceHt
     * @return NextPaiement
     */
    public function setUnitPriceHt($unitPriceHt)
    {
        $this->unitPriceHt = $unitPriceHt;

        return $this;
    }

    /**
     * Get unitPriceHt
     *
     * @return float
     */
    public function getUnitPriceHt()
    {
        return $this->unitPriceHt;
    }

    /**
     * Set percenttax
     *
     * @param float $percenttax
     * @return NextPaiement
     */
    public function setPercentTax($percentTax)
    {
        $this->percentTax = $percentTax;

        return $this;
    }

    /**
     * Get percenttax
     *
     * @return float
     */
    public function getPercentTax()
    {
        return $this->percentTax;
    }

    /**
     * Set totalHT
     *
     * @param float $totalHT
     * @return NextPaiement
     */
    public function setTotalHT($totalHT)
    {
        $this->totalHT = $totalHT;

        return $this;
    }

    /**
     * Get totalHT
     *
     * @return float
     */
    public function getTotalHT()
    {
        return $this->totalHT;
    }

    /**
     * Set totalTAX
     *
     * @param float $totalTAX
     * @return NextPaiement
     */
    public function setTotalTax($totalTax)
    {
        $this->totalTax = $totalTax;

        return $this;
    }

    /**
     * Get totalTAX
     *
     * @return float
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * Set features
     *
     * @param string $features
     * @return NextPaiement
     */
    public function setFeatures($features)
    {
        $this->features = $features;

        return $this;
    }

    /**
     * Get features
     *
     * @return string
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return NextPaiement
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
     * @return NextPaiement
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
     * @return NextPaiement
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
     * @return NextPaiement
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
     * Set agency
     *
     * @param \AppBundle\Entity\Agency $agency
     * @return NextPaiement
     */
    public function setAgency(\AppBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \AppBundle\Entity\Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     * @return NextPaiement
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
     * Set isArchived
     *
     * @param boolean $isArchived
     * @return NextPaiement
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    /**
     * Get isArchived
     *
     * @return boolean
     */
    public function getIsArchived()
    {
        return $this->isArchived;
    }

    /**
     * Set clientCart
     *
     * @param \AppBundle\Entity\Cart $clientCart
     * @return NextPaiement
     */
    public function setClientCart(\AppBundle\Entity\Cart $clientCart = null)
    {
        $this->clientCart = $clientCart;

        return $this;
    }

    /**
     * Get clientCart
     *
     * @return \AppBundle\Entity\Cart
     */
    public function getClientCart()
    {
        return $this->clientCart;
    }

    /**
     * Set agencyCart
     *
     * @param \AppBundle\Entity\Cart $agencyCart
     * @return NextPaiement
     */
    public function setAgencyCart(\AppBundle\Entity\Cart $agencyCart = null)
    {
        $this->agencyCart = $agencyCart;

        return $this;
    }

    /**
     * Get agencyCart
     *
     * @return \AppBundle\Entity\Cart
     */
    public function getAgencyCart()
    {
        return $this->agencyCart;
    }

    /**
     * Set userFinal
     *
     * @param \AppBundle\Entity\User $userFinal
     * @return NextPaiement
     */
    public function setUserFinal(\AppBundle\Entity\User $userFinal = null)
    {
        $this->userFinal = $userFinal;

        return $this;
    }

    /**
     * Get userFinal
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserFinal()
    {
        return $this->userFinal;
    }

    /**
     * Set inCart
     *
     * @param boolean $inCart
     * @return NextPaiement
     */
    public function setInCart($inCart)
    {
        $this->inCart = $inCart;

        return $this;
    }

    /**
     * Get inCart
     *
     * @return boolean
     */
    public function getInCart()
    {
        return $this->inCart;
    }
}
