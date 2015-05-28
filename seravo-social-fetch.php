<?php
/**
 * Plugin Name: Seravo Social Fetch
 * Description: Fetches Social Media Feeds to WordPress custom post types
 * Version: 0.1
 */

// Custom post type for feed items
require 'inc/cpt.php';

function ssf_fetch_feeds() {
  // Facebook
  if( defined('SSF_FACEBOOK_PAGE_ID') ) {
    require 'inc/sources/facebook/facebook.php';
  }
  // Twitter
  if( defined('SSF_TWITTER_USERNAME') ) {
    require 'inc/sources/twitter/twitter.php';
  }
}

if(isset($_GET['fetch_feeds'])) {
  echo "<pre>";
  ssf_fetch_feeds();
  echo "</pre>";
  die();
}
