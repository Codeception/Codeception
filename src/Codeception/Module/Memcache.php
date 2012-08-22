<?php
namespace Codeception\Module;

/**
 * Connects to [memcached](http://www.memcached.org/) using [PECL](http://www.php.net/manual/en/intro.memcache.php) extension.
 * Performs a cleanup by flushing all values after each test run.
 *
 * ## Configuration
 *
 * * host: localhost - memcached host to connect
 * * port: 11211 - default memcached port.
 *
 * Be sure you don't use the production server to connect.
 *
 * ## Public Properties
 *
 * * memcache - instance of Memcache object
 *
 */

class Memcache extends \Codeception\Module
{
    /**
     * @var \Memcache
     */
    public $memcache = null;

    protected $config = array('host' => 'localhost', 'port' => 11211);

    public function _before()
    {
        $this->memcache = new \Memcache;
        $this->memcache->connect($this->config['host'], $this->config['port']);
    }

    public function _after()
    {
        $this->memcache->flush();
        $this->memcache->close();
    }

    /**
     * Grabs value from memcached by key
     *
     * Example:
     *
     * ``` php
     * <?php
     * $users_count = $I->grabValueFromMemcached('users_count');
     * ?>
     * ```
     *
     * @param $key
     * @return array|string
     */
    public function grabValueFromMemcached($key)
    {
        $value = $this->memcache->get($key);
        $this->debugSection("Value", $value);
        return $value;
    }

    /**
     * Checks item in Memcached is the same as expected.
     *
     * @param $key
     * @param $value
     */
    public function seeInMemcached($key, $value = null)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);
        $this->assertEquals($value, $actual);
    }

    public function dontSeeInMemcached($key, $value = null)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);
        $this->assertNotEquals($value, $actual);
    }

    public function clearMemcache()
    {
        $this->memcache->flush();
    }

}
