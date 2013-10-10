<?php
namespace Codeception\Module;

/**
 * Uses Mink to manipulate Zombie.js headless browser (http://zombie.labnotes.org/)
 *
 * Note, all methods take CSS selectors to fetch elements.
 * For links, buttons, fields you can use names/values/ids of elements.
 * For form fields you can use input[name=fieldname] notation.
 *
 * <div class="alert alert-info">
 * To use this module with Composer you need <em>"behat/mink-zombie-driver": "1.1.*"</em> package.
 * This module is considered <strong>deprecated</strong> and will be replaced with WebDriver module.
 * </div>
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
 * ``` $ npm install -g zombie@1```
 *
 * After installing npm and zombie.js, you’ll need to add npm libs to your **NODE_PATH**. The easiest way to do this is to add:
 *
 * ``` export NODE_PATH="/PATH/TO/NPM/node_modules" ```
 * into your **.bashrc**.
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
            if(http.readyState==4){
                stream.end('HTTP/1.0 '+http.status+' '+http.statusText+'\\n'+http.getAllResponseHeaders());
            }
        };
        http.send(null);
JS
            ,addslashes($url))
        );

        if(class_exists('\Guzzle\Parser\Message\MessageParser'))
        {
            $p = new \Guzzle\Parser\Message\MessageParser();
            $parts = $p->parseResponse($headers);
            return $parts['headers'];
        }
        else{
            throw new \Exception("Could not parse response headers. Please install Guzzle");
        }
    }
}
