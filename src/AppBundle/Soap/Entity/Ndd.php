<?php

namespace AppBundle\Soap\Entity;


/**
 * Ndd
 */
class Ndd
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     *
     */
    public $name;
    /**
     * @var string
     *
     */
    public $status;
    /**
     * @var string
     *
     */
    public $date_pending_delete_end;
    /**
     * @var int
     *
     */
    public $date_updated;
    /**
     * @var int
     *
     */
    public $date_delete;
    /**
     * @var int
     *
     */
    public $date_hold_end;
    /**
     * @var string
     *
     */
    public $fqdn;
    /**
     * @var int
     *
     */
    public $date_registry_end;

    /**
     * @var string
     *
     */
    public $authinfo;
    /**
     * @var int
     *
     */
    public $date_registry_creation;
    /**
     * @var int

     *
     */
    public $date_renew_begin;
    /**
     * @var string

     *
     */
    public $tld;

    /**
     * @var int
     *
     */
    public $date_created;
    /**
     * @var int
     *
     */
    public $date_restore_end;

    /**
     * @var int
     *
     */
    public $date_hold_begin;


    /**
     * @var mixed
     *
     */
    public $nameservers;


    /**
     *  @var \AppBundle\Soap\Entity\Product
     */
    public $product;


    /**
     * @var mixed
     *
     */
    public $services;

    /**
     * @var mixed
     */
    public $options;
    /**
     * @var \AppBundle\Soap\Entity\ContactInfos
     */
    public $ownerContact;

    /**
     * @var \AppBundle\Soap\Entity\User
     */
    public $user;

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Ndd constructor.
     * @param int $id
     * @param string $name
     * @param $status
     * @param $date_pending_delete_end
     * @param $date_updated
     * @param $date_delete
     * @param $date_hold_end
     * @param $fqdn
     * @param $date_registry_end
     * @param $authinfo
     * @param $date_registry_creation
     * @param $date_renew_begin
     * @param $tld
     * @param $date_created
     * @param $date_restore_end
     * @param $date_hold_begin
     * @param $nameservers
     * @param $product
     * @param $services
     * @param $options
     * @param $ownerContact
     * @param $user
     */
    public function __construct($id=null, $name=null, $status=null, $date_pending_delete_end=null, $date_updated=null, $date_delete=null,
                                $date_hold_end=null, $fqdn=null, $date_registry_end=null, $authinfo=null, $date_registry_creation=null, $date_renew_begin=null, $tld=null, $date_created=null,
                                $date_restore_end=null, $date_hold_begin=null,$nameservers=null,$product=null,$services=null,$options=null,$ownerContact=null,$user = null)
    {
        $this->id = $id;
        $this->name = $name;

        $this->status = $status;
        $this->date_pending_delete_end = $date_pending_delete_end;
        $this->date_updated = $date_updated;
        $this->date_delete = $date_delete;
        $this->date_hold_end = $date_hold_end;
        $this->fqdn = $fqdn;
        $this->date_registry_end = $date_registry_end;
        $this->authinfo = $authinfo;
        $this->date_registry_creation = $date_registry_creation;
        $this->date_renew_begin = $date_renew_begin;
        $this->tld = $tld;
        $this->date_created = $date_created;
        $this->date_restore_end = $date_restore_end;

        $this->date_hold_begin = $date_hold_begin;
        $this->nameservers=$nameservers;
        $this->product=$product;
        $this->services=$services;
        $this->options = $options;
        $this->ownerContact = $ownerContact;
        $this->user = $user;
        $this->productRenew = $productRenew;
    }





}
