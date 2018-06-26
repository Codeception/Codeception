# REST


Module for testing REST WebService.

This module can be used either with frameworks or PHPBrowser.
If a framework module is connected, the testing will occur in the application directly.
Otherwise, a PHPBrowser should be specified as a dependency to send requests and receive responses from a server.

## Configuration

* url *optional* - the url of api

This module requires PHPBrowser or any of Framework modules enabled.

### Example

    modules:
       enabled:
           - REST:
               depends: PhpBrowser
               url: 'http://serviceapp/api/v1/'

## Public Properties

* headers - array of headers going to be sent.
* params - array of sent data
* response - last response (string)

## Parts

* Json - actions for validating Json responses (no Xml responses)
* Xml - actions for validating XML responses (no Json responses)

## Conflicts

Conflicts with SOAP module


## Actions

### amAWSAuthenticated
 
Allows to send REST request using AWS Authorization
Only works with PhpBrowser
Example
Config -

modules:
     enabled:
         - REST:
             aws:
                 key: accessKey
                 secret: accessSecret
                 service: awsService
                 region: awsRegion

```php
<?php
$I->amAWSAuthenticated();
?>
```
 * `param array` $additionalAWSConfig
@throws ModuleException


### amBearerAuthenticated
 
Adds Bearer authentication via access token.

 * `param` $accessToken
 * `[Part]` json
 * `[Part]` xml


### amDigestAuthenticated
 
Adds Digest authentication via username/password.

 * `param` $username
 * `param` $password
 * `[Part]` json
 * `[Part]` xml


### amHttpAuthenticated
 
Adds HTTP authentication via username/password.

 * `param` $username
 * `param` $password
 * `[Part]` json
 * `[Part]` xml


### amNTLMAuthenticated
 
Adds NTLM authentication via username/password.
Requires client to be Guzzle >=6.3.0
Out of scope for functional modules.

Example:
```php
<?php
$I->amNTLMAuthenticated('jon_snow', 'targaryen');
?>
```

 * `param` $username
 * `param` $password
@throws ModuleException
 * `[Part]` json
 * `[Part]` xml


### deleteHeader
 
Deletes the header with the passed name.  Subsequent requests
will not have the deleted header in its request.

Example:
```php
<?php
$I->haveHttpHeader('X-Requested-With', 'Codeception');
$I->sendGET('test-headers.php');
// ...
$I->deleteHeader('X-Requested-With');
$I->sendPOST('some-other-page.php');
?>
```

 * `param string` $name the name of the header to delete.
 * `[Part]` json
 * `[Part]` xml


### dontSeeBinaryResponseEquals
 
Checks if the hash of a binary response is not the same as provided.

```php
<?php
$I->dontSeeBinaryResponseEquals("8c90748342f19b195b9c6b4eff742ded");
?>
```
Opposite to `seeBinaryResponseEquals`

 * `param` $hash the hashed data response expected
 * `param` $algo the hash algorithm to use. Default md5.
 * `[Part]` json
 * `[Part]` xml


### dontSeeHttpHeader
 
Checks over the given HTTP header and (optionally)
its value, asserting that are not there

 * `param` $name
 * `param` $value
 * `[Part]` json
 * `[Part]` xml


### dontSeeResponseCodeIs
 
Checks that response code is not equal to provided value.

```php
<?php
$I->dontSeeResponseCodeIs(200);

// preferred to use \Codeception\Util\HttpCode
$I->dontSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
```

 * `[Part]` json
 * `[Part]` xml
 * `param` $code


### dontSeeResponseContains
 
Checks whether last response do not contain text.

 * `param` $text
 * `[Part]` json
 * `[Part]` xml


### dontSeeResponseContainsJson
 
Opposite to seeResponseContainsJson

 * `[Part]` json
 * `param array` $json


### dontSeeResponseJsonMatchesJsonPath
 
Opposite to seeResponseJsonMatchesJsonPath

 * `param string` $jsonPath
 * `[Part]` json


### dontSeeResponseJsonMatchesXpath
 
Opposite to seeResponseJsonMatchesXpath

 * `param string` $xpath
 * `[Part]` json


### dontSeeResponseMatchesJsonType
 
Opposite to `seeResponseMatchesJsonType`.

 * `[Part]` json
@see seeResponseMatchesJsonType
 * `param` $jsonType jsonType structure
 * `param null` $jsonPath optionally set specific path to structure with JsonPath
 * `Available since` 2.1.3


### dontSeeXmlResponseEquals
 
Checks XML response does not equal to provided XML.
Comparison is done by canonicalizing both xml`s.

Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

 * `param` $xml
 * `[Part]` xml


### dontSeeXmlResponseIncludes
 
Checks XML response does not include provided XML.
Comparison is done by canonicalizing both xml`s.
Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

 * `param` $xml
 * `[Part]` xml


