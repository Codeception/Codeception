# SOAP Module

Module for testing SOAP WSDL web services.
Send requests and check if response matches the pattern.

THis module can be used either with frameworks or PHPBrowser.
It tries to guess the framework is is attached to.
If a endpoint is a full url then it uses PHPBrowser.

### Using Inside Framework
Please note, that PHP SoapServer::handle method sends additional headers.
This may trigger warning: "Cannot modify header information"
If you use PHP SoapServer with framework, try to block call to this method in testing environment.

## Configuration

* endpoint *required* - soap wsdl endpoint

## Public Properties

* request - last soap request
* response - last soap response


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


### seeSoapResponseEquals


Checks XML response equals provided XML.
Comparison is done by canonicalizing both xml`s.

Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).

 * param $xml


### seeSoapResponseIncludes


Checks XML response includes provided XML.
Comparison is done by canonicalizing both xml`s.
Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

 * param $xml


### sendSoapRequest


Submits request to endpoint.

Requires of api function name and parameters.
Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).

Example:

``` php
$I->sendRequest('UpdateUser', '<user><id>1</id><name>notdavert</name></user>');
```

 * param $request
 * param $body
