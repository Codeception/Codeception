<?php
namespace Codeception\Lib\Connector;

class Phalcon2 extends Phalcon1
{
}

class Phalcon2MemorySession extends Phalcon1MemorySession
{
    /**
     * @inheritdoc
     * Due to difference of version 1.3.x
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);
    }
}
