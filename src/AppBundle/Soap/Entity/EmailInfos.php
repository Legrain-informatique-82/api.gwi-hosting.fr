<?php
/**
 *
Name	Type
autorenew	struct
date_created	dateTime.iso8601
date_end	dateTime.iso8601
domain	string
domain_id	int
forward_quota	int
The limit to the number of forwards
id	int
mailbox_quota	int
The limit to the number of mailboxes
status	string
storage_quota
 */

namespace AppBundle\Soap\Entity;


/**
 * Class EmailInfos
 */
class EmailInfos{


    /**
     * @var int
     */
    public $date_created;
    /**
     * @var int
     */
    public $date_end;
    /**
     * @var string
     */
    public $domain;
    /**
     * @var int
     */
    public $domain_id;
    /**
     * @var int
     */
    public $forward_quota;

    /**
     * @var int
     */
    public $mailbox_quota;
    /**
     * @var string
     */
    public $status;
    /**
     * @var int
     */
    public $storage_quota;

    /**
     * @var bool
     */
    public $packMailPro;


    /**
     * @var int
     */
    public $sizePackMailPro;

    function __construct( $date_created, $date_end, $domain, $domain_id, $forward_quota, $mailbox_quota, $status, $storage_quota,$packMailPro,$sizePackMailPro)
    {
        $this->date_created = $date_created;
        $this->date_end = $date_end;
        $this->domain = $domain;
        $this->domain_id = $domain_id;
        $this->forward_quota = $forward_quota;
        $this->mailbox_quota = $mailbox_quota;
        $this->status = $status;
        $this->storage_quota = $storage_quota;
        $this->packMailPro = $packMailPro;
        $this->sizePackMailPro = $sizePackMailPro;
    }


}