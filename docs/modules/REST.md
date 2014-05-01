# REST Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/REST.php)**
## Codeception\Module\REST

* *Extends* `Codeception\Module`

Module for testing REST WebService.

This module can be used either with frameworks or PHPBrowser.
It tries to guess the framework is is attached to.

Whether framework is used it operates via standard framework modules.
Otherwise sends raw HTTP requests to url via PHPBrowser.

## Status

* Maintainer: **tiger-seo**, **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua
* Contact: tiger.seo@gmail.com

## Configuration

* url *optional* - the url of api
* timeout *optional* - the maximum number of seconds to allow cURL functions to execute

### Example

    modules:
       enabled: [REST]
       config:
          REST:
             url: 'http://serviceapp/api/v1/'
             timeout: 90

## Public Properties

* headers - array of headers going to be sent.
* params - array of sent data
* response - last response (string)



#### *public* client* `var`  \Symfony\Component\HttpKernel\Client|\Symfony\Component\BrowserKit\Client|\Behat\Mink\Driver\Goutte\Client
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

 * `param`  $name
 * `param`  $value
### seeHttpHeader
#### *public* seeHttpHeader($name, $value = null)Checks over the given HTTP header and (optionally)
its value, asserting that are there

 * `param`  $name
 * `param`  $value
### dontSeeHttpHeader
#### *public* dontSeeHttpHeader($name, $value = null)Checks over the given HTTP header and (optionally)
its value, asserting that are not there

 * `param`  $name
 * `param`  $value
### seeHttpHeaderOnce
#### *public* seeHttpHeaderOnce($name)Checks that http response header is received only once.
HTTP RFC2616 allows multiple response headers with the same name.
You can check that you didn't accidentally sent the same header twice.

``` php
<?php
$I->seeHttpHeaderOnce('Cache-Control');
?>>
```

 * `param`  $name
### grabHttpHeader
#### *public* grabHttpHeader($name, $first = null)Returns the value of the specified header name

 * `param`  $name
 * `param`  Boolean $first  Whether to return the first value or all header values

 * `return`  string|array The first header value if $first is true, an array of values otherwise
### amHttpAuthenticated
#### *public* amHttpAuthenticated($username, $password)Adds HTTP authentication via username/password.

 * `param`  $username
 * `param`  $password
### amDigestAuthenticated
#### *public* amDigestAuthenticated($username, $password)s Digest authentication via username/password.

ram $username
ram $password
### sendPOST
#### *public* sendPOST($url, $params = null, $files = null)Sends a POST request to given uri.

Parameters and files (as array of filenames) can be provided.

 * `param`  $url
 * `param`  array $params
 * `param`  array $files
### sendHEAD
#### *public* sendHEAD($url, $params = null)Sends a HEAD request to given uri.

 * `param`  $url
 * `param`  array $params
### sendOPTIONS
#### *public* sendOPTIONS($url, $params = null)Sends an OPTIONS request to given uri.

 * `param`  $url
 * `param`  array $params
### sendGET
#### *public* sendGET($url, $params = null)Sends a GET request to given uri.

 * `param`  $url
 * `param`  array $params
### sendPUT
#### *public* sendPUT($url, $params = null, $files = null)Sends PUT request to given uri.

 * `param`  $url
 * `param`  array $params
 * `param`  array $files
### sendPATCH
#### *public* sendPATCH($url, $params = null, $files = null)Sends PATCH request to given uri.

 * `param`        $url
 * `param`  array $params
 * `param`  array $files
### sendDELETE
#### *public* sendDELETE($url, $params = null, $files = null)Sends DELETE request to given uri.

 * `param`  $url
 * `param`  array $params
 * `param`  array $files

### sendLINK
#### *public* sendLINK($url, array $linkEntries)Sends LINK request to given uri.

 * `param`        $url
 * `param`  array $linkEntries (entry is array with keys "uri" and "link-param")

 * `link`  http://tools.ietf.org/html/rfc2068#section-19.6.2.4

 * `author`  samva.ua * `gmail.com`
### sendUNLINK
#### *public* sendUNLINK($url, array $linkEntries)Sends UNLINK request to given uri.

 * `param`        $url
 * `param`  array $linkEntries (entry is array with keys "uri" and "link-param")

 * `link`  http://tools.ietf.org/html/rfc2068#section-19.6.2.4

 * `author`  samva.ua * `gmail.com`


### seeResponseIsJson
#### *public* seeResponseIsJson()Checks whether last response was valid JSON.
This is done with json_last_error function.
### seeResponseIsXml
#### *public* seeResponseIsXml()Checks whether last response was valid XML.
This is done with libxml_get_last_error function.
### seeResponseContains
#### *public* seeResponseContains($text)Checks whether the last response contains text.

 * `param`  $text
### dontSeeResponseContains
#### *public* dontSeeResponseContains($text)Checks whether last response do not contain text.

 * `param`  $text
### seeResponseContainsJson
#### *public* seeResponseContainsJson($json = null)Checks whether the last JSON response contains provided array.
The response is converted to array with json_decode($response, true)
Thus, JSON is represented by associative array.
This method matches that response array contains provided array.

Examples:

``` php
<?php
// response: {name: john, email: john * `gmail.com}` 
$I->seeResponseContainsJson(array('name' => 'john'));

// response {user: john, profile: { email: john * `gmail.com`  }}
$I->seeResponseContainsJson(array('email' => 'john * `gmail.com'));` 

?>
```

This method recursively checks if one array can be found inside of another.

 * `param`  array $json
### grabResponse
#### *public* grabResponse()Returns current response so that it can be used in next scenario steps.

Example:

``` php
<?php
$user_id = $I->grabResponse();
$I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
?>
```

 * `version`  1.1
 * `return`  string
### grabDataFromJsonResponse
#### *public* grabDataFromJsonResponse($path)Returns data from the current JSON response using specified path
so that it can be used in next scenario steps

Example:

``` php
<?php
$user_id = $I->grabDataFromJsonResponse('user.user_id');
$I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
?>
```

 * `param`  string $path

 * `since`  1.1.2
 * `return`  string

 * `author`  tiger.seo * `gmail.com`


### dontSeeResponseContainsJson
#### *public* dontSeeResponseContainsJson($json = null)Opposite to seeResponseContainsJson

 * `param`  array $json
### seeResponseEquals
#### *public* seeResponseEquals($response)Checks if response is exactly the same as provided.

 * `param`  $response
### seeResponseCodeIs
#### *public* seeResponseCodeIs($code)Checks response code equals to provided value.

 * `param`  $code
### dontSeeResponseCodeIs
#### *public* dontSeeResponseCodeIs($code)Checks that response code is not equal to provided value.

 * `param`  $code






































