# REST Module

Module for testing REST WebService.

This module can be used either with frameworks or PHPBrowser.
It tries to guess the framework is is attached to.

Whether framework is used it operates via standard framework modules.
Otherwise sends raw HTTP requests to url via PHPBrowser.

## Configuration

* url *optional* - the url of api

## Public Properties

* headers - array of headers going to be sent.
* params - array of sent data
* response - last response (string)



## Actions


### amHttpAuthenticated


Adds HTTP authentication via username/password.

 * param $username
 * param $password


### dontSeeResponseContains


Checks weather last response do not contain text.

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


### seeResponseCodeIs


Checks response code.

 * param $num


### seeResponseContains


Checks weather the last response contains text.

 * param $text


### seeResponseContainsJson


Checks weather the last JSON response contains provided array.
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


Checks weather last response was valid JSON.
This is done with json_last_error function.



### sendDELETE


Sends DELETE request to given uri.

 * param $url
 * param array $params
 * param array $files


### sendGET


Sends a GET request to given uri.

 * param $url
 * param array $params


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
