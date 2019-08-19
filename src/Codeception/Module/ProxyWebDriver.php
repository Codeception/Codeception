<?php

namespace Codeception\Module;

class ProxyWebDriver extends WebDriver
{
    public function _initialize()
    {
        if (!$this->configKeyExists('webdriver_proxy')) {
            parent::_initialize();
            return;
        }

        $this->httpProxy = $this->config['webdriver_proxy'];
        if ($this->configKeyExists('webdriver_proxy_port')) {
            $this->httpProxyPort = $this->config['webdriver_proxy_port'];
        }

        parent::_initialize();
    }

    private function configKeyExists($key)
    {
        return key_exists($key, $this->config);
    }
}
