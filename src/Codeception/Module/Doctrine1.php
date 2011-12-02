<?php
namespace Codeception\Module;

class Doctrine1 extends \Codeception\Module
{
    public function _initialize() {

        if (isset(\Codeception\SuiteManager::$modules['\Codeception\Module\Db'])) {
            $dbh = \Codeception\SuiteManager::$modules['\Codeception\Module\Db']->_getDbh();
            \Doctrine_Manager::connection($dbh);
        }
    }

    
    public function _after(\Codeception\TestCase $test)
    {
        $this->tables = \Doctrine_Manager::connection()->getTables();
        foreach ($this->tables as $table) {
            foreach ($table->getRepository() as $record) {
                $record->clearRelated();
            }
            $table->getRepository()->evictAll();
            $table->clear();
        }
    }

    protected function proceedSeeInDatabase($model, $values = array())
    {
        $query = \Doctrine_Core::getTable($model)->createQuery();
        $string = array();
        foreach ($values as $key => $value) {
            $query->addWhere("$key = ?", $value);
            $string[] = "$key = '$value'";
        }
        return array('True', ($query->count() > 0), "$model with " . implode(', ', $string));
    }

    public function seeInDatabase($model, $values = array())
    {
        $res = $this->proceedSeeInDatabase($model, $values);
        $this->assert($res);
    }


    public function dontSeeInDatabase($model, $values = array())
    {
        $res = $this->proceedSeeInDatabase($model, $values);
        $this->assertNot($res);
    }

}
