<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vhosts
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\VhostsRepository")
 */
class Vhosts
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
     * @ORM\Column(name="dateCrea", type="datetime")
     */
    private $dateCrea;

    /**
     * @var integer
     *
     * @ORM\Column(name="idGandi", type="integer")
     */
    private $idGandi;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255)
     */
    private $state;



    /**
     * @ORM\ManyToOne(targetEntity="Instance", inversedBy="vhosts", cascade={"detach"})
     * @ORM\JoinColumn(name="instance_id", referencedColumnName="id")
     */
    private $instance;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="vhosts", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;



    /**
     * @ORM\ManyToOne(targetEntity="Ndd", inversedBy="vhosts", cascade={"detach"})
     * @ORM\JoinColumn(name="ndd_id", referencedColumnName="id")
     */
    private $ndd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="inMaintenance", type="boolean")
     */
    private $inMaintenance;


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
     * Set dateCrea
     *
     * @param \DateTime $dateCrea
     * @return Vhosts
     */
    public function setDateCrea($dateCrea)
    {
        $this->dateCrea = $dateCrea;

        return $this;
    }

    /**
     * Get dateCrea
     *
     * @return \DateTime 
     */
    public function getDateCrea()
    {
        return $this->dateCrea;
    }

    /**
     * Set idGandi
     *
     * @param integer $idGandi
     * @return Vhosts
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
     * Set name
     *
     * @param string $name
     * @return Vhosts
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
     * Set state
     *
     * @param string $state
     * @return Vhosts
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return Vhosts
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
     * @return Vhosts
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
     * @return Vhosts
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
     * @return Vhosts
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
     * Set instance
     *
     * @param \AppBundle\Entity\Instance $instance
     * @return Vhosts
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Vhosts
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
     * Set ndd
     *
     * @param \AppBundle\Entity\Ndd $ndd
     * @return Vhosts
     */
    public function setNdd(\AppBundle\Entity\Ndd $ndd = null)
    {
        $this->ndd = $ndd;

        return $this;
    }

    /**
     * Get ndd
     *
     * @return \AppBundle\Entity\Ndd 
     */
    public function getNdd()
    {
        return $this->ndd;
    }

    /**
     * Set inMaintenance
     *
     * @param boolean $inMaintenance
     * @return Vhosts
     */
    public function setInMaintenance($inMaintenance)
    {
        $this->inMaintenance = $inMaintenance;

        return $this;
    }

    /**
     * Get inMaintenance
     *
     * @return boolean 
     */
    public function getInMaintenance()
    {
        return $this->inMaintenance;
    }
}
