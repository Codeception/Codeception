<?php
namespace Codeception\Module;

/**
 * Uses Mink to manipulate Zombie.js headless browser (http://zombie.labnotes.org/)
 *
 * Note, all methods take CSS selectors to fetch elements.
 * For links, buttons, fields you can use names/values/ids of elements.
 * For form fields you can use input[name=fieldname] notation.
 *
 * ## Status
 *
 * * Maintainer: **synchrone**
 * * Stability: **stable**
 * * Contact: https://github.com/synchrone
 * * relies on [Mink](http://mink.behat.org)
 *
 *
 * ## Installation
 *
 * In order to talk with zombie.js server, you should install and configure zombie.js first:
 *
 * * Install node.js by following instructions from the official site: http://nodejs.org/.
 * * Install npm (node package manager) by following instructions from the http://npmjs.org/.
 * * Install zombie.js with npm:
 * ``` $ npm install -g zombie@0.13.0 @```
 * Note: Behat/Mink states that there are compatibility issues with zombie > 0.13, and their manual
 * says to install version 0.12.15, BUT it has some bugs, so you'd rather install 0.13
 *
 * After installing npm and zombie.js, you’ll need to add npm libs to your **NODE_PATH**. The easiest way to do this is to add:
 *
 * ``` export NODE_PATH="/PATH/TO/NPM/node_modules" ```
 * into your **.bashrc**.
 *
 * Also note that this module requires php5-http PECL extension to parse returned headers properly
 *
 * Don't forget to turn on Db repopulation if you are using database.
 *
 * ## Configuration
 *
 * * url  *required*- url of your site
 * * host - simply defines the host on which zombie.js will be started. It’s **127.0.0.1** by default.
 * * port - defines a zombie.js port. Default one is **8124**.
 * * node_bin - defines full path to node.js binary. Default one is just **node**
 * * script - defines a node.js script to start zombie.js server. If you pass a **null** the default script will be used. Use this option carefully!
 * * threshold - amount of milliseconds (1/1000 of second) for the process to wait  (as of \Behat\Mink\Driver\Zombie\Server)
 * * autostart - whether zombie.js should be started automatically. Defaults to **true**
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules:
 *        enabled: [ZombieJS]
 *        config:
 *           ZombieJS:
 *              url: 'http://localhost/'
 *              host: '127.0.0.1'
 *              port: 8124
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 */

use Behat\Mink\Mink,
    Behat\Mink\Session,
    Behat\Mink\Driver\ZombieDriver,
    Behat\Mink\Driver\NodeJS\Server\ZombieServer;

class ZombieJS extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('url');
    protected $config = array(
        'host' => '127.0.0.1', 'port' => 8124,
        'node_bin' => null, 'script' => null,
        'threshold' => 20000,
        'autostart' => true
    );

    /** @var Session */
    public $session;

    /** @var ZombieServer */
    protected $server;

    /** @var ZombieDriver */
    protected $driver;

    public function _initialize()
    {
        $this->server = new ZombieServer(
            $this->config['host'],$this->config['port'],
            $this->config['node_bin'],$this->config['script'],
            $this->config['threshold'] * 1000
        );

        $this->driver = new ZombieDriver($this->server);
        $this->session = new Session($this->driver);
        parent::_initialize();
    }

    public function _failed(\Codeception\TestCase $test, $error) {
        $this->_after($test);
    }

    public function _after(\Codeception\TestCase $test) {
        //that call does not really terminate node process
        //@see https://github.com/symfony/symfony/issues/5499
        $this->session->stop();

        //so we kill it ourselves
        exec('killall '.pathinfo($this->server->getNodeBin(),PATHINFO_BASENAME).' > /dev/null 2>&1');
    }

    public function _getUrl() {
        return 'http://'.$this->config['host'].':'.$this->config['port'];
    }

    /**
     * @param string $url The URL to make HEAD request to
     * @return array Header-Name => Value array
     */
    public function headRequest($url){
        $headers = $this->server->evalJS(sprintf(<<<JS
        var http = new browser.window.XMLHttpRequest();
        http.open('HEAD', '%s');
        http.onreadystatechange = function(){
            stream.end(http.getAllResponseHeaders());
        };
        http.send(null);
JS
            ,addslashes($url))
        );

        if (method_exists('\http\Header', 'parse')) {
            return \http\Header::parse(str_replace("\n","\r\n",$headers));
        } else {
            return http_parse_headers(str_replace("\n","\r\n",$headers));
        }
    }
}
