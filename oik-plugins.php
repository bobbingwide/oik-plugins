<?php 
/**
Plugin Name: oik plugins server
Depends: oik base plugin, oik-fields
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-plugins
Description: oik plugins server for premium and free(mium) oik plugins
Version: 1.21.1
Author: bobbingwide
Author URI: https://www.bobbingwide.com/about-bobbing-wide
Text Domain: oik-plugins
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2012-2023 Bobbing Wide (email : herb@bobbingwide.com )

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

oik_plugins_loaded();

/**
 * Function to invoke when oik-plugins is loaded
 */
function oik_plugins_loaded() {
	add_action( "init", "oikp_plugin_rewrite" );
	add_action( 'oik_fields_loaded', 'oikp_init', 11 );
	add_action( "admin_notices", "oikp_activation" );
	add_filter( "oik_validate_apikey", "oikp_oik_validate_apikey", 10, 2 );
	add_filter( "oik_clone_filter_all_post_meta", "oikp_oik_clone_filter_all_post_meta" );
	add_filter( "oik_clone_filter_media_file", "oikp_oik_clone_filter_media_file", 10, 2 );
}

/** 
 * Implement "init" action for oik plugins server
 * 
 * Implement the oik equivalent of WordPress.org responding to 
 *  http://api.wordpress.org/plugins/update-check/1.0/
 *  http://api.wordpress.org/plugins/info/1.0/
 */
function oikp_plugin_rewrite() {
  add_rewrite_tag( "%oik-plugin%", '([^/]+)' );
  add_permastruct( 'oik-plugin', 'plugins/%oik-plugin%' );
  add_action( "template_redirect", "oikp_template_redirect" ); 
  add_filter( "wp_handle_upload", "oikp_handle_upload", 10, 2 );
  add_filter( "posts_request", "oikp_posts_request", 10, 2 );
  
  // Handle
   http://oik-plugins.co.uk/plugins/download?plugin=oik-often-included-key-information-kit&version=1.17.1002.1444&id=245&action=update
  
  add_rewrite_tag( "%oik-banner%", '([^/]+)' ); 
  add_permastruct( 'oik-banner', 'banner/%oik-banner%' );
  
  //add_rewrite_tag( "%oik-tab%", '([^/]+)' );
  //add_permastruct( 'oik-plugins-tab', 'oik-plugins//%oik-tab%' );
  //add_rewrite_rule( "oik-plugins/([^/]+)/?([^/]+)?", 'index.php?post_type=oik-plugins&postname=$matches[1]&oik-tab=$matches[2]' );
}

/**
 * Handle the plugins/%oik-plugin% request
 *
 * Ignore query_var="oik-plugin" when oik-tab is set
 */
function oikp_template_redirect() {
  $oik_tab = get_query_var( "oik-tab" );
  if ( $oik_tab ) {
    bw_trace2( $oik_tab, "oik-tab" );
  } else {
    $oik_plugin = get_query_var( "oik-plugin" );
    //bw_trace2( $oik_plugin, "oik-plugin", false );
    if ( $oik_plugin ) {
      oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
      oikp_lazy_redirect( $oik_plugin ); 
    }
    $oik_banner = get_query_var( "oik-banner" );
    //bw_trace2( $oik_banner, "oik-banner", false );
    if ( $oik_banner ) {
      oik_require( "feed/oik-banner-feed.php", "oik-plugins" );
      oikp_lazy_redirect_banner( $oik_banner );
    }
  }  
}

/** 
 * Implement "posts_request" filter to intercept the main query 
 * 
 * In {@link http://wordpress.stackexchange.com/questions/98326/disable-the-mysql-query-in-the-main-query}
 * there are a number of recommendations.... 
 * just return nothing in the $request or 
 * handle 'posts_where' and set 'AND 1=0' to cause the SQL to return nothing
 * 
 * In this case we're looking for a query_var of "oik-plugin" which will be set when the request is /plugins/update-check
 * Can we also check for the "oik-banner" query_var and achieve the same thing?
 * 
 * Can we remove the filter for ANY invocation, or only when it's the main query or when we've decided to intercept the query.
 * What does that mean when WooCommerce is doing its nasty stuff with webhooks?
 *
 */
