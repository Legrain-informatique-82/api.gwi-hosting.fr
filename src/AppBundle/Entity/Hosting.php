<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hosting
 *
 * @ORM\Table(name="hosting")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HostingRepository")
 */
class Hosting
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
     * @var string
     *
     * @ORM\Column(name="vhost", type="string", length=255, unique=true,nullable=true)
     */
    private $vhost;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEnding", type="datetime")
     */
    private $dateEnding;


    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="ProductHosting", inversedBy="hosts", cascade={"detach"})
     * @ORM\JoinColumn(name="product_hosting_id", referencedColumnName="id")
     */
    private $productHosting;


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
     * @ORM\Column(name="niveauNotification", type="integer")
     */
    private $niveauNotification;

    /**
     * Hosting constructor.
     */
    public function __construct(){
        $this->niveauNotification = (int)0;
    }

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
     * Set vhost
     *
     * @param string $vhost
     *
     * @return Hosting
     */
    public function setVhost($vhost)
    {
        $this->vhost = $vhost;

        return $this;
    }

    /**
     * Get vhost
     *
     * @return string
     */
    public function getVhost()
    {
        return $this->vhost;
    }

    /**
     * Set dateEnding
     *
     * @param \DateTime $dateEnding
     *
     * @return Hosting
     */
    public function setDateEnding($dateEnding)
    {
        $this->dateEnding = $dateEnding;

        return $this;
    }

    /**
     * Get dateEnding
     *
     * @return \DateTime
     */
    public function getDateEnding()
    {
        return $this->dateEnding;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     *
     * @return Hosting
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
     * @return Hosting
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
     * @return Hosting
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
     * @return Hosting
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
     *
     * @return Hosting
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
     * Set productHosting
     *
     * @param \AppBundle\Entity\ProductHosting $productHosting
     *
     * @return Hosting
     */
    public function setProductHosting(\AppBundle\Entity\ProductHosting $productHosting = null)
    {
        $this->productHosting = $productHosting;

        return $this;
    }

    /**
     * Get productHosting
     *
     * @return \AppBundle\Entity\ProductHosting
     */
    public function getProductHosting()
    {
        return $this->productHosting;
    }

    /**
     * Set niveauNotification
     *
     * @param \DateTime $niveauNotification
     *
     * @return Hosting
     */
    public function setNiveauNotification($niveauNotification)
    {
        $this->niveauNotification = $niveauNotification;

        return $this;
    }

    /**
     * Get niveauNotification
     *
     * @return \DateTime
     */
    public function getNiveauNotification()
    {
        return $this->niveauNotification;
    }
}
