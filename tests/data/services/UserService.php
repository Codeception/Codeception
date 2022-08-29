<?php

class UserService
{
    public function __construct(protected UserModel $user)
    {
    }

    public function create($name): bool
    {
        $this->user->setName($name);
        $this->user->set('role', 'user');
        $this->user->set('email', $name . '@service.com');
        $this->user->save();
        return true;
    }

    public static function validateName($name): bool
    {
        return $name != 'admin';
    }
}
