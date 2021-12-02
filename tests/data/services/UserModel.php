<?php
require_once __DIR__.'/../app/data.php';

class UserModel
{
    protected ?string $id = null;
    protected array $data = array();
    protected bool $saved = false;

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
        if (!isset($this->data[$param])) throw new \Exception('Key does not exist!');
        return $this->data[$param];
    }

    function set($param, $value)
    {
        $this->data[$param] = $value;
    }

    function save(): bool
    {
        if (!$this->id) $this->id = uniqid();
        data::set($this->id, $this->data);
        $this->saved = true;
        return true;
    }
}
