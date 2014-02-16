<?php
require_once __DIR__ . '/TestsForWeb.php';

class FrameworksTest extends TestsForWeb
{
    /**
     * @var \Codeception\Lib\Framework
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\PhpSiteHelper();
    }


}
