<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;
use Exception;

use function codecept_debug;
use function ucfirst;
use function usleep;

class Retry extends Assertion implements GeneratedStep
{
    protected static string $methodTemplate = <<<EOF

    /**
     * [!] Method is generated.
     * 
     * {{doc}}
     * 
     * Retry number and interval set by \$I->retry();
     *
     * @see \{{module}}::{{method}}()
     */
    public function {{action}}({{params}}) {
        \$retryNum = isset(\$this->retryNum) ? \$this->retryNum : 1;
        \$retryInterval = isset(\$this->retryInterval) ? \$this->retryInterval : 200;
        return \$this->getScenario()->runStep(new \Codeception\Step\Retry('{{method}}', func_get_args(), \$retryNum, \$retryInterval));
    }
EOF;

    public function __construct($action, array $arguments, private int $retryNum, private int $retryInterval)
    {
        $this->action = $action;
        $this->arguments = $arguments;
    }

    public function run(ModuleContainer $container = null)
    {
        $retry = 0;
        $interval = $this->retryInterval;
        while (true) {
            try {
                $this->isTry = $retry < $this->retryNum;
                return parent::run($container);
            } catch (Exception $e) {
                ++$retry;
                if (!$this->isTry) {
                    throw $e;
                }
                codecept_debug("Retrying #{$retry} in {$interval}ms");
                usleep($interval * 1000);
                $interval *= 2;
            }
        }
    }

    public static function getTemplate(Template $template): ?Template
    {
        $action = $template->getVar('action');

        if ((str_starts_with($action, 'have')) || (str_starts_with($action, 'am'))) {
            return null; // dont retry conditions
        }

        if (str_starts_with($action, 'wait')) {
            return null; // dont retry waiters
        }

        $doc = "* Executes {$action} and retries on failure.";

        return (new Template(self::$methodTemplate))
            ->place('method', $template->getVar('method'))
            ->place('module', $template->getVar('module'))
            ->place('params', $template->getVar('params'))
            ->place('doc', $doc)
            ->place('action', 'retry' . ucfirst($action));
    }
}
