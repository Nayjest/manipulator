<?php
namespace Nayjest\Manipulator\Test\Mock;

class PersonStruct
{
    protected $email;

    public $name;
    public $age;
    public $gender;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}
