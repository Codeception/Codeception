# SOAP Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/SOAP.php)**
## Codeception\Module\SOAP

* *Extends* `Codeception\Module`

Module for testing SOAP WSDL web services.
Send requests and check if response matches the pattern.

This module can be used either with frameworks or PHPBrowser.
It tries to guess the framework is is attached to.
If a endpoint is a full url then it uses PHPBrowser.

### Using Inside Framework

Please note, that PHP SoapServer::handle method sends additional headers.
This may trigger warning: "Cannot modify header information"
If you use PHP SoapServer with framework, try to block call to this method in testing environment.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

## Configuration

* endpoint *required* - soap wsdl endpoint

## Public Properties

* request - last soap request (DOMDocument)
* response - last soap response (DOMDocument)



#### *public* client* `var`  \Symfony\Component\BrowserKit\Client
#### *public* is_functional
#### *public* xmlRequest* `var`  \DOMDocument
#### *public* xmlResponse* `var`  \DOMDocument
#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array


### haveSoapHeader
#### *public* haveSoapHeader($header, $params = null) Prepare SOAP header.
Receives header name and parameters as array.

Example:

``` php
<?php
$I->haveSoapHeader('AuthHeader', array('username' => 'davert', 'password' => '123345'));
```

Will produce header:

```
   <soapenv:Header>
     <SessionHeader>
     <AuthHeader>
         <username>davert</username>
         <password>12345</password>
     </AuthHeader>
  </soapenv:Header>
```

 * `param`  $header
 * `param`  array $params
### sendSoapRequest
#### *public* sendSoapRequest($action, $body = null) Submits request to endpoint.

Requires of api function name and parameters.
Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).

You are allowed to execute as much requests as you need inside test.

Example:

``` php
$I->sendRequest('UpdateUser', '<user><id>1</id><name>notdavert</name></user>');
$I->sendRequest('UpdateUser', \Codeception\Utils\Soap::request()->user
  ->id->val(1)->parent()
  ->name->val('notdavert');
```

 * `param`  $request
 * `param`  $body
### seeSoapResponseEquals
#### *public* seeSoapResponseEquals($xml) Checks XML response equals provided XML.
Comparison is done by canonicalizing both xml`s.

Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).

Example:

``` php
<?php
$I->seeSoapResponseEquals("<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope><SOAP-ENV:Body><result>1</result></SOAP-ENV:Envelope>");

$dom = new \DOMDocument();
$dom->load($file);
$I->seeSoapRequestIncludes($dom);

```

 * `param`  $xml
### seeSoapResponseIncludes
#### *public* seeSoapResponseIncludes($xml) Checks XML response includes provided XML.
Comparison is done by canonicalizing both xml`s.
Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

Example:

``` php
<?php
$I->seeSoapResponseIncludes("<result>1</result>");
$I->seeSoapRequestIncludes(\Codeception\Utils\Soap::response()->result->val(1));

$dom = new \DDOMDocument();
$dom->load('template.xml');
$I->seeSoapRequestIncludes($dom);
?>
```

 * `param`  $xml
### dontSeeSoapResponseEquals
#### *public* dontSeeSoapResponseEquals($xml) Checks XML response equals provided XML.
Comparison is done by canonicalizing both xml`s.

Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

 * `param`  $xml
### dontSeeSoapResponseIncludes
#### *public* dontSeeSoapResponseIncludes($xml) Checks XML response does not include provided XML.
Comparison is done by canonicalizing both xml`s.
Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

 * `param`  $xml
### seeSoapResponseContainsStructure
#### *public* seeSoapResponseContainsStructure($xml) Checks XML response contains provided structure.
Response elements will be compared with XML provided.
Only nodeNames are checked to see elements match.

Example:

``` php
<?php

$I->seeResponseContains("<user><query>CreateUser<name>Davert</davert></user>");
$I->seeSoapResponseContainsStructure("<query><name></name></query>");
?>
```

Use this method to check XML of valid structure is returned.
This method does not use schema for validation.
This method does not require path from root to match the structure.

 * `param`  $xml
### seeSoapResponseContainsXPath
#### *public* seeSoapResponseContainsXPath($xpath) Checks XML response with XPath locator

``` php
<?php
$I->seeSoapResponseContainsXPath('//root/user[ * `id=1]');` 
?>
```

 * `param`  $xpath
### dontSeeSoapResponseContainsXPath
#### *public* dontSeeSoapResponseContainsXPath($xpath) Checks XML response doesn't contain XPath locator

``` php
<?php
$I->dontSeeSoapResponseContainsXPath('//root/user[ * `id=1]');` 
?>
```

 * `param`  $xpath
### seeResponseCodeIs
#### *public* seeResponseCodeIs($code) Checks response code from server.

 * `param`  $code
### grabTextContentFrom
#### *public* grabTextContentFrom($cssOrXPath) Finds and returns text contents of element.
Element is matched by either CSS or XPath

 * `version`  1.1
 * `param`  $cssOrXPath
 * `return`  string
### grabAttributeFrom
#### *public* grabAttributeFrom($cssOrXPath, $attribute) Finds and returns attribute of element.
Element is matched by either CSS or XPath

 * `version`  1.1
 * `param`  $cssOrXPath
 * `param`  $attribute
 * `return`  string















































