<?php

namespace Behat\Mink\Driver\NodeJS\Server;

use Behat\Mink\Driver\NodeJS\Connection;
use Behat\Mink\Driver\NodeJS\Server;

class ZombieServer extends Server
{
    protected function doEvalJS(Connection $conn, $str, $returnType = 'js')
    {
        $result = null;
        switch ($returnType) {
            case 'js':
                $result = $conn->socketSend($str);
                break;
            case 'json':
                $result = json_decode($conn->socketSend("stream.end(JSON.stringify({$str}))"));
                break;
            default:
                break;
        }

        return $result;
    }

    protected function getServerScript()
    {
      return <<<'JS'
var net      = require('net')
  , zombie   = require('zombie')
  , browser  = null
  , pointers = []
  , buffer   = ""
  , host     = '%host%'
  , port     = %port%;

net.createServer(function (stream) {
  stream.setEncoding('utf8');
  stream.allowHalfOpen = true;

  stream.on('data', function (data) {
    buffer += data;
  });

  stream.on('end', function () {
    if (browser == null) {
      browser = new zombie.Browser();

      // Clean up old pointers
      pointers = [];
    }

    eval(buffer);
    buffer = "";
  });
}).listen(port, host, function() {
  console.log('server started on ' + host + ':' + port);
});
JS;
    }
}
