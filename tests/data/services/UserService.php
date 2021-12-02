<?php

class UserService
{
    /**
     * @var UserModel
     */
    protected UserModel $user;

    function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    function create($name): bool
    {
        $this->user->setName($name);
        $this->user->set('role','user');
        $this->user->set('email',$name.'@service.com');
        $this->user->save();
        return true;
    }

    public static function validateName($name): bool
    {
        return $name != 'admin';

    }

}
