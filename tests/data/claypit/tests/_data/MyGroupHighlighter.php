<?php

class MyGroupHighlighter extends \Codeception\Platform\Group {

    static $group = 'notorun';

    public function _before(\Codeception\Event\Test $e)
    {
        $this->writeln("======> Entering NoGroup Test Scope");

    }

    public function _after(\Codeception\Event\Test $e)
    {
        $this->writeln("<====== Ending NoGroup Test Scope");
    }


}
