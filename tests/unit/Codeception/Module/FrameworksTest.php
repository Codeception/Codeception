<?php
require_once __DIR__ . '/TestsForWeb.php';

class FrameworksTest extends TestsForWeb
{
    /**
     * @var \Codeception\Util\Framework
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\PhpSiteHelper();
    }


}
