<?php
namespace TestFramework\Module\src;

class Modules
{
    /** @var Runner */
    private $modulesRunner = null;

    public function setUp($config)
    {
        $this->modulesRunner = new Runner($config);
        $this->modulesRunner->start();
        return true;
    }

    public function tearDown()
    {
        $this->modulesRunner->kill();
    }
}
