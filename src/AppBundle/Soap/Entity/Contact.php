<?php

namespace AppBundle\Soap\Entity;


/**
 * Contact
 */
class Contact
{
    /**
     * @var integer
     *
     */
    public $id;

    /**
     * @var string
     *
     */
    public $email;

    /**
     * @var string
     *
     */
    public $name
    ;  /**
     * @var string
     *
     */
    public $codeFacturation;
    /**
     * @var string
     *
     */
    public $firstname;
    /**
     * @var string
     *
     */
    public $fakeEmail;

    /**
     * @var string
     *
     */
    public $code;

    /**
     * @var string
     *
     */
    public $isDefault;

    /**
     * Contact constructor.
     * @param int $id
     * @param string $email
     * @param string $fakeEmail
     * @param string $code
     * @param boolean $isDefault
     * @param string $name
     * @param string $firstname
     * @param string $codeFacturation
     */
    public function __construct($id, $email, $fakeEmail, $code, $isDefault,$name,$firstname,$codeFacturation=null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->fakeEmail = $fakeEmail;
        $this->code = $code;
        $this->isDefault = $isDefault;
        $this->name = $name;
        $this->firstname = $firstname;
        $this->codeFacturation = $codeFacturation;
    }


}
