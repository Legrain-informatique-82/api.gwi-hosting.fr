<?php

namespace AppBundle\Soap\Entity;


/**
 * Instance
 *
 */
class Instance
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     *
     */
    public $catalogName;

    /**
     * @var string
     *
     */
    public $console;

    /**
     * @var int
     *
     */
    public $dataDiskAdditionalSize;

    /**
     * @var int
     *
     */
    public $quantityPartDataDiskAdditionalSize;

    /**
     * @var string
     *
     */
    public $dataDiskTotalSize;

    /**
     * @var \DateTime
     *
     */
    public $dateEnd;

    /**
     * @var \DateTime
     *
     */
    public $dateEndCommitment;

    /**
     * @var \DateTime
     *
     */
    public $dateStart;

    /**
     * @var string
     *
     */
    public $ftpServer;

    /**
     * @var string
     *
     */
    public $gitServer;


    /**
     * @var string
     *
     */
    public $name;

    /**
     * @var bool
     *
     */
    public $needUpgrade;

    /**
     * @var string
     *
     */
    public $userFtp;


    /**
     *  @var \AppBundle\Soap\Entity\Vhosts[]
     *
     */
    public $vhosts;

    /**
     *  @var \AppBundle\Soap\Entity\DataCenter
     *
     */
    public $dataCenter;

    /**
     *  @var \AppBundle\Soap\Entity\Product
     *
     */
    public $product;

    /**
     *  @var \AppBundle\Soap\Entity\Product
     *
     */
    public $productRenew;

    /**
     *  @var \AppBundle\Soap\Entity\Product
     *
     */
    public $productPartHdd;


    /**
     * @var string
     *
     */
    public $sizeInstance;

    /**
     *  @var \AppBundle\Soap\Entity\SnapshotProfile
     *
     */
    public $snapshotProfileInstance;
    /**
     * @var string
     *
     */
    public $typeInstance;



    /**
     * @var bool
     *
     */
    public $active;

    /**
     * @var mixed
     */
    public $options;

    /**
     *  @var \AppBundle\Soap\Entity\User
     *
     */
    public $user;

    /**
     * @var int
     */
    public $nbreVhostsMax;



    /**
     * @var float
     */
    public $freeDiskInBytes;
    /**
     * @var float
     */
    public $usedDiskInBytes;


    /**
     * @var boolean
     */
    public $gestionConsole;

    /**
     * @var boolean
     */
    public $etatConsole;
   


    /**
     * Instance constructor.
     * @param int $id
     * @param string $catalogName
     * @param string $console
     * @param string $dataDiskAdditionalSize
     * @param string $quantityPartDataDiskAdditionalSize
     * @param string $dataDiskTotalSize
     * @param string $dateEnd
     * @param string $dateEndCommitment
     * @param string $dateStart
     * @param string $ftpServer
     * @param string $gitServer
     * @param string $name
     * @param string $needUpgrade
     * @param string $userFtp
     * @param $vhosts
     * @param $dataCenter
     * @param $product
     * @param string $sizeInstance
     * @param string $snapshotProfileInstance
     * @param string $typeInstance
     * @param string $active
     * @param $productRenew
     * @param $productPartHdd
     * @param $options
     * @param $user
     * @param $nbreVhostsMax
     * @param $freeDiskInBytes
     * @param $usedDiskInBytes
     * @param $gestionConsole
     */
    public function __construct($id, $catalogName, $console, $dataDiskAdditionalSize,$quantityPartDataDiskAdditionalSize, $dataDiskTotalSize, $dateEnd, $dateEndCommitment, $dateStart, $ftpServer, $gitServer, $name, $needUpgrade, $userFtp, $vhosts, $dataCenter, $product, $sizeInstance, $snapshotProfileInstance, $typeInstance, $active,$productRenew,$productPartHdd,$options=null,$user= null,$nbreVhostsMax=null,$freeDiskInBytes=null,$usedDiskInBytes=null,$gestionConsole=null,$etatConsole=null)
    {
        $this->id = $id;
        $this->catalogName = $catalogName;
        $this->console = $console;
        $this->quantityPartDataDiskAdditionalSize = $quantityPartDataDiskAdditionalSize;
        $this->dataDiskAdditionalSize = $dataDiskAdditionalSize;
        $this->dataDiskTotalSize = $dataDiskTotalSize;
        $this->dateEnd = $dateEnd;
        $this->dateEndCommitment = $dateEndCommitment;
        $this->dateStart = $dateStart;
        $this->ftpServer = $ftpServer;
        $this->gitServer = $gitServer;
        $this->name = $name;
        $this->needUpgrade = $needUpgrade;
        $this->userFtp = $userFtp;
        $this->vhosts = $vhosts;
        $this->dataCenter = $dataCenter;
        $this->product = $product;
        $this->sizeInstance = $sizeInstance;
        $this->snapshotProfileInstance = $snapshotProfileInstance;
        $this->typeInstance = $typeInstance;
        $this->active = $active;
        $this->productRenew = $productRenew;
        $this->productPartHdd=$productPartHdd;
        $this->options=$options;
        $this->user = $user;
        $this->nbreVhostsMax = $nbreVhostsMax;
        $this->freeDiskInBytes=$freeDiskInBytes;
        $this->usedDiskInBytes = $usedDiskInBytes;
        $this->gestionConsole = $gestionConsole;
        $this->etatConsole=$etatConsole;

    }


}
