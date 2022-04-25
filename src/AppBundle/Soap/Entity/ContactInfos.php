<?php

namespace AppBundle\Soap\Entity;


/**
 * Contact
 */
class ContactInfos
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
    public $fakeEmail;



    /**
     * @var bool
     *
     */
    public $isDefault;

    /**
     *
     * @var \AppBundle\Soap\Entity\User
     */
    public $user;

    /**
     * @var string
     **/
    public $code;

    /**
     * ContactInfos constructor.
     * @param int $id
     * @param string $fakeEmail
     * @param string $isDefault
     * @param string $user
     */
    public function __construct($id, $fakeEmail, $isDefault, $user,$code)
    {
        $this->id = $id;
        $this->fakeEmail = $fakeEmail;
        $this->isDefault = $isDefault;
        $this->user = $user;
        $this->code = $code;
    }


}
