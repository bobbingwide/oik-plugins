<?php
/*

    Copyright 2012-2015 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/


/**
 * Define oik plugin server settings
 *
 * Settings are saved in 'bw_plugins_server'
 * Users with 'manage_options' can update them
 */
function oikp_lazy_admin_menu() {
  register_setting( 'oik_plugins_server', 'bw_plugins_server', 'oik_plugins_validate' ); // No validation for oik-plugins
  add_submenu_page( 'oik_menu', 'oik server settings', "Plugin server settings", 'manage_options', 'oik_server', "oik_plugins_options_do_page" );
}

/**
 * oik plugins server settings
 * 
 * Display two boxes
 * 1. The settings
 * 2. The download / updates status totalled by plugin
 */
function oik_plugins_options_do_page() {
  oik_menu_header( "plugins server settings", "w90pc" );
  oik_box( NULL, NULL, "Defaults", "oik_plugins_server_options" );
  oik_box( null, null, "Status", "oik_plugins_status" );
  oik_menu_footer();
  bw_flush();
}

function oik_plugins_default_plugins_server_options() {
    $options = [];
    $options['zipdir'] = '';
    $options['apikey'] = '';
    $options['faq'] = null;
    $options['apiref'] = null;
    return $options;
}

/**
 * Display the oik-plugins server options
 * 
 * 
 */
function oik_plugins_server_options() {
  $option = 'bw_plugins_server'; 
  $options = bw_form_start( $option, 'oik_plugins_server' );
  if ( false === $options ) {
      $options = oik_plugins_default_plugins_server_options();
  }
  bw_textfield_arr( $option, "Folder for premium plugins", $options, 'zipdir', 40 );
  bw_textfield_arr( $option, "API key", $options, 'apikey', 26 );
  bw_form_field_noderef( "bw_plugins_server[faq]", "", "FAQ page", $options['faq'], array( "#type" => "page", "#optional" => true ));
  bw_checkbox_arr( $option, "Use [apiref] shortcode", $options, 'apiref' );
  etag( "table" );   
  p( isubmit( "ok", "Update", null, "button-primary" ) );
  etag( "form" );
  bw_flush();
}

/** 
 * Summarise the downloads and updates for each plugin
 */
function oik_plugins_status() {
  $atts = array( "post_type" => "oik-plugins" 
               , "orderby" => "meta_value"
               , "meta_key" => "_oikp_slug" 
  );
  $posts = bw_get_posts( $atts );
  foreach ( $posts as $post ) {
    oik_plugins_summarise_versions( $post ); 
  }
  oik_plugins_status_report();
}

/**
 * Accumulate the figures for the plugin version
 *
 * @param object $post - the post object ( plugin version )
 * @param string $plugin - the plugin slug
 */ 
function oik_plugins_accumulate_version( $post, $plugin ) {
  $version = get_post_meta( $post->ID, "_oikpv_version", true );
  $downloads = get_post_meta( $post->ID, "_oikpv_download_count", true );
  $updates = get_post_meta( $post->ID, "_oikpv_update_count", true );
  //e( $downloads );
  //e( $updates );
  oik_plugins_add_version( $plugin, $version, $downloads, $updates );
}

/**
 * Return the result of adding $amount to $array[$index1][$index2] 
 * 
 * Example: bw_array_add2( $downloads, $plugins, $versions, $download );
 * 
 */
if ( !function_exists( "bw_array_add2" ) ) { 
function bw_array_add2( &$array, $index, $index2, $amount ) {
	$amount = intval( $amount );
  if ( ! isset($array[$index][$index2]) ) {
    $value = $amount;
  } else {
    $value = $array[$index][$index2] + $amount;
  }
  return( $value );  
}
}

/**
 * Add the version's figures to the total  
 */
function oik_plugins_add_version( $plugin, $version, $download, $updates ) {
  global $bw_plugin_totals;
  $bw_plugin_totals['Total']['downloads'] = bw_array_add2( $bw_plugin_totals, "Total", "downloads", $download ); 
  $bw_plugin_totals['Total']['updates'] = bw_array_add2( $bw_plugin_totals, "Total", "updates", $updates );
  $bw_plugin_totals[$plugin]['downloads'] = bw_array_add2( $bw_plugin_totals, $plugin, "downloads", $download );
  $bw_plugin_totals[$plugin]['updates'] = bw_array_add2( $bw_plugin_totals, $plugin, "updates", $updates );
}

/**
 * Summarise the versions for this plugin
 */
function oik_plugins_summarise_versions( $post ) {
  //bw_trace2();
  //p( $post->post_title );
  $plugin = get_post_meta( $post->ID, "_oikp_slug", true );
  $version_type = get_post_meta( $post->ID, "_oikp_type", true );
  //e( $version_type );
  $versions = bw_plugin_post_types();
  $post_type = bw_array_get( $versions, $version_type, null ); 
  //e( $post_type );
  $atts = array( "post_type" => $post_type 
               , "numberposts" => -1
               , "meta_key" => "_oikpv_plugin" 
               , "meta_value" => $post->ID
               );
  $posts = bw_get_posts( $atts );
  if ( $posts ) {
    foreach ( $posts as $post ) {
      //p( $post->post_title .  $post->post_type );
      oik_plugins_accumulate_version( $post, $plugin );
    }
  }
}

 
/**
 * Produce a plugin status report
 */
function oik_plugins_status_report() {
  global $bw_plugin_totals;
  stag( "table", "widefat bw_plugins" );
  stag( "tr" );
  th( "Plugin" );
  //th( "Version" );
  th( "Downloads" );
  th( "Updates" );
  th( "Totals" ); 
  etag( "tr" );

  foreach ( $bw_plugin_totals as $plugin => $plugin_total  ) {
    stag( "tr" );
    td( $plugin );
    td( $plugin_total['downloads'] );
    td( $plugin_total['updates'] );
    td( $plugin_total['downloads'] + $plugin_total['updates'] );
    etag( "tr" );
  } 
  etag( "table" );
}

/** 
 * Build a simple ID, title array from an array of $user objects
 * @param array $user - array of user objects
 * @return array - associative array of user ID to user_title
 */
if ( !function_exists( "bw_user_array" ) ) { 
function bw_user_array( $users ) {
  $options = array();
  foreach ($users as $user ) {
    $options[$user->ID] = $user->display_name; 
  }
  return bw_trace2( $options );
}

/**
 * Return an associative array of all users
 * @return array - associative array of user ID to user_title
 */
function bw_user_list() {
  $users = bw_get_users( array( "number" => "" )) ;
  $userlist = bw_user_array( $users );
  return( $userlist );
}
}