function oikp_posts_request( $request, $query ) {
  $oik_plugin = get_query_var( "oik-plugin" );
  if ( $oik_plugin ) {
    //bw_trace2();
    //bw_backtrace();
    //oikp_template_redirect();
    $request = false;
  } else {
    $oik_banner = get_query_var( "oik-banner" );
    if ( $oik_banner ) {
      $request = false;
    }
  }
  if ( !$request ) {
    remove_filter( "posts_request", "oikp_posts_request" );
  }  
  return( $request );
}

/**
 * Implement the "oik_fields_loaded" action for oik plugins server
 */
function oikp_init( ) {
 
  /*
   * We need to register the custom categories and associate them with mutiple post types
   * so we have to register these first, then associate them to the post_types when they are registered
   */
  bw_register_custom_category( "required_version", null, "Required version" );
  bw_register_custom_category( "compatible_up_to", null, "Compatible up to" );
  bw_register_custom_tags( "oik_tags", null, "Plugin Tags" );

  oik_register_oik_plugin();
  oik_register_oik_pluginversion();
  oik_register_oik_premiumversion();
  
  add_action( 'add_meta_boxes', 'oikp_header_meta_box' );
  bw_add_shortcode( "oikp_download", "oikp_download", oik_path("shortcodes/oik-plugins.php", "oik-plugins"), false );
  // add_action( 'the_post', "oikp_the_post", 10, 1 );
  
  // As a temporary workaround to a problem where oikp_the_content thinks we've gone recursive
  // stop jetpack from messing with opengraph tags
  // or stop wp_trim_excerpt() from being called when processing the 'get_the_excerpt' filter
  // 'get_the_excerpt'problem will probably not be fixed with #19927 or #26649
  
  remove_action( 'wp_head', 'jetpack_og_tags' );
  //remove_action( 'get_the_excerpt', 'wp_trim_excerpt'  );
  add_filter( 'the_content', "oikp_the_content", 1, 1 );
  add_action( "oik_admin_menu", "oikp_admin_menu" );
}

/**
 * Return an array of plugin types
 * This is used as a select field. The alternative is to use a custom category
 * @return array - values for different plugin types
 */
function bw_plugin_types() {
  $plugin_types = array( 0 => "None"
                       , 1 => "WordPress plugin"
                       , 2 => "FREE oik plugin"
                       , 3 => "Premium oik plugin"
                       , 4 => "Other premium plugin"
                       , 5 => "Bespoke plugin"
                       , 6 => "WordPress and FREE plugin"
                       );
  return( $plugin_types );                      
}

/**
 * Return an array of plugin version types associated with different plugins types
 * @return array - post_types for the plugin version
 */
function bw_plugin_post_types() {
  $post_types = array( null
                     , "oik_pluginversion"
                     , "oik_pluginversion"
                     , "oik_premiumversion"
                     , "oik_premiumversion"
                     , "oik_pluginversion"
                     , "oik_pluginversion"
                     );
  return( $post_types );
}                   

/**
 * Register the oik-plugins custom post type
 * 
 * The oik-plugins custom post type supports:
 * - title - which may be set to the same as the Description field or "Plugin slug - description" 
 * - editor - where the main body or subset of the readme.txt file might get transferred
 * - thumbnail - used for the banner image 
 * - excerpt - when the automatic excerpt is not suitable   
 */
