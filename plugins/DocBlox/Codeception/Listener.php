<?php
class DocBlox_Plugin_Codeception_Listener extends DocBlox_Plugin_ListenerAbstract
{

    protected $source;

    /**
     * Append Codeception docs.
     *
     * @docblox-event transformer.transform.pre
     *
     * @param sfEvent $data
     *
     * @return void
     */
    public function applyBehaviours(sfEvent $data)
    {
        require_once 'Codeception/autoload.php';

        $config = \Codeception\Configuration::config();

        $xml = $data['source'];
        $this->source = $xml;

        $xpath = new DOMXPath($xml);
        $classes = $xpath->query(
            '/project/file/class/full_name'
        );

        $testsPerClass = $this->getTestsPerClass($config);

        $testedClasses = array_keys($testsPerClass);

        foreach ($classes as $class) {
            $class = $class->textContent;
            if (in_array($class, $testedClasses)) $this->processClass($testsPerClass[$class]);
        }
    }

    protected function getTestsPerClass($config)
    {
        $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $suites = \Codeception\Configuration::suites();

        $testedClasses = array();

        foreach ($suites as $suite) {
            $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);
            $suiteManager = new \Codeception\SuiteManager($dispatcher, $suite, $suiteconf);

            $suiteManager->loadTests();
            $tests = $suiteManager->getSuite()->tests();

            foreach ($tests as $test) {

                if (!($test instanceof \Codeception\TestCase\Cest)) continue;

                $class = $test->getCoveredClass();
                if (!$class) continue;

                isset($testedClasses[$class])
                        ? $testedClasses[$class][] = $test
                        : $testedClasses[$class] = array($test);

            }
        }
        return $testedClasses;
    }

    protected function processClass($tests)
    {
        $class = $tests[0]->getCoveredClass();

        $class_description = '';

        $xpath = new DOMXPath($this->source);

        foreach ($tests as $test) {
            $test->loadScenario();
            if ($test->getFeature()) $class_description .= "\nCan ".$test->getFeature();
            $method = $test->getCoveredMethod();
            if (!$method) continue;

            $nodes = $xpath->query(sprintf('/project/file/class[full_name="%s"]/method[name="%s"]/docblock/long-description',$class, $method));
            if (empty($nodes)) continue;

            $node = $nodes->item(0);
            if (!$node) continue;
            $text = $this->getScenarioText($test, $class, $method);
            $text = nl2br("<p>$text</p>");
            $node->nodeValue .= "<h4>Usage in Codeception test</h4><p>Taken from <strong>{$test->getFileName()}</strong></p>".$text;
        }

        if (!$class_description) return;
        $nodes = $xpath->query(sprintf('/project/file/class[full_name="%s"]/docblock/long-description', $class));
        if (empty($nodes)) return;
        $node = $nodes->item(0);
        if (!$node) return;

        $node->nodeValue .= "<h4>Specification</h4>".nl2br($class_description);

    }

    protected  function getScenarioText($test, $class, $method)
    {
        $steps = $test->getScenario()->getSteps();

        $text = "";

        $used_objects = array();
        $declared_vars = array();

        foreach ($steps as $step) {

            $args = $step->getArguments();


            $args = array_map(function ($a) use (&$used_objects, &$declared_vars) {
                if (!is_string($a) and is_callable($a, true)) return 'lambda function';
                if (is_object($a)) {
                    if ($key = array_search(spl_object_hash($a), $used_objects)) {
                        return $key;
                    }
                    $hash = spl_object_hash($a);
                    $suffix = count($used_objects)+1;
                    $class = $classname = isset($a->__mocked) ? $a->__mocked : get_class($a);
                    $namespaces = explode('\\', $class);
                    $class = end($namespaces);
                    $var = '<span style="color: #28A107">$'.lcfirst($class).$suffix.'</span>';
                    $used_objects[$var] = $hash;
                    $declared_vars[] = $var." ($classname)";
                    return $var;
                }
                return $a;

            }, $args);

            if (in_array($step->getAction(), array('executeTestedMethodOn'))) {
                $stub = array_shift($args);
            }

            switch (count($args)) {
                case 0: $args = ''; break;
                case 1: $args = '"' . $args[0] . '"'; break;
                default: $args = stripcslashes(json_encode($args));
            }

            $args = '<span color="green">' .trim($args,'[]') .'</span>';

            if (in_array($step->getAction(), array('haveStub','haveFakeClass','testMethod'))) continue;
            if ($step->getAction() == 'execute') {
                $text .= "I execute code defined in test\n";
                continue;
            }

            if (in_array($step->getAction(), array('executeTestedMethod', 'executeTestedMethodWith'))) {
                $text .= "If I execute <span style=\"background: #ddd;\">$class::$method($args)</span>\n";
                continue;
            }

            if (in_array($step->getAction(), array('executeTestedMethodOn'))) {
                $text .= "If I execute <span style=\"background: #ddd;\">$stub->$method($args)</span>\n";
                continue;
            }


            if ($step->getName() == 'Comment') {
                $text .= "\nI ".$this->getStepAction($step)." $args\n";
                continue;
            }

            if ($step->getName() == 'Assertion') {
                $text .= "I will ".$this->getStepAction($step)." $args\n";
                continue;
            }
            $text .= "I ".$this->getStepAction($step)." $args\n";

        }

        if (count($declared_vars)) {
            $declared_vars = "<strong>Declared Variables:</strong><ul><li>".implode("</li><li>", $declared_vars)."</li></ul>";
            $text = $declared_vars.$text;
        }

        if ($test->getFeature()) return $text = 'With this method I can  ' . $test->getFeature() . "\n\n" . $text."\n";
        return $text;
    }

    protected function getStepAction($step)
    {
        return '<span style="color: #732E81;">' . $step->getHumanizedActionWithoutArguments() .'</span>';
    }

}