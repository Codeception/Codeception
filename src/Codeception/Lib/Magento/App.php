<?php

namespace Codeception\Lib\Magento;

use \Mage;

class App
{
	protected $config = array(
		'folder' => 'magento',
	);

	public function __construct(array $config)
	{
		$this->config = array_merge($this->config, $config);
	}

    public function initMagento()
    {
        if ($file = $this->getBaseDir('app/Mage.php')) {
            Mage::app(null, null, array('config_model' => '\Codeception\Lib\Magento\Config'));
            Mage::getConfig()->reinit();
        };

        return false;    	
    }

    public function resetMagento()
    {
        return Mage::reset();
    }

    protected function getBaseDir($file = null)
    {
        $baseDir = getcwd();
        $mageDir = $this->config['folder'];

        $file = "{$baseDir}/{$mageDir}/{$file}";

        if (file_exists($file)) {
            require_once $file;

            return $this;
        }

        return false;
    }
}