function oik_register_oik_plugin() {
  $post_type = 'oik-plugins';
  $post_type_args = array();
  $post_type_args['label'] = 'Plugins';
  $post_type_args['description'] = 'oik plugin';
  $post_type_args['supports'] = array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'publicize', 'home', 'clone', 'custom-fields' );
  $post_type_args['taxonomies'] = array( "oik_tags" );
  $post_type_args['has_archive'] = true;
  $post_type_args['menu_icon'] = 'dashicons-admin-plugins';
	$post_type_args['show_in_rest'] = true;
	$post_type_args['template'] = oikp_oik_plugins_CPT_template();
  bw_register_post_type( $post_type, $post_type_args );
	
	if ( oik_require_lib( "bobbfunc" ) ) {
		bw_dtt( "_oikp_slug", "plugin folder name (e.g. oik)" );
		$oikp_name = bw_dtt( "_oikp_name", "plugin file name (e.g. oik/oik.php)" );
		$oikp_git = bw_dtt( "_oikp_git", "GitHub repository name (e.g. bobbingwide/oik)");
	}
  bw_register_field( "_oikp_type", "select", "Plugin type", array( '#options' => bw_plugin_types() ) ); 
  bw_register_field( "_oikp_slug", "text", "Plugin slug", array( '#hint' => "_oikp_slug" ) );
  bw_register_field( "_oikp_name", "text", "Plugin name", array( '#hint' => $oikp_name ) ); 
  bw_register_field( "_oikp_desc", "text", "Description" ); 
  bw_register_field( "_oikp_git", "sctext", "GitHub repository", array( "#hint" => $oikp_git ) );
 bw_register_field( "_oikp_uri", "URL", "Plugin URI", array( '#hint' => 'optional', '#theme' => null ) );
 bw_register_field( "_oikp_dependency", "noderef", "Depends on", array( '#type' => 'oik-plugins', '#optional' => true, '#multiple' => 5, '#theme_null' => false ) );
	// bw_register_field( "_oikp_banner", "noderef", "banner image link", array( '#type' => 'attachment', '#optional' => true ) );
	bw_register_field( '_oikp_block_count', 'numeric', 'Blocks delivered', array( '#theme_null' => false ));

  /** Currently we support two different systems for delivering Premium plugins: WooCommerce and Easy Digital Downloads 
   * The Purchasable product should be completed for each Premium oik plugin (and Other premium plugin? )
  */
  $purchasable_product_type = array();
  $purchasable_product_type[] = "download"; 
  $purchasable_product_type[] = "product"; 
  bw_register_field( "_oikp_prod", "noderef", "Purchasable product", array( '#type' => $purchasable_product_type, '#optional' => true, '#theme_null' => false ) );   
  bw_register_field_for_object_type( "_component_version", $post_type, true );
  bw_register_field_for_object_type( "_oikp_type", $post_type, true );
  bw_register_field_for_object_type( "_oikp_slug", $post_type, true );
  bw_register_field_for_object_type( "_oikp_name", $post_type, true );
  bw_register_field_for_object_type( "_oikp_desc", $post_type, true );
	
  bw_register_field_for_object_type( "_oikp_git", $post_type, true );
  bw_register_field_for_object_type( "_oikp_prod", $post_type, true );
	bw_register_field_for_object_type( "_oikp_uri", $post_type, true );
  bw_register_field_for_object_type( "_oikp_dependency", $post_type, true );
  bw_register_field_for_object_type( '_oikp_block_count', $post_type, true );
  oikp_columns_and_titles( $post_type );
}

function oikp_oik_plugins_CPT_template() {
	$template = array();
	$template[] = [ 'core/paragraph', [ 'placeholder' => 'Copy the plugin description'] ];
	$template[] = [ 'core/shortcode', [ 'text' => '[bw_plug name=plugin banner=p]' ] ];
	$template[] = [ 'core/paragraph', [ 'content' => 'v[bw_field _component_version] delivers [bw_field _oikp_block_count] blocks.' ] ];
	$template[] = [ 'core/more' ];
	$template[] = [ 'oik-block/blocklist' ];
	$template[] = [ 'core/shortcode', [ 'text' => '[bw_plug name=plugin table=y]' ] ];

	return $template;
}

/** 
 * Return a candidate function name from the given string
 * 
 * Converts spaces and hyphens to underscores 
 * and converts to lowercase - which is not actually necessary for PHP code but can help in legibility
 *
 */
if ( !function_exists( "bw_function_namify" ) ) {
function bw_function_namify( $name ) {
  $name = trim( $name );
  $name = str_replace( ' ', '_', $name );
  $name = str_replace( '-', '_', $name );
  $name = strtolower( $name );
  //bw_trace2( $name );
  return( $name ); 
}
} 

/** 
 * Add filters for the $post_type
 * @param string $post_type - the Custom Post type name
 */ 
