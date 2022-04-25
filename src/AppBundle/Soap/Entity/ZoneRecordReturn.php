<?php

namespace AppBundle\Soap\Entity;


/**
 * ZoneRecordReturn
 */
class ZoneRecordReturn
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $ttl;


    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $value;

    /**
     * ZoneRecordReturn constructor.
     * @param string $name
     * @param int $ttl
     * @param string $type
     * @param string $value
     */
    public function __construct($name, $ttl, $type, $value)
    {
        $this->name = $name;
        $this->ttl = $ttl;
        $this->type = $type;
        $this->value = $value;
    }


}
