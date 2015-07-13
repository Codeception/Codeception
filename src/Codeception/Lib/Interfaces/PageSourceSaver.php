<?php
namespace Codeception\Lib\Interfaces;

interface PageSourceSaver
{
    /**
     * Saves page source of to a file
     *
     * ```php
     * $this->getModule('{{MODULE_NAME}}')->_savePageSource(codecept_output_dir().'page.html');
     * ```
     * @api
     * @param $filename
     */
    public function _savePageSource($filename);
}
