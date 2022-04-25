<?php

namespace AppBundle\Soap\Entity;
use AppBundle\AppBundle;


/**
 * Hosting
 */
class Hosting
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $vhost;

    /**
     * @var \DateTime
     */
    public $dateEnding;

    /**
     * @var \AppBundle\Soap\Entity\ProductHosting
     */
    public $productHosting;

    /**
     * @var \AppBundle\Soap\Entity\User
     */
    public $user;
    /**
     * Hosting constructor.
     * @param int $id
     * @param string $vhost
     * @param \DateTime $dateEnding
     */
    public function __construct($id, $vhost =null, \DateTime $dateEnding,\AppBundle\Soap\Entity\ProductHosting $productHosting,\AppBundle\Soap\Entity\User $user)
    {
        $this->id = $id;
        $this->vhost = $vhost;
        $this->dateEnding = $dateEnding;
        $this->productHosting = $productHosting;
        $this->user = $user;
    }


}
