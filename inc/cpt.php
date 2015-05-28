<?php

/**
 * CPT for the feed items
 */
add_action( 'init', 'fsp_register_social_cpt' );
function fsp_register_social_cpt() {
  $labels = array(
    'name'               => _x( 'Social', 'post type general name', 'seravo-social-feed' ),
    'singular_name'      => _x( 'Social', 'post type singular name', 'seravo-social-feed' ),
    'menu_name'          => _x( 'Social', 'admin menu', 'seravo-social-feed' ),
    'name_admin_bar'     => _x( 'Social', 'add new on admin bar', 'seravo-social-feed' ),
    'add_new'            => _x( 'Add New', 'form', 'seravo-social-feed' ),
    'add_new_item'       => __( 'Add New Social', 'seravo-social-feed' ),
    'new_item'           => __( 'New Social', 'seravo-social-feed' ),
    'edit_item'          => __( 'Edit Social', 'seravo-social-feed' ),
    'view_item'          => __( 'View Social', 'seravo-social-feed' ),
    'all_items'          => __( 'All Social', 'seravo-social-feed' ),
    'search_items'       => __( 'Search Social', 'seravo-social-feed' ),
    'parent_item_colon'  => __( 'Parent Social:', 'seravo-social-feed' ),
    'not_found'          => __( 'No social found.', 'seravo-social-feed' ),
    'not_found_in_trash' => __( 'No social in Trash.', 'seravo-social-feed' )
  );
  $args = array(
    'labels'             => $labels,
    'public'             => false,
    'publicly_queryable' => false,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => false,
    'rewrite'            => null,
    'capability_type'    => 'post',
    'has_archive'        => false,
    'hierarchical'       => false,
    'menu_position'      => null,
    'supports'           => array( 'title', 'editor', 'custom-fields' )
  );
  register_post_type( 'fsp-social', $args );

  // fsp-social has a taxonomy for feed type e.g. twitter or facebook
  register_taxonomy(
    'fsp-social-type',
    'fsp-social',
    array(
      'labels' => array(
        'name' => _x( 'Source', 'taxonomy general name', 'seravo-social-feed' ),
      ),
      'show_ui' => true,
      'show_tagcloud' => false,
      'hierarchical' => false,
      'show_admin_column' => true,
      'show_ui' => false,
    )
  );

}

