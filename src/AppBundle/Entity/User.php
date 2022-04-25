<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User
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
     * @ORM\Column(name="email", type="string", length=255,unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=60)
     *
     */
    private $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registrationDate", type="datetime")
     */
    private $registrationDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accountLgr", type="boolean")
     */
    private $accountLgr;

    /**
     * @var string
     * @ORM\Column(name="codeClient", type="string", length=10,nullable=true)
     */
    private $codeClient;

    /**
     * @var string
     * @ORM\Column(name="address1", type="string", length=255,nullable=true)
     */
    private $address1;

    /**
     * @var string
     * @ORM\Column(name="address2", type="string", length=255,nullable=true)
     */
    private $address2;

    /**
     * @var string
     * @ORM\Column(name="address3", type="string", length=255,nullable=true)
     */
    private $address3;


    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="users", cascade={"detach"})
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="ZipCode", inversedBy="users", cascade={"detach"})
     * @ORM\JoinColumn(name="zipCode_id", referencedColumnName="id")
     */
    protected $zipcode;

    /**
     * @ORM\ManyToOne(targetEntity="Agency", inversedBy="users", cascade={"detach"})
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;


    /**
     * @ORM\OneToMany(targetEntity="User_ServiceProvider", mappedBy="user", cascade={"detach", "persist"})
     */
    protected $userServiceProviders;

    /**
     * @ORM\OneToMany(targetEntity="Bugzilla", mappedBy="user", cascade={"remove", "persist"})
     */
    protected $tagsBugzilla;


    /**
     * @ORM\ManyToOne(targetEntity="PriceList", inversedBy="users", cascade={"detach"})
     * @ORM\JoinColumn(name="price_list_id", referencedColumnName="id")
     */
    protected $priceList;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=15,nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="cellPhone", type="string", length=15,nullable=true)
     */
    private $cellPhone;
    /**
     * @var string
     *
     * @ORM\Column(name="workPhone", type="string", length=15,nullable=true)
     */
    private $workPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="companyName", type="string", length=255,nullable=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="numTVA", type="string", length=255,nullable=true)
     */
    private $numTVA;



    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToOne(targetEntity="AccountBalance", mappedBy="user")
     */
    private $accountBalance;


    /**
     * @ORM\OneToMany(targetEntity="Log", mappedBy="user", cascade={"remove", "persist"})
     */
    protected $logs;

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
     * @ORM\OneToMany(targetEntity="User", mappedBy="parent", cascade={"remove", "persist"})
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="children", cascade={"detach"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;


    /**
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="user", cascade={"detach", "persist"})
     */
    private $contacts;







    /**
     * @ORM\ManyToMany(targetEntity="Roles", mappedBy="users")
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity="Ndd", mappedBy="user", cascade={"remove", "persist"})
     */
    protected $ndds;

    /**
     * @ORM\OneToMany(targetEntity="Cart", mappedBy="user", cascade={"remove", "persist"})
     */
    protected $carts;

    /**
     * @ORM\OneToMany(targetEntity="Instance", mappedBy="user", cascade={"remove", "persist"})
     */
    private $instances;



    /**
     * @ORM\OneToMany(targetEntity="Vhosts", mappedBy="user", cascade={"remove", "persist"})
     */
    private $vhosts;



    /**
     * @ORM\ManyToOne(targetEntity="TiersPourTVA", cascade={"detach"})
     * @ORM\JoinColumn(name="tiersPourTVA_id", referencedColumnName="id")
     */
    protected $tiersPourTVA;


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
     * @return User
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
     * @return User
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

    /**
     * Set email
     *
     * @param string $email
     * @return User
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
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set registrationDate
     *
     * @param \DateTime $registrationDate
     * @return User
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * Get registrationDate
     *
     * @return \DateTime 
     */
    public function getRegistrationDate()
    {

        return $this->registrationDate;
    }

    /**
     * Set accountLgr
     *
     * @param boolean $accountLgr
     * @return User
     */
    public function setAccountLgr($accountLgr)
    {
        $this->accountLgr = $accountLgr;

        return $this;
    }

    /**
     * Get accountLgr
     *
     * @return boolean 
     */
    public function getAccountLgr()
    {
        return $this->accountLgr;
    }


    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setParent(null);
        $this->setCodeClient('');
        $this->setAccountLgr(false);

    }

    /**
     * Add children
     *
     * @param \AppBundle\Entity\User $children
     * @return User
     */
    public function addChild(\AppBundle\Entity\User $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \AppBundle\Entity\User $children
     */
    public function removeChild(\AppBundle\Entity\User $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\User $parent
     * @return User
     */
    public function setParent(\AppBundle\Entity\User $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\User 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set agency
     *
     * @param \AppBundle\Entity\Agency $agency
     * @return User
     */
    public function setAgency(\AppBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \AppBundle\Entity\Agency 
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set address1
     *
     * @param string $address1
     * @return User
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string 
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return User
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string 
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set address3
     *
     * @param string $address3
     * @return User
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    /**
     * Get address3
     *
     * @return string 
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     * @return User
     */
    public function setCity(\AppBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zipcode
     *
     * @param \AppBundle\Entity\ZipCode $zipcode
     * @return User
     */
    public function setZipcode(\AppBundle\Entity\ZipCode $zipcode = null)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return \AppBundle\Entity\ZipCode 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Add roles
     *
     * @param \AppBundle\Entity\Roles $roles
     * @return User
     */
    public function addRole(\AppBundle\Entity\Roles $roles)
    {
        $roles->addUser($this); // synchronously updating inverse side
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \AppBundle\Entity\Roles $roles
     */
    public function removeRole(\AppBundle\Entity\Roles $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add ndds
     *
     * @param \AppBundle\Entity\Ndd $ndds
     * @return User
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
     * Add userServiceProviders
     *
     * @param \AppBundle\Entity\User_ServiceProvider $userServiceProviders
     * @return User
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

    /**
     * Set accountBalance
     *
     * @param \AppBundle\Entity\AccountBalance $accountBalance
     * @return User
     */
    public function setAccountBalance(\AppBundle\Entity\AccountBalance $accountBalance = null)
    {
        $this->accountBalance = $accountBalance;

        return $this;
    }

    /**
     * Get accountBalance
     *
     * @return \AppBundle\Entity\AccountBalance 
     */
    public function getAccountBalance()
    {
        return $this->accountBalance;
    }

    /**
     * Set priceList
     *
     * @param \AppBundle\Entity\PriceList $priceList
     * @return User
     */
    public function setPriceList(\AppBundle\Entity\PriceList $priceList = null)
    {
        $this->priceList = $priceList;

        return $this;
    }

    /**
     * Get priceList
     *
     * @return \AppBundle\Entity\PriceList 
     */
    public function getPriceList()
    {
        return $this->priceList;
    }

    /**
     * Add logs
     *
     * @param \AppBundle\Entity\Log $logs
     * @return User
     */
    public function addLog(\AppBundle\Entity\Log $logs)
    {
        $this->logs[] = $logs;

        return $this;
    }

    /**
     * Remove logs
     *
     * @param \AppBundle\Entity\Log $logs
     */
    public function removeLog(\AppBundle\Entity\Log $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add carts
     *
     * @param \AppBundle\Entity\Cart $carts
     * @return User
     */
    public function addCart(\AppBundle\Entity\Cart $carts)
    {
        $this->carts[] = $carts;

        return $this;
    }

    /**
     * Remove carts
     *
     * @param \AppBundle\Entity\Cart $carts
     */
    public function removeCart(\AppBundle\Entity\Cart $carts)
    {
        $this->carts->removeElement($carts);
    }

    /**
     * Get carts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * Add contacts
     *
     * @param \AppBundle\Entity\Contact $contacts
     * @return User
     */
    public function addContact(\AppBundle\Entity\Contact $contacts)
    {
        $this->contacts[] = $contacts;

        return $this;
    }

    /**
     * Remove contacts
     *
     * @param \AppBundle\Entity\Contact $contacts
     */
    public function removeContact(\AppBundle\Entity\Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add instances
     *
     * @param \AppBundle\Entity\Instance $instances
     * @return User
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

    /**
     * Add vhosts
     *
     * @param \AppBundle\Entity\Vhosts $vhosts
     * @return User
     */
    public function addVhost(\AppBundle\Entity\Vhosts $vhosts)
    {
        $this->vhosts[] = $vhosts;

        return $this;
    }

    /**
     * Remove vhosts
     *
     * @param \AppBundle\Entity\Vhosts $vhosts
     */
    public function removeVhost(\AppBundle\Entity\Vhosts $vhosts)
    {
        $this->vhosts->removeElement($vhosts);
    }

    /**
     * Get vhosts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVhosts()
    {
        return $this->vhosts;
    }

    /**
     * Add tagsBugzilla
     *
     * @param \AppBundle\Entity\Bugzilla $tagsBugzilla
     * @return User
     */
    public function addTagsBugzilla(\AppBundle\Entity\Bugzilla $tagsBugzilla)
    {
        $this->tagsBugzilla[] = $tagsBugzilla;

        return $this;
    }

    /**
     * Remove tagsBugzilla
     *
     * @param \AppBundle\Entity\Bugzilla $tagsBugzilla
     */
    public function removeTagsBugzilla(\AppBundle\Entity\Bugzilla $tagsBugzilla)
    {
        $this->tagsBugzilla->removeElement($tagsBugzilla);
    }

    /**
     * Get tagsBugzilla
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTagsBugzilla()
    {
        return $this->tagsBugzilla;
    }

    /**
     * Set codeClient
     *
     * @param string $codeClient
     * @return User
     */
    public function setCodeClient($codeClient)
    {
        $this->codeClient = $codeClient;

        return $this;
    }

    /**
     * Get codeClient
     *
     * @return string 
     */
    public function getCodeClient()
    {
        return $this->codeClient;
    }

    /**
     * Set cellPhone
     *
     * @param string $cellPhone
     * @return User
     */
    public function setCellPhone($cellPhone)
    {
        $this->cellPhone = $cellPhone;

        return $this;
    }

    /**
     * Get cellPhone
     *
     * @return string 
     */
    public function getCellPhone()
    {
        return $this->cellPhone;
    }

    /**
     * Set workPhone
     *
     * @param string $workPhone
     * @return User
     */
    public function setWorkPhone($workPhone)
    {
        $this->workPhone = $workPhone;

        return $this;
    }

    /**
     * Get workPhone
     *
     * @return string 
     */
    public function getWorkPhone()
    {
        return $this->workPhone;
    }

    /**
     * Set companyName
     *
     * @param string $companyName
     * @return User
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string 
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set numTVA
     *
     * @param string $numTVA
     * @return User
     */
    public function setNumTVA($numTVA)
    {
        $this->numTVA = $numTVA;

        return $this;
    }

    /**
     * Get numTVA
     *
     * @return string 
     */
    public function getNumTVA()
    {
        return $this->numTVA;
    }

    /**
     * Set tiersPourTVA
     *
     * @param \AppBundle\Entity\TiersPourTVA $tiersPourTVA
     * @return User
     */
    public function setTiersPourTVA(\AppBundle\Entity\TiersPourTVA $tiersPourTVA = null)
    {
        $this->tiersPourTVA = $tiersPourTVA;

        return $this;
    }

    /**
     * Get tiersPourTVA
     *
     * @return \AppBundle\Entity\TiersPourTVA 
     */
    public function getTiersPourTVA()
    {
        return $this->tiersPourTVA;
    }
}
