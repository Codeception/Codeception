<?php

namespace Codeception\Module;

use \Codeception\Lib\Magento\App;
use \Codeception\Lib\Magento\Mock;

class Magento extends \Codeception\Module
{
    protected $app;
    protected $mock;

 	public function _initialize()
 	{
 		$this->getApp()->initMagento();
    }

    public function replaceByMock($type, $name, $mock)
    {
        $this->getMock()->replaceByMock($type, $name, $mock);
    }

    protected function getApp()
    {
        if (!$this->app) {
            $this->app = new App($this->config);
        }

        return $this->app;
    }

    protected function getMock()
    {
        if (!$this->mock) {
            $this->mock = new Mock($this->config);
        }

        return $this->mock;
    }
}
