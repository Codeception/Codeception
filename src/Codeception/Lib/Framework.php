<?php

namespace Codeception\Lib;

/**
 * Abstract module for PHP frameworks connected via Symfony BrowserKit components
 * Each framework is connected with it's own connector defined in \Codeception\Lib\Connector
 * Each module for framework should extend this class.
 *
 */
abstract class Framework extends InnerBrowser
{
    /**
     * Returns a list of recognized domain names
     *
     * @return array
     */
    protected function getInternalDomains()
    {
        return [];
    }

    public function _beforeSuite($settings = [])
    {
        /**
         * reset internal domains before suite, because each suite can have a different configuration
         */
        $this->internalDomains = null;
    }

}