if ( !function_exists( "oikp_columns_and_titles" ) ) {
function oikp_columns_and_titles( $post_type ) {
  $post_type_namify = bw_function_namify( $post_type );
  add_filter( "manage_edit-{$post_type}_columns", "{$post_type_namify}_columns", 10 );
  add_action( "manage_{$post_type}_posts_custom_column", "bw_custom_column_admin", 10, 2 );
  add_filter( "oik_table_fields_{$post_type}", "{$post_type_namify}_fields", 10, 2 );
  //add_filter( "oik_table_titles_{$post_type}", "{$post_type_namify}_titles", 10, 3 ); 
}
}

/**
 * Return the columns to be displayed in the All post_type display admin page
 */
function oik_plugins_columns( $columns ) {
	//bw_backtrace();
  $columns['_oikp_type'] = __("Type"); 
  $columns['_oikp_slug'] = __("Slug" );
  $columns['_oikp_name'] = __("Name" );
  $columns['_oikp_prod'] = __("Product" );
  //bw_trace2();
  return( $columns ); 
}

/**
 * Return the fields to be displayed in a table
 */ 
function oik_plugins_fields( $fields, $arg2 ) {
  $fields['title'] = 'title';
  $fields['excerpt'] = 'excerpt';
  $fields['_oikp_type'] = '_oikp_type';
  $fields['_oikp_slug'] = '_oikp_slug';
  $fields['_oikp_name'] = '_oikp_name' ;
  // $fields['_oikp_prod'] = '_oikp_prod' ;
  return( $fields );
}

/**
 * Titles are remarkably similar to columns for the admin pages
 * We remove the Product column since it's not working properly - it's an optional field! 
 */
function oik_plugins_titles( $titles, $arg2=null, $fields=null ) {
  $titles = oik_plugins_columns( $titles, $arg2 );
  unset( $titles['_oikp_prod'] );
  return( $titles );
}

/** 
 * Add custom header support as required 
 */
function oikp_header_meta_box() {
  if ( function_exists( "bw_oik_header_box2" ) ) {
    add_meta_box( 'bw_oik_header2', 'Custom header image', 'bw_oik_header_box2', "oik-plugin" );
    //add_meta_box( 'bw_oik_header2', 'Custom header image', 'bw_oik_header_box2', "oik-premium" );
  }
}

/**
 * Map WP versions - soon to be deprecated
 * 
 */
function oik_map_WP_versions() { 
  $wp_versions = array( 0 => "3.0.4"
                      , 1 => "3.4.1"
                      , 2 => "3.4.2"
                      , 3 => "3.5" 
                      , 4 => "3.5.1"
                      //, 5 => "3.6-alpha-23386" // 8 Feb 2013"
                      //, 5 => "3.6-beta1-24041" // 19 Apr 2013
                      //, 5 => "3.6-beta2-24163"  // 29 Apr 2013
                      //, 5 => "3.6-beta3" //  11 May 2013
                      //, 5 => "3.6-beta4" // 21 Jun 2013
                      , 5 => "3.6-RC1-24750" // xx Jul 2013
                      , 6 => "3.5.2"
                      );
  return( $wp_versions );
}                        

/**
 * Create the oik_pluginversion custom post type
 *
 * The title should contain the plugin name 
 * The description is the content field
 * 
 * Any zip file that is attached to this post type should automatically be stored
 * in a safe location so that it can only be downloaded by a controlled request
 * Protected (premium) files will require a valid API key to be updated
 * 
 */
function oik_register_oik_pluginversion() {
  $post_type = 'oik_pluginversion';
  $post_type_args = array();
  $post_type_args['label'] = 'oik plugin versions';
  
  $post_type_args['description'] = 'oik plugin version';
  $post_type_args['taxonomies'] = array( "required_version", "compatible_up_to" );
  
  $post_type_args['menu_icon'] = 'dashicons-shield';
  
  $post_type_args['supports'] = array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'publicize', 'home', 'clone', 'custom-fields' );
  $post_type_args['show_in_rest'] = true;
  $post_type_args['template'] = oikp_oik_pluginversion_CPT_template();
  bw_register_post_type( $post_type, $post_type_args );
  
  oik_register_oik_pluginversion_fields( $post_type );
  
}

