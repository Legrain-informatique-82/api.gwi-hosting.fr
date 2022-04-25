<?php

namespace AppBundle\Soap\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 */
class CGU
{
    /**
     * @var integer
     *
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $url;

    /**
     * CGU constructor.
     * @param int $id
     * @param string $name
     * @param string $content
     * @param string $url
     */
    public function __construct($id, $name, $content, $url)
    {
        $this->id = $id;
        $this->name = $name;
        $this->content = $content;
        $this->url = $url;
    }

}
