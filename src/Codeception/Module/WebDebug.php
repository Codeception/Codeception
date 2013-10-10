<?php
namespace Codeception\Module;

/**
 * This is a mini-module with helper actions to debug acceptance tests.
 * Use it with Selenium, Selenium2, ZombieJS, or PhpBrowser module.
 * Whenever none of this modules are connected the exception is thrown.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Configuration:
 *
 * * disable: false (optional) - stop making dumps and screenshots. Useful when you don't need debug anymore but you don't wanna change the code of your tests.
 *
 * ## Features
 *
 * * save screenshots of current page
 * * save html (xml, json) code of current page
 * * more to come...
 *
 * ## Example configuration
 *
 * ``` yaml
 *
 * class_name: WebGuy
 * modules:
 *      enabled:
 *          - Selenium
 *          - WebDebug # <-- this module
 *          - WebHelper
 *          - Db 
 *      config:
 *          Selenium:
 *              url: http://web.tenderway
 *              browser: firefox
 * ```
 *
 */


class WebDebug extends \Codeception\Module
{

    protected $test = null;
    protected $module = null;

    protected $config = array('disable' => false);

    private $fileOrderCounter = 0;

    public function _initialize()
    {
        foreach ($this->getModules() as $module) {
            if (method_exists($module, '_saveScreenshot')) {
                $this->module = $module;
                return;
            }
        }
        throw new \Codeception\Exception\ModuleConfig(__CLASS__,"couldn't connect to any of web manipulation modules.");
    }

    public function _before(\Codeception\TestCase $test) {
        $this->test = $test;
        $this->fileOrderCounter = 0;
   	}

    /**
     * Saves screenshot of browser window and saves to `_logs/debug/`
     *
     * Optionally you can provide a screenshot name.
     *
     * @param $name
     */
    public function makeAScreenshot($name = null)
    {
        if ($this->config['disable']) return;
        if (!method_exists($this->module,'_saveScreenshot')) {
            $this->debugSection('Warning',"Screenshot taking disabled for this backend. Source code will be saved instead");
            $this->makeAResponseDump($name);
            return;
        }
        $filename = $this->generateFilename($name);

        try {
            $this->module->_saveScreenshot($filename.'.png');
        } catch (\Exception $e) {
            $this->debugSection('Warning', "Screenshot couldn't be saved. HTML dump will be stored instead. ");
            $this->debug('Screenshot saving error:'. $e->getMessage());
        }

    }

    /**
     * Saves current response content to `_logs/debug/`
     * By default a response is treated as HTML, so all stored files will have html extension
     *
     * Optionally you can provide a dump name.
     *
     * @param $name
     */
    public function makeAResponseDump($name) {
        if ($this->config['disable']) return;
        $filename = $this->generateFilename($name);
        file_put_contents($filename.'.html', $this->module->session->getPage()->getContent());
    }


    protected function generateFilename($name = null)
    {
        $debugDir = \Codeception\Configuration::logDir().'debug';
        if (!is_dir($debugDir)) mkdir($debugDir, 0777);

        //make dir for screens
        //define screenshot name
        $this->fileOrderCounter++;

        $caseName = str_replace('Cept.php', '', $this->test->getFileName());
        $screenName = is_null($name) ? $caseName.' - '.$this->fileOrderCounter : $caseName.' - '.$this->fileOrderCounter.' - '.$name;
        return $debugDir . DIRECTORY_SEPARATOR . $screenName;
    }

}
