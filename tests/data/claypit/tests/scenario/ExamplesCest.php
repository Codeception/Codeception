<?php
use Codeception\Example;

class ExamplesCest
{
    /**
     * @example(path=".", file="scenario.suite.yml")
     * @example(path=".", file="dummy.suite.yml")
     * @example(path=".", file="unit.suite.yml")
     */
    public function filesExistsAnnotation(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }


    /**
     * @example { "path":".", "file":"scenario.suite.yml" }
     * @example { "path":".", "file":"dummy.suite.yml" }
     * @example { "path":".", "file":"unit.suite.yml" }
     */
    public function filesExistsByJson(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    /**
     * @example [".", "scenario.suite.yml"]
     * @example [".", "dummy.suite.yml"]
     * @example [".", "unit.suite.yml"]
     */
    public function filesExistsByArray(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example[0]);
        $I->seeFileFound($example[1]);
    }

}