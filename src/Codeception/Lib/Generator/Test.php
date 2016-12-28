<?php
namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Test
{
    use Namespaces;
    use Shared\Classname;

    protected $template = <<<EOF
<?php

{{namespace}}

use {{actorClass}};
use Codeception\TestCase\Test;

/**
 * Tests {{name}}.
 *
 * @author
 * @package {{namespace}}
 */
class {{name}}Test extends Test
{
    /**
     * Tester class instance that may be used to access all module methods.
     *
     * @type {{actorClass}}
     */
    protected \${{actor}};

    // utility methods

    /**
     * This method is called only once before any test is run. You may use it to
     * run heavy single-time preparations, in most cases you should prefer
     * using `_before()`.
     *
     * @return void
     */
    // public static function setUpBeforeClass() {}

    /**
     * This method is called only once after all tests have been run. You may
     * use it to perform complete cleanup, but in most cases you should prefer
     * `_after()` over this method.
     *
     * Please note that there are occasions when this method may not be run at
     * all: https://github.com/sebastianbergmann/phpunit/issues/1295
     *
     * @return void
     */
    // public static function tearDownAfterClass() {}

    /**
     * This method will be run before every test. You will probably want to use
     * it to create virtual file system, create expected environment, etc.
     *
     * Please note that there are occasions when this method may not be run at
     * all: https://github.com/sebastianbergmann/phpunit/issues/1295
     *
     * @return void
     */
    protected function _before()
    {
    }

    /**
     * This method will be run after every test. Feel free to use it to free up
     * any allocated resources, tear down virtual filesystems and perform
     * cleanups.
     *
     * @return void
     */
    protected function _after()
    {
    }

    // data providers

    // tests

    public function testMe()
    {

    }
}

EOF;

    protected $settings;
    protected $name;

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $this->removeSuffix($name, 'Test');
    }

    public function produce()
    {
        $actor = $this->settings['class_name'];
        if ($this->settings['namespace']) {
            $actor = $this->settings['namespace'] . '\\' . $actor;
        }

        $ns = $this->getNamespaceString($this->settings['namespace'] . '\\' . $this->name);

        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('name', $this->getShortClassName($this->name))
            ->place('actorClass', $actor)
            ->place('actor', lcfirst(Configuration::config()['actor']))
            ->produce();
    }

}
