<?php

// Include the Facebook PHP SDK
define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__ . '/facebook-php-sdk-v4/src/Facebook/');
require __DIR__ . '/facebook-php-sdk-v4/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

FacebookSession::setDefaultApplication(SSF_FACEBOOK_APP_ID, SSF_FACEBOOK_APP_SECRET);
$session = new FacebookSession(SSF_FACEBOOK_APP_TOKEN);

// Get the GraphUser object for the current user:

try {

  /* make the API call */
  $request = new FacebookRequest(
    $session,
      'GET',
        '/' . SSF_FACEBOOK_PAGE_ID . '/feed'
      );
  $response = $request->execute();
  $graphObject = $response->getGraphObject();

  print_r($graphObject);

} catch (FacebookRequestException $e) {
  // The Graph API returned an error
  print_r( $e );
} catch (\Exception $e) {
  // Some other error occurred
  print_r( $e );
}

