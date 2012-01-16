<?php

class UserService
{
    /**
     * @var UserModel
     */
    protected $user;

    function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    function create($name)
    {
        $this->user->setName($name);
        $this->user->set('role','user');
        $this->user->set('email',$name.'@service.com');
        $this->user->save();
        return true;
    }

    public static function validateName($name)
    {
        if ($name == 'admin') return false;
        return true;
    }

}
