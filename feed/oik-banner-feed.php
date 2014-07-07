<?php
/** 
Author: bobbingwide
Author URI: http://www.bobbingwide.com
License: GPL2

    Copyright 2013 Bobbing Wide (email : herb@bobbingwide.com )

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
 * Return the banner image for the chosen plugin or a default image
 *
 * If we simply echo the thumbnail this is fine for when the request is for the URL alone
 * but when the browser wants an image we need to return the actual image
 * @uses exit()
 *
 * @param string $oik_plugin - the plugin slug 
 */
function oikp_lazy_redirect_banner( $oik_plugin ) {
  oik_require( "feed/oik-plugins-feed.php", "oik-plugins" );
  $post = oikp_load_plugin( $oik_plugin );
  $thumbnail = null;
  if ( $post ) {
    $post_id = $post->ID;
    if ( has_post_thumbnail( $post_id ) ) {
      $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), "full" );
      bw_trace2( $thumbnail, "thumbnail", false );
      if ( $thumbnail ) {
        $thumbnail = $thumbnail[0];
      } else {
        bw_trace2( "Thumbnail not found" );
      }   
    } else {
      bw_trace2( "Plugin has no thumbnail" );
    }  
  } else {
    bw_trace2( "Invalid plugin slug" ); 
  }
  if ( !$thumbnail ) {
    $thumbnail = oik_path( "images/oik-plugins-banner-772x250.jpg", "oik-plugins" );
  }  
  oikp_force_image( $thumbnail );
  exit();
}


/**
 * Force the download of an image
 *
 * @uses exit()
 * @param string $file - the fully qualified file name, which may be in URL format
 *
 */
function oikp_force_image( $file ) {
  bw_trace2();
  $file_content = file_get_contents( $file );  
  header( 'Content-type: image' );  
  echo $file_content;  
  exit();
}
