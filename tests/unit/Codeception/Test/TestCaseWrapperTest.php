<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Test\TestCaseWrapper;
use Codeception\Test\Unit;
use Codeception\Test\Descriptor;

#[Group('testCaseWrapper')]
final class TestCaseWrapperTest extends Unit
{
    #[Group('core')]
    public function testNamings()
    {
        $test = new TestCaseWrapper($this);

        $this->assertSame(__FILE__, $test->getFileName());

        $path = codecept_relative_path(__DIR__) . DIRECTORY_SEPARATOR;
        $this->assertSame($path . 'TestCaseWrapperTest.php', Descriptor::getTestFileName($test));

        $this->assertSame(
            $path . 'TestCaseWrapperTest.php:testNamings',
            Descriptor::getTestFullName($test)
        );

        $this->assertSame(
            'TestCaseWrapperTest:testNamings',
            Descriptor::getTestSignature($test)
        );

        $this->assertSame(['testCaseWrapper', 'core'], $test->getMetadata()->getGroups());
    }
}
