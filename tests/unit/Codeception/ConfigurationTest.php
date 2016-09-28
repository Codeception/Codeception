<?php

use Codeception\Util\Stub;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->config = \Codeception\Configuration::config();
    }

    protected function tearDown()
    {
        \Codeception\Module\UniversalFramework::$includeInheritedActions = true;
        \Codeception\Module\UniversalFramework::$onlyActions = [];
        \Codeception\Module\UniversalFramework::$excludeActions = [];
    }

    /**
     * @group core
     */
    public function testSuites()
    {
        $suites = \Codeception\Configuration::suites();
        $this->assertContains('unit', $suites);
        $this->assertContains('cli', $suites);
    }

    /**
     * @group core
     */
    public function testFunctionForStrippingClassNames()
    {
        $matches = array();
        $this->assertEquals(1, preg_match('~\\\\?(\\w*?Helper)$~', '\\Codeception\\Module\\UserHelper', $matches));
        $this->assertEquals('UserHelper', $matches[1]);
        $this->assertEquals(1, preg_match('~\\\\?(\\w*?Helper)$~', 'UserHelper', $matches));
        $this->assertEquals('UserHelper', $matches[1]);
    }

    /**
     * @group core
     */
    public function testModules()
    {
        $settings = array('modules' => array('enabled' => array('EmulateModuleHelper')));
        $modules = \Codeception\Configuration::modules($settings);
        $this->assertContains('EmulateModuleHelper', $modules);
        $settings = array('modules' => array(
            'enabled' => array('EmulateModuleHelper'),
            'disabled' => array('EmulateModuleHelper'),
        ));
        $modules = \Codeception\Configuration::modules($settings);
        $this->assertNotContains('EmulateModuleHelper', $modules);
    }

    /**
     * @group core
     */
    public function testDefaultCustomCommandConfig()
    {
        $defaultConfig = \Codeception\Configuration::$defaultConfig;

        $this->assertArrayHasKey('extensions', $defaultConfig);

        $commandsConfig = $defaultConfig['extensions'];
        $this->assertArrayHasKey('commands', $commandsConfig);
    }

    /**
     * data provider for testGetRelativeDir
     * 
     * @return array(array(strings))
     */
    public function getRelativeDirTestData()
    {
        return [
            // Unix style paths:
                // projectDir() with & without trailing directory seperator: actual subdir
                    ['/my/proj/path/some/file/in/my/proj.txt',           '/my/proj/path/',               '/',  'some/file/in/my/proj.txt'],
                    ['/my/proj/path/some/file/in/my/proj.txt',           '/my/proj/path',                '/',  'some/file/in/my/proj.txt'],
                    ['/my/proj/pathsome/file/in/my/proj.txt',            '/my/proj/path',                '/',  '../pathsome/file/in/my/proj.txt'],
                // Case sensitive:
                    ['/my/proj/Path/some/file/in/my/proj.txt',           '/my/proj/path/',               '/',  '../Path/some/file/in/my/proj.txt'],
                    ['/my/Proj/path/some/file/in/my/proj.txt',           '/my/proj/path',                '/',  '../../Proj/path/some/file/in/my/proj.txt'],
                    ['My/proj/path/some/file/in/my/proj.txt',            'my/proj/path/foo/bar',         '/',  '../../../../../My/proj/path/some/file/in/my/proj.txt'],
                    ['/my/proj/path/some/file/in/my/proj.txt',           '/my/proj/Path/foobar/',        '/',  '../../path/some/file/in/my/proj.txt'],
                    ['/my/PROJ/path/some/dir/in/my/proj/',               '/my/proj/path/foobar/',        '/',  '../../../PROJ/path/some/dir/in/my/proj/'],
                // Absolute $path, Relative projectDir()
                    ['/my/proj/path/some/file/in/my/proj.txt',           'my/proj/path/',                '/',  '/my/proj/path/some/file/in/my/proj.txt'],
                    ['/My/proj/path/some/file/in/my/proj.txt',           'my/proj/path/',                '/',  '/My/proj/path/some/file/in/my/proj.txt'],
                // Relative $path, Absolute projectDir()
                    ['my/proj/path/some/file/in/my/proj.txt',            '/my/proj/path/',               '/',  'my/proj/path/some/file/in/my/proj.txt'],
                // $path & projectDir() both relative
                    ['my/proj/path/some/file/in/my/proj.txt',            'my/proj/path/foo/bar',         '/',  '../../some/file/in/my/proj.txt'],
                // $path & projectDir() both absolute: not a subdir
                    ['/my/proj/path/some/file/in/my/proj.txt',           '/my/proj/path/foobar/',        '/',  '../some/file/in/my/proj.txt'],
                // ensure trailing DIRECTORY_SEPERATOR maintained
                    ['/my/proj/path/some/dir/in/my/proj/',               '/my/proj/path/foobar',         '/',  '../some/dir/in/my/proj/'],
            // Windows style paths:
                // projectDir() with & without trailing directory seperator: actual subdir
                    ['C:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt', 'C:\\my\\proj\\path\\',         '\\', 'some\\file\\in\\my\\proj.txt'],
                    ['C:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt', 'C:\\my\\proj\\path',           '\\', 'some\\file\\in\\my\\proj.txt'],
                    ['C:\\my\\proj\\pathsome\\file\\in\\my\\proj.txt',   'C:\\my\\proj\\path',           '\\', '..\\pathsome\\file\\in\\my\\proj.txt'],
                // No device letter... absoluteness mismatch
                    ['\\my\\proj\\path\\some\\file\\in\\my\\proj.txt',   'my\\proj\\path\\',             '\\',  '\\my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['my\\proj\\path\\some\\file\\in\\my\\proj.txt',     '\\my\\proj\\path\\',           '\\',  'my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                // No device letter... absoluteness match
                    ['my\\proj\\path\\some\\file\\in\\my\\proj.txt',     'my\\proj\\path\\foo\\bar',     '\\',  '..\\..\\some\\file\\in\\my\\proj.txt'],
                    ['\\my\\proj\\path\\some\\file\\in\\my\\proj.txt',   '\\my\\proj\\path\\foobar\\',   '\\',  '..\\some\\file\\in\\my\\proj.txt'],
                    ['\\my\\proj\\path\\some\\dir\\in\\my\\proj\\',      '\\my\\proj\\path\\foobar\\',   '\\',  '..\\some\\dir\\in\\my\\proj\\'],
                // Device letter (both)... path absoluteness mismatch
                    ['C:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt', 'C:my\\proj\\path\\',           '\\',  '\\my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['d:my\\proj\\path\\some\\file\\in\\my\\proj.txt',   'd:\\my\\proj\\path\\',         '\\',  'my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                // Device letter (both)... path absoluteness match... case-insensitivity
                    ['E:my\\proj\\path\\some\\file\\in\\my\\proj.txt',   'E:my\\proj\\PATH\\foo\\bar',   '\\',  '..\\..\\some\\file\\in\\my\\proj.txt'],
                    ['f:\\my\\Proj\\path\\some\\file\\in\\my\\proj.txt', 'F:\\my\\proj\\path\\foobar\\', '\\',  '..\\some\\file\\in\\my\\proj.txt'],
                    ['A:\\MY\\proj\\path\\some\\dir\\in\\my\\proj\\',    'a:\\my\\proj\\path\\foobar\\', '\\',  '..\\some\\dir\\in\\my\\proj\\'],
                // Absoluteness mismatch
                    ['z:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt', 'my\\proj\\path\\',             '\\',  'z:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['Y:my\\proj\\path\\some\\file\\in\\my\\proj.txt',   '\\my\\proj\\path\\',           '\\',  'Y:my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['x:my\\proj\\path\\some\\file\\in\\my\\proj.txt',   'my\\proj\\path\\foo\\bar',     '\\',  'x:my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['P:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt', '\\my\\proj\\path\\foobar\\',   '\\',  'P:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['Q:\\my\\proj\\path\\some\\dir\\in\\my\\proj\\',    '\\my\\proj\\path\\foobar\\',   '\\',  'Q:\\my\\proj\\path\\some\\dir\\in\\my\\proj\\'],
                    ['\\my\\proj\\path\\some\\file\\in\\my\\proj.txt',   'm:my\\proj\\path\\',           '\\',  '\\my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['my\\proj\\path\\some\\file\\in\\my\\proj.txt',     'N:\\my\\proj\\path\\',         '\\',  'my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['my\\proj\\path\\some\\file\\in\\my\\proj.txt',     'o:my\\proj\\path\\foo\\bar',   '\\',  'my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['\\my\\proj\\path\\some\\file\\in\\my\\proj.txt',   'P:\\my\\proj\\path\\foobar\\', '\\',  '\\my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['\\my\\proj\\path\\some\\dir\\in\\my\\proj\\',      'q:\\my\\proj\\path\\foobar\\', '\\',  '\\my\\proj\\path\\some\\dir\\in\\my\\proj\\'],
                // Device letter mismatch
                    ['A:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt', 'B:my\\proj\\path\\',           '\\',  'A:\\my\\proj\\path\\some\\file\\in\\my\\proj.txt'],
                    ['c:my\\proj\\path\\some\\file\\in\\my\\proj.json',  'd:\\my\\proj\\path\\',         '\\',  'c:my\\proj\\path\\some\\file\\in\\my\\proj.json'],
                    ['M:my\\proj\\path\\foo.txt',                        'N:my\\proj\\path\\foo\\bar',   '\\',  'M:my\\proj\\path\\foo.txt'],
                    ['G:\\my\\proj\\path\\baz.exe',                      'C:\\my\\proj\\path\\foobar\\', '\\',  'G:\\my\\proj\\path\\baz.exe'],
                    ['C:\\my\\proj\\path\\bam\\',                        'G:\\my\\proj\\path\\foobar\\', '\\',  'C:\\my\\proj\\path\\bam\\'] ];
    }

    /**
     * @dataProvider getRelativeDirTestData
     * @group core
     */
    public function testGetRelativeDir($path, $projDir, $dirSep, $expectedOutput)
    {
        $relativeDir = \Codeception\Configuration::getRelativeDir($path, $projDir, $dirSep);
        $this->assertEquals($expectedOutput, $relativeDir);
    }

}
