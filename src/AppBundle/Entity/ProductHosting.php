<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductHosting
 *
 * @ORM\Table(name="product_hosting")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductHostingRepository")
 */
class ProductHosting
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="priceHt", type="float")
     */
    private $priceHt;



    /**
     * @var bool
     *
     * @ORM\Column(name="bookableByCustomer", type="boolean")
     */
    private $bookableByCustomer;

    /**
     * @var bool
     *
     * @ORM\Column(name="renewByCustomer", type="boolean")
     */
    private $renewByCustomer;

    /**
     * @var string
     *
     * @ORM\Column(name="detail", type="string", length=255,nullable=true)
     */
    private $detail;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="features", type="json_array", length=700,nullable=true)
     */
    private $features;


    /**
     * @ORM\ManyToOne(targetEntity="Product",  cascade={"detach"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Instance", cascade={"detach"})
     * @ORM\JoinColumn(name="instance_id", referencedColumnName="id")
     */
    private $instance;

    /**
     * @ORM\ManyToOne(targetEntity="TvaRate", cascade={"detach"})
     * @ORM\JoinColumn(name="tva_rate_id", referencedColumnName="id")
     */
    protected $tvaRate;

    /**
     * @ORM\ManyToOne(targetEntity="Agency", cascade={"detach"})
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;

    /**
     * @ORM\OneToMany(targetEntity="Hosting", mappedBy="productHosting", cascade={"remove", "persist"})
     */
    protected $hosts;


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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set priceHt
     *
     * @param float $priceHt
     *
     * @return ProductHosting
     */
    public function setPriceHt($priceHt)
    {
        $this->priceHt = $priceHt;

        return $this;
    }

    /**
     * Get priceHt
     *
     * @return float
     */
    public function getPriceHt()
    {
        return $this->priceHt;
    }

   
    /**
     * Set bookableByCustomer
     *
     * @param boolean $bookableByCustomer
     *
     * @return ProductHosting
     */
    public function setBookableByCustomer($bookableByCustomer)
    {
        $this->bookableByCustomer = $bookableByCustomer;

        return $this;
    }

    /**
     * Get bookableByCustomer
     *
     * @return bool
     */
    public function getBookableByCustomer()
    {
        return $this->bookableByCustomer;
    }

    /**
     * Set renewByCustomer
     *
     * @param boolean $renewByCustomer
     *
     * @return ProductHosting
     */
    public function setRenewByCustomer($renewByCustomer)
    {
        $this->renewByCustomer = $renewByCustomer;

        return $this;
    }

    /**
     * Get renewByCustomer
     *
     * @return bool
     */
    public function getRenewByCustomer()
    {
        return $this->renewByCustomer;
    }

    /**
     * Set detail
     *
     * @param string $detail
     *
     * @return ProductHosting
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get detail
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hosts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set features
     *
     * @param array $features
     *
     * @return ProductHosting
     */
    public function setFeatures($features)
    {
        $this->features = $features;

        return $this;
    }

    /**
     * Get features
     *
     * @return array
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     *
     * @return ProductHosting
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
     *
     * @return ProductHosting
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
     *
     * @return ProductHosting
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
     *
     * @return ProductHosting
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
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     *
     * @return ProductHosting
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
     * Set instance
     *
     * @param \AppBundle\Entity\Instance $instance
     *
     * @return ProductHosting
     */
    public function setInstance(\AppBundle\Entity\Instance $instance = null)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * Get instance
     *
     * @return \AppBundle\Entity\Instance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Set tvaRate
     *
     * @param \AppBundle\Entity\TvaRate $tvaRate
     *
     * @return ProductHosting
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
     * Set agency
     *
     * @param \AppBundle\Entity\Agency $agency
     *
     * @return ProductHosting
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
     * Add host
     *
     * @param \AppBundle\Entity\Hosting $host
     *
     * @return ProductHosting
     */
    public function addHost(\AppBundle\Entity\Hosting $host)
    {
        $this->hosts[] = $host;

        return $this;
    }

    /**
     * Remove host
     *
     * @param \AppBundle\Entity\Hosting $host
     */
    public function removeHost(\AppBundle\Entity\Hosting $host)
    {
        $this->hosts->removeElement($host);
    }

    /**
     * Get hosts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProductHosting
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
}
