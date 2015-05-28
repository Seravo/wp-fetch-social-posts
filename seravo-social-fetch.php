<?php
/**
 * Plugin Name: Seravo Social Fetch
 * Description: Fetches Social Media Feeds to WordPress custom post types
 * Version: 0.1
 */

// Custom post type for feed items
require 'inc/cpt.php';

add_action( 'ssf_fetch_feeds', 'ssf_do_fetch_feeds' );
function ssf_do_fetch_feeds() {

  if(isset($_GET['fetch_feeds'])) {
    echo "<pre>";
  }

  // give it some time...
  set_time_limit(180);

  // array to store fetched items
  $fetched = array();

  // Facebook
  if( defined('SSF_FACEBOOK_PAGE_ID') ) {
    require 'inc/sources/facebook/facebook.php';
    $fetched = array_merge($fetched, ssf_fetch_facebook());
  }
  // Twitter
  if( defined('SSF_TWITTER_USERNAME') ) {
    require 'inc/sources/twitter/twitter.php';
    $fetched = array_merge($fetched, ssf_fetch_twitter());
  }

  // store available ids to keep track of published posts
  $available = array();

  // keep track of actions
  $added = array();
  $updated = array();
  $deleted = array();

  // store the data into wordpress posts
  foreach($fetched as $item) {

    // make this available
    $available[] = $item['uid'];

    $post = array(
      'post_content'   => $item['content'],
      'post_name'      => sanitize_title( $item['title'] ),
      'post_title'     => $item['title'],
      'post_date'      => date('Y-m-d H:i:s', $item['created']),
      'post_status'    => 'publish',
      'post_type'      => 'ssf-social',
    );

    // check if this already exists as a post
    $matching = get_posts( array(
      'post_type' => 'ssf-social',
      'meta_query' => array(
        array(
          'key' => '_uid',
          'value' => $item['uid'],
        )
      )
    ));

    if( !empty($matching) ) {
      // post exists, update it
      $updated[] = $post['ID'] = $matching[0]->ID;
      $post_id = wp_insert_post( $post );
    }
    else {
      $added[] = $post_id = wp_insert_post( $post );
    }

    $meta = array();
    $meta['_uid'] = $item['uid'];
    $meta['source'] = $item['source'];
    $meta['type'] = $item['type'];
    isset($item['image']) && $meta['image'] = $item['image'];

    // set type for this post

    // first see if this term already exists in the db and add it if it doesn't
    if( ! $term_id = term_exists( $item['type'], 'ssf-social-type'  )) {
      $term_id = wp_insert_term(
        $meta['type'],
        'ssf-social-type',
        array(
          'slug' => strtolower( $item['type'] ),
        )
      );
    } 

    // attach taxonomy terms to this post
    wp_set_object_terms( $post_id, $item['type'], 'ssf-social-type' );

    // store meta fields
    foreach($meta as $key => $value) {

      // store arrays in JSON format instead of WP's stupid serialized format
      if(is_array($value))
        $value = json_encode( $value );

      // add or update the value
      if( !add_post_meta($post_id, trim( $key ), sanitize_text_field( $value ), true) )
          update_post_meta($post_id, trim( $key ), sanitize_text_field( $value ) );

    }

  }

  // find posts to eliminate
  $eliminate = get_posts( array(
    'posts_per_page' => -1,
    'post_type' => 'ssf-social',
    'meta_query' => array(
      array(
        'key' => '_uid',
        'compare' => 'NOT IN',
        'value' => $available,
      )
    )
  ));

  // eliminate them
  foreach($eliminate as $post) {
    wp_delete_post($post->ID, true);
    $deleted[] = $post->ID;
  }

  if(isset($_GET['fetch_feeds'])) {

    echo "Added: ";
    print_r($added);

    echo "Updated: ";
    print_r($updated);

    echo "Deleted: ";
    print_r($deleted);

    echo "</pre>";
    die();
  }

}

if(isset($_GET['fetch_feeds'])) {
  add_action('init', 'ssf_do_fetch_feeds');
}
