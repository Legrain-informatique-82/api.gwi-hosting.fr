<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceProvider
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ServiceProviderRepository")
 */
class ServiceProvider
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
     * @ORM\OneToMany(targetEntity="User_ServiceProvider", mappedBy="serviceProvider", cascade={"remove", "persist"})
     */
    protected $userServiceProviders;

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
     * Set name
     *
     * @param string $name
     * @return ServiceProvider
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
     * @return ServiceProvider
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
     * @return ServiceProvider
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
     * @return ServiceProvider
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
     * @return ServiceProvider
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
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return ServiceProvider
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean 
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Add userServiceProviders
     *
     * @param \AppBundle\Entity\User_ServiceProvider $userServiceProviders
     * @return ServiceProvider
     */
    public function addUserServiceProvider(\AppBundle\Entity\User_ServiceProvider $userServiceProviders)
    {
        $this->userServiceProviders[] = $userServiceProviders;

        return $this;
    }

    /**
     * Remove userServiceProviders
     *
     * @param \AppBundle\Entity\User_ServiceProvider $userServiceProviders
     */
    public function removeUserServiceProvider(\AppBundle\Entity\User_ServiceProvider $userServiceProviders)
    {
        $this->userServiceProviders->removeElement($userServiceProviders);
    }

    /**
     * Get userServiceProviders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserServiceProviders()
    {
        return $this->userServiceProviders;
    }
}
