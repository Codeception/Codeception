# XMLRPC Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/XMLRPC.php)**
## Codeception\Module\XMLRPC

* *Extends* `Codeception\Module`

Module for testing XMLRPC WebService.

This module can be used either with frameworks or PHPBrowser.
It tries to guess the framework is is attached to.

Whether framework is used it operates via standard framework modules.
Otherwise sends raw HTTP requests to url via PHPBrowser.

## Requirements

* Module requires installed php_xmlrpc extension

## Status

* Maintainer: **tiger-seo**
* Stability: **beta**
* Contact: tiger.seo@gmail.com

## Configuration

* url *optional* - the url of api

## Public Properties

* headers - array of headers going to be sent.
* params - array of sent data
* response - last response (string)

@since 1.1.5
@author tiger.seo@gmail.com

#### *public* client* `var`  \Symfony\Component\BrowserKit\Client
#### *public* is_functional
#### *public* headers
#### *public* params
#### *public* response
#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array




### haveHttpHeader
#### *public* haveHttpHeader($name, $value)Sets HTTP header

 * `param`  string $name
 * `param`  string $value
### seeResponseCodeIs
#### *public* seeResponseCodeIs($num)Checks response code.

 * `param`  $num
### seeResponseIsXMLRPC
#### *public* seeResponseIsXMLRPC()Checks weather last response was valid XMLRPC.
This is done with xmlrpc_decode function.
### sendXMLRPCMethodCall
#### *public* sendXMLRPCMethodCall($methodName, $parameters = null)Sends a XMLRPC method call to remote XMLRPC-server.

 * `param`  string $methodName
 * `param`  array $parameters





































