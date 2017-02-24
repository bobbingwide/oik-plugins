<?php // (C) Copyright Bobbing Wide 2012-2017

/**
 * Display EDD purchase link for a premium plugin
 *
 * `
 * [purchase_link id="4747" text="Purchase" style="button" color="green"]
 * `
 */
function _oikp_purchase_premiumversion_edd( $link_id, $linked_post, $class ) {
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
 *
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
	return( $version );   
}

/**
 * Create a link to download the WordPress plugin
 *
 * 
 */
function _oikp_download_wordpressversion( $post, $slug ) {
  $link = "http://downloads.wordpress.org/plugin/$slug.zip";
  $text = sprintf( __( 'Download %1$s from wordpress.org', "oik-plugins" ), $slug );
  //art_button( $link, $text, $text, "wordpress" );
  alink( "wordpress", $link, $text, $text );
} 

/**
 * Create link(s) to download a version
 *
 */
function _oikp_download_version( $version, $post, $class, $slug ) {
	$free_version = null;    
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
				$free_version = _oikp_download_freeversion( $version, $post, $class );  
        break;
        
      case 6: 
        _oikp_download_wordpressversion( $post, $slug );
        br();
				$free_version = _oikp_download_freeversion( $version, $post, $class );  
        break;    
        
      default: 
        // Do nothing for Premium  (3 or 4 ) or Bespoke ( 5 ) plugin types
    }    
  }
	return( $free_version );
}

function _oikp_download_plugin_version( $plugin_version, $post, $class, $slug ) {
	$version = get_post( $plugin_version );
	//p( "Download version $plugin_version " );	
	br();
	_oikp_download_freeversion( $version, $post, $class );

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
	$plugin_version = 0;
	if ( $plugin == '.' ) {
		$post_type = bw_global_post_type();
		if ( $post_type == "oik-plugins" ) {
			$post_id = bw_current_post_id();
			$slug = get_post_meta( $post_id, "_oikp_slug", true );
			//bw_trace2( $slug, "slug" );
		} elseif ( $post_type == "oik_pluginversion" ) {
			$plugin_version = bw_current_post_id();
			$plugin_id = get_post_meta( $plugin_version, "_oikpv_plugin", true );
			$slug = get_post_meta( $plugin_id, "_oikp_slug", true );
			
		} elseif ( $post_type == "oik_premiumversion" ) {
			$plugin_version = bw_current_post_id();
			$plugin_id = get_post_meta( $plugin_version, "_oikpv_plugin", true );
			$slug = get_post_meta( $plugin_id, "_oikp_slug", true );
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
				$free_version = _oikp_download_version( $version, $post, $class, $slug );
				if ( $plugin_version && $plugin_version != $version->ID && $free_version ) {
					_oikp_download_plugin_version( $plugin_version, $post, $class . " previous", $slug );
				} 
			} else {
					_oikp_download_version_not_available( $post, $class, $slug );
			}
		}	   
	} else {
		if ( $plugin != '.' ) {
			p( "Unknown plugin: $plugin " );
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

/**
 * Produces a download link when no plugin version is available.
 *
 * $plugin_type           | Processing 
 * -----------------      | ----- 
 * 0=None				          | Don't create a link
 * 1=WordPress            | Create a link to download from wordpress.org - if we want another link then use [bw_plug]
 * 2=FREE oik plugin      | Create a link to oik-plugins.com
 * 3=Premium oik plugin   |	Create a link to oik-plugins.com
 * 4=Other premium plugin |	Create a link using the Plugin URI
 * 5=Bespoke plugin       | Create a link using the Plugin URI
 * 6=WordPress and FREE plugin |   treat as 1 and 2
 *
 * @param object $post - the oik-plugins object
 * @param string $class - class for the link
 * @param string $slug - the plugin name
 * 
 */
function _oikp_download_version_not_available( $post, $class, $slug ) {
	$plugin_type = get_post_meta( $post->ID, "_oikp_type", true );
	switch ( $plugin_type ) {
		case 0:
			//  **?** Don't do anything yet
			// alink( null, "http://wordpress.org", "
			break;
		case 1:
			_oikp_download_wordpressversion( $post, $slug );
			break;
			
		case 2: 
			$text = __( "Download from" );
			$text .= "&nbsp;";
			$text .= "oik-plugins.com";
			_oikp_download_from_oikplugins( $post, $class, $slug, $text );
			break;
			
		case 3:
			$text = __( "Purchase from" );
			$text .= "&nbsp;";
			$text .= "oik-plugins.com";
			_oikp_download_from_oikplugins( $post, $class, $slug, $text );
			break;
			
		case 4:
			$text .= $slug;
			$text .= "&nbsp;";
			$text .= __( "home" );
			_oikp_download_from_uri( $post, $class, $slug, $text );
			break;
			
			
		case 6:
			_oikp_download_wordpressversion( $post, $slug );
			
			$text = __( "Download from" );
			$text .= "&nbsp;";
			$text .= "oik-plugins.com";
			_oikp_download_from_oikplugins( $post, $class, $slug, $text );
			break;
		
		
		default:
			p( "$slug: latest version not available for download" );
	}
}

/**
 * Creates a link to oik-plugins
 */
function _oikp_download_from_oikplugins( $post, $class, $slug, $text ) {
	$url = oik_get_plugins_server();
	$url .= "/oik-plugins/";
	$url .= $slug; 
	alink( $class, $url, $text );
}


/**
 * Creates a link to the plugin home 
 */
function _oikp_download_from_uri( $post, $class, $slug, $text ) {
	$oikp_uri = get_post_meta( $post->ID, "_oikp_uri", true );
	if ( $oikp_uri ) {
		alink( $class, $oikp_uri, $text );
	}
}

  

