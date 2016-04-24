<?php
/** 
Author: bobbingwide
Author URI: http://www.bobbingwide.com
License: GPL2

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
 * Handle invalid request
 */
function _oikp_lazy_redirect( $oik_plugin_action ) {
  echo "Invalid request $oik_plugin_action";
}

/**
 * Support /plugins/download/?plugin=plugin&version=version&apikey=apikey&action=update/download&id=id 
 */

function _oikp_lazy_redirect_download( $oik_plugin_action ) {
  $plugin =  bw_array_get( $_REQUEST, "plugin", null );
  $version = bw_array_get( $_REQUEST, "version", null );
  $apikey = bw_array_get( $_REQUEST, "apikey", null );
  $id = bw_array_get( $_REQUEST, "id", null );
  oikp_download_file( $plugin, $version, $apikey, $id ); 
}

/**
 * validate the plugin and version against the given post ID
 * @param post $plugin_version 
 * @param string $plugin - plugin name - the "post_name"
 * @param string $version - plugin version string e.g. 1.16.0821 
 * @return mixed $response - null if OK or WP_error 
 */
function oikp_validate_pluginversion( $plugin_version, $plugin, $version, $apikey ) {
  $actual_version = oikp_get_latestversion( $plugin_version );  
  if ( $actual_version == $version ) {
    $actual_plugin = oikp_get_plugin( $plugin_version );
    bw_trace2( $actual_plugin, "actual_plugin" );   
    if ( $actual_plugin && $actual_plugin->post_name == $plugin ) {
      $response = oikp_validate_apikey( $plugin_version, $actual_plugin, $apikey );
    } else {
      $response = bw_wp_error( "not-found", "Invalid plugin name", $plugin );
    }
  } else {
    $response = bw_wp_error( "not-found", "Invalid version" );
  }
  return( $response );
}

/** 
 * Load a record for this apikey and check that it's OK
 *
 */
function oikp_load_apikey( $apikey, $actual_plugin ) {
  $result = apply_filters( "validate_apikey", $apikey, $actual_plugin );
  bw_trace2( $result, "validate_apikey result" );
  return( $result );
}

/**
 * validate the apikey for this selected plugin version 
 * @param post - $actual_version
 */ 
function oikp_validate_apikey( $actual_version, $actual_plugin, $apikey ) {
  if ( $actual_version->post_type == "oik_pluginversion" ) {
    $response = null;  // We don't need to check the API key for free plugins 
  } else {
    $response = oikp_load_apikey( $apikey, $actual_plugin );
  }
  return( $response );
} 

/**
 * Increment the downloads for this plugin version 
 * 
 * The field incremented depends upon the &action=update/download
 * It's OK for the count to start from blank ( = 0 )
 * 
 * 
 */
function oikp_increment_downloads( $id ) {
  $action = bw_array_get( $_REQUEST, "action", "download" );
  $count = get_post_meta( $id, "_oikpv_${action}_count", true );
  $new_count = $count + 1;
  $success = update_post_meta( $id, "_oikpv_${action}_count", $new_count, $count  );
} 

/**
 * Deliver the plugin version requested
*/ 
function oikp_download_file( $plugin, $version, $apikey, $id ) {
  $plugin_version = get_post( $id );  
  bw_trace2( $plugin_version );
  /* check the $version and $plugin for the post version that we have loaded */
  $response = oikp_validate_pluginversion( $plugin_version, $plugin, $version, $apikey );
  if ( !is_wp_error( $response ) ) {
    $file = oikp_get_attachment( $plugin_version );
    if ( $file ) {
      oikp_increment_downloads( $id );
      if ( $plugin_version->post_type == "oik_premiumversion" ) {
        $file = oikp_create_new_file_name( $file ); 
      } else {
        $upload_dir = wp_upload_dir();
        $baseurl = $upload_dir['baseurl'];
        $file = $baseurl . "/". $file;
      }
      oikp_force_download( $file );  
      // Nothing happens after this
    } else {
      $response = bw_wp_error( "not-found", "Attachment not found" );
    }  
      
  } else { 
    //oikp_error( __FUNCTION__ );
    bw_trace2();
    //$response = bw_wp_error( "not-found", "plugin version not found" );
  }
  echo serialize( $response );
} 
     

/**
 * Force the download of a file
 *
 * @param string $file - full file name 
 *
 */
