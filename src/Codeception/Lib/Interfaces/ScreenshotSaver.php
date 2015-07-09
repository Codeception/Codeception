<?php
namespace Codeception\Lib\Interfaces;

interface ScreenshotSaver
{
    /**
     * Saves screenshot of current page to a file
     *
     * ```php
     * $this->getModule('{{MODULE_NAME}}')->_saveScreenshot(codecept_output_dir().'screenshot_1.png');
     * ```
     * @api
     * @param $filename
     */
    public function _saveScreenshot($filename);
}
