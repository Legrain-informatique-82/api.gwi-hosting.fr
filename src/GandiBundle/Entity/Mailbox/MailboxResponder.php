<?php


namespace GandiBundle\Entity\Mailbox;


/**
 * Class MailboxResponder
 */
class MailboxResponder{
    /**
     * @var bool
     */
    public $active;
    /**
     * @var string
     */
    public $text;

    /**
     * @var \DateTime
     */
    public $initDate;

    /**
     * @var \DateTime
     */
    public $endDate;

    public function __construct($active,$text=null,$initDate=null,$endDate=null){
        $this->active=$active;
        $this->text=$text;
        $this->initDate=$initDate;
        $this->endDate = $endDate;
    }

}