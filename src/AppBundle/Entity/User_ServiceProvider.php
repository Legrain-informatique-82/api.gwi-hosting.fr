<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User_ServiceProvider
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\User_ServiceProviderRepository")
 */
class User_ServiceProvider
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
     * @ORM\Column(name="idApi", type="string", length=255)
     */
    private $idApi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDefault", type="boolean")
     */
    private $isDefault;


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userServiceProviders", cascade={"remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceProvider", inversedBy="userServiceProviders", cascade={"remove"})
     * @ORM\JoinColumn(name="service_provider_id", referencedColumnName="id")
     */
    protected $serviceProvider;


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
     * Set idApi
     *
     * @param string $idApi
     * @return User_ServiceProvider
     */
    public function setIdApi($idApi)
    {
        $this->idApi = $idApi;

        return $this;
    }

    /**
     * Get idApi
     *
     * @return string 
     */
    public function getIdApi()
    {
        return $this->idApi;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return User_ServiceProvider
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return User_ServiceProvider
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
     * Set serviceProvider
     *
     * @param \AppBundle\Entity\ServiceProvider $serviceProvider
     * @return User_ServiceProvider
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
}
