<?php
/**
 * Plugin Name: Fetch Social Posts
 * Plugin URI: http://seravo.fi
 * Description: Fetches Social Media Feeds to WordPress custom post types
 * Version: 1.0
 * Author: Antti Kuosmanen (Seravo Oy)
 * Author URI: http://seravo.fi
 * License: GPLv3
*/

/**
 * Copyright 2015 Antti Kuosmanen / Seravo Oy
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.a
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Custom post type for feed items
require 'inc/cpt.php';

/**
 * Define once 5 mins interval
 */
add_filter('cron_schedules', '_fsp_new_interval');
function _fsp_new_interval($interval) {
    $interval['*/5'] = array('interval' => 5 * 60, 'display' => 'Once 5 minutes');
    return $interval;
}

/**
 * Schedule the fetch action to be done every 5 minutes via WP-Cron
 */
add_action( 'wp', '_fsp_setup_schedule' );
function _fsp_setup_schedule() {
  if ( ! wp_next_scheduled( 'fsp_fetch_feeds' ) ) {
    wp_schedule_event( time(), '*/5', 'fsp_fetch_feeds');
  }
}

/**
 * Fetches data from social networks
 */
add_action( 'fsp_fetch_feeds', 'fsp_do_fetch_feeds' );
function fsp_do_fetch_feeds() {

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
    $fetched = array_merge($fetched, fsp_fetch_facebook());
  }
  // Twitter
  if( defined('SSF_TWITTER_USERNAME') ) {
    require 'inc/sources/twitter/twitter.php';
    $fetched = array_merge($fetched, fsp_fetch_twitter());
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
      'post_type'      => 'fsp-social',
    );

    // check if this already exists as a post
    $matching = get_posts( array(
      'post_type' => 'fsp-social',
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
    if( ! $term_id = term_exists( $item['type'], 'fsp-social-type'  )) {
      $term_id = wp_insert_term(
        $meta['type'],
        'fsp-social-type',
        array(
          'slug' => strtolower( $item['type'] ),
        )
      );
    } 

    // attach taxonomy terms to this post
    wp_set_object_terms( $post_id, $item['type'], 'fsp-social-type' );

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
    'post_type' => 'fsp-social',
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
  add_action('init', 'fsp_do_fetch_feeds');
}