function oikp_oik_pluginversion_CPT_template() {
	$template = array();
	$template[] = [ 'core/paragraph', [ 'placeholder' => 'Copy the upgrade notice'] ];
	//$template[] = [ 'oik-block/fields', [ 'fields' => 'featured' ] ];
	$template[] = ['core/post-featured-image'];
	$template[] = [ 'core/more' ];
	$template[] = [ 'core/heading', ['content' => 'Changes'] ];
	$template[] = [ 'oik-bbw/csv', [ 'content' => 'Change,Reference' ] ];
	$template[] = [ 'core/heading', ['content' => 'Tested'] ];
	$template[] = [ 'core/list' ];
	$template[] = [ 'core/file' ];

	return $template;
}

/**
 * Register the fields for oik_pluginversion
 *  
 * - The title should contain the plugin name and version.
 * - The description is the content field.
 * - The upgrade notice is part of the description. 
 * - We don't display the _component_version virtual field since this might confuse the user seeing two versions.
 * - Requires and tested are custom taxonomies.
 * - Download count and Update count are not displayed on the front end.
 */ 
function oik_register_oik_pluginversion_fields( $post_type ) { 
  bw_register_field( "_oikpv_plugin", "noderef", "Plugin", array( '#type' => 'oik-plugins') );   
  bw_register_field( "_oikpv_version", "text", "Version", array( '#hint' => " (omit the v)" ) ); 
  bw_register_field( "_oikpv_download_count", "numeric", "Download count", array( '#theme' => false ) );
  bw_register_field( "_oikpv_update_count", "numeric", "Update count", array( '#theme' => false ) );
  //bw_register_field( "oikpv_no_underscore", "numeric", "No underscore", array( '#theme' => false ) );
  bw_register_field_for_object_type( "_oikpv_version", $post_type, true );
  bw_register_field_for_object_type( "_oikpv_plugin", $post_type, true );
  bw_register_field_for_object_type( "_oikpv_download_count", $post_type, true );
  bw_register_field_for_object_type( "_oikpv_update_count", $post_type, true );
  //bw_register_field_for_object_type( "oikpv_no_underscore", $post_type, true );
  oikp_columns_and_titles( $post_type );
}

function oik_pluginversion_columns( $columns ) {
  $columns['_oikpv_version'] = __("Version"); 
  $columns['_oikpv_plugin'] = __("Plugin" );
  $columns['_oikpv_download_count'] = __("Downloads" );
  $columns['_oikpv_update_count'] = __("Updates" );
  return( $columns ); 
}

/**
 * Return the fields to be displayed in a table
 */ 
function oik_pluginversion_fields( $fields, $arg2 ) {
  $fields['title'] = 'title';
  $fields['excerpt'] = 'excerpt';
  $fields['_oikpv_version'] = '_oikpv_version' ;
  return( $fields );
}

/**
 * Titles are remarkably similar to columns for the admin pages
 * Except when you don't want them by default
 */
function oik_pluginversion_titles( $titles, $arg2, $fields=null ) {
  $titles['_oikpv_version'] = __("Version"); 
  //return( oik_pluginversion_columns( $titles, $arg2 ) );
  return( $titles ); 
}

/**
 * Create the oik_premiumversion custom post type
 *
 * Any zip file that is attached to this post type should automatically be stored
 * in a safe location so that it can only be downloaded by a controlled request
 * Protected (premium) files will require a valid API key
 */
function oik_register_oik_premiumversion() {
  $post_type = 'oik_premiumversion';
  $post_type_args = array();
  $post_type_args['label'] = 'oik premium versions';
  $post_type_args['description'] = 'oik premium plugin version';
  $post_type_args['taxonomies'] = array( "required_version", "compatible_up_to" );
  $post_type_args['menu_icon'] = 'dashicons-shield-alt';
  $post_type_args['supports'] = array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'publicize', 'home', 'clone', 'custom-fields' );
  $post_type_args['show_in_rest'] = true;
  //	$post_type_args['has_archive'] = true;
  bw_register_post_type( $post_type, $post_type_args );
  oik_register_oik_pluginversion_fields( $post_type );
}


function oik_premiumversion_columns( $columns ) {
  return( oik_pluginversion_columns( $columns ) );
}

/**
 * Return the fields to be displayed in a table
 */ 
