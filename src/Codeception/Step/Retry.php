<?php

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;

class Retry extends Assertion implements GeneratedStep
{

    protected static $methodTemplate = <<<EOF
    
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

    private $retryNum;
    private $retryInterval;

    public function __construct($action, array $arguments = [], $retryNum, $retryInterval)
    {
        $this->action = $action;
        $this->arguments = $arguments;
        $this->retryNum = $retryNum;
        $this->retryInterval = $retryInterval;
    }

    public function run(ModuleContainer $container = null)
    {
        $retry = 0;
        $interval = $this->retryInterval;
        while (true) {
            try {
                return parent::run($container);
            } catch (\Exception $e) {
                $retry++;
                if ($retry > $this->retryNum) {
                    throw $e;
                }
                codecept_debug("Retrying #$retry in ${interval}ms");
                usleep($interval * 1000);
                $interval *= 2;
            }
        }
    }

    public static function getTemplate(Template $template)
    {
        $action = $template->getVar('action');

        if ((strpos($action, 'have') === 0) || (strpos($action, 'am') === 0)) {
            return; // dont retry conditions
        }

        if (strpos($action, 'wait') === 0) {
            return; // dont retry waiters
        }

        $doc = "* Executes $action and retries on failure.";

        return (new Template(self::$methodTemplate))
            ->place('method', $template->getVar('method'))
            ->place('module', $template->getVar('module'))
            ->place('params', $template->getVar('params'))
            ->place('doc', $doc)
            ->place('action', 'retry'. ucfirst($action));
    }
}
