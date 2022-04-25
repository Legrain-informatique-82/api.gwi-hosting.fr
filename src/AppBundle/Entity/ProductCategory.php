<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductCategory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductCategoryRepository")
 */
class ProductCategory
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
     * @ORM\Column(name="isVisible", type="boolean")
     */
    private $isVisible;
    /**
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="categories")
     */
    private $products;



    /**
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="dependanciesPerCategories")
     */
    private $productsWhichDepend;

    public function __construct() {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @ORM\OneToOne(targetEntity="ProductCategory")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentCategory;
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
     * @return ProductCategory
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
     * Add products
     *
     * @param \AppBundle\Entity\Product $products
     * @return ProductCategory
     */
    public function addProduct(\AppBundle\Entity\Product $products)
    {
        $this->products[] = $products;

        return $this;
    }

    /**
     * Remove products
     *
     * @param \AppBundle\Entity\Product $products
     */
    public function removeProduct(\AppBundle\Entity\Product $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set parentCategory
     *
     * @param \AppBundle\Entity\ProductCategory $parentCategory
     * @return ProductCategory
     */
    public function setParentCategory(\AppBundle\Entity\ProductCategory $parentCategory = null)
    {
        $this->parentCategory = $parentCategory;

        return $this;
    }

    /**
     * Get parentCategory
     *
     * @return \AppBundle\Entity\ProductCategory
     */
    public function getParentCategory()
    {
        return $this->parentCategory;
    }

    /**
     * Set isVisible
     *
     * @param boolean $isVisible
     * @return ProductCategory
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Get isVisible
     *
     * @return boolean 
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return ProductCategory
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
     * @return ProductCategory
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
     * @return ProductCategory
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
     * @return ProductCategory
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
     * Add productsWhichDepend
     *
     * @param \AppBundle\Entity\Product $productsWhichDepend
     * @return ProductCategory
     */
    public function addProductsWhichDepend(\AppBundle\Entity\Product $productsWhichDepend)
    {
        $this->productsWhichDepend[] = $productsWhichDepend;

        return $this;
    }

    /**
     * Remove productsWhichDepend
     *
     * @param \AppBundle\Entity\Product $productsWhichDepend
     */
    public function removeProductsWhichDepend(\AppBundle\Entity\Product $productsWhichDepend)
    {
        $this->productsWhichDepend->removeElement($productsWhichDepend);
    }

    /**
     * Get productsWhichDepend
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductsWhichDepend()
    {
        return $this->productsWhichDepend;
    }
}
