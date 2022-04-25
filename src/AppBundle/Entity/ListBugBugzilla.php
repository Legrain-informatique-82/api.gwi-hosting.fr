<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListBugBugzilla
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ListBugBugzillaRepository")
 */
class ListBugBugzilla
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
     * @var integer
     *
     * @ORM\Column(name="idBug", type="integer")
     */
    private $idBug;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateLastUpdate", type="datetime")
     */
    private $dateLastUpdate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isRead", type="boolean")
     */
    private $isRead;



    /**
     * @ORM\ManyToOne(targetEntity="Ndd", inversedBy="listBugs", cascade={"detach"})
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
     * Set idBug
     *
     * @param integer $idBug
     * @return ListBugBugzilla
     */
    public function setIdBug($idBug)
    {
        $this->idBug = $idBug;

        return $this;
    }

    /**
     * Get idBug
     *
     * @return integer 
     */
    public function getIdBug()
    {
        return $this->idBug;
    }

    /**
     * Set dateLastUpdate
     *
     * @param \DateTime $dateLastUpdate
     * @return ListBugBugzilla
     */
    public function setDateLastUpdate($dateLastUpdate)
    {
        $this->dateLastUpdate = $dateLastUpdate;

        return $this;
    }

    /**
     * Get dateLastUpdate
     *
     * @return \DateTime 
     */
    public function getDateLastUpdate()
    {
        return $this->dateLastUpdate;
    }

    /**
     * Set isRead
     *
     * @param boolean $isRead
     * @return ListBugBugzilla
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return boolean 
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set ndd
     *
     * @param \AppBundle\Entity\Ndd $ndd
     * @return ListBugBugzilla
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
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return ListBugBugzilla
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
     * @return ListBugBugzilla
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
     * @return ListBugBugzilla
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
     * @return ListBugBugzilla
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
}
