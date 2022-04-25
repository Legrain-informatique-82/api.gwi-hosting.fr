<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 17/07/15
 * Time: 09:18
 */

namespace GandiBundle\Entity\Mailbox;


use GandiBundle\Entity\Mailbox\MailboxQuota;
use GandiBundle\Entity\Mailbox\MailboxResponder;

/**
 * Class MailboxListReturn
 */
class ForwardReturn{
    /**
     * @var mixed
     */
    public $destinations;


    /**
     * @var string
     */
    public $source;

    /**
     * ForwardReturn constructor.
     * @param $destinations
     * @param $source
     */
    public function __construct($destinations, $source)
    {
        $this->destinations = $destinations;
        $this->source = $source;
    }


}