function oik_premiumversion_fields( $fields, $arg2 ) {
  $fields['title'] = 'title';
  $fields['excerpt'] = 'excerpt';
  $fields['_oikpv_version'] = '_oikpv_version' ;
  return( $fields );
}

/**
 * Titles are remarkably similar to columns for the admin pages
 */
function oik_premiumversion_titles( $titles, $arg2, $fields=null ) {
  return( oik_pluginversion_titles( $titles, $arg2 ) );
}

/** 
 * Add the "oik_plugin" feed
 *
 * @TODO ... don't know why! Herb 2012/07/27. Still not checked 2014/10/12
 */
function oikp_plugin_add_feed() {
 $hook = add_feed( 'oik_plugin', "oikp_plugin_feed");
 bw_trace2( $hook );
}

function oikp_plugin_feed() {
  oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
  oik_lazy_oikp_plugin_feed();
}

/* 
 * Return true if the current post is of the selected $post_type
 * @param string $test_post_type - post type to check for
 * @return book - true if the current post IS of this type, false in all other cases
 */
function oikp_check_post_type( $test_post_type="oik_premiumversion" ) {
  //bw_trace2( $pagenow, "pagenow" );
  $post_id = bw_array_get( $_REQUEST, "post_id", null );
  if ( $post_id ) { 
    $post_type = get_post_type( $post_id );
    $result = $post_type == $test_post_type ;
  } else {
    $result = false; 
  }  
  return( $result );
}

/**
 * Builds the external directory name
 * 
 * For non Windows servers (e.g. Linux) we need to find the "home" directory and build $external_dir from there
 * e.g.
 * If [DOCUMENT_ROOT] => /home/t10scom/public_html
 * and $dir parameter is '/zipdir/'
 * then external_directory will become "/home/t10scom/zipdir/"
 * 
 * @param string - required external directory name with leading and trailing slashes
 * @return string - external directory with "home" directory prepended
 */
function oikp_build_external_dir( $dir ) {
  $external_dir = dirname( $_SERVER['DOCUMENT_ROOT'] );
  $external_dir .=  $dir;
  return( $external_dir );
}

/**
 * Can we alter the filter in wp_handle_upload to control where the file gets stored and the 
 * download URL for it?
 
 * custom backgrounds and custom headers are created using 
 * wp_file_upload then wp_insert_attachment
 *  


by renaming the .zip file then it's no longer accessible from the uploads directory
BUT we still have links to it and to all intents and purposes it still exists.
So now we can intercept calls to 
download?plugin=fred&version=1.18 and access the file from the renamed directory.


*/
function oikp_create_new_file_name( $old_file ) {
  //global $pagenow;
  $file = basename( $old_file, ".zip" );
  list( $plugin, $version ) = explode( ".", $file, 2);
  if ( $plugin && $version ) {
     $zipdir = bw_get_option( "zipdir", "bw_plugins_server" );
     if ( PHP_OS == "WINNT" ) {
       $new_file = "C:\\{$zipdir}\\";
     } else {
       $new_file = oikp_build_external_dir( "/{$zipdir}/" );
     }   
     $new_file .= $plugin;
     $new_file .= ".";
     $new_file .= $version;
     $new_file .= ".zip";
   } else {
     $new_file = null;
   }  
  return( $new_file );
}


/**
 * Implement 'wp_handle_upload' filter 
 *
 * @param array $file array containing file, url and type
 
    C:\apache\htdocs\wordpress\wp-includes\plugin.php(142:0) 2012-07-23T21:46:22+00:00 8485 cf! apply_filters(14338) 3 Array
    (
        [0] => wp_handle_upload
        [1] => Array
            (
                [file] => C:\apache\htdocs\wordpress/wp-content/uploads/2012/07/blogger-301-redirect.1.9.51.zip
                [url] => http://qw/wordpress/wp-content/uploads/2012/07/blogger-301-redirect.1.9.51.zip
                [type] => application/zip
            )

        [2] => upload
    ) 
    
 * @param string action. e.g. 'upload' 
 * @returns array $file - unchanged
 * 
 * In [bw_plug name="easy-digital-downloads"] the files are uploaded to an 'edd' directory in the uploads folder
 * if the post type of the current post is "download" and the current page ( $pagenow ) is  'async-upload.php' or 'media-upload.php'
 *
 * Here we check for a zip file ( with plugin name and version number) being uploaded for post_type "oik_premiumversion"
 *
 * If so the file is renamed ( moved ) to a secret target directory
 * if not then we don't do anything
 * In either case the attachment is recorded as if the file has been stored in the uploads
*/
function oikp_handle_upload( $file, $action ) {
  bw_trace2();
  $type = bw_array_get( $file, "type", null );
  if ( $type == "application/zip" ) {
     $rename = oikp_check_post_type( "oik_premiumversion" );
     if ( $rename ) {  
       $old_file = bw_array_get( $file, 'file', null );
       $new_file = oikp_create_new_file_name( $old_file );
       if ( $new_file ) {
         $renamed = rename( $old_file, $new_file );
       }
     }    
  }     
  return( $file );
}

