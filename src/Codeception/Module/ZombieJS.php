<?php
/**
 * * Uses Mink to manipulate Zombie.js headless browser (http://zombie.labnotes.org/)
 * *
 * * Note, all methods take CSS selectors to fetch elements.
 * * For links, buttons, fields you can use names/values/ids of elements.
 * * For form fields you can use input[name=fieldname] notation.
 * *
 * * ## Installation
 * *
 * In order to talk with zombie.js server, you should install and configure zombie.js first:
 *
 * * Install node.js by following instructions from the official site: http://nodejs.org/.
 * * Install npm (node package manager) by following instructions from the http://npmjs.org/.
 * * Install zombie.js with npm:
 * ``` $ npm install -g zombie ```
 * After installing npm and zombie.js, you’ll need to add npm libs to your **NODE_PATH**. The easiest way to do this is to add:
 *
 * ``` export NODE_PATH="/PATH/TO/NPM/node_modules" ```
 * into your **.bashrc**.
 *
 * Don't forget to turn on Db repopulation if you are using database.
 *
 * ## Configuration
 *
 * * host - simply defines the host on which zombie.js will be started. It’s **127.0.0.1** by default.
 * * port - defines a zombie.js port. Default one is **8124**.
 * * node_bin - defines full path to node.js binary. Default one is just **node**
 * * script - defines a node.js script to start zombie.js server. If you pass a **null** the default script will be used. Use this option carefully!
 * * threshold - amount of microseconds for the process to wait (as of \Behat\Mink\Driver\Zombie\Server)
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 */
namespace Codeception\Module;

use \Behat\Mink\Driver as MinkDriver;

class ZombieJS extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array();
    protected $config = array(
        'host' => '127.0.0.1', 'port' => 8124,
        'node_bin' => null, 'script' => null,
        'threshold' => 20000000
    );
    /** @var \Behat\Mink\Driver\Zombie\Connection */
    protected $connection;
    public function _cleanup() {
        $this->connection = new MinkDriver\Zombie\Connection($this->config['host'],$this->config['port']);
        $driver  = new MinkDriver\ZombieDriver(
            $this->connection,
            null,false /*new MinkDriver\Zombie\Server(
                $this->config['host'],$this->config['port'],
                $this->config['node_bin'],   $this->config['script'],
                $this->config['threshold']
            )*/
        );
        $this->session = new \Behat\Mink\Session($driver);
        $this->session->start();
    }

    public function _failed(\Codeception\TestCase $test, $error) {
        $this->session->stop();
    }

    /**
     * @param string $url The URL to make HEAD request to
     * @return array Header-Name => Value array
     */
    public function headRequest($url){
        $headers = $this->connection->evalJS(sprintf(<<<JS
        var http = new browser.window.XMLHttpRequest();
        http.open('HEAD', '%s');
        http.onreadystatechange = function(){
            stream.end(http.getAllResponseHeaders());
        };
        http.send(null);
JS
            ,addslashes($url))
        );

        return http_parse_headers(str_replace("\n","\r\n",$headers));
    }
}
