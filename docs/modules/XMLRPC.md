# XMLRPC Module

Module for testing XMLRPC WebService.

This module can be used either with frameworks or PHPBrowser.
It tries to guess the framework is is attached to.

Whether framework is used it operates via standard framework modules.
Otherwise sends raw HTTP requests to url via PHPBrowser.

## Requirements

* Module requires installed php_xmlrpc extension

## Configuration

* url *optional* - the url of api

## Public Properties

* headers - array of headers going to be sent.
* params - array of sent data
* response - last response (string)

 * available since version 1.1.5
 * author tiger.seo@gmail.com

## Actions


### haveHttpHeader


Sets HTTP header

 * param string $name
 * param string $value


### seeResponseCodeIs


Checks response code.

 * param $num


### seeResponseIsXMLRPC


Checks weather last response was valid XMLRPC.
This is done with xmlrpc_decode function.



### sendXMLRPCMethodCall


Sends a XMLRPC method call to remote XMLRPC-server.

 * param string $methodName
 * param array $parameters
