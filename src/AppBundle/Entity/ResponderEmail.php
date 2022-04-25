<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponderEmail
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ResponderEmailRepository")
 */
class ResponderEmail
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initDate", type="datetime")
     */
    private $initDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=1000)
     */
    private $message;
    /**
     * @ORM\Column(name="activeGandi", type="boolean")
     */
    private $activeGandi;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ndd", cascade={"detach"})
     * @ORM\JoinColumn(name="ndd_id", referencedColumnName="id")
     */
    private $ndd;
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
        $this->activeGandi=false;
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
     * Set email
     *
     * @param string $email
     * @return ResponderEmail
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
     * Set initDate
     *
     * @param \DateTime $initDate
     * @return ResponderEmail
     */
    public function setInitDate($initDate)
    {
        $this->initDate = $initDate;

        return $this;
    }

    /**
     * Get initDate
     *
     * @return \DateTime 
     */
    public function getInitDate()
    {
        return $this->initDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return ResponderEmail
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return ResponderEmail
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set ndd
     *
     * @param \AppBundle\Entity\Ndd $ndd
     * @return ResponderEmail
     */
    public function setNdd(\AppBundle\Entity\Ndd $ndd)
    {
        $this->ndd = $ndd;

        return $this;
    }

    /**
     * Get ndd
     *
     * @return \AppBundle\Entity\NddNdd
     */
    public function getNdd()
    {
        return $this->ndd;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return ResponderEmail
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
     * @return ResponderEmail
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
     * @return ResponderEmail
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
     * @return ResponderEmail
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
     * Set activeGandi
     *
     * @param boolean $activeGandi
     * @return ResponderEmail
     */
    public function setActiveGandi($activeGandi)
    {
        $this->activeGandi = $activeGandi;

        return $this;
    }

    /**
     * Get activeGandi
     *
     * @return boolean 
     */
    public function getActiveGandi()
    {
        return $this->activeGandi;
    }
}