function oikp_force_download( $file ) {
  bw_trace2();
  $file_content = file_get_contents( $file );  
  $filename = basename( $file );
  header( 'Content-type: application/force-download' );  
  header( "Content-Disposition: attachment; filename=\"$filename\"" );  
  echo $file_content;  
  exit;
}

/**
 * Perform oikp_update_check
 * 
 * @param string $oik_plugin_action - expected to be "update-check"
 */
function _oikp_lazy_redirect_update_check( $oik_plugin_action ) {
  oikp_update_check( $oik_plugin_action );
}

/**
 * Perform oikp_plugin_information
 * 
 * @param string $oik_plugin_action - expected to be "redirect_info"
 */
function _oikp_lazy_redirect_info( $oik_plugin_action ) {
  oikp_plugin_information( $oik_plugin_action );
}

/**
 * Perform oikp_check_these 
 *
 * Given an array of plugins and their current versions to check
 * create a response indicating which of them have new versions.
 * Don't return any WP_errors
 *
 * @param string $oik_plugin_action - expected to be "check-these"
 */
function _oikp_lazy_redirect_check_these( $oik_plugin_action ) {
  bw_trace2();
  $response = array();
  $action = bw_array_get( $_REQUEST, "action", null );
  if ( $action == $oik_plugin_action ) {
    $check = bw_array_get( $_REQUEST, "check", null );
    if ( $check ) {
      $check = stripslashes( $check ); 
      bw_trace2( $check, "check", false ); 
      $check_these = unserialize( $check );
      
      bw_trace2( $check_these, "check_these", false );
      
      $checked = bw_array_get( $check_these, "checked", null );  
      bw_trace2( $checked, "checked", false );
      foreach ( $checked as $plugin => $version ) {
        $result = oikp_perform_update_check( $plugin, $version );
        bw_trace2( $result, "result", false );
        if ( $result ) {
          $response[$plugin] = $result;
        }
      }
    }
  }
  $toecho = serialize( $response ); 
  bw_trace2( $toecho, "toecho" );
  echo $toecho;
}

/**
 * Check for a version or new version
 *
 * @param string $plugin - the plugin to check 
 * @param string $current_version - current version to check against
 * @return stdClass - 
 * 
 */
function oikp_perform_update_check( $plugin, $current_version=null ) {
  $response = new stdClass;
  //$version = bw_array_get( $_REQUEST, "version", null );
  oik_require( "admin/oik-admin.inc" );
   
  $slug = bw_get_slug( $plugin );   
  $post = oikp_load_plugin( $slug );
  if ( $post ) { 
    $version = oikp_load_pluginversion( $post );
    if ( $version ) { 
      $response->slug = $slug;
      $response->new_version = oikp_get_latestversion( $version );
      if ( $current_version && version_compare( $response->new_version, $current_version, "<=" ) ) {
        $response = null;
      } else {
        $response->url = "http://qw/wpit/oik_plugin/" . $slug;
        $response->url = home_url( "/oik_plugin/" . $slug );
        $response->plugin = get_post_meta( $post->ID, "_oikp_name", true );
       
        $apikey = bw_array_get( $_REQUEST, "apikey", null );
      
        $package = oikp_get_package( $post, $version, $response->new_version, $apikey );
        if ( $package ) {  
          $response->package = $package; 
        } else { 
          $response = bw_wp_error( "not-found", "Package not found" );
        }
      }      
    } else {
      $response = bw_wp_error( "not-found", "Version not found" );
    }  
  } else {
    $response = bw_wp_error( "not-found", "Plugin not found" );  
  }
  
  if ( $current_version && is_wp_error( $response ) ) {
    $response = null;
  }
  return( $response );
} 

/**
 * Invoke the correct server function
 * 
 * Pretend it's AJAX so that we don't inadvertently echo 'wrong stuff' to the client
 *
 */ 
function oikp_lazy_redirect( $oik_plugin_action ) {
  if ( !defined('DOING_AJAX') ) {
    define( 'DOING_AJAX', true );
  }
  $funcname = bw_funcname( "_oikp_lazy_redirect", $oik_plugin_action );
  $funcname( $oik_plugin_action );
  //session_write_close();
  exit();
}

/**
 * Load the plugin by $slug
 * 
 * Load the plugin given the main plugin's folder name
 * e.g. for the original version of oik we load the oik plugin even when the plugin name is oik/oik-bbpress.php
 * 
 * @param string $slug - the plugin slug = folder
 * @return post|null
 */
