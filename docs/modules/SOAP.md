# SOAP Module

## Actions


### dontSeeSoapResponseEquals


Checks XML response equals provided XML.
Comparison is done by canonicalizing both xml`s.

Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

 * param $xml


### dontSeeSoapResponseIncludes


Checks XML response does not include provided XML.
Comparison is done by canonicalizing both xml`s.
Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

 * param $xml


### haveSoapHeader


Prepare SOAP header.
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

 * param $header
 * param array $params


### seeResponseCodeIs


Checks response code from server.

 * param $code


### seeSoapResponseContainsStructure


Checks XML response contains provided structure.
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
This method doesn't use schema for validation.
This method dosn't require whole response XML to match the structure.

 * param $xml


### seeSoapResponseEquals


Checks XML response equals provided XML.
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

 * param $xml


### seeSoapResponseIncludes


Checks XML response includes provided XML.
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

 * param $xml


### sendSoapRequest


Submits request to endpoint.

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

 * param $request
 * param $body
