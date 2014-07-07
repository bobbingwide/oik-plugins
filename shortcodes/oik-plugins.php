<?php // (C) Copyright Bobbing Wide 2012-2014

/**
 * [purchase_link id="4747" text="Purchase" style="button" color="green"]
 */
function _oikp_purchase_premiumversion_edd( $link_id, $linked_post, $class ) {
  //p( "purchase link" );
  
  $atts = array( "id" => $link_id );
  e( edd_download_shortcode( $atts ) );
 
} 
 


/**
 * Create a link to purchase a premium plugin
 * 
 */
function _oikp_purchase_premiumversion( $version, $post, $class ) {
  $link_id = get_post_meta( $post->ID, "_oikp_prod", true );
  if ( $link_id ) {
    $linked_post = get_post( $link_id );
    if ( $linked_post && $linked_post->post_type == "download" && class_exists( 'Easy_Digital_Downloads' ) ) {
      _oikp_purchase_premiumversion_edd( $link_id, $linked_post, $class );  
    } else {
      $link = get_permalink( $link_id );
      $text = "Purchase " . $version->post_title ;
      $title = "Purchase " . $version->post_title ;  
      //art_button( $link, $text, $title, $class );
      alink( $class, $link, $text, $title );
    }  
  } else {
    p( "Sorry: Product not available for download for: " . $version->post_name );
  }
}

/** 
 * Create a link to download the FREE version
 */
function _oikp_download_freeversion( $version, $post, $class ) {
  $new_version = oikp_get_latestversion( $version );
  $link = oikp_get_package( $post, $version, $new_version, null, "download" );
  if ( $link ) {
    $text = __( "Download" ); 
    $text .= "&nbsp;";
    $text .= $post->post_name; 
    $text .= "&nbsp;";
    $text .= retstag( "span", "version" );
    $text .= __("version" );
    $text .= "&nbsp;";
    $text .= $new_version ;
    $text .= retetag( "span" );
  
    // $title = "Download " . $post->post_name . " version " . $new_version;
    $title = $text; 
    alink( $class, $link, $text, $title );
    //or 
    //art_button( $link, $text, $title, $class );
  } else {
    p( "Sorry: No download file available for: " . $version->post_name );
  }   
}

/**
 * Create a link to download the WordPress plugin
 */
function _oikp_download_wordpressversion( $post, $slug ) {
  $link = "http://downloads.wordpress.org/plugin/$slug.zip";
  $text = sprintf( __( 'Download %1$s from wordpress.org', "oik-plugins" ), $slug );
  //art_button( $link, $text, $text, "wordpress" );
  alink( "wordpress", $link, $text, $text );
} 

/**
 * 
 */
function _oikp_download_version( $version, $post, $class, $slug ) {
  if ( $version->post_type == "oik_premiumversion" ) {
    _oikp_purchase_premiumversion( $version, $post, $class );    
  } else {
    $plugin_type = get_post_meta( $post->ID, "_oikp_type", true );
    switch ( $plugin_type ) {
      case 0:
        // No plugin type specified - do not create a download link
        break;
      case 1:
        _oikp_download_wordpressversion( $post, $slug );
        break;
        
      case 2:
        _oikp_download_freeversion( $version, $post, $class );      
        break;
        
      case 6: 
        _oikp_download_wordpressversion( $post, $slug );
        br();
        _oikp_download_freeversion( $version, $post, $class );  
        break;    
        
      default: 
        // Do nothing for Premium  (3 or 4 ) or Bespoke ( 5 ) plugin types
    }    
  }
}

/**
 * Provide a download button for the zip attachment to this content or a selected plugin ( plugin="oik-fum" )
 *
 * 
 * For a premium plugin the download should be of the form
 *   plugins/download/?plugin=oik-blogger-redirect&version=1.1.0802&id=51&action=download&apikey=herb
 
 * For a FREE plugin the download should be of the form
 *   plugins/download/?plugin=oik-fum&version=1.1.0802&id=51&action=download&apikey=
 
 * @param array $atts - array of shortcode parameters
 *   plugin=  default: oik
 *   class=   default: download
 * 
 */
function oikp_download( $atts=null ) {
  oik_require( "includes/bw_posts.inc" );
  oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
  oik_require( "admin/oik-admin.inc" );
  // @TODO **?** return the plugin slug from the currently selected $post if it is of type "oik-plugins"
  $slug = null;
  $class = bw_array_get( $atts, 'class', NULL ) . "download" ;
  $plugin = bw_array_get( $atts, "plugin", "oik" );
  if ( $plugin == '.' ) {
    $post_type = bw_global_post_type();
    if ( $post_type == "oik-plugins" ) {
      $post_id = bw_current_post_id();
      $slug = get_post_meta( $post_id, "_oikp_slug", true );
      //bw_trace2( $slug, "slug" );
    } else {
      bw_trace2( "not an oik plugin" );
    }  
  } else {
    $slug = bw_get_slug( $plugin ); 
  } 
  if ( $slug ) { 
    $post = oikp_load_plugin( $slug );
    if ( $post ) {
      $version = oikp_load_pluginversion( $post );
      if ( $version ) { 
        _oikp_download_version( $version, $post, $class, $slug );
      } else {
        $plugin_type = get_post_meta( $post->ID, "_oikp_type", true );
        if ( $plugin_type == 0 ) {
          //  **?** Don't do anything yet
          // alink( null, "http://wordpress.org", "
        } elseif ( $plugin_type == 1 ) {
          _oikp_download_wordpressversion( $post, $slug );
        } else {  
          p( "$plugin: latest version not available for download" );
        }  
      }   
    } else {
      p( "Unknown plugin: $slug " );
    }  
  }  
  return( bw_ret());
}


function oikp_download__help( $shortcode='oikp_download' ) {
  return( "Produce a download button for a plugin" );
}

function oikp_download__syntax( $shortcode='oikp_download' ) {
  oik_require( "includes/oik-sc-help.inc" );
  $syntax = array( "plugin" => bw_skv( "oik", "plugin", "name of the plugin" ) 
//                 , "text" => bw_skv( "dummy", "", "text for the button" )
//                 , "title" => bw_skv( "as text", "", "title for the tooltip" )
                 , "class" => bw_skv( "download", "", "CSS classes for the button" )
                 );
  return( $syntax ); 
}

function oikp_download__example( $shortcode='oikp_download' ) {

  oik_require( "includes/oik-sc-help.inc" );
  $text = "To create a button to download the bbboing plugin" ;
  $example = "plugin=bbboing";
  bw_invoke_shortcode( $shortcode, $example, $text );
}
  

