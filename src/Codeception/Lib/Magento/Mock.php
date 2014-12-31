<?php

namespace Codeception\Lib\Magento;

use \Mage;

class Mock
{
	const HELPER = 'helper';
	const HELPER_REGISTER_KEY = '_helper/';
	const MODEL = 'model';
	const MODEL_REGISTER_KEY = '_model/';

	public function replaceByMock($type, $name, $mock)
	{
		switch ($type) {
			case self::HELPER:
				return $this->replaceHelperByMock($name, $mock);
				break;
			case self::MODEL:
				return $this->replaceModelByMock($name, $mock);
				break;
		}	
	}

	protected function replaceHelperByMock($name, $mock)
	{
        $registryKey = self::HELPER_REGISTER_KEY . $name;
        return $this->registerMock($registryKey, $mock);
	}

	protected function replaceModelByMock($name, $mock)
	{
        $registryKey = self::MODEL_REGISTER_KEY . $name;
        return $this->registerMock($registryKey, $mock);
	}

	protected function registerMock($registryKey, $mock)
	{
        Mage::unregister($registryKey);
        Mage::register($registryKey, $mock);
          
        return Mage::registry($registryKey);
	}
}