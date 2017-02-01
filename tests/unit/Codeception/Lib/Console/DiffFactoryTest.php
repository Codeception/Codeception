<?php
namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * DiffFactoryTest
 **/
class DiffFactoryTest extends \Codeception\Test\Unit
{
    /**
     * @var DiffFactory
     */
    protected $diffFactory;

    protected function setUp()
    {
        $this->diffFactory = new DiffFactory();
    }

    public function testItCreatesMessageForComparisonFailure()
    {
        $expectedDiff = $this->getExpectedDiff();
        $failure = $this->createFailure();
        $message = $this->diffFactory->createDiff($failure);

        $this->assertEquals($expectedDiff, (string) $message, 'The diff should be generated.');
    }

    /**
     * @return ComparisonFailure
     */
    protected function createFailure()
    {
        $expectedXml = <<<XML
<note>
    <to>Tove</to>
    <from>Jani</from>
    <heading>Reminder</heading>
    <body>Don't forget me this weekend!</body>
</note>
XML;

        $actualXml = <<<XML
<note>
    <to>Tove</to>
    <from>Jani</from>
    <heading>Reminder
    </heading>
    <body>Don't forget me this weekend!</body>
</note>
XML;

        return new ComparisonFailure($expectedXml, $actualXml, $expectedXml, $actualXml);
    }

    /**
     * @return string
     */
    protected function getExpectedDiff()
    {
        $expectedDiff = <<<TXT
@@ @@
 <note>
     <to>Tove</to>
     <from>Jani</from>
-    <heading>Reminder</heading>
+    <heading>Reminder
+    </heading>
     <body>Don't forget me this weekend!</body>
 </note>
TXT;

        return $expectedDiff . "\n";
    }
}
