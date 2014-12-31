<?php

namespace Codeception\Lib\Magento;

use \Mage;
use \Codeception\Lib\Magento\Mock;

class Config extends \Mage_Core_Model_Config
{
	public function getModelInstance($modelClass = '', $arguments = array())
	{
		if ($model = $this->getModelMock($modelClass)) {
			return $model;	
		}

		return parent::getModelInstance($modelClass, $arguments);
	}

	public function getResourceModelInstance($modelClass = '', $arguments = array())
	{
		if ($model = $this->getModelMock($modelClass)) {
			return $model;	
		}

		return parent::getResourceModelInstance($modelClass, $arguments);
	}

	protected function getModelMock($modelClass)
	{
		$registryKey = Mock::MODEL_REGISTER_KEY . $modelClass;
		return Mage::registry($registryKey);
	}
}