<?php
/**
 * Plugin Name: Seravo Social Fetch
 * Description: Fetches Social Media Feeds to WordPress custom post types
 * Version: 0.1
 */

function ssf_fetch_feeds() {
  // Facebook
  if( defined('SSF_FACEBOOK_PAGE_ID') ) {
    echo "FACEBOOK\n";
    require 'inc/sources/facebook/facebook.php';
  }
  // Twitter
  if( defined('SSF_TWITTER_USERNAME') ) {
    echo "TWITTER\n";
    require 'inc/sources/twitter/twitter.php';
  }
}

if(isset($_GET['fetch_feeds'])) {
  echo "<pre>";
  ssf_fetch_feeds();
  echo "</pre>";
  die();
}
