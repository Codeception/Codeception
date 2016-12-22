<?php
namespace TestFramework\Module\src\Module\Source;

class FileSystem implements PathInterface
{
    private $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getPath()
    {
        if ($this->config['type'] != 'path') {
            return '';
        }

        if ($this->config['path'][0] !== '/') {
            return PROJECT_ROOT . '/' . $this->config['path'];
        }

        return $this->config['path'];
    }
}