/**
 * Add some content before 'the_content' filtering
 * 
 * @param post $post
 * @param string $content - the current content
 * @return string additional content
 */
function oikp_the_post_oik_plugins( $post, $content ) {
  do_action( "oik_add_shortcodes" );
  $additional_content = null;
  $slug = get_post_meta( $post->ID, "_oikp_slug", true );
  if ( !is_single() && false === strpos( $post->post_content, "[oikp_download" ) ) {
    $additional_content .= "[clear][oikp_download plugin='$slug' text='" ;
    $additional_content .= __( "Download", 'oik-plugins' );
    $additional_content .= " ";
    $additional_content .= $post->post_title;
    $additional_content .= "']";
    //  gobang();
  }

  if ( is_single() ) {
    oik_require( "includes/class-oik-plugins-content.php", "oik-plugins" );
		$oik_plugins_content = new OIK_plugins_content();
		$content = $oik_plugins_content->additional_content( $post, $slug );
    //$content = oikp_additional_content( $post, $slug );
    // $content = $additional_content . $content;
  } else {
    $content .= $additional_content;
  }  
  //bw_trace2( $additional_content, "additional content" );
  return( $content );
}

/**
 * Add some content before 'the_content' filtering completes for oik_pluginversion
 *
 * If neither [bw_fields] nor [bw_field is included in the content so far then append it.
 * To cater for WordPress 5.0 and the fields block we've made the search less limiting
 * ... now just looking for 'field', was '[bw_field'.
 *
 * @param post $post - the current post
 * @param string $content - the current content
 * @return string additional content
 *
 */
function oikp_the_post_oik_pluginversion( $post, $content ) {
  // bw_trace2();
  if ( false === strpos( $post->post_content, "field" ) ) {
    $additional_content = "[bw_fields]";
  } else {
    $additional_content = null;
  }     
  return( $additional_content ); 
}

/**
 * Autogenerate additional content for selected post_types
 *
 * We've remove the $recursed logic as it didn't work when "the_content" was invoked more than once.
 * It means we can manually code [oikp_download] or [bw_fields] where we like and not have it added automatically every time.
 * 
 * 
 */
function oikp_the_content( $content ) {
  //bw_backtrace();
  //static $recursed = false;
  //if ( !$recursed ) {
  global $post;
  //  bw_trace2( $post, "global post" );
  if ( $post ) {
    switch ( $post->post_type ) {
      case "oik-plugins": 
        
        $content = oikp_the_post_oik_plugins( $post, $content );
        break;
          
      case "oik_pluginversion": 
      case "oik_premiumversion":
        $content .= oikp_the_post_oik_pluginversion( $post, $content ); 
        break;  
    }
  }  
  //}  
  //$recursed = true; 
  //remove_annoying_filters();
  //global $wp_filter;
  //bw_trace2( $wp_filter, "wp_filter", false ); 
  return( $content );
}

/**
 * Removed annoying filters
 *
 
                    [wptexturize] => Array
                        (
                            [function] => wptexturize
                            [accepted_args] => 1
                        )

                    [convert_smilies] => Array
                        (
                            [function] => convert_smilies
                            [accepted_args] => 1
                        )

                    [convert_chars] => Array
                        (
                            [function] => convert_chars
                            [accepted_args] => 1
                        )

                    [wpautop] => Array
                        (
                            [function] => wpautop
                            [accepted_args] => 1
                        )

                    [shortcode_unautop] => Array
                        (
                            [function] => shortcode_unautop
                            [accepted_args] => 1
                        )

                    [prepend_attachment] => Array
                        (
                            [function] => prepend_attachment
                            [accepted_args] => 1
                        )

                )
 */
 
