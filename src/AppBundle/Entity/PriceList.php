<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PriceList
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PriceListRepository")
 */
class PriceList
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
     * @var boolean
     *
     * @ORM\Column(name="isDefault", type="boolean")
     */
    private $isDefault;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isApplicationDefault", type="boolean")
     */
    private $isApplicationDefault;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="priceList", cascade={"detach", "persist"})
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="PriceListLine", mappedBy="priceList", cascade={"remove", "persist"})
     */
    protected $priceListLines;


    /**
     * @ORM\ManyToOne(targetEntity="Agency", inversedBy="priceLists", cascade={"detach"})
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    private $parentAgency;

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
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return PriceList
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean 
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setIsApplicationDefault(false);
    }

    /**
     * Add users
     *
     * @param \AppBundle\Entity\User $users
     * @return PriceList
     */
    public function addUser(\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \AppBundle\Entity\User $users
     */
    public function removeUser(\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
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
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return PriceList
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
     * @return PriceList
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
     * @return PriceList
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
     * @return PriceList
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
     * Set name
     *
     * @param string $name
     * @return PriceList
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
     * Add priceListLines
     *
     * @param \AppBundle\Entity\PriceListLine $priceListLines
     * @return PriceList
     */
    public function addPriceListLine(\AppBundle\Entity\PriceListLine $priceListLines)
    {
        $this->priceListLines[] = $priceListLines;

        return $this;
    }

    /**
     * Remove priceListLines
     *
     * @param \AppBundle\Entity\PriceListLine $priceListLines
     */
    public function removePriceListLine(\AppBundle\Entity\PriceListLine $priceListLines)
    {
        $this->priceListLines->removeElement($priceListLines);
    }

    /**
     * Get priceListLines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPriceListLines()
    {
        return $this->priceListLines;
    }

    /**
     * Set parentAgency
     *
     * @param \AppBundle\Entity\Agency $parentAgency
     * @return PriceList
     */
    public function setParentAgency(\AppBundle\Entity\Agency $parentAgency = null)
    {
        $this->parentAgency = $parentAgency;

        return $this;
    }

    /**
     * Get parentAgency
     *
     * @return \AppBundle\Entity\Agency 
     */
    public function getParentAgency()
    {
        return $this->parentAgency;
    }

    /**
     * Set isApplicationDefault
     *
     * @param boolean $isApplicationDefault
     * @return PriceList
     */
    public function setIsApplicationDefault($isApplicationDefault)
    {
        $this->isApplicationDefault = $isApplicationDefault;

        return $this;
    }

    /**
     * Get isApplicationDefault
     *
     * @return boolean 
     */
    public function getIsApplicationDefault()
    {
        return $this->isApplicationDefault;
    }
}
