<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contacts
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ContactRepository")
 */
class Contact
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
     * @var string
     *
     * @ORM\Column(name="fake_email", type="string", length=255)
     */
    private $fakeEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,nullable=true)
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255,nullable=true)
     */
    private $firstname;
    /**
     * @var string
     *
     * @ORM\Column(name="codeGandi", type="string", length=255)
     */
    private $codeGandi;

    /**
     * @var int
     *
     * @ORM\Column(name="idGandi", type="integer")
     */
    private $idGandi;
    /**
     * @var bool
     *
     * @ORM\Column(name="isDefault", type="boolean")
     */
    private $isDefault;

    /**
     * @ORM\OneToMany(targetEntity="Ndd", mappedBy="contact", cascade={"detach", "persist"})
     */
    private $ndds;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="contacts", cascade={"detach"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

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
     * Set email
     *
     * @param string $email
     * @return Contacts
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
     * Set code
     *
     * @param string $code
     * @return Contacts
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ndds = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isDefault=false;
        $this->email="";
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return Contact
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
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * Add ndds
     *
     * @param \AppBundle\Entity\Ndd $ndds
     * @return Contact
     */
    public function addNdd(\AppBundle\Entity\Ndd $ndds)
    {
        $this->ndds[] = $ndds;

        return $this;
    }

    /**
     * Remove ndds
     *
     * @param \AppBundle\Entity\Ndd $ndds
     */
    public function removeNdd(\AppBundle\Entity\Ndd $ndds)
    {
        $this->ndds->removeElement($ndds);
    }

    /**
     * Get ndds
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNdds()
    {
        return $this->ndds;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Contact
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
     * Set codeGandi
     *
     * @param string $codeGandi
     * @return Contact
     */
    public function setCodeGandi($codeGandi)
    {
        $this->codeGandi = $codeGandi;

        return $this;
    }

    /**
     * Get codeGandi
     *
     * @return string 
     */
    public function getCodeGandi()
    {
        return $this->codeGandi;
    }


    /**
     * Set idGandi
     *
     * @param integer $idGandi
     * @return Contact
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
     * Set fakeEmail
     *
     * @param string $fakeEmail
     * @return Contact
     */
    public function setFakeEmail($fakeEmail)
    {
        $this->fakeEmail = $fakeEmail;

        return $this;
    }

    /**
     * Get fakeEmail
     *
     * @return string 
     */
    public function getFakeEmail()
    {
        return $this->fakeEmail;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Contact
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
     * Set firstname
     *
     * @param string $firstname
     * @return Contact
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }
}