function oikp_load_plugin( $slug ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array();
  $atts['post_type'] = "oik-plugins";
  // $atts['name'] = $slug;
  $atts['meta_key'] = '_oikp_slug';
  $atts['meta_value'] = $slug; 
  $atts['numberposts'] = 1; 
  $atts['exclude'] = -1;
  $posts = bw_get_posts( $atts );
  $post = bw_array_get( $posts, 0, null );
  bw_trace2( $post, "post", true, BW_TRACE_VERBOSE );
  return( $post );
}

/**
 * Load the latest plugin version
 *
 * Retrieve the latest plugin version for the given plugin.
 * Note: We needed to add exclude=-1 to the call to get posts 
 * to cater for when the latest plugin version happens to be the first "post" displayed on the home page.
 * 
 * @param object $post - the selected oik_plugin post
 * @return object - the pluginversion post
 */
function oikp_load_pluginversion( $post ) {
  oik_require( "includes/bw_posts.inc" );
  //$post_types = array( 2 => "oik_pluginversion"
  //                   , 3 => "oik_premiumversion"
  //                   
  //                  );
  $post_types = bw_plugin_post_types();
  
  $plugin_type = get_post_meta( $post->ID, "_oikp_type", true );
  bw_trace2( $plugin_type, "plugin_type" );
  if ( $plugin_type ) {
    $atts = array();
    $atts['post_type'] = bw_array_get( $post_types, $plugin_type, "oik_pluginversion" );
    $atts['meta_key'] = "_oikpv_plugin";
    $atts['meta_value'] = $post->ID;
    $atts['numberposts'] = 1;
    $atts['orderby'] = "post_date";
    $atts['order'] = "desc";
    $atts['exclude'] = -1;
    $posts = bw_get_posts( $atts );
    $version = bw_array_get( $posts, 0, null );
  } else {
    // None - so we don't have a plugin type - this is how the WordPress core is catalogued
    bw_backtrace();
    $version = null;
  }  
  return( $version );
}

/**
 * Return the version metadata
 */
function oikp_get_latestversion( $version ) { 
  if ( $version ) 
    $plugin_version = get_post_meta( $version->ID, "_oikpv_version", true );
  else
    $plugin_version = null;
  return( $plugin_version );
}


/**
 * Load the plugin for the given plugin version or premium version node
 */

function oikp_get_plugin( $version ) { 
  if ( $version ) { 
    $plugin_id = get_post_meta( $version->ID, "_oikpv_plugin", true );
    $plugin = get_post( $plugin_id );
  } else {
    $plugin = null;
  }
  return( $plugin );
}

/**
 * Return the value of the field depending on the field type
 */
if ( !function_exists( "bw_return_field" ) ) {
function bw_return_field( $field_name=null, $data=null ) {
  global $bw_fields;
  $value = $data; 
  $field = bw_array_get( $bw_fields, $field_name, null );
  if ( $field ) {
    $type = bw_array_get( $field, "#field_type", null );
    if ( $type ) {
      $funcname = "bw_return_field_$type";
      if ( is_callable( $funcname ) ) {
        $value = call_user_func( $funcname, $field_name, $data, $field );
     }  
    }  
  }  
  return( $value );
}
}     
 
/**
 * Get the value of the "required_version" custom category ( was _oikpv_requires ) 
 * 
 * Note: There should only be ONE value but WordPress does cater for a list
 */
function oikp_get_requires( $version ) { 
  if ( $version ) { 
    // $requires = get_post_meta( $version->ID, "_oikpv_requires", true );
    $requires = get_the_term_list( $version->ID, "required_version", "", ",", "" );
    bw_trace2( $requires, "required_version" );
    //$requires = bw_return_field( "_oikpv_requires", $requires );
    
  } else {
    $requires = null;
  }  
  bw_trace2( $requires, "requires" ); 
  return( $requires );
}


/**
 * Return largest value of a given field from array of objects
 * 
 * @param array $objects - array of objects, each of which should contain the given field name
 * @param string $field - the field name
 * @return string - the largest value - determined using version_compare()
 */
function oikp_get_largest( $objects, $field ) {
  //bw_trace2();
  $largest = "";
  if ( is_array( $objects ) && count( $objects ) ) {
    foreach ( $objects as $object ) {
      $value = $object->$field;
      if ( version_compare( $value, $largest ) ) {
        $largest = $value;
      }
    }
  }
  return( $largest ); 
} 

