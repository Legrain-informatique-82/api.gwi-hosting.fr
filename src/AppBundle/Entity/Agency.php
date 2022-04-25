<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Agency
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\AgencyRepository")
 */
class Agency
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
     * @ORM\Column(name="name", type="string", length=500)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="siret", type="string", length=14,nullable=true)
     */
    private $siret;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=255,nullable=true)
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=255,nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="address3", type="string", length=255,nullable=true)
     */
    private $address3;


    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="agency", cascade={"remove", "persist"})
     */
    protected $users;
    /**
     * @ORM\OneToMany(targetEntity="NextPaiement", mappedBy="agency", cascade={"remove", "persist"})
     */
    protected $nextPayements;


    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=15,nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=500,nullable=true)
     */
    private $website;




    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="agencies", cascade={"detach"})
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="ZipCode", inversedBy="agencies", cascade={"detach"})
     * @ORM\JoinColumn(name="zipCode_id", referencedColumnName="id")
     */
    protected $zipCode;


    /**
     * @ORM\OneToMany(targetEntity="ProductAgency", mappedBy="agency", cascade={"remove", "persist"})
     */
    protected $codesFacturationsAgences;


    /**
     * @ORM\OneToMany(targetEntity="AccountBalance", mappedBy="agency", cascade={"remove", "persist"})
     */
    protected $accountBalances;

    /**
     * @ORM\OneToMany(targetEntity="PriceList", mappedBy="parentAgency", cascade={"remove", "persist"})
     */
    private $priceLists;

    /**
     * @var boolean
     *
     * @ORM\Column(name="facturationBylegrain", type="boolean")
     */
    private $facturationBylegrain;

    /**
     * @var boolean
     *
     * @ORM\Column(name="facturationBylegrainIsDefined", type="boolean")
     */
    private $facturationBylegrainIsDefined;



    /**
     * @var string
     *
     * @ORM\Column(name="infosCheque", type="string", length=1000,nullable=true)
     */
    private $infosCheque;
    /**
     * @var string
     *
     * @ORM\Column(name="infosVirement", type="string", length=1000,nullable=true)
     */
    private $infosVirement;

    /**
     * @var string
     *
     * @ORM\Column(name="urlApp", type="string",nullable=true)
     */
    private $urlApp;


    /**
     * @var string
     *
     * @ORM\Column(name="stripeKey", type="string",nullable=true)
     */
    private $stripeKey;

    /**
     * @var string
     *
     * @ORM\Column(name="useTva", type="boolean",options={"default" = 1})
     */
    private $useTva;

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
     * @var string
     *
     * @ORM\Column(name="descriptionhtml", type="text",nullable=true)
     */
    private $descriptionHtml;
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
     * Set name
     *
     * @param string $name
     * @return Agency
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
     * Set siret
     *
     * @param string $siret
     * @return Agency
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get siret
     *
     * @return string 
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * Set address1
     *
     * @param string $address1
     * @return Agency
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string 
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return Agency
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string 
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set address3
     *
     * @param string $address3
     * @return Agency
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    /**
     * Get address3
     *
     * @return string 
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Agency
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Agency
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Agency
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }



    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     * @return Agency
     */
    public function setCity(\AppBundle\Entity\City $city = null)
    {
        $this->city = $city;


        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return Agency
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
     * @return Agency
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
     * @return Agency
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
     * @return Agency
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
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->useTva=true;
        $this->facturationBylegrainIsDefined=false;
        $this->facturationBylegrain=false;
    }

    /**
     * Add users
     *
     * @param \AppBundle\Entity\User $user
     * @return Agency
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \AppBundle\Entity\User $user
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set zipCode
     *
     * @param \AppBundle\Entity\ZipCode $zipCode
     * @return Agency
     */
    public function setZipCode(\AppBundle\Entity\ZipCode $zipCode = null)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return \AppBundle\Entity\ZipCode 
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set accountBalance
     *
     * @param \AppBundle\Entity\AccountBalance $accountBalance
     * @return Agency
     */
    public function setAccountBalance(\AppBundle\Entity\AccountBalance $accountBalance = null)
    {
        $this->accountBalance = $accountBalance;

        return $this;
    }

    /**
     * Get accountBalance
     *
     * @return \AppBundle\Entity\AccountBalance 
     */
    public function getAccountBalance()
    {
        return $this->accountBalance;
    }

    /**
     * Add accountBalances
     *
     * @param \AppBundle\Entity\AccountBalance $accountBalances
     * @return Agency
     */
    public function addAccountBalance(\AppBundle\Entity\AccountBalance $accountBalances)
    {
        $this->accountBalances[] = $accountBalances;

        return $this;
    }

    /**
     * Remove accountBalances
     *
     * @param \AppBundle\Entity\AccountBalance $accountBalances
     */
    public function removeAccountBalance(\AppBundle\Entity\AccountBalance $accountBalances)
    {
        $this->accountBalances->removeElement($accountBalances);
    }

    /**
     * Get accountBalances
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccountBalances()
    {
        return $this->accountBalances;
    }

    /**
     * Add codesFacturationsAgences
     *
     * @param \AppBundle\Entity\ProductAgency $codesFacturationsAgences
     * @return Agency
     */
    public function addCodesFacturationsAgence(\AppBundle\Entity\ProductAgency $codesFacturationsAgences)
    {
        $this->codesFacturationsAgences[] = $codesFacturationsAgences;

        return $this;
    }

    /**
     * Remove codesFacturationsAgences
     *
     * @param \AppBundle\Entity\ProductAgency $codesFacturationsAgences
     */
    public function removeCodesFacturationsAgence(\AppBundle\Entity\ProductAgency $codesFacturationsAgences)
    {
        $this->codesFacturationsAgences->removeElement($codesFacturationsAgences);
    }

    /**
     * Get codesFacturationsAgences
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodesFacturationsAgences()
    {
        return $this->codesFacturationsAgences;
    }

    /**
     * Add priceLists
     *
     * @param \AppBundle\Entity\PriceList $priceLists
     * @return Agency
     */
    public function addPriceList(\AppBundle\Entity\PriceList $priceLists)
    {
        $this->priceLists[] = $priceLists;

        return $this;
    }

    /**
     * Remove priceLists
     *
     * @param \AppBundle\Entity\PriceList $priceLists
     */
    public function removePriceList(\AppBundle\Entity\PriceList $priceLists)
    {
        $this->priceLists->removeElement($priceLists);
    }

    /**
     * Get priceLists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPriceLists()
    {
        return $this->priceLists;
    }





    /**
     * Set facturationBylegrain
     *
     * @param boolean $facturationBylegrain
     * @return Agency
     */
    public function setFacturationBylegrain($facturationBylegrain)
    {
        $this->facturationBylegrain = (bool)$facturationBylegrain;

        return $this;
    }

    /**
     * Get facturationBylegrain
     *
     * @return boolean 
     */
    public function getFacturationBylegrain()
    {
        return $this->facturationBylegrain;
    }

    /**
     * Set infosCheque
     *
     * @param string $infosCheque
     * @return Agency
     */
    public function setInfosCheque($infosCheque)
    {
        $this->infosCheque = $infosCheque;

        return $this;
    }

    /**
     * Get infosCheque
     *
     * @return string 
     */
    public function getInfosCheque()
    {
        return $this->infosCheque;
    }

    /**
     * Set infosVirement
     *
     * @param string $infosVirement
     * @return Agency
     */
    public function setInfosVirement($infosVirement)
    {
        $this->infosVirement = $infosVirement;

        return $this;
    }

    /**
     * Get infosVirement
     *
     * @return string 
     */
    public function getInfosVirement()
    {
        return $this->infosVirement;
    }

    /**
     * Set urlApp
     *
     * @param string $urlApp
     * @return Agency
     */
    public function setUrlApp($urlApp)
    {
        $this->urlApp = $urlApp;

        return $this;
    }

    /**
     * Get urlApp
     *
     * @return string 
     */
    public function getUrlApp()
    {
        return $this->urlApp;
    }

    /**
     * Add nextPayements
     *
     * @param \AppBundle\Entity\NextPaiement $nextPayements
     * @return Agency
     */
    public function addNextPayement(\AppBundle\Entity\NextPaiement $nextPayements)
    {
        $this->nextPayements[] = $nextPayements;

        return $this;
    }

    /**
     * Remove nextPayements
     *
     * @param \AppBundle\Entity\NextPaiement $nextPayements
     */
    public function removeNextPayement(\AppBundle\Entity\NextPaiement $nextPayements)
    {
        $this->nextPayements->removeElement($nextPayements);
    }

    /**
     * Get nextPayements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNextPayements()
    {
        return $this->nextPayements;
    }

    /**
     * Set stripeKey
     *
     * @param string $stripeKey
     *
     * @return Agency
     */
    public function setStripeKey($stripeKey)
    {
        $this->stripeKey = $stripeKey;

        return $this;
    }

    /**
     * Get stripeKey
     *
     * @return string
     */
    public function getStripeKey()
    {
        return $this->stripeKey;
    }

    /**
     * Set useTva
     *
     * @param boolean $useTva
     *
     * @return Agency
     */
    public function setUseTva($useTva)
    {
        $this->useTva = $useTva;

        return $this;
    }

    /**
     * Get useTva
     *
     * @return boolean
     */
    public function getUseTva()
    {
        return $this->useTva;
    }

    /**
     * Set facturationBylegrainIsDefined
     *
     * @param boolean $facturationBylegrainIsDefined
     *
     * @return Agency
     */
    public function setFacturationBylegrainIsDefined($facturationBylegrainIsDefined)
    {
        $this->facturationBylegrainIsDefined = $facturationBylegrainIsDefined;

        return $this;
    }

    /**
     * Get facturationBylegrainIsDefined
     *
     * @return boolean
     */
    public function getFacturationBylegrainIsDefined()
    {
        return $this->facturationBylegrainIsDefined;
    }

    /**
     * Set descriptionHtml
     *
     * @param string $descriptionHtml
     *
     * @return Agency
     */
    public function setDescriptionHtml($descriptionHtml)
    {
        $this->descriptionHtml = $descriptionHtml;

        return $this;
    }

    /**
     * Get descriptionHtml
     *
     * @return string
     */
    public function getDescriptionHtml()
    {
        return $this->descriptionHtml;
    }
}
