<?php

use Codeception\Test\Unit;
use Codeception\Util\Stub as Stub;

class StubWorkerTest extends Unit
{
    /**
     * @var \Codeception\Module\StubWorker
     */
    protected $module;

    public function setUp()
    {
        $this->module = Stub::make('\Codeception\Module\StubWorker');
        $this->module->_initialize();
        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Cest'));
    }

    public function test_if_stub_can_be_loaded()
    {
        $replacements = [];

        $stub = $this->module->loadAndPrepareStub('stubs/stub1.json', $replacements);

        $this->assertNotEmpty($stub);
        $this->assertJson($stub);

        $this->assertContains('{{book-title}}', $stub);
        $this->assertContains('{{book-author}}', $stub);
        $this->assertContains('{{book-year}}', $stub);
    }

    public function test_if_stub_can_be_loaded_and_placeholders_are_replaced()
    {
        $replacements = [
            'book-author' => 'JRR Tolkien',
            'book-title' => 'Lord of the Rings',
            'book-year' => 1954,
        ];

        $stub = $this->module->loadAndPrepareStub('stubs/stub1.json', $replacements);

        $this->assertNotEmpty($stub);
        $this->assertJson($stub);

        $this->assertNotContains('{{book-title}}', $stub);
        $this->assertNotContains('{{book-author}}', $stub);
        $this->assertNotContains('{{book-year}}', $stub);

        $this->assertContains('JRR Tolkien', $stub);
        $this->assertContains('Lord of the Rings', $stub);
        $this->assertContains('1954', $stub);
    }

    public function test_if_stub_can_be_loaded_and_placeholders_are_replaced_with_custom_patterns()
    {
        $replacements = [
            'book-author' => 'JRR Tolkien',
            'book-title' => 'Lord of the Rings',
            'book-year' => 1954,
        ];

        $stub = $this->module->loadAndPrepareStub('stubs/stub2.json', $replacements, '##', '++');

        $this->assertNotEmpty($stub);
        $this->assertJson($stub);

        $this->assertNotContains('{{book-title}}', $stub);
        $this->assertNotContains('{{book-author}}', $stub);
        $this->assertNotContains('{{book-year}}', $stub);

        $this->assertContains('JRR Tolkien', $stub);
        $this->assertContains('Lord of the Rings', $stub);
        $this->assertContains('1954', $stub);
    }

    public function test_if_exception_is_thrown_when_missing_stub_is_loaded()
    {
        $this->expectException(InvalidArgumentException::class);
        $stub = $this->module->loadAndPrepareStub('stubs/missing.stub', []);
    }

}