### dontSeeXmlResponseMatchesXpath
 
Checks whether XML response does not match XPath

```php
<?php
$I->dontSeeXmlResponseMatchesXpath('//root/user[@id=1]');
```
 * `[Part]` xml
 * `param` $xpath


### grabAttributeFromXmlElement
 
Finds and returns attribute of element.
Element is matched by either CSS or XPath

 * `param` $cssOrXPath
 * `param` $attribute
 * `return` string
 * `[Part]` xml


### grabDataFromJsonResponse
 
Deprecated since 2.0.9 and removed since 2.1.0

 * `param` $path
@throws ModuleException
@deprecated


### grabDataFromResponseByJsonPath
 
Returns data from the current JSON response using [JSONPath](http://goessner.net/articles/JsonPath/) as selector.
JsonPath is XPath equivalent for querying Json structures.
Try your JsonPath expressions [online](http://jsonpath.curiousconcept.com/).
Even for a single value an array is returned.

This method **require [`flow/jsonpath` > 0.2](https://github.com/FlowCommunications/JSONPath/) library to be installed**.

Example:

``` php
<?php
// match the first `user.id` in json
$firstUserId = $I->grabDataFromResponseByJsonPath('$..users[0].id');
$I->sendPUT('/user', array('id' => $firstUserId[0], 'name' => 'davert'));
?>
```

 * `param string` $jsonPath
 * `return` array Array of matching items
 * `Available since` 2.0.9
@throws \Exception
 * `[Part]` json


### grabHttpHeader
 
Returns the value of the specified header name

 * `param` $name
 * `param Boolean` $first Whether to return the first value or all header values

 * `return string|array The first header value if` $first is true, an array of values otherwise
 * `[Part]` json
 * `[Part]` xml


### grabResponse
 
Returns current response so that it can be used in next scenario steps.

Example:

``` php
<?php
$user_id = $I->grabResponse();
$I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
?>
```

 * `Available since` 1.1
 * `return` string
 * `[Part]` json
 * `[Part]` xml


### grabTextContentFromXmlElement
 
Finds and returns text contents of element.
Element is matched by either CSS or XPath

 * `param` $cssOrXPath
 * `return` string
 * `[Part]` xml


### haveHttpHeader
 
Sets HTTP header valid for all next requests. Use `deleteHeader` to unset it

```php
<?php
$I->haveHttpHeader('Content-Type', 'application/json');
// all next requests will contain this header
?>
```

 * `param` $name
 * `param` $value
 * `[Part]` json
 * `[Part]` xml


### seeBinaryResponseEquals
 
Checks if the hash of a binary response is exactly the same as provided.
Parameter can be passed as any hash string supported by hash(), with an
optional second parameter to specify the hash type, which defaults to md5.

Example: Using md5 hash key

```php
<?php
$I->seeBinaryResponseEquals("8c90748342f19b195b9c6b4eff742ded");
?>
```

Example: Using md5 for a file contents

```php
<?php
$fileData = file_get_contents("test_file.jpg");
$I->seeBinaryResponseEquals(md5($fileData));
?>
```
Example: Using sha256 hash

```php
<?php
$fileData = '/9j/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/yQALCAABAAEBAREA/8wABgAQEAX/2gAIAQEAAD8A0s8g/9k='; // very small jpeg
$I->seeBinaryResponseEquals(hash("sha256", base64_decode($fileData)), 'sha256');
?>
```

 * `param` $hash the hashed data response expected
 * `param` $algo the hash algorithm to use. Default md5.
 * `[Part]` json
 * `[Part]` xml


### seeHttpHeader
 
Checks over the given HTTP header and (optionally)
its value, asserting that are there

 * `param` $name
 * `param` $value
 * `[Part]` json
 * `[Part]` xml


### seeHttpHeaderOnce
 
Checks that http response header is received only once.
HTTP RFC2616 allows multiple response headers with the same name.
You can check that you didn't accidentally sent the same header twice.

``` php
<?php
$I->seeHttpHeaderOnce('Cache-Control');
?>>
```

 * `param` $name
 * `[Part]` json
 * `[Part]` xml


### seeResponseCodeIs
 
Checks response code equals to provided value.

```php
<?php
$I->seeResponseCodeIs(200);

// preferred to use \Codeception\Util\HttpCode
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
```

 * `[Part]` json
 * `[Part]` xml
 * `param` $code


### seeResponseCodeIsClientError
 
Checks that the response code is 4xx


### seeResponseCodeIsRedirection
 
Checks that the response code 3xx


### seeResponseCodeIsServerError
 
Checks that the response code is 5xx


### seeResponseCodeIsSuccessful
 
Checks that the response code is 2xx


### seeResponseContains
 
Checks whether the last response contains text.

 * `param` $text
 * `[Part]` json
 * `[Part]` xml


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

 * `param array` $json
 * `[Part]` json


### seeResponseEquals
 
Checks if response is exactly the same as provided.

 * `[Part]` json
 * `[Part]` xml
 * `param` $response


### seeResponseIsJson
 
Checks whether last response was valid JSON.
This is done with json_last_error function.

 * `[Part]` json


### seeResponseIsXml
 
Checks whether last response was valid XML.
This is done with libxml_get_last_error function.

 * `[Part]` xml


### seeResponseJsonMatchesJsonPath
 
Checks if json structure in response matches [JsonPath](http://goessner.net/articles/JsonPath/).
JsonPath is XPath equivalent for querying Json structures.
Try your JsonPath expressions [online](http://jsonpath.curiousconcept.com/).
This assertion allows you to check the structure of response json.

This method **require [`flow/jsonpath` > 0.2](https://github.com/FlowCommunications/JSONPath/) library to be installed**.

```json
  { "store": {
      "book": [
        { "category": "reference",
          "author": "Nigel Rees",
          "title": "Sayings of the Century",
          "price": 8.95
        },
        { "category": "fiction",
          "author": "Evelyn Waugh",
          "title": "Sword of Honour",
          "price": 12.99
        }
   ],
      "bicycle": {
        "color": "red",
        "price": 19.95
      }
    }
  }
```

```php
<?php
// at least one book in store has author
$I->seeResponseJsonMatchesJsonPath('$.store.book[*].author');
// first book in store has author
$I->seeResponseJsonMatchesJsonPath('$.store.book[0].author');
// at least one item in store has price
$I->seeResponseJsonMatchesJsonPath('$.store..price');
?>
```

 * `param string` $jsonPath
 * `[Part]` json
 * `Available since` 2.0.9


### seeResponseJsonMatchesXpath
 
Checks if json structure in response matches the xpath provided.
JSON is not supposed to be checked against XPath, yet it can be converted to xml and used with XPath.
This assertion allows you to check the structure of response json.
    *
```json
  { "store": {
      "book": [
        { "category": "reference",
          "author": "Nigel Rees",
          "title": "Sayings of the Century",
          "price": 8.95
        },
        { "category": "fiction",
          "author": "Evelyn Waugh",
          "title": "Sword of Honour",
          "price": 12.99
        }
   ],
      "bicycle": {
        "color": "red",
        "price": 19.95
      }
    }
  }
```

```php
<?php
// at least one book in store has author
$I->seeResponseJsonMatchesXpath('//store/book/author');
// first book in store has author
$I->seeResponseJsonMatchesXpath('//store/book[1]/author');
// at least one item in store has price
$I->seeResponseJsonMatchesXpath('/store//price');
?>
```
 * `param string` $xpath
 * `[Part]` json
 * `Available since` 2.0.9


### seeResponseMatchesJsonType
 
Checks that Json matches provided types.
In case you don't know the actual values of JSON data returned you can match them by type.
Starts check with a root element. If JSON data is array it will check the first element of an array.
You can specify the path in the json which should be checked with JsonPath

Basic example:

```php
<?php
// {'user_id': 1, 'name': 'davert', 'is_active': false}
$I->seeResponseMatchesJsonType([
     'user_id' => 'integer',
     'name' => 'string|null',
     'is_active' => 'boolean'
]);

// narrow down matching with JsonPath:
// {"users": [{ "name": "davert"}, {"id": 1}]}
$I->seeResponseMatchesJsonType(['name' => 'string'], '$.users[0]');
?>
```

In this case you can match that record contains fields with data types you expected.
The list of possible data types:

* string
* integer
* float
* array (json object is array as well)
* boolean

You can also use nested data type structures:

```php
<?php
// {'user_id': 1, 'name': 'davert', 'company': {'name': 'Codegyre'}}
$I->seeResponseMatchesJsonType([
     'user_id' => 'integer|string', // multiple types
     'company' => ['name' => 'string']
]);
?>
```

You can also apply filters to check values. Filter can be applied with `:` char after the type declaration.

Here is the list of possible filters:

* `integer:>{val}` - checks that integer is greater than {val} (works with float and string types too).
* `integer:<{val}` - checks that integer is lower than {val} (works with float and string types too).
* `string:url` - checks that value is valid url.
* `string:date` - checks that value is date in JavaScript format: https://weblog.west-wind.com/posts/2014/Jan/06/JavaScript-JSON-Date-Parsing-and-real-Dates
* `string:email` - checks that value is a valid email according to http://emailregex.com/
* `string:regex({val})` - checks that string matches a regex provided with {val}

This is how filters can be used:

```php
<?php
// {'user_id': 1, 'email' => 'davert@codeception.com'}
$I->seeResponseMatchesJsonType([
     'user_id' => 'string:>0:<1000', // multiple filters can be used
     'email' => 'string:regex(~\@~)' // we just check that @ char is included
]);

// {'user_id': '1'}
$I->seeResponseMatchesJsonType([
     'user_id' => 'string:>0', // works with strings as well
}
?>
```

You can also add custom filters y accessing `JsonType::addCustomFilter` method.
See [JsonType reference](http://codeception.com/docs/reference/JsonType).

 * `[Part]` json
 * `Available since` 2.1.3
 * `param array` $jsonType
 * `param string` $jsonPath


### seeXmlResponseEquals
 
Checks XML response equals provided XML.
Comparison is done by canonicalizing both xml`s.

Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).

 * `param` $xml
 * `[Part]` xml


### seeXmlResponseIncludes
 
Checks XML response includes provided XML.
Comparison is done by canonicalizing both xml`s.
Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).

Example:

``` php
<?php
$I->seeXmlResponseIncludes("<result>1</result>");
?>
```

 * `param` $xml
 * `[Part]` xml


### seeXmlResponseMatchesXpath
 
Checks whether XML response matches XPath

```php
<?php
$I->seeXmlResponseMatchesXpath('//root/user[@id=1]');
```
 * `[Part]` xml
 * `param` $xpath


### sendDELETE
 
Sends DELETE request to given uri.

 * `param` $url
 * `param array` $params
 * `param array` $files
 * `[Part]` json
 * `[Part]` xml


### sendGET
 
Sends a GET request to given uri.

 * `param` $url
 * `param array` $params
 * `[Part]` json
 * `[Part]` xml


### sendHEAD
 
Sends a HEAD request to given uri.

 * `param` $url
 * `param array` $params
 * `[Part]` json
 * `[Part]` xml


### sendLINK
 
Sends LINK request to given uri.

 * `param`       $url
 * `param array` $linkEntries (entry is array with keys "uri" and "link-param")

@link http://tools.ietf.org/html/rfc2068#section-19.6.2.4

@author samva.ua@gmail.com
 * `[Part]` json
 * `[Part]` xml


### sendOPTIONS
 
Sends an OPTIONS request to given uri.

 * `param` $url
 * `param array` $params
 * `[Part]` json
 * `[Part]` xml


### sendPATCH
 
Sends PATCH request to given uri.

 * `param`       $url
 * `param array` $params
 * `param array` $files
 * `[Part]` json
 * `[Part]` xml


### sendPOST
 
Sends a POST request to given uri. Parameters and files can be provided separately.

Example:
```php
<?php
//simple POST call
$I->sendPOST('/message', ['subject' => 'Read this!', 'to' => 'johndoe@example.com']);
//simple upload method
$I->sendPOST('/message/24', ['inline' => 0], ['attachmentFile' => codecept_data_dir('sample_file.pdf')]);
//uploading a file with a custom name and mime-type. This is also useful to simulate upload errors.
$I->sendPOST('/message/24', ['inline' => 0], [
    'attachmentFile' => [
         'name' => 'document.pdf',
         'type' => 'application/pdf',
         'error' => UPLOAD_ERR_OK,
         'size' => filesize(codecept_data_dir('sample_file.pdf')),
         'tmp_name' => codecept_data_dir('sample_file.pdf')
    ]
]);
```

 * `param` $url
 * `param array|\JsonSerializable` $params
 * `param array` $files A list of filenames or "mocks" of $_FILES (each entry being an array with the following
                    keys: name, type, error, size, tmp_name (pointing to the real file path). Each key works
                    as the "name" attribute of a file input field.

@see http://php.net/manual/en/features.file-upload.post-method.php
@see codecept_data_dir()
 * `[Part]` json
 * `[Part]` xml


### sendPUT
 
Sends PUT request to given uri.

 * `param` $url
 * `param array` $params
 * `param array` $files
 * `[Part]` json
 * `[Part]` xml


### sendUNLINK
 
Sends UNLINK request to given uri.

 * `param`       $url
 * `param array` $linkEntries (entry is array with keys "uri" and "link-param")
@link http://tools.ietf.org/html/rfc2068#section-19.6.2.4
@author samva.ua@gmail.com
 * `[Part]` json
 * `[Part]` xml


### startFollowingRedirects
 
Enables automatic redirects to be followed by the client

```php
<?php
$I->startFollowingRedirects();
```

 * `[Part]` xml
 * `[Part]` json


### stopFollowingRedirects
 
Prevents automatic redirects to be followed by the client

```php
<?php
$I->stopFollowingRedirects();
```

 * `[Part]` xml
 * `[Part]` json

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.4/src/Codeception/Module/REST.php">Help us to improve documentation. Edit module reference</a></div>
