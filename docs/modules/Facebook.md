# Facebook


Provides testing for projects integrated with Facebook API.
Relies on Facebook's tool Test User API.

<div class="alert alert-info">
To use this module with Composer you need <em>"facebook/php-sdk4": "5.*"</em> package.
</div>

## Status

[ ![Facebook Status for Codeception/Codeception](https://codeship.com/projects/e4bc90d0-1ed5-0134-566c-1ed679ae6c9d/status?branch=2.2)](https://codeship.com/projects/160201)

* Stability: **beta**
* Maintainer: **tiger-seo**
* Contact: tiger.seo@codeception.com

## Config

* app_id *required* - Facebook application ID
* secret *required* - Facebook application secret
* test_user - Facebook test user parameters:
    * name - You can specify a name for the test user you create. The specified name will also be used in the email address assigned to the test user.
    * locale - You can specify a locale for the test user you create, the default is en_US. The list of supported locales is available at https://www.facebook.com/translations/FacebookLocales.xml
    * permissions - An array of permissions. Your app is granted these permissions for the new test user. The full list of permissions is available at https://developers.facebook.com/docs/authentication/permissions

### Config example

    modules:
        enabled:
            - Facebook:
                depends: PhpBrowser
                app_id: 412345678901234
                secret: ccb79c1b0fdff54e4f7c928bf233aea5
                test_user:
                    name: FacebookGuy
                    locale: uk_UA
                    permissions: [email, publish_stream]

###  Test example:

``` php
<?php
$I = new ApiGuy($scenario);
$I->am('Guest');
$I->wantToTest('check-in to a place be published on the Facebook using API');
$I->haveFacebookTestUserAccount();
$accessToken = $I->grabFacebookTestUserAccessToken();
$I->haveHttpHeader('Auth', 'FacebookToken ' . $accessToken);
$I->amGoingTo('send request to the backend, so that it will publish on user\'s wall on Facebook');
$I->sendPOST('/api/v1/some-api-endpoint');
$I->seePostOnFacebookWithAttachedPlace('167724369950862');

```

``` php
<?php
$I = new WebGuy($scenario);
$I->am('Guest');
$I->wantToTest('log in to site using Facebook');
$I->haveFacebookTestUserAccount(); // create facebook test user
$I->haveTestUserLoggedInOnFacebook(); // so that facebook will not ask us for login and password
$fbUserFirstName = $I->grabFacebookTestUserFirstName();
$I->amOnPage('/welcome');
$I->see('Welcome, Guest');
$I->click('Login with Facebook');
$I->see('Welcome, ' . $fbUserFirstName);

```

@since 1.6.3
@author tiger.seo@gmail.com


## Actions

### grabFacebookTestUserAccessToken
 
Returns the test user access token.

 * `return` string


### grabFacebookTestUserEmail
 
Returns the test user email.

 * `return` string


### grabFacebookTestUserId
 
Returns the test user id.

 * `return` string


### grabFacebookTestUserLoginUrl
 
Returns URL for test user auto-login.

 * `return` string


### grabFacebookTestUserName
 
Returns the test user name.

 * `return` string


### grabFacebookTestUserPassword
__not documented__


### haveFacebookTestUserAccount
 
Get facebook test user be created.

*Please, note that the test user is created only at first invoke, unless $renew arguments is true.*

 * `param bool` $renew true if the test user should be recreated


### haveTestUserLoggedInOnFacebook
 
Get facebook test user be logged in on facebook.
This is done by going to facebook.com

@throws ModuleConfigException


### postToFacebookAsTestUser
 
Please, note that you must have publish_actions permission to be able to publish to user's feed.

 * `param array` $params


### seePostOnFacebookWithAttachedPlace
 

Please, note that you must have publish_actions permission to be able to publish to user's feed.

 * `param string` $placeId Place identifier to be verified against user published posts


### seePostOnFacebookWithMessage
 

Please, note that you must have publish_actions permission to be able to publish to user's feed.

 * `param string` $message published post to be verified against the actual post on facebook

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.3/src/Codeception/Module/Facebook.php">Help us to improve documentation. Edit module reference</a></div>
