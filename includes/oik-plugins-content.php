<?php // (C) Copyright Bobbing Wide 2015


/**
 * Add the sections links for the plugin
 *
 * Here we use a style similar to wordpress.org
 *
 * We omit these at present:
 *    
               //, "installation" => "Installation"
               //, "Other notes" => "Other notes"
               //, "Stats" => "Stats"
               //, "Support" => "Support"
               //, "Reviews" => "Reviews"
               //, "Developers" => "Developers"
 */
function oikp_additional_content_links( $post, $current_tab ) {
  $tabs = array( "description" => "Description"
               , "faq" => "FAQ"
               , "screenshots" => "Screenshots"
               , "changelog" => "Changelog"
               , "shortcodes" => "Shortcodes"
               , "apiref" => "API Ref"
               , "documentation" => "Documentation"
               );
  $url = get_permalink( $post->ID );
  wp_enqueue_style( "oik-pluginsCSS", oik_url( "css/oik-plugins.css", "oik-plugins" ) );
  bw_push();
  sdiv( "plugin-info" );
  sul( null, "sections" );
  foreach ( $tabs as $tab => $label ) {
    $class = "section-$tab" ;
    $target_url = add_query_arg( "oik-tab", $tab, $url );
    if ( $tab === $current_tab ) {
      stag( "li", "current" );
    } else {
      stag( "li" );
    }
    alink( $class, $target_url, $label ); 
    etag( "li" );
  }
  eul();
  ediv();
  sediv( "clear" );
  sdiv( "plugin-body" );
  $ret = bw_ret(); 
  bw_pop();
  return( $ret );
}

/**
 * Handle varying requests for additional content
 *
 * Default to displaying the description if "oik-tab" is not set
 *  
 *
 */
function oikp_additional_content( $post, $slug=null ) {
  $oik_tab = bw_array_get( $_REQUEST, "oik-tab", "description" ); 
  $additional_content = oikp_additional_content_links( $post, $oik_tab );
  if ( $oik_tab ) {
    $tabs = array( "description" => "oikp_display_description"
                 , "faq" => "oikp_display_faq"
                 , "screenshots" => "oikp_display_screenshots"
                 , "changelog" => "oikp_tabulate_pluginversion" 
                 , "shortcodes" => "oikp_display_shortcodes" 
                 , "apiref" => "oikp_display_apiref"
                 , "documentation" => "oikp_display_documentation" 
                 );
    $oik_tab_function = bw_array_get( $tabs, $oik_tab, "oikp_display_unknown" );
    if ( $oik_tab_function ) {
      if ( is_callable( $oik_tab_function ) ) {
        $additional_content .= $oik_tab_function( $post, $slug );
      } else {
        $additional_content .= "Missing: $oik_tab_function";
      }
    }  
  }
  $additional_content .= "</div>";
  return( $additional_content );
}

 

/**
 * Automatically add the table of version information for a FREE or Premium oik plugin
 * 
 *  [bw_table post_type="oik_pluginversion" fields="title,excerpt,_oikpv_version" meta_key="_oikpv_plugin" meta_value=89 orderby=date order=DESC]
 */
function oikp_tabulate_pluginversion( $post ) {
  $version_type = get_post_meta( $post->ID, "_oikp_type", true );
  
  // $versions = array( null, null, "oik_pluginversion", "oik_premiumversion", null, null, "oik_pluginversion" );
  $versions = bw_plugin_post_types();
  $post_type = bw_array_get( $versions, $version_type, null ); 
  if ( $post_type ) {
    //$additional_content = "<!--nextpage-->";
    $additional_content = "[bw_table";
    $additional_content .= kv( "post_type", $post_type );
    
    $additional_content .= kv( "fields", "title,excerpt,_oikpv_version" );
    $additional_content .= kv( "meta_key", "_oikpv_plugin" );
    $additional_content .= kv( "meta_value", $post->ID );
    $additional_content .= kv( "orderby", "date" );
    $additional_content .= kv( "order", "DESC" );
    $additional_content .= kv( "posts_per_page", "." );
    $additional_content .= "]";
  } else {
    $additional_content = null;
  }     
  return( $additional_content ); 
}

/**
 */
function oikp_display_unknown( $post, $slug ) {
  
  $oik_tab = bw_array_get( $_REQUEST, "oik-tab", "description" ); 
  $oik_tab = esc_html( $oik_tab );
  return( "No logic for displaying: $oik_tab ");

}

/**
 * Display the description of the plugin 
 *
 * @param object $post - the post object
 * @return string - the post content - shortcode will be expanded later
 */
function oikp_display_description( $post ) {
  return( $post->post_content );
}

/**
 * Display the FAQ's for the plugin
 */
function oikp_display_faq( $post ) {
  $id = $post->ID;
  return( "[bw_accordion post_type=oik-faq meta_key=_plugin_ref meta_value=$id format=TEM]" );
} 

 
/**
 * Display the screenshots for the plugin
 *
 * This uses the nivo shortcode. 
 * We should probably test if it's available.
 * If not then we need to do what?
 * 
 */
function oikp_display_screenshots( $post, $slug ) {
  $additional_content = "[nivo post_type=screenshot:$slug]";
  return( $additional_content ); 
}

/**
 * Display the shortcodes for the plugin
 * 
 * Uses the [codes] shortcode which determines the plugin automatically
 *
 */
function oikp_display_shortcodes( $post, $slug ) {
  $additional_content = "[codes posts_per_page=.]";
  return( $additional_content ); 
}



/**
 * Display the API reference for the plugin
 * 
 * Uses the [apiref] shortcode which determines the plugin automatically
 *
 */
function oikp_display_apiref( $post, $slug ) {
  $additional_content = "[apiref]";
  return( $additional_content ); 
}

/**
 * Display the documentation for the plugin
 * Pages
 * [bw_related post_type=page meta_key=_plugin_ref posts_per_page=5 ] - temporarily disabled 2015/03/15
 * 
 * [clear]
 * 
 * Posts
 * [bw_related post_type=post meta_key=_plugin_ref posts_per_page=5 ] - temporarily disabled 2015/03/15
 *
 * Examples
 * Tutorials
 */
 
function oikp_display_documentation( $post, $slug ) {
  $additional_content = "[bw_related post_type='page,post' meta_key=_plugin_ref posts_per_page=. orderby=title order=asc ]";
  return( $additional_content ); 
}
  
