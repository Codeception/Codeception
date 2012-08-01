<?php
require_once __DIR__.'/../app/data.php';

class UserModel
{
    protected $id;
    protected $data = array();
    protected $saved = false;

    public function getName()
    {
        return $this->data['name'];
    }

    function setName($name)
    {
        $this->data['name'] = 'Mr. '.$name;
    }

    function getId()
    {
        return $this->id;
    }

    function get($param)
    {
        if (!isset($this->data[$param])) throw new \Exception('Key does not exists!');
        return $this->data[$param];
    }

    function set($param, $value)
    {
        $this->data[$param] = $value;
    }

    function save()
    {
        if (!$this->id) $this->id = uniqid();
        data::set($this->id, $this->data);
        $this->saved = true;
        return true;
    }
}
