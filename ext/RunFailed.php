<?php
namespace Codeception\Extension;

use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

/**
 * Saves failed tests into tests/_output/failed in order to rerun failed tests.
 *
 * To rerun failed tests just run the `failed` group:
 *
 * ```
 * php codecept run -g failed
 * ```
 *
 * To change failed group name add:
 * ```
 * --override "extensions: config: Codeception\Extension\RunFailed: fail-group: another_group1"
 * ```
 * Remember: if you run tests and they generated custom-named fail group, to run this group, you should add override too
 *
 * Starting from Codeception 2.1 **this extension is enabled by default**.
 *
 * ``` yaml
 * extensions:
 *     enabled: [Codeception\Extension\RunFailed]
 * ```
 *
 * On each execution failed tests are logged and saved into `tests/_output/failed` file.
 */
class RunFailed extends Extension
{
    public static $events = [
        Events::RESULT_PRINT_AFTER => 'saveFailed'
    ];

    /** @var string filename/groupname for failed tests */
    protected $group = 'failed';

    public function _initialize()
    {
        if (array_key_exists('fail-group', $this->config) && $this->config['fail-group']) {
            $this->group = $this->config['fail-group'];
        }
        $logPath = str_replace($this->getRootDir(), '', $this->getLogDir()); // get local path to logs
        $this->_reconfigure(['groups' => [$this->group => $logPath . $this->group]]);
    }

    public function saveFailed(PrintResultEvent $e)
    {
        $file = $this->getLogDir() . $this->group;
        $result = $e->getResult();
        if ($result->wasSuccessful()) {
            if (is_file($file)) {
                unlink($file);
            }
            return;
        }
        $output = [];
        foreach ($result->failures() as $fail) {
            $output[] = $this->localizePath(Descriptor::getTestFullName($fail->failedTest()));
        }
        foreach ($result->errors() as $fail) {
            $output[] = $this->localizePath(Descriptor::getTestFullName($fail->failedTest()));
        }

        file_put_contents($file, implode("\n", $output));
    }

    protected function localizePath($path)
    {
        $root = realpath($this->getRootDir()) . DIRECTORY_SEPARATOR;
        if (substr($path, 0, strlen($root)) == $root) {
            return substr($path, strlen($root));
        }
        return $path;
    }
}
