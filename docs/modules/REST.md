# REST Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/REST.php)**

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



## Actions

### amDigestAuthenticated

s Digest authentication via username/password.

ram $username
ram $password

### amHttpAuthenticated

Adds HTTP authentication via username/password.

 * param $username
 * param $password

### dontSeeHttpHeader

Checks over the given HTTP header and (optionally)
its value, asserting that are not there

 * param $name
 * param $value

### dontSeeResponseCodeIs

Checks that response code is not equal to provided value.

 * param $code

### dontSeeResponseContains

Checks whether last response do not contain text.

 * param $text

### dontSeeResponseContainsJson

Opposite to seeResponseContainsJson

 * param array $json

### grabDataFromJsonResponse

Returns data from the current JSON response using specified path
so that it can be used in next scenario steps

Example:

``` php
<?php
$user_id = $I->grabDataFromJsonResponse('user.user_id');
$I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
?>
```

 * param string $path

 * available since version 1.1.2
 * return string

 * author tiger.seo@gmail.com

### grabHttpHeader

Returns the value of the specified header name

 * param $name
 * param Boolean $first  Whether to return the first value or all header values

 * return string|array The first header value if $first is true, an array of values otherwise

### grabResponse

Returns current response so that it can be used in next scenario steps.

Example:

``` php
<?php
$user_id = $I->grabResponse();
$I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
?>
```

 * version 1.1
 * return string

### haveHttpHeader

Sets HTTP header

 * param $name
 * param $value

### seeHttpHeader

Checks over the given HTTP header and (optionally)
its value, asserting that are there

 * param $name
 * param $value

### seeHttpHeaderOnce

Checks that http response header is received only once.
HTTP RFC2616 allows multiple response headers with the same name.
You can check that you didn't accidentally sent the same header twice.

``` php
<?php
$I->seeHttpHeaderOnce('Cache-Control');
?>>
```

 * param $name

### seeResponseCodeIs

Checks response code equals to provided value.

 * param $code

### seeResponseContains

Checks whether the last response contains text.

 * param $text

### seeResponseContainsJson

Checks whether the last JSON response contains provided array.
The response is converted to array with json_decode($response, true)
Thus, JSON is represented by associative array.
This method matches that response array contains provided array.

Examples:

``` php
<?php
// response: {name: john, email: john@gmail.com}
$I->seeResponseContainsJson(array('name' => 'john'));

// response {user: john, profile: { email: john@gmail.com }}
$I->seeResponseContainsJson(array('email' => 'john@gmail.com'));

?>
```

This method recursively checks if one array can be found inside of another.

 * param array $json

### seeResponseEquals

Checks if response is exactly the same as provided.

 * param $response

### seeResponseIsJson

Checks whether last response was valid JSON.
This is done with json_last_error function.


### seeResponseIsXml

Checks whether last response was valid XML.
This is done with libxml_get_last_error function.


### sendDELETE

Sends DELETE request to given uri.

 * param $url
 * param array $params
 * param array $files

### sendGET

Sends a GET request to given uri.

 * param $url
 * param array $params

### sendHEAD

Sends a HEAD request to given uri.

 * param $url
 * param array $params

### sendLINK

Sends LINK request to given uri.

 * param       $url
 * param array $linkEntries (entry is array with keys "uri" and "link-param")

 * link http://tools.ietf.org/html/rfc2068#section-19.6.2.4

 * author samva.ua@gmail.com

### sendOPTIONS

Sends an OPTIONS request to given uri.

 * param $url
 * param array $params

### sendPATCH

Sends PATCH request to given uri.

 * param       $url
 * param array $params
 * param array $files

### sendPOST

Sends a POST request to given uri.

Parameters and files (as array of filenames) can be provided.

 * param $url
 * param array $params
 * param array $files

### sendPUT

Sends PUT request to given uri.

 * param $url
 * param array $params
 * param array $files

### sendUNLINK

Sends UNLINK request to given uri.

 * param       $url
 * param array $linkEntries (entry is array with keys "uri" and "link-param")

 * link http://tools.ietf.org/html/rfc2068#section-19.6.2.4

 * author samva.ua@gmail.com

