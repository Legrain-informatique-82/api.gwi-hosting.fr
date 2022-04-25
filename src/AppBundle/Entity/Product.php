<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductRepository")
 */
class Product
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
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="codeLgr", type="string", length=255)
     */
    private $codeLgr;

    /**
     * @var string
     *
     * @ORM\Column(name="shortDescription", type="string", length=5000)
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="longDescription", type="string", length=5000)
     */
    private $longDescription;

    /**
     * @var integer ( 0 si illimité)
     *
     * @ORM\Column(name="minPeriod", type="integer")
     */
    private $minPeriod;


    /**
     * @ORM\OneToMany(targetEntity="ProductPicture", mappedBy="product", cascade={"remove", "persist"})
     */
    protected $pictures;

    /**
     * @ORM\ManyToMany(targetEntity="ProductCategory", inversedBy="products")
     * @ORM\JoinTable(name="product_product_category")
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="CGU", inversedBy="products")
     * @ORM\JoinTable(name="product_cgu")
     */
    private $cgus;


    /**
     * @ORM\OneToMany(targetEntity="PriceListLine", mappedBy="product", cascade={"remove", "persist"})
     */
    protected $priceListLines;

    /**
     * @ORM\OneToMany(targetEntity="CartLine", mappedBy="product", cascade={ "persist"})
     */
    protected $cartLines;

    /**
     * @ORM\OneToMany(targetEntity="Instance", mappedBy="product", cascade={"remove", "persist"})
     */
    private $instances;

    /**
     * @ORM\OneToMany(targetEntity="ProductAgency", mappedBy="product", cascade={"remove", "persist"})
     */
    protected $codesFacturationsAgences;



    /**
     * @ORM\OneToMany(targetEntity="NextPaiement", mappedBy="agency", cascade={"remove", "persist"})
     */
    protected $nextPayements;


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
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="parentsDependencies")
     * @ORM\JoinTable(name="product_dependancy")
     */
    private $dependancies;
    /**
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="dependancies")
     */
    private $parentsDependencies;

    /**
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="parentsProduitsComposes")
     * @ORM\JoinTable(name="product_compose")
     */
    private $produitsComposes;

    /**
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="produitsComposes")
     */
    private $parentsProduitsComposes;

    /**
     * @ORM\ManyToMany(targetEntity="ProductCategory", inversedBy="productsWhichDepend")
     * @ORM\JoinTable(name="product_product_category_dependancy")
     */
    private $dependanciesPerCategories;

    /**
     * @var Bool
     *
     * @ORM\Column(name="sousProduit", type="boolean")
     */
    private $sousProduit;

    /**
     * @var string
     *
     * @ORM\Column(name="features", type="text",nullable=true)
     */
    private $features;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="order", type="integer",nullable=true)
     */
    private $order;

    public function __construct() {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parentsDependencies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dependancies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->produitsComposes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parentsProduitsComposes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Product
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
     * Set reference
     *
     * @param string $reference
     * @return Product
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string 
     */
    public function getReference()
    {
        return $this->reference;
    }



    /**
     * Add pictures
     *
     * @param \AppBundle\Entity\ProductPicture $pictures
     * @return Product
     */
    public function addPicture(\AppBundle\Entity\ProductPicture $pictures)
    {
        $this->pictures[] = $pictures;
        $pictures->setProduct($this);
        return $this;
    }

    /**
     * Remove pictures
     *
     * @param \AppBundle\Entity\ProductPicture $pictures
     */
    public function removePicture(\AppBundle\Entity\ProductPicture $pictures)
    {
        $this->pictures->removeElement($pictures);
    }

    /**
     * Get pictures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * Add categories
     *
     * @param \AppBundle\Entity\ProductCategory $categories
     * @return Product
     */
    public function addCategory(\AppBundle\Entity\ProductCategory $categories)
    {
        $this->categories[] = $categories;

        return $this;
    }

    /**
     * Remove categories
     *
     * @param \AppBundle\Entity\ProductCategory $categories
     */
    public function removeCategory(\AppBundle\Entity\ProductCategory $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add priceListLines
     *
     * @param \AppBundle\Entity\PriceListLine $priceListLines
     * @return Product
     */
    public function addPriceListLine(\AppBundle\Entity\PriceListLine $priceListLines)
    {
        $this->priceListLines[] = $priceListLines;

        return $this;
    }

    /**
     * Remove priceListLines
     *
     * @param \AppBundle\Entity\PriceListLine $priceListLines
     */
    public function removePriceListLine(\AppBundle\Entity\PriceListLine $priceListLines)
    {
        $this->priceListLines->removeElement($priceListLines);
    }

    /**
     * Get priceListLines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPriceListLines()
    {
        return $this->priceListLines;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * Set codeLgr
     *
     * @param string $codeLgr
     * @return Product
     */
    public function setCodeLgr($codeLgr)
    {
        $this->codeLgr = $codeLgr;

        return $this;
    }

    /**
     * Get codeLgr
     *
     * @return string 
     */
    public function getCodeLgr()
    {
        return $this->codeLgr;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     * @return Product
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string 
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set longDescription
     *
     * @param string $longDescription
     * @return Product
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * Get longDescription
     *
     * @return string 
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }



    /**
     * Add cartLines
     *
     * @param \AppBundle\Entity\CartLine $cartLines
     * @return Product
     */
    public function addCartLine(\AppBundle\Entity\CartLine $cartLines)
    {
        $this->cartLines[] = $cartLines;

        return $this;
    }

    /**
     * Remove cartLines
     *
     * @param \AppBundle\Entity\CartLine $cartLines
     */
    public function removeCartLine(\AppBundle\Entity\CartLine $cartLines)
    {
        $this->cartLines->removeElement($cartLines);
    }

    /**
     * Get cartLines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCartLines()
    {
        return $this->cartLines;
    }

    /**
     * Set minPeriod
     *
     * @param integer $minPeriod
     * @return Product
     */
    public function setMinPeriod($minPeriod)
    {
        $this->minPeriod = $minPeriod;

        return $this;
    }

    /**
     * Get minPeriod
     *
     * @return integer 
     */
    public function getMinPeriod()
    {
        return $this->minPeriod;
    }

    /**
     * Add codesFacturationsAgences
     *
     * @param \AppBundle\Entity\ProductAgency $codesFacturationsAgences
     * @return Product
     */
    public function addCodesFacturationsAgence(\AppBundle\Entity\ProductAgency $codesFacturationsAgences)
    {
        $this->codesFacturationsAgences[] = $codesFacturationsAgences;

        return $this;
    }

    /**
     * Remove codesFacturationsAgences
     *
     * @param \AppBundle\Entity\ProductAgency $codesFacturationsAgences
     */
    public function removeCodesFacturationsAgence(\AppBundle\Entity\ProductAgency $codesFacturationsAgences)
    {
        $this->codesFacturationsAgences->removeElement($codesFacturationsAgences);
    }

    /**
     * Get codesFacturationsAgences
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodesFacturationsAgences()
    {
        return $this->codesFacturationsAgences;
    }

    /**
     * Add instances
     * @param \AppBundle\Entity\Instance $instances
     * @return Product
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
     * Add dependancies
     *
     * @param \AppBundle\Entity\Product $dependancies
     * @return Product
     */
    public function addDependancy(\AppBundle\Entity\Product $dependancies)
    {
        $this->dependancies[] = $dependancies;

        return $this;
    }

    /**
     * Remove dependancies
     *
     * @param \AppBundle\Entity\Product $dependancies
     */
    public function removeDependancy(\AppBundle\Entity\Product $dependancies)
    {
        $this->dependancies->removeElement($dependancies);
    }

    /**
     * Get dependancies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDependancies()
    {
        return $this->dependancies;
    }

    /**
     * Add parentsDependencies
     *
     * @param \AppBundle\Entity\Product $parentsDependencies
     * @return Product
     */
    public function addParentsDependency(\AppBundle\Entity\Product $parentsDependencies)
    {
        $this->parentsDependencies[] = $parentsDependencies;

        return $this;
    }

    /**
     * Remove parentsDependencies
     *
     * @param \AppBundle\Entity\Product $parentsDependencies
     */
    public function removeParentsDependency(\AppBundle\Entity\Product $parentsDependencies)
    {
        $this->parentsDependencies->removeElement($parentsDependencies);
    }

    /**
     * Get parentsDependencies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParentsDependencies()
    {
        return $this->parentsDependencies;
    }

    /**
     * Add produitsComposes
     *
     * @param \AppBundle\Entity\Product $produitCompose
     * @return Product
     */
    public function addProduitCompose(\AppBundle\Entity\Product $produitsComposes)
    {
        $this->produitsComposes[] = $produitsComposes;

        return $this;
    }

    /**
     * Remove produitsComposes
     *
     * @param \AppBundle\Entity\Product $produitsComposes
     */
    public function removeProduitsCompose(\AppBundle\Entity\Product $produitsComposes)
    {
        $this->produitsComposes->removeElement($produitsComposes);
    }

    /**
     * Get produitsComposes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProduitsComposes()
    {
        return $this->produitsComposes;
    }

    /**
     * Add parentsProduitsComposes
     *
     * @param \AppBundle\Entity\Product $parentsProduitsComposes
     * @return Product
     */
    public function addParentsProduitsCompose(\AppBundle\Entity\Product $parentsProduitsComposes)
    {
        $this->parentsProduitsComposes[] = $parentsProduitsComposes;

        return $this;
    }

    /**
     * Remove parentsProduitsComposes
     *
     * @param \AppBundle\Entity\Product $parentsProduitsComposes
     */
    public function removeParentsProduitsCompose(\AppBundle\Entity\Product $parentsProduitsComposes)
    {
        $this->parentsProduitsComposes->removeElement($parentsProduitsComposes);
    }

    /**
     * Get parentsProduitsComposes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParentsProduitsComposes()
    {
        return $this->parentsProduitsComposes;
    }

    /**
     * Set sousProduit
     *
     * @param boolean $sousProduit
     * @return Product
     */
    public function setSousProduit($sousProduit)
    {
        $this->sousProduit = $sousProduit;

        return $this;
    }

    /**
     * Get sousProduit
     *
     * @return boolean 
     */
    public function getSousProduit()
    {
        return $this->sousProduit;
    }

    /**
     * Set features
     *
     * @param array $features
     * @return Product
     */
    public function setFeatures($features)
    {
        $this->features = json_encode($features);

        return $this;
    }

    /**
     * Get features
     *
     * @return array
     */
    public function getFeatures()
    {
        return json_decode(($this->features==null?'[]':$this->features));
    }

    /**
     * Get features
     *
     * @return array
     */
    public function getFeaturesAsArray()
    {
        return json_decode(($this->features==null?'[]':$this->features),true);
    }

    /**
     * Add dependanciesPerCategories
     *
     * @param \AppBundle\Entity\ProductCategory $dependanciesPerCategories
     * @return Product
     */
    public function addDependancyPerCategory(\AppBundle\Entity\ProductCategory $dependanciesPerCategories)
    {
        $this->dependanciesPerCategories[] = $dependanciesPerCategories;

        return $this;
    }

    /**
     * Remove dependanciesPerCategories
     *
     * @param \AppBundle\Entity\ProductCategory $dependanciesPerCategories
     */
    public function removeDependanciesPerCategory(\AppBundle\Entity\ProductCategory $dependanciesPerCategories)
    {
        $this->dependanciesPerCategories->removeElement($dependanciesPerCategories);
    }

    /**
     * Get dependanciesPerCategories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDependanciesPerCategories()
    {
        return $this->dependanciesPerCategories;
    }

    /**
     * Add produitsComposes
     *
     * @param \AppBundle\Entity\Product $produitsComposes
     * @return Product
     */
    public function addProduitsCompose(\AppBundle\Entity\Product $produitsComposes)
    {
        $this->produitsComposes[] = $produitsComposes;

        return $this;
    }

    /**
     * Add dependanciesPerCategories
     *
     * @param \AppBundle\Entity\ProductCategory $dependanciesPerCategories
     * @return Product
     */
    public function addDependanciesPerCategory(\AppBundle\Entity\ProductCategory $dependanciesPerCategories)
    {
        $this->dependanciesPerCategories[] = $dependanciesPerCategories;

        return $this;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Product
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
     * Add cgus
     *
     * @param \AppBundle\Entity\CGU $cgus
     * @return Product
     */
    public function addCgus(\AppBundle\Entity\CGU $cgus)
    {
        $this->cgus[] = $cgus;

        return $this;
    }

    /**
     * Remove cgus
     *
     * @param \AppBundle\Entity\CGU $cgus
     */
    public function removeCgus(\AppBundle\Entity\CGU $cgus)
    {
        $this->cgus->removeElement($cgus);
    }

    /**
     * Get cgus
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCgus()
    {
        return $this->cgus;
    }

    /**
     * Add nextPayements
     *
     * @param \AppBundle\Entity\NextPaiement $nextPayements
     * @return Product
     */
    public function addNextPayement(\AppBundle\Entity\NextPaiement $nextPayements)
    {
        $this->nextPayements[] = $nextPayements;

        return $this;
    }

    /**
     * Remove nextPayements
     *
     * @param \AppBundle\Entity\NextPaiement $nextPayements
     */
    public function removeNextPayement(\AppBundle\Entity\NextPaiement $nextPayements)
    {
        $this->nextPayements->removeElement($nextPayements);
    }

    /**
     * Get nextPayements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNextPayements()
    {
        return $this->nextPayements;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Product
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }
}