/**
 *
[compatibility] => Array
    (
        [3.4.1] => Array
            (
                [2.1.11] => Array
                    (
                        [0] => 80
                        [1] => 5
                        [2] => 4
                    )

                [2.1.12] => Array
                    (
                        [0] => 87
                        [1] => 15
                        [2] => 13
                    )

                [2.1.13] => Array
                    (
                        [0] => 83
                        [1] => 6
                        [2] => 5
                    )

                [2.1.9] => Array
                    (
                        [0] => 100
                        [1] => 1
                        [2] => 1
                    )

            )

    ) 
    
    */

function oikp_get_compatibility( $version, $version_string ) {
  $tested = get_the_terms( $version->ID, "compatible_up_to" );
  $compatibility = array();
  if ( !is_WP_error( $tested ) && is_array( $tested) && count( $tested ) ) {
    foreach ( $tested as $object ) {
      $wordpress_version = $object->name;
      $compatibility[ $wordpress_version ] = array( $version_string => array( 100, 1, 1  ) );
    } 
  }  
  return( $compatibility );
} 

/**
 * Get the value of the "compatible_up_to" custom category ( was _oikpv_tested ) 
 * 
 * Note: There should only be ONE value but WordPress does cater for a list
 * However, this means the "Compatible with WordPress" test fails
 * So now we don't return the term_list
 */
function oikp_get_tested( $version ) { 
  if ( $version ) {
    // $tested = get_post_meta( $version->ID, "_oikpv_tested", true );
    //$tested = get_the_term_list( $version->ID, "compatible_up_to", "", ",", "" );
    //$tested = bw_return_field( "_oikpv_tested", $tested );
    $tested2 = get_the_terms( $version->ID, "compatible_up_to" );
    //bw_trace2( $tested2, "tested2" );
    $tested = oikp_get_largest( $tested2, "name" );
    //bw_trace2( $tested, "tested" );
  }  
  else
    $tested = null;
  return( $tested );
}


function oikp_get_attachment( $version ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array( "post_type" => "attachment" 
               , "post_parent" => $version->ID
               , "numberposts" => 1
               , "post_mime_type" => "application/zip"
               );
  $posts = bw_get_posts( $atts );
  $attachment = bw_array_get( $posts, 0, null );
  bw_trace2( $attachment );
  if ( $attachment ) {
    $file = get_post_meta( $attachment->ID, "_wp_attached_file", true );
  } else {
    $file = null;
  }
  bw_trace2( $file );
  return( $file );
}

/** 
 * Return the package URL for this plugin
 * 
 * @param post $post the plugin being downloaded
 * @param post $version the post of the plugin version
 * @param string $new_version the new version number
 * @param string $action the action being performed
 * @param string $apikey the (validated) apikey passed on the request
 *   
 * Even when it's a FREE plugin version type we use the /plugins/download URL form rather
 * than a direct download of the file from the original upload directory
 * that way we can keep track of the number of downloads and the number of updates (from previous versions)
 * 
 * Note: The "plugin" name is the "post_name" not the slug. This is checked during oikp_validate_pluginversion()
 */
function oikp_get_package( $post, $version, $new_version, $apikey=null, $action="update") {

  $file = oikp_get_attachment( $version );
  if  ( $file ) {
    $package = home_url( "/plugins/download" );
    $package = add_query_arg( array( "plugin" => $post->post_name
                                   , "version" => $new_version
                                   , "id" =>  $version->ID 
                                   , "action" => $action
                                   , "apikey" => $apikey
                                   ), $package );
  } else {
    $package = null;
  } 
  bw_trace2( $package, "package" );   
  return( $package );
  
} 
 
/** 
 * @link http://lewayotte.com/2012/04/18/custom-wordpress-plugin-update-repository/
 
   [action] => update-check
    [plugin_name] => oik-fum/oik-fum.php
    [version] => 1.0
 */
function oikp_update_check( $oik_plugin_action="update-check" ) {
  $response = new stdClass;
  $action = bw_array_get( $_REQUEST, "action", null );
  if ( $action == $oik_plugin_action ) {
    $plugin = bw_array_get( $_REQUEST, "plugin_name", null );
    if ( $plugin ) {
      $response = oikp_perform_update_check( $plugin ); 
    }
  } else {
    $response = bw_wp_error( "invalid-action", "Invalid action $action" );
  }
  bw_trace2( $response, "response" );
  echo serialize( $response );      
} 

/** 
 * Get userdata for the selected user ID 
 *
 */
