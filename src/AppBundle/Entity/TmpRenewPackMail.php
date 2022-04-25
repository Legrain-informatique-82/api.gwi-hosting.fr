<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TmpRenewPackMail
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TmpRenewPackMailRepository")
 */
class TmpRenewPackMail
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
     * @var \DateTime
     *
     * @ORM\Column(name="dateShouldBeTheDomain", type="datetime")
     */
    private $dateShouldBeTheDomain;

    /**
     * @ORM\ManyToOne(targetEntity="Ndd", cascade={"detach"})
     * @ORM\JoinColumn(name="ndd_id", referencedColumnName="id")
     */
    private $ndd;

    /**
     * @ORM\ManyToOne(targetEntity="EmailGandiPackPro", cascade={"detach"})
     * @ORM\JoinColumn(name="pack_mail_id", referencedColumnName="id")
     */
    private $packMail;




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
     * Set dateShouldBeTheDomain
     *
     * @param \DateTime $dateShouldBeTheDomain
     * @return TmpRenewPackMail
     */
    public function setDateShouldBeTheDomain($dateShouldBeTheDomain)
    {
        $this->dateShouldBeTheDomain = $dateShouldBeTheDomain;

        return $this;
    }

    /**
     * Get dateShouldBeTheDomain
     *
     * @return \DateTime 
     */
    public function getDateShouldBeTheDomain()
    {
        return $this->dateShouldBeTheDomain;
    }

    /**
     * Set whoCreate
     *
     * @param integer $whoCreate
     * @return TmpRenewPackMail
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
     * @return TmpRenewPackMail
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
     * @return TmpRenewPackMail
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
     * @return TmpRenewPackMail
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
     * Set ndd
     *
     * @param \AppBundle\Entity\Ndd $ndd
     * @return TmpRenewPackMail
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
     * Set packMail
     *
     * @param \AppBundle\Entity\EmailGandiPackPro $packMail
     * @return TmpRenewPackMail
     */
    public function setPackMail(\AppBundle\Entity\EmailGandiPackPro $packMail = null)
    {
        $this->packMail = $packMail;

        return $this;
    }

    /**
     * Get packMail
     *
     * @return \AppBundle\Entity\EmailGandiPackPro 
     */
    public function getPackMail()
    {
        return $this->packMail;
    }
}
