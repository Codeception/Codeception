<?php

class UserControllerTest extends PHPUnit_Framework_TestCase
{
    protected function prepareController()
    {
        $controller = $this->getMock('UserController', array('render', 'render404'), null, false, false);
        $db = $this->getMock('DbConnector');
        $db->expects($this->any())
            ->method('find')
            ->will($this->returnCallback(function ($id)
        {
            return $id > 0 ? new User() : null;
        }));

        // connecting stubs together
        $r = new ReflectionObject($controller);
        $dbProperty = $r->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($controller, $db);

    }

    public function testShowForExistingUser()
    {
        $controller = $this->prepareController();
        $controller->expects($this->once())->method('render')->with($this->anything());
        $this->assertTrue($controller->show(1));
    }

    public function testShowForUnexistingUser()
    {
        $controller = $this->prepareController();
        $controller->expects($this->never())->method('render')->with($this->anything());
        $controller->expects($this->once())
            ->method('404')
            ->with($this->equalTo('User not found'));

        $this->assertNotEquals(true, $controller->show(0));
    }
}
