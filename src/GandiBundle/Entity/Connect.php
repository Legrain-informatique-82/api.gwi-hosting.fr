<?php

namespace GandiBundle\Entity;


/**
 * Class Connect
 */
class Connect{

    /**
     * user
     * @var String
     */
    public $user;
    /**
     * password
     * @var String
     */
    public $password;

    /**
     *  new Connect
     * @api
     * @param String $user
     * @param String $password
     *
     */
    function __construct($user,$password){
        $this->user = $user;
        $this->password = $password;
    }

}
