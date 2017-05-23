<?php

namespace Tests\Codeception\Command\Shared;

use Codeception\Command\Shared\FileSystem;

class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testSave()
    {
        //be sure to have a unique filename
        $filename = sys_get_temp_dir() . '/MyClass.'.uniqid().'.php' ;
        $fileSystem = new MyClassUsingFileSystemTrait();
        $content = '1234';

        $result = $this->invokeMethod($fileSystem, 'save', [$filename, $content]);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($filename));
        $this->assertEquals('1234', file_get_contents($filename));
    }

    public function testSaveIfFileExists()
    {
        //be sure to have a unique filename
        $filename = sys_get_temp_dir() . '/MyClass.'.uniqid().'.php' ;
        $fileSystem = new MyClassUsingFileSystemTrait();
        $content = '1234';

        //the file already exists
        touch($filename);

        $result = $this->invokeMethod($fileSystem, 'save', [$filename, $content]);

        $this->assertFalse($result);
        $this->assertNotEquals('1234', file_get_contents($filename));
    }

    public function testForcedSaveIfFileExists()
    {
        //be sure to have a unique filename
        $filename = sys_get_temp_dir() . '/MyClass.'.uniqid().'.php' ;
        $fileSystem = new MyClassUsingFileSystemTrait();
        $content = '1234';

        //the file already exists
        touch($filename);

        $result = $this->invokeMethod($fileSystem, 'save', [$filename, $content, true]);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($filename));
        $this->assertEquals('1234', file_get_contents($filename));
    }
}

class MyClassUsingFileSystemTrait
{
    use FileSystem;
}