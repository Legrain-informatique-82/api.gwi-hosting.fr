<?php

namespace AppBundle\Soap\Entity;


/**
 * DomainZoneVersion
 */
class DomainZoneVersion
{
    /**
     * @var int
     */
    public $idVersion;

    /**
     * @var \AppBundle\Soap\Entity\ZoneRecordReturn[]
     */
    public $versions;

    /**
     * DomainZone constructor.
     * @param int $idVersion
     * @param  $versions
     */
    public function __construct($idVersion, $versions)
    {
        $this->idVersion = $idVersion;
        $this->versions = $versions;
    }


}
