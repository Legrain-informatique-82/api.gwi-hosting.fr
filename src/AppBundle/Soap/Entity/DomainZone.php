<?php

namespace AppBundle\Soap\Entity;


/**
 * DomainZone
 */
class DomainZone
{
    /**
     * @var int
     */
    public $idVersionActive;

    /**
     * @var \AppBundle\Soap\Entity\DomainZoneVersion[]
     */
    public $versions;

    /**
     * DomainZone constructor.
     * @param int $idVersionActive
     * @param \AppBundle\Soap\Entity\DomainZoneVersion[] $versions
     */
    public function __construct($idVersionActive, $versions)
    {
        $this->idVersionActive = $idVersionActive;
        $this->versions = $versions;
    }


}
