<?php
namespace Codeception\Lib\Console;

use Codeception\Util\Stub;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * MessageFactoryTest
 **/
class MessageFactoryTest extends \Codeception\Test\Unit
{
    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    protected function setUp()
    {
        /**
         * @var Output $stub
         */
        $stub = Stub::make('\Codeception\Lib\Console\Output');
        $this->messageFactory = new MessageFactory($stub);
    }

    public function testItCreatesMessageForComparisonFailure()
    {
        $expectedDiff = $this->getExpectedDiff();
        $failure = $this->createFailure();
        $message = $this->messageFactory->prepareCompMessage($failure);

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
- Expected | + Actual
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
