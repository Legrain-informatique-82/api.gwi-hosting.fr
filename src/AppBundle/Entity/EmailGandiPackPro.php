<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmailGandiPackPro
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EmailGandiPackProRepository")
 */
class EmailGandiPackPro
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
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @ORM\OneToOne(targetEntity="Ndd", inversedBy="emailGandiPackPro", cascade={"detach"})
     * @ORM\JoinColumn(name="ndd_id", referencedColumnName="id")
     */
    private $ndd;




    /**
     * @ORM\ManyToOne(targetEntity="ServiceProvider", cascade={"detach"})
     * @ORM\JoinColumn(name="service_provider_id", referencedColumnName="id")
     */
    private $serviceProvider;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateending", type="datetime")
     */
    private $dateEnding;

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
     *
     * @ORM\Column(name="whenupdate", type="datetime")
     */
    private $whenUpdate;


    public function __construct(){
        $this->niveauNotification=(int)0;
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
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return EmailGandiPackPro
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
     * @return EmailGandiPackPro
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
     * @return EmailGandiPackPro
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
     * @return EmailGandiPackPro
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
     * Set serviceProvider
     *
     * @param \AppBundle\Entity\ServiceProvider $serviceProvider
     * @return EmailGandiPackPro
     */
    public function setServiceProvider(\AppBundle\Entity\ServiceProvider $serviceProvider = null)
    {
        $this->serviceProvider = $serviceProvider;

        return $this;
    }

    /**
     * Get serviceProvider
     *
     * @return \AppBundle\Entity\ServiceProvider 
     */
    public function getServiceProvider()
    {
        return $this->serviceProvider;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return EmailGandiPackPro
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
     * Set dateEnding
     *
     * @param \DateTime $dateEnding
     * @return EmailGandiPackPro
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
     * Set size
     *
     * @param integer $size
     * @return EmailGandiPackPro
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set ndd
     *
     * @param \AppBundle\Entity\Ndd $ndd
     * @return EmailGandiPackPro
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
     * Set niveauNotification
     *
     * @param integer $niveauNotification
     * @return EmailGandiPackPro
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
