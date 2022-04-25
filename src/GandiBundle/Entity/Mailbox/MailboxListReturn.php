<?php


namespace GandiBundle\Entity\Mailbox;


use GandiBundle\Entity\Mailbox\MailboxQuota;
use GandiBundle\Entity\Mailbox\MailboxResponder;
/**
 * Class MailboxListReturn
 */
class MailboxListReturn{
    /**
     * @var string
     */
    public $login;


    /**
     * @var \GandiBundle\Entity\Mailbox\MailboxQuota
     */
    public $quota;

    /**
     * @var \GandiBundle\Entity\Mailbox\MailboxResponder
     */
    public $responder;

    /**
     * @var bool
     */
    public $accountApiExist;
    public function __construct($login,$quota,$responder,$accountApiExist){
        $this->login=$login;
        $this->quota=$quota;
        $this->responder=$responder;
        $this->accountApiExist = $accountApiExist;
    }

}