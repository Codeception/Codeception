# Testing WebServices

The same way we tested a web site, Codeception allows you to test web services. They are very hard to test manually, so it's really good idea to automate web service testing. As a standards we have SOAP and REST, which are represented in corresponding modules. We will cover them in this chapter.

You should start with creating a new test suite, which was not provided by the `bootstrap` command. We recommend to call it **api** and use the `ApiTester` class for it.

```bash
$ php codecept.phar generate:suite api
```

We will put all the api tests there.

## REST

The REST web service is accessed via HTTP with standard methods: `GET`, `POST`, `PUT`, `DELETE`. They allow to receive and manipulate entities from the service. Accessing WebService requires HTTP client, so for using it you need the module `PhpBrowser` or one of framework modules set up. For example, we can use the `Symfony2` module for Symfony2 applications in order to ignore web server and test web service internally.

Configure modules in `api.suite.yml`:

``` yaml
class_name: ApiTester
modules:
    enabled: [PhpBrowser, REST, ApiHelper]
    config:
		PhpBrowser:
			url: http://serviceapp/
		REST:
		    url: http://serviceapp/api/v1/
```

The REST module will automatically connect to `PhpBrowser`. In case you provide it with Symfony2, Laravel4, Zend, or other framework module, it will connect to them as well. Don't forget to run the `build` command once you finished editing configuration.

Let's create the first sample test:

```bash
$ php codecept.phar generate:cept api CreateUser
```

It will be called `CreateUserCept.php`. We can use it to test creation of user via web service.

```php
<?php
$I = new ApiTester($scenario);
$I->wantTo('create a user via API');
$I->amHttpAuthenticated('service_user', '123456');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('users', ['name' => 'davert', 'email' => 'davert@codeception.com']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('{"result":"ok"}');
?>
```

REST module is designed to be used with services that serve responses in JSON format. For example, method `seeResponseContainsJson` will convert provided array to JSON and check whether response contains it.

You may want to perform more complex assertions on response. This can be done with writing your own methods in [Helper](http://codeception.com/docs/03-ModulesAndHelpers#Helpers) classes. To access the latest JSON response you will need to get `response` property of `REST` module. Let's demonstrate it with `seeResponseIsHtml` method:

```php
<?php
class ApiHelper extends \Codeception\Module
{
	public function seeResponseIsHtml()
	{
		$response = $this->getModule('REST')->response;
        \PHPUnit_Framework_Assert::assertRegex('~^<!DOCTYPE HTML(.*?)<html>.*?<\/html>~m', $response);
	}
}
?>
```

The same way you can receive request parameters and headers.

## SOAP

SOAP web services are usually more complex. You will need PHP [configured with SOAP support](http://php.net/manual/en/soap.installation.php). Good knowledge of XML is required too. `SOAP` module uses specially formatted POST request to connect to WSDL web services. Codeception uses `PhpBrowser` or one of framework modules to perform interactions. If you choose using a framework module, SOAP will automatically connect to the underliying framework. That may improve the speed of a test execution and will provide you with more detailed stack traces.

Let's configure `SOAP` module to be used with `PhpBrowser`:

``` yaml
class_name: ApiTester
modules:
    enabled: [PhpBrowser, SOAP, ApiHelper]
    config:
		PhpBrowser:
			url: http://serviceapp/
		SOAP:
		    endpoint: http://serviceapp/api/v1/
```

SOAP request may contain application specific information, like authentication or payment. This information is provided with SOAP header inside the `<soap:Header>` element of XML request. In case you need to submit such header, you can use `haveSoapHeader` action. For example, next line of code

```php
<?php
$I->haveSoapHeader('Auth', array('username' => 'Miles', 'password' => '123456'));
?>
```
will produce this XML header

```xml
<soap:Header>
<Auth>
	<username>Miles</username>
	<password>123456</password>
</Auth>
</soap:Header>
```

Use `sendSoapRequest` method to define the body of your request.

```php
<?php
$I->sendSoapRequest('CreateUser', '<name>Miles Davis</name><email>miles@davis.com</email>');
?>
```

This call will be translated to XML:

```xml
<soap:Body>
<ns:CreateUser>
	<name>Miles Davis</name>
	<email>miles@davis.com</email>
</ns:CreateUser>
</soap:Body>
```

And here is the list of sample assertions that can be used with SOAP.

```php
<?php
$I->seeSoapResponseEquals('<?xml version="1.0"?><error>500</error>');
$I->seeSoapResponseIncludes('<result>1</result>');
$I->seeSoapResponseContainsStructure('<user><name></name><email></email>');
$I->seeSoapResponseContainsXPath('//result/user/name[@id=1]');
?>
```

In case you don't want to write long XML strings, consider using [XmlBuilder](http://codeception.com/docs/reference/XmlBuilder) class. It will help you to build complex XMLs in jQuery-like style.
In the next example we will use `XmlBuilder` (created from SoapUtils factory) instead of regular XMLs.

```php
<?php
use \Codeception\Util\Soap;

$I = new ApiTester($scenario);
$I->wantTo('create user');
$I->haveSoapHeader('Session', array('token' => '123456'));
$I->sendSoapRequest('CreateUser', Soap::request()
	->user->email->val('miles@davis.com'));
$I->seeSoapResponseIncludes(Soap::response()
	->result->val('Ok')
		->user->attr('id', 1)
);
?>
```

It's up to you to decide whether to use `XmlBuilder` or plain XML. `XmlBuilder` will return XML string as well.

You may extend current functionality by using `SOAP` module in your helper class. To access the SOAP response as `\DOMDocument` you can use `response` property of `SOAP` module.

```php
<?php
class ApiHelper extends \Codeception\Module {

	public function seeResponseIsValidOnSchema($schema)
	{
		$response = $this->getModule('SOAP')->response;
		$this->assertTrue($response->schemaValidate($schema));
	}
}
?>
```

## Conclusion

Codeception has two modules that will help you to test various web services. They need a new `api` suite to be created. Remember, you are not limited to test only response body. By including `Db` module you may check if a user has been created after the `CreateUser` call. You can improve testing scenarios by using REST or SOAP responses in your helper methods.
