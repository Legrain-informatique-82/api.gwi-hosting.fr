<?php

namespace AppBundle\Soap\Entity;


/**
 * Product
 *

 */
class Feature
{
    /**
     * @var string
     *
     */
    public $key;

    /**
     * @var string
     *
     */
    public $value;

    /**
     * Product constructor.
     * @param string $key
     * @param string $value
     */
    public function __construct($key,$value)
    {
        $this->key = $key;
        $this->value = $value;

    }


}
