<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

use Facebook\Exceptions\FacebookSDKException;

/**
 * Facebook gives cross-site-request-forgery-validation-failed without
 * initializing session data and without having the
 * 'persistent_data_handler' => 'session' property below
 */
session_start();

/**
 * you should update these values when debugging,
 * NOTE website URL for the app must be be set to http://localhost:8000/
 */
$fb = new Facebook\Facebook(array(
    'app_id' => '460287924057084',
    'app_secret' => 'e27a5a07f9f07f52682d61dd69b716b5',
    'default_graph_version' => 'v2.5',
    'persistent_data_handler' => 'session'
));

$helper = $fb->getRedirectLoginHelper();

$permissions = [];

//after logging in facebook will redirect us to this callback page
$callback = 'http://localhost:8000/facebook';

try {
    $accessToken = $helper->getAccessToken();
    if ($accessToken) {
        //if everything is ok we have accessToken from the callback
        $response = $fb->get('/me', $accessToken);
        $user = $response->getGraphUser()->asArray();
        $logoutUrl = $helper->getLogoutUrl($accessToken, $callback);
        $errorCode = 0;
    } else {
        //the first time we come to this page access token will be null
        $loginUrl = $helper->getLoginUrl($callback);
        $errorCode = 1;
        $user = null;
    }
} catch (FacebookSDKException $e) {
    //the second time we come to this we might get this if something is wrong with login
    $errorCode = " 3 " . $e->getMessage();
    $user = null;
}

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>

    <pre><?php print_r("\n errorCode: $errorCode\n"); ?></pre>

    <?php if ($user): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <?php if ($user): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $user['id']; ?>/picture">

      <h3>Your User Object (/me)</h3>
      <pre><?php print_r($user); ?></pre>
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>
  </body>
</html>
