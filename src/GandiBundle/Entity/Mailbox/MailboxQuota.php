<?php


namespace GandiBundle\Entity\Mailbox;


/**
 * Class MailboxQuota
 */
class MailboxQuota{
    /**
     * @var string
     */
    public $granted;
    /**
     * @var string
     */
    public $used;

    public function __construct($granted,$used){
        $this->granted=$granted;
        $this->used=$used;
    }

}