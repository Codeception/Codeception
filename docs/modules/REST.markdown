---
layout: doc
title: Codeception - Documentation
---

# REST Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/REST.php)**


Module for testing REST WebService.

This module can be used either with frameworks or PHPBrowser.
It tries to guess the framework is is attached to.

Whether framework is used it operates via standard framework modules.
Otherwise sends raw HTTP requests to url via PHPBrowser.

### Status

* Maintainer: **tiger-seo**, **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua
* Contact: tiger.seo@gmail.com

### Configuration

* url *optional* - the url of api
* timeout *optional* - the maximum number of seconds to allow cURL functions to execute

#### Example

    modules: 
       enabled: [REST]
       config:
          REST:
             url: 'http://serviceapp/api/v1/' 
             timeout: 90

### Public Properties

* headers - array of headers going to be sent.
* params - array of sent data
* response - last response (string)



### Actions


#### amDigestAuthenticated


s Digest authentication via username/password.

ram $username
ram $password


#### amHttpAuthenticated


Adds HTTP authentication via username/password.

 * param $username
 * param $password


#### dontSeeResponseContains


Checks weather last response do not contain text.

 * param $text


#### dontSeeResponseContainsJson


Opposite to seeResponseContainsJson

 * param array $json


#### grabDataFromJsonResponse


Returns data from the current JSON response using specified path
so that it can be used in next scenario steps

Example:

{% highlight php %}

<?php
$user_id = $I->grabDataFromJsonResponse('user.user_id');
$I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
?>

{% endhighlight %}

 * param string $path

 * available since version 1.1.2
 * return string

 * author tiger.seo@gmail.com


#### grabResponse


Returns current response so that it can be used in next scenario steps.

Example:

{% highlight php %}

<?php
$user_id = $I->grabResponse();
$I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
?>

{% endhighlight %}

 * version 1.1
 * return string


#### haveHttpHeader


Sets HTTP header

 * param $name
 * param $value


#### seeResponseCodeIs


Checks response code.

 * param $num


#### seeResponseContains


Checks weather the last response contains text.

 * param $text


#### seeResponseContainsJson


Checks weather the last JSON response contains provided array.
The response is converted to array with json_decode($response, true)
Thus, JSON is represented by associative array.
This method matches that response array contains provided array.

Examples:

{% highlight php %}

<?php
// response: {name: john, email: john@gmail.com}
$I->seeResponseContainsJson(array('name' => 'john'));

// response {user: john, profile: { email: john@gmail.com }}
$I->seeResponseContainsJson(array('email' => 'john@gmail.com'));

?>

{% endhighlight %}

This method recursively checks if one array can be found inside of another.

 * param array $json


#### seeResponseEquals


Checks if response is exactly the same as provided.

 * param $response


#### seeResponseIsJson


Checks weather last response was valid JSON.
This is done with json_last_error function.



#### sendDELETE


Sends DELETE request to given uri.

 * param $url
 * param array $params
 * param array $files


#### sendGET


Sends a GET request to given uri.

 * param $url
 * param array $params


#### sendLINK


Sends LINK request to given uri.

 * param       $url
 * param array $linkEntries (entry is array with keys "uri" and "link-param")

 * link http://tools.ietf.org/html/rfc2068#section-19.6.2.4

 * author samva.ua@gmail.com


#### sendPATCH


Sends PATCH request to given uri.

 * param       $url
 * param array $params
 * param array $files


#### sendPOST


Sends a POST request to given uri.

Parameters and files (as array of filenames) can be provided.

 * param $url
 * param array $params
 * param array $files


#### sendPUT


Sends PUT request to given uri.

 * param $url
 * param array $params
 * param array $files


#### sendUNLINK


Sends UNLINK request to given uri.

 * param       $url
 * param array $linkEntries (entry is array with keys "uri" and "link-param")

 * link http://tools.ietf.org/html/rfc2068#section-19.6.2.4

 * author samva.ua@gmail.com
