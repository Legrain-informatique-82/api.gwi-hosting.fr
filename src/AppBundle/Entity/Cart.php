<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CartRepository")
 */
class Cart
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
     * @var boolean
     *
     * @ORM\Column(name="isPaid", type="boolean")
     */
    private $isPaid;

    /**
     * @var \Datetime()
     *
     * @ORM\Column(name="dateIsPaid", type="datetime",nullable=true)
     */
    private $dateIsPaid;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="carts", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;



    /**
     * @ORM\OneToOne(targetEntity="AccountBalanceLine", mappedBy="cart")
     */
    private $accountBalanceLine;

    /**
     * @ORM\OneToMany(targetEntity="CartLine", mappedBy="cart", cascade={"remove", "persist"})
     */
    private $cartLines;

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
     * Set totalHt
     *
     * @param float $totalHt
     * @return Cart
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
     * @return Cart
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
        return $this->totalHt+$this->totalTax;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     * @return Cart
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean 
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cartLines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isPaid=false;
        $this->totalHt=0;
        $this->totalTax=0;
    }

    /**
     * Set dateIsPaid
     *
     * @param \DateTime $dateIsPaid
     * @return Cart
     */
    public function setDateIsPaid($dateIsPaid)
    {
        $this->dateIsPaid = $dateIsPaid;

        return $this;
    }

    /**
     * Get dateIsPaid
     *
     * @return \DateTime 
     */
    public function getDateIsPaid()
    {
        return $this->dateIsPaid;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return Cart
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
     * @return Cart
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
     * @return Cart
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
     * @return Cart
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Cart
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * Add cartLines
     *
     * @param \AppBundle\Entity\CartLine $cartLines
     * @return Cart
     */
    public function addCartLine(\AppBundle\Entity\CartLine $cartLines)
    {
        $this->cartLines[] = $cartLines;
        $cartLines->setCart($this);
        return $this;
    }

    /**
     * Remove cartLines
     *
     * @param \AppBundle\Entity\CartLine $cartLines
     */
    public function removeCartLine(\AppBundle\Entity\CartLine $cartLines)
    {
        $this->cartLines->removeElement($cartLines);
    }

    /**
     * Get cartLines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCartLines()
    {
        return $this->cartLines;
    }

    /**
     * Set accountBalanceLine
     *
     * @param \AppBundle\Entity\AccountBalanceLine $accountBalanceLine
     * @return Cart
     */
    public function setAccountBalanceLine(\AppBundle\Entity\AccountBalanceLine $accountBalanceLine = null)
    {
        $this->accountBalanceLine = $accountBalanceLine;

        return $this;
    }

    /**
     * Get accountBalanceLine
     *
     * @return \AppBundle\Entity\AccountBalanceLine 
     */
    public function getAccountBalanceLine()
    {
        return $this->accountBalanceLine;
    }
}
