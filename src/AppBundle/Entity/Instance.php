<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Instance
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\InstanceRepository")
 */
class Instance
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
     * @ORM\Column(name="CatalogName", type="string", length=255)
     */
    private $catalogName;

    /**
     * @var string
     *
     * @ORM\Column(name="console", type="string", length=255)
     */
    private $console;

    /**
     * @var integer
     *
     * @ORM\Column(name="dataDiskAdditionalSize", type="integer")
     */
    private $dataDiskAdditionalSize;

    /**
     * @var string
     *
     * @ORM\Column(name="dataDiskTotalSize", type="string", length=255)
     */
    private $dataDiskTotalSize;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEnd", type="datetime",nullable=true)
     */
    private $dateEnd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEndCommitment", type="datetime",nullable=true)
     */
    private $dateEndCommitment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateStart", type="datetime",nullable=true)
     */
    private $dateStart;

    /**
     * @var string
     *
     * @ORM\Column(name="ftpServer", type="string", length=255)
     */
    private $ftpServer;

    /**
     * @var string
     *
     * @ORM\Column(name="gitServer", type="string", length=255)
     */
    private $gitServer;

    /**
     * @var integer
     *
     * @ORM\Column(name="idGandi", type="integer", nullable=true)
     */
    private $idGandi;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="needUpgrade", type="boolean")
     */
    private $needUpgrade;

    /**
     * @var string
     *
     * @ORM\Column(name="userFtp", type="string", length=255)
     */
    private $userFtp;


    /**
     * @ORM\OneToMany(targetEntity="Vhosts", mappedBy="instance", cascade={"remove", "persist"})
     */
    private $vhosts;

    /**
     * @ORM\ManyToOne(targetEntity="DataCenter", inversedBy="instances", cascade={"detach"})
     * @ORM\JoinColumn(name="datacenter_id", referencedColumnName="id")
     */
    private $dataCenter;
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="instances", cascade={"detach"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Product", cascade={"detach"})
     * @ORM\JoinColumn(name="product_renew_id", referencedColumnName="id")
     */
    private $productRenew;

    /**
     * @ORM\ManyToOne(targetEntity="Product", cascade={"detach"})
     * @ORM\JoinColumn(name="product_part_hdd_id", referencedColumnName="id")
     */
    private $productPartHdd;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="instances", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    /**
     * @ORM\ManyToOne(targetEntity="SizeInstance", inversedBy="instances", cascade={"detach"})
     * @ORM\JoinColumn(name="size_instance__id", referencedColumnName="id")
     */
    private $sizeInstance;

    /**
     * @ORM\ManyToOne(targetEntity="SnapshotProfileInstance", inversedBy="instances", cascade={"detach"})
     * @ORM\JoinColumn(name="snapshop_profile_instance_id", referencedColumnName="id")
     */
    private $snapshopProfileInstance;
    
    /**
     * @ORM\ManyToOne(targetEntity="TypeInstance", inversedBy="instances", cascade={"detach"})
     * @ORM\JoinColumn(name="type_instance_id", referencedColumnName="id")
     */
    private $typeInstance;

    /**
     * @var integer
     *
     * @ORM\Column(name="niveauNotification", type="integer")
     */
    private $niveauNotification;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;


    /**
     * @ORM\ManyToOne(targetEntity="NumberVhostsInstance", inversedBy="instances", cascade={"detach"})
     * @ORM\JoinColumn(name="numberMaxVhosts_id", referencedColumnName="id")
     */
    private $numberMaxVhosts;

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

    /**
     * @var float
     * @ORM\Column(name="freeDisk", type="float",nullable=true)
     */
    private $freeDisk;
    /**
     * @var float
     * @ORM\Column(name="usedDisk", type="float",nullable=true)
     */
    private $usedDisk;
    /**
     * @var int
     * @ORM\Column(name="nbvhosts", type="integer")
     */
    private $nbVhosts;
    /**
     * @var int
     * @ORM\Column(name="nbemptyherbermutu", type="integer")
     */
    private $nbEmptyHerberMutu;

    /**
     * @var int
     * @ORM\Column(name="totalherbermutu", type="integer")
     */
    private $totalHerberMutu;

    /**
     * @var boolean
     * @ORM\Column(name="ismutu", type="boolean")
     */
    private $isMutu;

    /**
     * @var boolean
     * @ORM\Column(name="gestionConsole", type="boolean")
     */
    private $gestionConsole ;


    /**
     * @var boolean
     * @ORM\Column(name="etatConsole", type="boolean")
     */
    private $etatConsole;


    /**
     * @var integer
     * @ORM\Column(name="dateactivationconsole", type="datetime")
     */
    private $dateActivationConsole;


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
     * Set catalogName
     *
     * @param string $catalogName
     * @return Instance
     */
    public function setCatalogName($catalogName)
    {
        $this->catalogName = $catalogName;

        return $this;
    }

    /**
     * Get catalogName
     *
     * @return string 
     */
    public function getCatalogName()
    {
        return $this->catalogName;
    }

    /**
     * Set console
     *
     * @param string $console
     * @return Instance
     */
    public function setConsole($console)
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Get console
     *
     * @return string 
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Set dataDiskAdditionalSize
     *
     * @param integer $dataDiskAdditionalSize
     * @return Instance
     */
    public function setDataDiskAdditionalSize($dataDiskAdditionalSize)
    {
        $this->dataDiskAdditionalSize = $dataDiskAdditionalSize;

        return $this;
    }

    /**
     * Get dataDiskAdditionalSize
     *
     * @return integer 
     */
    public function getDataDiskAdditionalSize()
    {
        return $this->dataDiskAdditionalSize;
    }

    /**
     * Set dataDiskTotalSize
     *
     * @param string $dataDiskTotalSize
     * @return Instance
     */
    public function setDataDiskTotalSize($dataDiskTotalSize)
    {
        $this->dataDiskTotalSize = $dataDiskTotalSize;

        return $this;
    }

    /**
     * Get dataDiskTotalSize
     *
     * @return string 
     */
    public function getDataDiskTotalSize(){
        return $this->dataDiskTotalSize;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     * @return Instance
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime 
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set dateEndCommitment
     *
     * @param \DateTime $dateEndCommitment
     * @return Instance
     */
    public function setDateEndCommitment($dateEndCommitment)
    {
        $this->dateEndCommitment = $dateEndCommitment;

        return $this;
    }

    /**
     * Get dateEndCommitment
     *
     * @return \DateTime 
     */
    public function getDateEndCommitment()
    {
        return $this->dateEndCommitment;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     * @return Instance
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime 
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set ftpServer
     *
     * @param string $ftpServer
     * @return Instance
     */
    public function setFtpServer($ftpServer)
    {
        $this->ftpServer = $ftpServer;

        return $this;
    }

    /**
     * Get ftpServer
     *
     * @return string 
     */
    public function getFtpServer()
    {
        return $this->ftpServer;
    }

    /**
     * Set gitServer
     *
     * @param string $gitServer
     * @return Instance
     */
    public function setGitServer($gitServer)
    {
        $this->gitServer = $gitServer;

        return $this;
    }

    /**
     * Get gitServer
     *
     * @return string 
     */
    public function getGitServer()
    {
        return $this->gitServer;
    }



    /**
     * Set name
     *
     * @param string $name
     * @return Instance
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
     * Set needUpgrade
     *
     * @param boolean $needUpgrade
     * @return Instance
     */
    public function setNeedUpgrade($needUpgrade)
    {
        $this->needUpgrade = $needUpgrade;

        return $this;
    }

    /**
     * Get needUpgrade
     *
     * @return boolean 
     */
    public function getNeedUpgrade()
    {
        return $this->needUpgrade;
    }

    /**
     * Set userFtp
     *
     * @param string $userFtp
     * @return Instance
     */
    public function setUserFtp($userFtp)
    {
        $this->userFtp = $userFtp;

        return $this;
    }

    /**
     * Get userFtp
     *
     * @return string 
     */
    public function getUserFtp()
    {
        return $this->userFtp;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vhosts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->niveauNotification=(int)0;
        $this->nbHerberMutu=(int)0;
        $this->totalHerberMutu=(int)0;
        $this->nbVhosts=(int)0;
        $this->isMutu=false;
        $this->nbEmptyHerberMutu=(int)0;
        $this->gestionConsole=false;
        $this->etatConsole=false;
        $this->dateActivationConsole = new \DateTime();
    


    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return Instance
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
     * @return Instance
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
     * @return Instance
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
     * @return Instance
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
     * Add vhosts
     *
     * @param \AppBundle\Entity\Vhosts $vhosts
     * @return Instance
     */
    public function addVhost(\AppBundle\Entity\Vhosts $vhosts)
    {
        $this->vhosts[] = $vhosts;

        return $this;
    }

    /**
     * Remove vhosts
     *
     * @param \AppBundle\Entity\Vhosts $vhosts
     */
    public function removeVhost(\AppBundle\Entity\Vhosts $vhosts)
    {
        $this->vhosts->removeElement($vhosts);
    }

    /**
     * Get vhosts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVhosts()
    {
        return $this->vhosts;
    }

    /**
     * Set dataCenter
     *
     * @param \AppBundle\Entity\DataCenter $dataCenter
     * @return Instance
     */
    public function setDataCenter(\AppBundle\Entity\DataCenter $dataCenter = null)
    {
        $this->dataCenter = $dataCenter;

        return $this;
    }

    /**
     * Get dataCenter
     *
     * @return \AppBundle\Entity\DataCenter 
     */
    public function getDataCenter()
    {
        return $this->dataCenter;
    }

    /**
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     * @return Instance
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Instance
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
     * Set sizeInstance
     *
     * @param \AppBundle\Entity\SizeInstance $sizeInstance
     * @return Instance
     */
    public function setSizeInstance(\AppBundle\Entity\SizeInstance $sizeInstance = null)
    {
        $this->sizeInstance = $sizeInstance;

        return $this;
    }

    /**
     * Get sizeInstance
     *
     * @return \AppBundle\Entity\SizeInstance 
     */
    public function getSizeInstance()
    {
        return $this->sizeInstance;
    }


    /**
     * Set typeInstance
     *
     * @param \AppBundle\Entity\TypeInstance $typeInstance
     * @return Instance
     */
    public function setTypeInstance(\AppBundle\Entity\TypeInstance $typeInstance = null)
    {
        $this->typeInstance = $typeInstance;

        return $this;
    }

    /**
     * Get typeInstance
     *
     * @return \AppBundle\Entity\TypeInstance 
     */
    public function getTypeInstance()
    {
        return $this->typeInstance;
    }



    /**
     * Set snapshopProfileInstance
     *
     * @param \AppBundle\Entity\SnapshotProfileInstance $snapshopProfileInstance
     * @return Instance
     */
    public function setSnapshopProfileInstance(\AppBundle\Entity\SnapshotProfileInstance $snapshopProfileInstance = null)
    {
        $this->snapshopProfileInstance = $snapshopProfileInstance;

        return $this;
    }

    /**
     * Get snapshopProfileInstance
     *
     * @return \AppBundle\Entity\SnapshotProfileInstance 
     */
    public function getSnapshopProfileInstance()
    {
        return $this->snapshopProfileInstance;
    }

    /**
     * Set idGandi
     *
     * @param integer $idGandi
     * @return Instance
     */
    public function setIdGandi($idGandi)
    {
        $this->idGandi = $idGandi;

        return $this;
    }

    /**
     * Get idGandi
     *
     * @return integer 
     */
    public function getIdGandi()
    {
        return $this->idGandi;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Instance
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set productRenew
     *
     * @param \AppBundle\Entity\Product $productRenew
     * @return Instance
     */
    public function setProductRenew(\AppBundle\Entity\Product $productRenew = null)
    {
        $this->productRenew = $productRenew;

        return $this;
    }

    /**
     * Get productRenew
     *
     * @return \AppBundle\Entity\Product 
     */
    public function getProductRenew()
    {
        return $this->productRenew;
    }

    /**
     * Set productPartHdd
     *
     * @param \AppBundle\Entity\Product $productPartHdd
     * @return Instance
     */
    public function setProductPartHdd(\AppBundle\Entity\Product $productPartHdd = null)
    {
        $this->productPartHdd = $productPartHdd;

        return $this;
    }

    /**
     * Get productPartHdd
     *
     * @return \AppBundle\Entity\Product 
     */
    public function getProductPartHdd()
    {
        return $this->productPartHdd;
    }

    /**
     * Set niveauNotification
     *
     * @param integer $niveauNotification
     * @return Instance
     */
    public function setNiveauNotification($niveauNotification)
    {
        $this->niveauNotification = $niveauNotification;

        return $this;
    }

    /**
     * Get niveauNotification
     *
     * @return integer 
     */
    public function getNiveauNotification()
    {
        return $this->niveauNotification;
    }

    /**
     * Set numberMaxVhosts
     *
     * @param \AppBundle\Entity\NumberVhostsInstance $numberMaxVhosts
     *
     * @return Instance
     */
    public function setNumberMaxVhosts(\AppBundle\Entity\NumberVhostsInstance $numberMaxVhosts = null)
    {
        $this->numberMaxVhosts = $numberMaxVhosts;

        return $this;
    }

    /**
     * Get numberMaxVhosts
     *
     * @return \AppBundle\Entity\NumberVhostsInstance
     */
    public function getNumberMaxVhosts()
    {
        return $this->numberMaxVhosts;
    }

    /**
     * Set freeDisk
     *
     * @param float $freeDisk
     *
     * @return Instance
     */
    public function setFreeDisk($freeDisk)
    {
        $this->freeDisk = $freeDisk;

        return $this;
    }

    /**
     * Get freeDisk
     *
     * @return float
     */
    public function getFreeDisk()
    {
        return $this->freeDisk;
    }

    /**
     * Get freeDiskInGigaBytes
     *
     * @return float
     */
    public function getFreeDiskInGigaBytes()
    {
        return $this->freeDisk/1073741824;
    }

    /**
     * Set usedDisk
     *
     * @param float $usedDisk
     *
     * @return Instance
     */
    public function setUsedDisk($usedDisk)
    {
        $this->usedDisk = $usedDisk;

        return $this;
    }

    /**
     * Get usedDisk
     *
     * @return float
     */
    public function getUsedDisk()
    {
        return $this->usedDisk;
    }
    /**
     * Get usedDiskInGigaBytes
     *
     * @return float
     */
    public function getUsedDiskInGigaBytes()
    {
        return $this->usedDisk/1073741824;
    }

    /**
     * Set nbVhosts
     *
     * @param integer $nbVhosts
     *
     * @return Instance
     */
    public function setNbVhosts($nbVhosts)
    {
        $this->nbVhosts = $nbVhosts;

        return $this;
    }

    /**
     * Get nbVhosts
     *
     * @return integer
     */
    public function getNbVhosts()
    {
        return $this->nbVhosts;
    }

    /**
     * Set nbHerberMutu
     *
     * @param integer $nbHerberMutu
     *
     * @return Instance
     */
    public function setNbHerberMutu($nbHerberMutu)
    {
        $this->nbHerberMutu = $nbHerberMutu;

        return $this;
    }

    /**
     * Get nbHerberMutu
     *
     * @return integer
     */
    public function getNbHerberMutu()
    {
        return $this->nbHerberMutu;
    }

    /**
     * Set nbEmptyHerberMutu
     *
     * @param integer $nbEmptyHerberMutu
     *
     * @return Instance
     */
    public function setNbEmptyHerberMutu($nbEmptyHerberMutu)
    {
        $this->nbEmptyHerberMutu = $nbEmptyHerberMutu;

        return $this;
    }

    /**
     * Get nbEmptyHerberMutu
     *
     * @return integer
     */
    public function getNbEmptyHerberMutu()
    {
        return $this->nbEmptyHerberMutu;
    }

    /**
     * Set totalHerberMutu
     *
     * @param integer $totalHerberMutu
     *
     * @return Instance
     */
    public function setTotalHerberMutu($totalHerberMutu)
    {
        $this->totalHerberMutu = $totalHerberMutu;

        return $this;
    }

    /**
     * Get totalHerberMutu
     *
     * @return integer
     */
    public function getTotalHerberMutu()
    {
        return $this->totalHerberMutu;
    }

    /**
     * Set isMutu
     *
     * @param boolean $isMutu
     *
     * @return Instance
     */
    public function setIsMutu($isMutu)
    {
        $this->isMutu = $isMutu;

        return $this;
    }

    /**
     * Get isMutu
     *
     * @return boolean
     */
    public function getIsMutu()
    {
        return $this->isMutu;
    }




    /**
     * Set gestionConsole
     *
     * @param boolean $gestionConsole
     *
     * @return Instance
     */
    public function setGestionConsole($gestionConsole)
    {
        $this->gestionConsole = $gestionConsole;

        return $this;
    }

    /**
     * Get gestionConsole
     *
     * @return boolean
     */
    public function getGestionConsole()
    {
        return $this->gestionConsole;
    }

    /**
     * Set etatConsole
     *
     * @param boolean $etatConsole
     *
     * @return Instance
     */
    public function setEtatConsole($etatConsole)
    {
        $this->etatConsole = $etatConsole;

        return $this;
    }

    /**
     * Get etatConsole
     *
     * @return boolean
     */
    public function getEtatConsole()
    {
        return $this->etatConsole;
    }

    /**
     * Set dateActivationConsole
     *
     * @param \DateTime $dateActivationConsole
     *
     * @return Instance
     */
    public function setDateActivationConsole($dateActivationConsole)
    {
        $this->dateActivationConsole = $dateActivationConsole;

        return $this;
    }

    /**
     * Get dateActivationConsole
     *
     * @return \DateTime
     */
    public function getDateActivationConsole()
    {
        return $this->dateActivationConsole;
    }
}
