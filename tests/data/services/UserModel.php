<?php

class UserModel
{
    protected ?string $id = null;

    protected array $data = [];

    protected bool $saved = false;

    public function getName()
    {
        return $this->data['name'];
    }

    public function setName($name)
    {
        $this->data['name'] = 'Mr. ' . $name;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function get($param)
    {
        if (!isset($this->data[$param])) {
            throw new \Exception('Key does not exist!');
        }

        return $this->data[$param];
    }

    public function set($param, $value)
    {
        $this->data[$param] = $value;
    }

    public function save(): bool
    {
        if (!$this->id) {
            $this->id = uniqid();
        }

        data::set($this->id, $this->data);
        $this->saved = true;
        return true;
    }
}
