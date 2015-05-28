<?php

// Include the Facebook PHP SDK
define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__ . '/facebook-php-sdk-v4/src/Facebook/');
require __DIR__ . '/facebook-php-sdk-v4/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;

/**
 * Returns a list of facebook statuses
 */
function ssf_fetch_facebook() {

  FacebookSession::setDefaultApplication(SSF_FACEBOOK_APP_ID, SSF_FACEBOOK_APP_SECRET);
  $session = new FacebookSession(SSF_FACEBOOK_APP_TOKEN);

  // Get the GraphUser object for the current user:
  try {
    $request = new FacebookRequest(
      $session,
        'GET',
          '/' . SSF_FACEBOOK_PAGE_ID . '/feed'
        );
    $response = $request->execute()
      ->getGraphObject()
      ->asArray();

  } catch (FacebookRequestException $e) {
    // The Graph API returned an error
    print_r( $e );
  } catch (\Exception $e) {
    // Some other error occurred
    print_r( $e );
  }

  $items = array();

  $fb_posts = $response['data'];
  foreach($fb_posts as $fb_post) {

    $item = array();

    $item['type'] = 'facebook';
    $ids = explode('_', $fb_post->id); 
    $item['uid'] = $ids[1]; // the last part is post id, first is user id
    $item['source'] = "https://www.facebook.com/" . $ids[0] . "/posts/" . $ids[1];
    $item['created'] = strtotime($fb_post->created_time);
    $item['title'] = wp_trim_words($fb_post->message, 6);

    // content is the text + link at the end
    $item['content'] = $fb_post->message;
    if(isset($fb_post->link)) {
      $item['content'] .=  $fb_post->link;
    }

    // post might have an image associated to it
    if(isset($fb_post->picture)) {
      $item['image'] = $fb_post->picture;
    }

    $items[] = $item;

    print_r($item);
  }

  return $items;
}
