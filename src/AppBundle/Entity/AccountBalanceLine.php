<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\DateTime;

/**
 * AccountBalanceLine
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\AccountBalanceLineRepository")
 */
class AccountBalanceLine
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
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="mouvement", type="float", scale=2)
     */
    private $mouvement;

    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float", scale=2)
     */
    private $balance;

    /**
     * @ORM\ManyToOne(targetEntity="AccountBalance", inversedBy="lines", cascade={"detach"})
     * @ORM\JoinColumn(name="account_balance_id", referencedColumnName="id")
     */
    protected $header;


    /**
     * @ORM\OneToOne(targetEntity="Cart", inversedBy="accountBalanceLine")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    private $cart;

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
     * @var integer
     *
     * @ORM\Column(name="idpaypal", type="string", nullable=true)
     */
    private $idPaypal;


    /**
     * @ORM\OneToOne(targetEntity="Transaction", inversedBy="accountBalanceLine",cascade={"persist", "remove"},)
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     **/
    private $transaction;

    /**
     * AccountBalanceLine constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();

        $this->transaction = new Transaction();

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
     * @return AccountBalanceLine
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
     * Set description
     *
     * @param string $description
     * @return AccountBalanceLine
     */
    public function setDescription($description)
    {
         $this->description = $description;
        return $this;
    }
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set mouvement
     *
     * @param float $mouvement
     * @return AccountBalanceLine
     */
    public function setMouvement($mouvement)
    {
        $this->mouvement = $mouvement;

        return $this;
    }

    /**
     * Get mouvement
     *
     * @return float
     */
    public function getMouvement()
    {
        return $this->mouvement;
    }

    /**
     * Set balance
     *
     * @param float $balance
     * @return AccountBalanceLine
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get balance
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return AccountBalanceLine
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
     * @return AccountBalanceLine
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
     * @return AccountBalanceLine
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
     * @return AccountBalanceLine
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
     * Set header
     *
     * @param \AppBundle\Entity\AccountBalance $header
     * @return AccountBalanceLine
     */
    public function setHeader(\AppBundle\Entity\AccountBalance $header = null)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return \AppBundle\Entity\AccountBalance
     */
    public function getHeader()
    {
        return $this->header;
    }


    /**
     * Set idTransaction
     *
     * @param integer $idTransaction
     * @return AccountBalanceLine
     */
    public function setIdTransaction($idTransaction)
    {
        $this->idTransaction = $idTransaction;

        return $this;
    }




    /**
     * Set idPaypal
     *
     * @param string $idPaypal
     * @return AccountBalanceLine
     */
    public function setIdPaypal($idPaypal)
    {
        $this->idPaypal = $idPaypal;

        return $this;
    }

    /**
     * Get idPaypal
     *
     * @return string 
     */
    public function getIdPaypal()
    {
        return $this->idPaypal;
    }

    /**
     * Set cart
     *
     * @param \AppBundle\Entity\Cart $cart
     * @return AccountBalanceLine
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
     * Set transaction
     *
     * @param \AppBundle\Entity\Transaction $transaction
     * @return AccountBalanceLine
     */
    public function setTransaction(\AppBundle\Entity\Transaction $transaction = null)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction
     *
     * @return \AppBundle\Entity\Transaction 
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
