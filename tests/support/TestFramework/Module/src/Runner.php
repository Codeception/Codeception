<?php
namespace TestFramework\Module\src;

use TestFramework\Module\src\Module\Executor\PhpBuiltIn;
use TestFramework\Module\src\Module\Module;
use TestFramework\Module\src\Module\Source\FileSystem;

class Runner
{
    /** @var Module[] */
    private $modules = array();

    public function __construct(array $config)
    {
        $this->modules = $this->buildModules($config);
    }

    public function start()
    {
        foreach ($this->modules as $module) {
            $module->start();
        }
        usleep(100000);
    }

    public function restart()
    {
        foreach ($this->modules as $module) {
            $module->restart();
        }
        usleep(100000);
    }

    public function kill()
    {
        foreach ($this->modules as $module) {
            $module->kill();
        }
    }

    /**
     * @param array $config
     * @return Module[]
     */
    private function buildModules(array $config)
    {
        $modules = [];
        foreach ($config as $moduleName => $moduleConfig) {
            $module = new Module($moduleName);

            if (!empty($moduleConfig['source'])) {
                $module->setPath(new FileSystem($moduleConfig['source']));
            }

            if (!empty($moduleConfig['executor'])) {
                switch ($moduleConfig['executor']['type']) {
                    case PhpBuiltIn::TYPE:
                        $module->setExecutor(new PhpBuiltIn($moduleConfig['executor']));
                        break;
                    default:
                        throw new \RuntimeException('Undefined executor type');
                }
            }

            $modules[] = $module;
        }
        return $modules;
    }
}