if ( !function_exists( "bw_get_userdata" ) ) { 
function bw_get_userdata( $id, $field, $default ) {
  $userdata = get_userdata( $id );
  bw_trace2( $userdata, "userdata" );
  
  $value = $userdata->data->$field;
  bw_trace2( $value );
  return( $value );
}
}  

/**
 * Return a link to the author's home page
 * Determine the author's display name from the post author
 * then append it to their website URL
 * 
 * Note: The value in the oik-plugins admin profile is not used in this version.
 * 
 */
function bw_get_author_name( $post ) {
  // bw_trace2( $post );
  $author = $post->post_author;
  oik_require( "admin/oik-plugins.php", "oik-plugins" );
  $users = bw_user_list();
  $author_name = bw_array_get( $users, $author, "bobbingwide" );
  $url = bw_get_userdata( $author, "user_url", "http://www.bobbingwidewebdesign.com/about/herb/" );
  $link = retlink( null, $url, $author_name );
  return( $link );
}

function bw_get_author_profile( $post ) {
  return( "http://profiles.wordpress.org/bobbingwide" );
} 

/**
 * Return the defined FAQ page for the plugins server
 *
 * @param ID $post - 
 */
function oikp_get_FAQ( $post ) {
  oik_require( "admin/oik-admin.inc" );
  $faq = bw_get_option( "faq", "bw_plugins_server" );
  if ( $faq ) {
    $post = bw_get_post( $faq, "page" );
    e( bw_excerpt( $post ));
  } else {  
    oik_documentation();
  }
  return( bw_ret() );
}

/**
 * Return the sections for the plugin version
 *
 * - Description comes from the excerpt
 * - Changelog comes from the plugin version
 * - Info - comes from the FAQ, if defined or the default oik documentation
 *
 * Shortcodes are expanded. 
 * 
 * @todo But note that some shortcodes (e.g. [bw_fields featured] ) are not happy to expand if is_single() returns false
 * which is what currently happens since we don't intercept the original wp_query.
 * 
 * Three options: 
 * 1. Force is_single() to return true
 * 2. Perform the interception
 * 3. Change the logic around is_single() 
 *  
 * 
 */
function oikp_get_sections( $post, $version ) {
  do_action( "oik_add_shortcodes" );
  $sections = array();
  $sections['description'] = do_shortcode( bw_excerpt( $post ));
  $sections['changelog' ] = do_shortcode( $version->post_content );
  $sections['info'] = do_shortcode( oikp_get_FAQ( $post ));
  return( $sections ); 
} 


/**
 * Return the number downloaded - when we're ready to tell them! 
 
 * Note: WordPress expects this figure to be numeric. It doesn't like strings such as "n/a"
 * a blank string seems OK for when it displays plugin information
 * [bw_plug name=oik table=y] just leaves a blank
 */
function oikp_get_downloaded( $post, $version ) { 
  return( "" );
} 


