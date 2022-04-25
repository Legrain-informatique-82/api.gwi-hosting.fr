<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SnapshopProfileInstance
 */
class SnapshopProfileInstance
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $idGandi;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $whoCreate;

    /**
     * @var \DateTime
     */
    private $whenCreate;

    /**
     * @var integer
     */
    private $whoUpdate;

    /**
     * @var \DateTime
     */
    private $whenUpdate;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $instances;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->instances = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set idGandi
     *
     * @param integer $idGandi
     * @return SnapshopProfileInstance
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
     * @return SnapshopProfileInstance
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
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return SnapshopProfileInstance
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
     * @return SnapshopProfileInstance
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
     * @return SnapshopProfileInstance
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
     * @return SnapshopProfileInstance
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
     * Add instances
     *
     * @param \AppBundle\Entity\Instance $instances
     * @return SnapshopProfileInstance
     */
    public function addInstance(\AppBundle\Entity\Instance $instances)
    {
        $this->instances[] = $instances;

        return $this;
    }

    /**
     * Remove instances
     *
     * @param \AppBundle\Entity\Instance $instances
     */
    public function removeInstance(\AppBundle\Entity\Instance $instances)
    {
        $this->instances->removeElement($instances);
    }

    /**
     * Get instances
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstances()
    {
        return $this->instances;
    }
}
