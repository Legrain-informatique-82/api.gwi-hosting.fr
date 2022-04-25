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

namespace GandiBundle\Entity;

/**
 * Class PackMailReturn
 */
class PackMailReturn{

    /**
     * @var \GandiBundle\Entity\AutorenewReturn
     */
    public $autorenew;
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
    public $id;
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

    function __construct($autorenew, $date_created, $date_end, $domain, $domain_id, $forward_quota, $id, $mailbox_quota, $status, $storage_quota)
    {
        $this->autorenew = $autorenew;
        $this->date_created = $date_created;
        $this->date_end = $date_end;
        $this->domain = $domain;
        $this->domain_id = $domain_id;
        $this->forward_quota = $forward_quota;
        $this->id = $id;
        $this->mailbox_quota = $mailbox_quota;
        $this->status = $status;
        $this->storage_quota = $storage_quota;
    }


}