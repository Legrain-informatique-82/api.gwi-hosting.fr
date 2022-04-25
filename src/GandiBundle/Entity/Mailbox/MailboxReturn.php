<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 16/06/15
 * Time: 15:05
 */

namespace GandiBundle\Entity\Mailbox;


/**
 * Class MailboxReturn
 */
class MailboxReturn {

    /**
     * @var mixed
     */
    public $aliases;

    /**
     * @var string
     *     */
    public $fallback_email;
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
    /**
     * @var bool
     */
    public $createAccountApiIsPossible;

    public function __construct($aliases,$fallback_email,$login,$quota,$responder,$accountApiExist,$createAccountApiIsPossible){
        $this->aliases=$aliases;
        $this->fallback_email=$fallback_email;
        $this->login=$login;
        $this->quota=$quota;
        $this->responder=$responder;
        $this->accountApiExist=$accountApiExist;
        $this->createAccountApiIsPossible=$createAccountApiIsPossible;
    }
}