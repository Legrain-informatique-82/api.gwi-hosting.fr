<?php

namespace AppBundle\Soap\Entity;


/**
 * WebRedir
 */
class WebRedir
{
    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     *
     */
    public $type;

    /**
     * @var string
     *
     */
    public $url;

    /**
     * WebRedir constructor.
     * @param string $host
     * @param string $type
     * @param string $url
     */
    public function __construct($host, $type, $url)
    {
        $this->host = $host;
        $this->type = $type;
        $this->url = $url;
    }


}
