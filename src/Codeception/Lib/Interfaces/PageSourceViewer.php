<?php
namespace Codeception\Lib\Interfaces;

interface PageSourceViewer
{
    /**
     * Returns current page source code.
     *
     * ```php
     * $this->getModule('{{MODULE_NAME}}')->_getPageSource();
     * ```
     * @api
     * @return string Current page source code.
     */
    public function _getPageSource();
}
