<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ndd
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\NddRepository")
 */
class Ndd
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="idGandi", type="string", length=255)
     */
    private $idGandi;

    /**
     * @var string
     *
     * @ORM\Column(name="idOvh", type="string", length=255)
     */
    private $idOvh;

    /**
     * @var string
     *
     * @ORM\Column(name="expirationDate", type="datetime",nullable=true)
     */
    private $expirationDate;



    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ndds", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;


    /**
      * @ORM\OneToOne(targetEntity="EmailGandiPackPro", mappedBy="ndd", cascade={"remove", "persist"})
     */
    protected $emailGandiPackPro;

    /**
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="ndds", cascade={"detach"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;


    /**
     * @ORM\ManyToOne(targetEntity="Product", cascade={"detach"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;


    /**
     * @var boolean
     *
     * @ORM\Column(name="services", type="string")
     */
    private $services;

    /**
     * @ORM\OneToMany(targetEntity="SubDomain", mappedBy="ndd", cascade={"remove", "persist"})
     */
    protected $subDomains;


    /**
     * @ORM\OneToMany(targetEntity="ListBugBugzilla", mappedBy="ndd", cascade={"remove", "persist"})
     */
    protected $listBugs;


    /**
     * @ORM\OneToMany(targetEntity="Vhosts", mappedBy="ndd", cascade={"remove", "persist"})
     */
    protected $vhosts;


    /**
     * @var integer
     *
     * @ORM\Column(name="niveauNotification", type="integer")
     */
    private $niveauNotification;

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
     * @return Ndd
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
     * Set idGandi
     *
     * @param string $idGandi
     * @return Ndd
     */
    public function setIdGandi($idGandi)
    {
        $this->idGandi = $idGandi;

        return $this;
    }

    /**
     * Get idGandi
     *
     * @return string 
     */
    public function getIdGandi()
    {
        return $this->idGandi;
    }

    /**
     * Set idOvh
     *
     * @param string $idOvh
     * @return Ndd
     */
    public function setIdOvh($idOvh)
    {
        $this->idOvh = $idOvh;

        return $this;
    }

    /**
     * Get idOvh
     *
     * @return string 
     */
    public function getIdOvh()
    {
        return $this->idOvh;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Ndd
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
     * Constructor
     */
    public function __construct()
    {
        $this->idOvh='';
        $this->idGandi='';
        $this->services='';
        $this->niveauNotification=(int)0;

    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return Ndd
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
     * @return Ndd
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
     * @return Ndd
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
     * @return Ndd
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
     * Set expirationDate
     *
     * @param \DateTime $expirationDate
     * @return Ndd
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate
     *
     * @return \DateTime 
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     * @return Ndd
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
     * Set services
     *
     * @param array $services
     * @return Ndd
     */
    public function setServices($services)
    {
        $this->services = str_replace('gandi','',json_encode($services));

        return $this;
    }

    /**
     * Get services
     *
     * @return array
     */
    public function getServices()
    {
        return json_decode($this->services);
    }



    /**
     * Set contact
     *
     * @param \AppBundle\Entity\Contact $contact
     * @return Ndd
     */
    public function setContact(\AppBundle\Entity\Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \AppBundle\Entity\Contact 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Add subDomains
     *
     * @param \AppBundle\Entity\SubDomain $subDomains
     * @return Ndd
     */
    public function addSubDomain(\AppBundle\Entity\SubDomain $subDomains)
    {
        $this->subDomains[] = $subDomains;

        return $this;
    }

    /**
     * Remove subDomains
     *
     * @param \AppBundle\Entity\SubDomain $subDomains
     */
    public function removeSubDomain(\AppBundle\Entity\SubDomain $subDomains)
    {
        $this->subDomains->removeElement($subDomains);
    }

    /**
     * Get subDomains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubDomains()
    {
        return $this->subDomains;
    }

    /**
     * Add listBugs
     *
     * @param \AppBundle\Entity\ListBugBugzilla $listBugs
     * @return Ndd
     */
    public function addListBug(\AppBundle\Entity\ListBugBugzilla $listBugs)
    {
        $this->listBugs[] = $listBugs;

        return $this;
    }

    /**
     * Remove listBugs
     *
     * @param \AppBundle\Entity\ListBugBugzilla $listBugs
     */
    public function removeListBug(\AppBundle\Entity\ListBugBugzilla $listBugs)
    {
        $this->listBugs->removeElement($listBugs);
    }

    /**
     * Get listBugs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getListBugs()
    {
        return $this->listBugs;
    }

    /**
     * Add vhosts
     *
     * @param \AppBundle\Entity\Vhosts $vhosts
     * @return Ndd
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
     * Set emailGandiPackPro
     *
     * @param \AppBundle\Entity\EmailGandiPackPro $emailGandiPackPro
     * @return Ndd
     */
    public function setEmailGandiPackPro(\AppBundle\Entity\EmailGandiPackPro $emailGandiPackPro = null)
    {
        $this->emailGandiPackPro = $emailGandiPackPro;

        return $this;
    }

    /**
     * Get emailGandiPackPro
     *
     * @return \AppBundle\Entity\EmailGandiPackPro 
     */
    public function getEmailGandiPackPro()
    {
        return $this->emailGandiPackPro;
    }

    /**
     * Set niveauNotification
     *
     * @param integer $niveauNotification
     * @return Ndd
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
}
