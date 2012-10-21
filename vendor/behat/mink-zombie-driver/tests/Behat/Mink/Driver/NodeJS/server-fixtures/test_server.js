var net = require('net');

net.createServer(function (stream) {

  // This server does nothing.

}).listen(8124, '127.0.0.1');

console.log('Server running at 127.0.0.1:8124');

