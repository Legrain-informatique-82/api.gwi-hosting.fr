<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CityRepository")
 */
class City
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="codeInsee", type="string", length=5)
     */
    private $codeInsee;


    /**
     * @ORM\OneToMany(targetEntity="Agency", mappedBy="city", cascade={"remove", "persist"})
     */
    protected $agencies;

    /**
     * @ORM\ManyToMany(targetEntity="ZipCode", inversedBy="cities")
     * @ORM\JoinTable(name="city_zipCode")
     */
    private $zipCodes;


    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="city", cascade={"remove", "persist"})
     */
    protected $users;

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
     * @return City
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
     * Constructor
     */
    public function __construct()
    {
        $this->agencies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->zipCodes = new \Doctrine\Common\Collections\ArrayCollection();



    }

    /**
     * Add agencies
     *
     * @param \AppBundle\Entity\Agency $agencies
     * @return City
     */
    public function addAgency(\AppBundle\Entity\Agency $agencies)
    {
        $this->agencies[] = $agencies;

        return $this;
    }

    /**
     * Remove agencies
     *
     * @param \AppBundle\Entity\Agency $agency
     */
    public function removeAgency(\AppBundle\Entity\Agency $agency)
    {
        $this->agencies->removeElement($agency);
    }

    /**
     * Get agencies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAgencies()
    {
        return $this->agencies;
    }




    /**
     * Add zipCodes
     *
     * @param \AppBundle\Entity\ZipCode $zipCode
     * @return City
     */
    public function addZipCode(\AppBundle\Entity\ZipCode $zipCode)
    {
        $this->zipCodes[] = $zipCode;

        return $this;
    }

    /**
     * Remove zipCodes
     *
     * @param \AppBundle\Entity\ZipCode $zipCodes
     */
    public function removeZipCode(\AppBundle\Entity\ZipCode $zipCodes)
    {
        $this->zipCodes->removeElement($zipCodes);
    }

    /**
     * Get zipCodes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getZipCodes()
    {
        return $this->zipCodes;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return City
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
     * @return City
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
     * @return City
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
     * @return City
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
     * Add users
     *
     * @param \AppBundle\Entity\User $users
     * @return City
     */
    public function addUser(\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \AppBundle\Entity\User $users
     */
    public function removeUser(\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set codeInsee
     *
     * @param string $codeInsee
     * @return City
     */
    public function setCodeInsee($codeInsee)
    {
        $this->codeInsee = $codeInsee;

        return $this;
    }

    /**
     * Get codeInsee
     *
     * @return string 
     */
    public function getCodeInsee()
    {
        return $this->codeInsee;
    }
}