/** 

https://spreadsheets.google.com/pub?key=0AqP80E74YcUWdEdETXZLcXhjd2w0cHMwX2U1eDlWTHc&authkey=CK7h9toK&hl=en&single=true&gid=0&output=html


        $response->name = 'my_plugin_name';  
        $response->slug = 'my_plugin_slug';  
        $response->requires = '3.3';  
        $response->tested = '3.3.1';  
    $response->rating = 100.0; //just for fun, gives us a 5-star rating :)  
        $response->num_ratings = 1000000000; //just for fun, a lot of people rated it :)  
        $response->downloaded = 1000000000; //just for fun, a lot of people downloaded it :)  
        $response->last_updated = "2012-04-15";  
        $response->added = "2012-02-01";  
        $response->homepage = "http://plugin.url/";  
        $response->sections = array(  
            'description' =>  'Add a description of your plugin',  
            'changelog' =>  'Add a list of changes to your plugin'  
        );  
        $response->download_link = 'http://plugin.url/download/location'; 
        
upgrade_notice = up to 300 characters saying why the user should upgrade 
http://qw/wordpress/plugins/info/?action=plugin-information&request=O:8:%22stdClass%22:2:%7Bs:4:%22slug%22;s:10:%22oik-fields%22;%7D
        
http://qw/wordpress/plugins/info?action=plugin-information&request=O:8:"stdClass":2:{s:4:"slug";s:7:"bbboing";s:8:"per_page";i:24;}
                                                                   O:8:"stdClass":2:{s:4:"slug";s:7:"bbboing";s:8:"per_page";i:24;}
                                                                   
http://qw/wordpress/plugins/info/?action=plugin-information&request=O%3A8%3A%22stdClass%22%3A1%3A%7Bs%3A4%3A%22slug%22%3Bs%3A10%3A%22oik-fields%22%3B%7D                                                                   
                                                              
           [body] => O:8:"stdClass":18:{s:4:"name";s:8:"BackWPup";
           s:4:"slug";s:8:"backwpup";
           s:7:"version";s:6:"2.1.13";
           s:6:"author";s:56:"<a href="http://danielhuesken.de">Daniel H&#252;sken</a>";
           s:14:"author_profile";s:43:"http://profiles.wordpress.org/danielhuesken";
           s:12:"contributors";a:1:{s:13:"danielhuesken";s:43:"http://profiles.wordpress.org/danielhuesken";}
           s:8:"requires";s:3:"3.1";
           s:6:"tested";s:5:"3.4.1";
           s:13:"compatibility";a:1:{s:5:"3.4.1";a:4:{s:6:"2.1.11";a:3:{i:0;i:80;i:1;i:5;i:2;i:4;}s:6:"2.1.12";a:3:{i:0;i:87;i:1;i:15;i:2;i:13;}s:6:"2.1.13";a:3:{i:0;i:83;i:1;i:6;i:2;i:5;}s:5:"2.1.9";a:3:{i:0;i:100;i:1;i:1;i:2;i:1;}}}
           s:6:"rating";d:91;
           s:11:"num_ratings";i:278;
           s:10:"downloaded";i:308766;
           s:12:"last_updated";s:10:"2012-07-30";
           s:5:"added";s:10:"2009-07-05";
           s:8:"homepage";s:19:"http://backwpup.com";
           s:8:"sections";a:5:{s:11:"description";s:739:"<p>Do backups and more for your WordPress Blog.</p>

<ul>
*/

function oikp_plugin_information( $oik_plugin_action="info" ) {
  $body = bw_array_get( $_REQUEST, "request", null );
  if ( $body ) {
  
    bw_trace2( $body, "body", false );
    $request = unserialize( stripslashes( $body) );
    
    bw_trace2( $request, "request", false );
    $slug = bw_array_get( $request, "slug", null );
    if ( $slug ) {
    
      $response = new stdClass;
      $response->slug = $slug;
      $post = oikp_load_plugin( $slug );
      if ( $post ) { 
        $version = oikp_load_pluginversion( $post );
        if ( $version ) { 
          $response->name = $slug;
          $response->last_updated = $version->post_modified;
          $response->version = oikp_get_latestversion( $version );
          $response->author = bw_get_author_name( $post );
          $response->author_profile = bw_get_author_profile( $post );
          $response->requires = oikp_get_requires( $version );
          $response->tested = oikp_get_tested( $version );
          $response->compatibility = oikp_get_compatibility( $version, $response->version );
          $response->homepage = get_permalink( $post->ID );
          $response->short_description = get_post_meta( $post->ID, "_oikp_desc", true );
          $response->sections = oikp_get_sections( $post, $version );
          $response->download_url = oikp_get_package( $post, $version, $response->version );
          $response->download_link = $response->download_url;
          $response->downloaded = oikp_get_downloaded( $post, $version ); 
        } else {
          $response = bw_wp_error( "not-found", "Version not found" );  
        }
      } else {
        $response = bw_wp_error( "not-found", "Plugin not found" );
      }  
    } else {
      $response = bw_wp_error( "missing_slug", "Slug missing from request" );
    }
  } else {
    $response = bw_wp_error( "invalid_request", "Request missing" );  
  }
  echo serialize( $response );
}    
     
 
/* 
function oikp_dummy_info() {
  $response = new stdClass;

  $response->slug = "oik-pro";
  $response->plugin_name = "oik-pro";  
  $response->new_version = "1.17i"; 
  $response->requires = "3.0.4"; 
  $response->tested = "3.4.1"; 
   
  $response->downloaded = 12126; 
  $response->rating = 100.0; //just for fun, gives us a 5-star rating :)  
  $response->num_ratings = 121; // downloaded / 100   
  $response->last_updated = bw_format_date();
     
  $response->homepage = "http://qw/wpit/oik_plugin/dumbo";
  $response->sections = array( 'description' => "over 70 shortcodes"  
                             , 'changelog' => "change log " 
                             , 'FAQ' => "see the FAQ"
                             );

  echo serialize( $response );
}

*/

