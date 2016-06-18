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
        $stub = Stub::make(Output::class);
        $this->messageFactory = new MessageFactory($stub);
    }


    public function testItCreatesMessageForComparisonFail()
    {
        $expectedDiff = $this->getExpectedDiff();
        $fail = $this->failure();
        $message = $this->messageFactory->prepareCompMessage($fail);

        $this->assertEquals($expectedDiff, (string) $message, 'The diff should be generated.');
    }

    /**
     * @return ComparisonFailure
     */
    protected function failure()
    {
        $leExpectedXml = <<<XML
<note>
    <to>Tove</to>
    <from>Jani</from>
    <heading>Reminder</heading>
    <body>Don't forget me this weekend!</body>
</note>
XML;

        $leActualXml = <<<XML
<note>
    <to>Tove</to>
    <from>Jani</from>
    <heading>Reminder
    </heading>
    <body>Don't forget me this weekend!</body>
</note>
XML;

        return new ComparisonFailure($leExpectedXml, $leActualXml, $leExpectedXml, $leActualXml);
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