function remove_annoying_filters() {
  //remove_filter( 'the_content', 'wptexturize' );
  //remove_filter( 'the_content', 'wpautop' );
  //remove_filter( 'the_content', 'convert_smilies' );
  //remove_filter( 'the_content', 'convert_chars' );
  //remove_filter( 'the_content', 'shortcode_unautop' );
  //remove_filter( 'the_content', 'prepend_attachment' );
}




/**
 * Implement "oik_admin_menu" for oik-plugins 
 */
function oikp_admin_menu() {
  oik_require( "admin/oik-plugins.php", "oik-plugins" );
  oikp_lazy_admin_menu();
}

/**
 * Theme the GitHub repository field if set
 *
 * @param string $key - the field name ( _oikp_git )
 * @param array $value - field values array
 * @param array $field - field definition
 */
function bw_theme_field_sctext__oikp_git( $key, $value, $field ) {
 //bw_trace2();
 if ( $value ) {
   $github = $value[0];
 } else {
   $github = false;
 }  
 if ( $github ) {
   alink( "github", "http://github.com/$github", $github );
 }  
}
  
/**
 * Theme the Purchasable product field to show FREE if not set
 *
 * @param string $key - the field name ( _oikp_prod )
 * @param array $value - field values array
 * @param array $field - field definition
 */
function bw_theme_field_noderef__oikp_prod( $key, $value, $field ) {
 //bw_trace2();
 if ( $value && $value[0] ) {
   bw_theme_field_noderef( $key, $value, $field );
 } else {
   e( "FREE" );
 }  
}  

/**
 * Validate the API key by comparing with the saved option value
 * 
 * @param string $return_value - the current value of the filter result
 * @param string $apikey - the original value of the apikey
 * @return string $return_value - which may become null
 */
function oikp_oik_validate_apikey( $return_value, $apikey ) {
  $bw_apikey = bw_get_option( "apikey", "bw_plugins_server" );
  if ( $bw_apikey == $apikey ) {
    $return_value = $apikey;
  } else {
    $return_value = null; 
  } 
  // bw_trace2();   
  return( $return_value );
}
 
/**
 * Implement "admin_notices" for oik-plugins 
 * 
 * Version | Dependencies
 * ------- | -------------------------------
 * v1.10   | oik v2.1 and oik-fields v1.33
 * v1.11   | oik v2.2 and oik-fields v1.36
 * v1.13   | oik v2.3 and oik-fields v1.39
 * v1.15.1 | oik v2.6 and oik-fields v1.40
 * v1.15.2 | oik v3.0.0-alpha.0820 and oik-fields v1.40
 * v1.16.0 | oik v3.2.1 and oik-fields v1.50.0
 */ 
function oikp_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-plugins/oik-plugins.php", "oikp_activation" ); 
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      require_once( "admin/oik-activation.php" );
    }  
  }  
  $depends = "oik-fields:1.50,oik:3.2.1";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Implement "oik_clone_filter_all_post_meta"
 *
 * We don't want the following fields to be updated during cloning
 * - _oikpv_download_count
 * - _oikpv_update_count
 */
function oikp_oik_clone_filter_all_post_meta( $post_meta ) {
	unset( $post_meta['_oikpv_download_count'] );
	unset( $post_meta['_oikpv_update_count'] );
	return( $post_meta );
}

/**
 * Implement "oik_clone_filter_media_file" for oik-plugins
 *
 * @param array $media_file
 * @param object $attachment
 * @return array $media_file
 */
function oikp_oik_clone_filter_media_file( $media_file, $attachment ) {
	if ( $media_file['type'] == "application/zip" ) {
		$attachment_post_type = get_post_type( $attachment->post_parent );
		if ( $attachment_post_type == "oik_premiumversion" ) {
			$new_file = oikp_create_new_file_name( $media_file[ 'file'] );
			$media_file['file'] = $new_file;
		}
		bw_trace2();
	}
	return( $media_file );
}
