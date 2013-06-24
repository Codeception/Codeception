---
layout: doc
title: Codeception - Documentation
---

# Facebook Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Facebook.php)**


Provides testing for projects integrated with Facebook API.
Relies on Facebook's tool Test User API.

### Status

* Maintainer: **tiger-seo**
* Stability: **beta**
* Contact: tiger.seo@gmail.com

### Config

* app_id *required* - Facebook application ID
* secret *required* - Facebook application secret
* test_user - Facebook test user parameters:
    * name - You can specify a name for the test user you create. The specified name will also be used in the email address assigned to the test user.
    * locale - You can specify a locale for the test user you create, the default is en_US. The list of supported locales is available at https://www.facebook.com/translations/FacebookLocales.xml
    * permissions - An array of permissions. Your app is granted these permissions for the new test user. The full list of permissions is available at https://developers.facebook.com/docs/authentication/permissions

#### Config example

    modules:
        enabled: [Facebook]
        config:
            Facebook:
                app_id: 412345678901234
                secret: ccb79c1b0fdff54e4f7c928bf233aea5
                test_user:
                    name: FacebookGuy
                    locale: uk_UA
                    permissions: [email, publish_stream]

####  Test example:

{% highlight php %}

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


{% endhighlight %}

{% highlight php %}

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


{% endhighlight %}

 * available since version 1.6.3
 * author tiger.seo@gmail.com

### Actions


#### grabFacebookTestUserAccessToken


Returns the test user access token.

 * return string


#### grabFacebookTestUserEmail


Returns the test user email.

 * return string


#### grabFacebookTestUserFirstName


Returns the test user first name.

 * return string


#### grabFacebookTestUserLoginUrl


Returns URL for test user auto-login.

 * return string


#### haveFacebookTestUserAccount


Get facebook test user be created.

*Please, note that the test user is created only at first invoke, unless $renew arguments is true.*

 * param bool $renew true if the test user should be recreated


#### haveTestUserLoggedInOnFacebook


Get facebook test user be logged in on facebook.

 * throws ModuleConfigException


#### seePostOnFacebookWithAttachedPlace



Please, note that you must have publish_stream permission to be able to publish to user's feed.

 * param string $placeId Place identifier to be verified against user published posts
