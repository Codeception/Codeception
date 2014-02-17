<?php
namespace Codeception\Lib\Actor\Shared;

trait Friend
{
    protected $friends = [];
    /**
     * @param $name
     * @return Friend
     */
    public function haveFriend($name)
    {
        if (!isset($this->friends[$name])) {
            $this->friends[$name] = new \Codeception\Lib\Friend($name, $this);
        }
        return $this->friends[$name];
    }

}