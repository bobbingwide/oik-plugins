<?php // (C) Copyright Bobbing Wide 2015-2019

/**
 * Class: OIK_plugins_content
 *
 */
class OIK_plugins_content { 

	public $post;
	public $post_id;
	public $slug;

	function __construct() {
	}
 
/**
 * Determine the tabs to display
 *
 * The tabs depend on the plugin type, which is currently a simple field,
 * then a whole load of other tests to see which of the tabs will have content.
 * 
 * Type | Means                           | Tabs to display
 * ---- | -----                           | -----------------------------
 * 0    | "None"													| ? Reserved for WordPress core
 * 1    | "WordPress plugin"							| Omit: FAQ, Changelog, Screenshots, Documentation
 * 2    | "FREE oik plugin"							  |	All
 * 3    | "Premium oik plugin"						| All
 * 4    | "Other premium plugin"					| Omit: FAQ, Changelog, Screenshots, Documentation
 * 5    | "Bespoke plugin"								| All
 * 6    | "WordPress and FREE plugin"		  | All
 *
 * @param object $post the post object
 * @return array keyed by tab of valid tabs for this plugin type
 *
 */	 
function additional_content_tabs( $post ) {
  $tabs = array( "description" => "Description"
               , "faq" => "FAQ"
               , "screenshots" => "Screenshots"
               , "changelog" => "Changelog"
               , "shortcodes" => "Shortcodes"
	            , "blocks" => "Blocks"
               , "apiref" => "API Ref"
               , "documentation" => "Documentation"
               );
								 
							 
	$plugin_type = get_post_meta( $post->ID,  "_oikp_type", true );
	switch ( $plugin_type ) {
		case 0:
		case 1:
		case 4:
		//case 5:
      unset( $tabs['documentation'] );
			unset( $tabs['faq'] );
			unset( $tabs['screenshots'] );	
			unset( $tabs['changelog'] );
			break;
	}	
	$tabs = $this->oikp_additional_content_tabs( $tabs, $post ); 
	$tabs = $this->check_content_for_tabs( $tabs ); 				 
	return( $tabs );
}

/**
 * Decide which tabs to display based on website information
 *
 * "apiref" or what?
 * 
 * Site | Tabs?
 * ---- | ---------------------------
 * WP-a2z |  displays APIs Classes Files Hooks
 * oik-plugins | displays apiref
 * bobbing wide | displays apiref
 * bobbing wide web design | No API stuff required
 *
 * @TODO Change the option field to be a comma separated list of tabs, in the order required
 * 
 * @param array $tabs
 * @param object $post
 * @return array updated tabs 
 */ 
function oikp_additional_content_tabs( $tabs, $post ) {
	$use_apiref_shortcode = bw_get_option( "apiref", "bw_plugins_server" );
	if ( $use_apiref_shortcode ) {
		// that's OK then... note if the shortcode is not defined we don't do this either
	} else {
		unset( $tabs['apiref'] );
		$tabs['apis'] = "APIs";
		$tabs['classes'] = "Classes";
		$tabs['files'] = "Files";
		$tabs['hooks'] = "Hooks";
	}
	return( $tabs );
}

/**
 * Checks content for each tab.
 *
 * @param array $tabs
 * @return array possibly updated to reflect how much content there is.
 */
function check_content_for_tabs( $tabs ) {
	foreach ( $tabs as $tab => $label ) {	
		$tab_has_content = $this->check_content_for_tab( $tab );
		if ( null === $tab_has_content ) {
			unset( $tabs[ $tab ] );
		}
	}
	return( $tabs );
}

/**
 * Count the content to be displayed in the tab
 * 
 * @param string $tab - the tab name
 * @return integer|null - the number of items to be displayed. 0 is acceptable for some tabs.
 */
function check_content_for_tab( $tab ) {
	$count = null;
	$method = "count_$tab";
	if ( is_callable( array( $this, $method  ) ) ) {
		$count = $this->$method();
	}
	return( $count );
}

/**
 * Counts the Description.
 */
function count_description() {
	return 1;
}

/**
 * Counts the FAQs
 *
 * @return integer|null count of FAQs associated to this plugin 
 */
function count_faq() {
	$count = null;
	if ( is_post_type_viewable( "oik-faq" ) ) {
		oik_require( "includes/bw_posts.php" );
		$atts = array( "post_type" => "oik-faq"
								 , "meta_key" => "_plugin_ref"
								 , "meta_value" => $this->post_id
								 );
		$posts = bw_get_posts( $atts );
		if ( $posts ) {
			$count = count( $posts );
		}
	}
	return $count ;
}

/**
 * Counts the screenshots 
 *
 * Count the screenshots associated with this plugin.
 * Treat 0 as null. 
 * 
 * @return integer
 */
function count_screenshots() {
	$count = null;
	if ( shortcode_exists( 'nivo' ) ) {
		oik_require( "nivo.php", "oik-nivo-slider" );
		$atts = array( "post_type" => "screenshot:" . $this->slug );
		$urls = bw_get_spt_screenshot( $atts );
		$url_count = count( $urls );
		if ( $url_count ) {
			$count = $url_count;
		}
	}
	return( $count );
}

/**
 * Counts the versions
 *
 * @TODO For the time being we'll always return 0 for any of our plugins since this is useful information
 * and we'll expect there to be at least one version. 
 *
 * @return 0  
 */
function count_changelog() {
	return 1;
}

/** 
 * Counts the shortcodes
 */
function count_shortcodes() {
	$count = null;
	if ( is_post_type_viewable( "oik_shortcodes" ) ) {
		oik_require( "includes/bw_posts.php" );
		$atts = array( "post_type" => "oik_shortcodes"
								 , "meta_key" => "_oik_sc_plugin"
								 , "meta_value" => $this->post_id
								 );
		$posts = bw_get_posts( $atts );
		if ( $posts ) {
			$count = count( $posts );
		}
	}
	return $count ;
}

	/**
	 * Counts the blocks
	 */
	function count_blocks() {
		$count = null;
		if ( is_post_type_viewable( "block" ) ) {
			oik_require( "includes/bw_posts.php" );
			$atts = array( "post_type" => "block"
			, "meta_key" => "_oik_sc_plugin"
			, "meta_value" => $this->post_id
			);
			$posts = bw_get_posts( $atts );
			if ( $posts ) {
				$count = count( $posts );
			}
		}
		return $count ;

	}

/**
 * Counts the files.
 *
 */
function count_files() {
	$count = $this->count_viewable( "oik_file", "_oik_api_plugin", $this->post_id );
	return $count ;
}
/**
 * Counts the APIs.
 *
 *
 */
function count_apis() {
	$count = $this->count_viewable( "oik_api", "_oik_api_plugin", $this->post_id );
	return $count ;
}
	/**
	 * Counts the Classes.
	 *
	 *
	 */
	function count_classes() {
		$count = $this->count_viewable( "oik_class", "_oik_api_plugin", $this->post_id );
		return $count ;
	}
	/**
	 * Counts the Hooks.
	 *
	 *
	 */
	function count_hooks() {
		$count = $this->count_viewable( "oik_hook", "_oik_hook_plugin", $this->post_id );
		return $count ;
	}

/**
 * Count the viewable items
 *
 * We don't actually count all of them since it can produce a Fatal error when there are too many.
 */
function count_viewable( $post_type, $meta_key, $meta_value ) {
	$count = null;
	if ( is_post_type_viewable( $post_type ) ) {
		oik_require( "includes/bw_posts.php" );
		$atts = array( "post_type" => $post_type
								 , "meta_key" => $meta_key
								 , "meta_value" => $meta_value
			, "numberposts" => 2
								 );
		$posts = bw_get_posts( $atts );
		if ( $posts ) {
			$count = count( $posts );
		}
	}
	return $count ;
}


/**
 * Count the APIs
 *  
 * [apiref] is a DIY shortcode which is expected to be defined like this:
 * `
 * <h3>APIs</h3> [apis] <h3>Classes</h3> [classes] <h3>Files</h3> [files] <h3>Hooks</h3> [hooks]
 * `
 * If it's defined we'll use it if we can also find some content. Using count_files() should be good enough.
 * 
 * 
 * @return integer|null 
 */
function count_apiref() {
	if ( shortcode_exists( 'apiref' ) ) {
		$count = $this->count_files();
		return( $count );
	}
	return( null ); 
}

/**
 * Determines if field is registered to post type
 *
 * @param string $object_type the post type e.g. 'page'
 * @param string $field_name the field name e.g. '_plugin_ref'
 * @return bool true when the field has been registered
 */
function is_field_registered( $object_type, $field_name ) {
	global $bw_mapping;
	$registered = isset( $bw_mapping['field'][$object_type][$field_name] );
	return( $registered );
}

/**
 * Counts the documentation pages
 * 
 * Checks for the relationship between page and _plugin_ref before counting the number of pages listed.
 * Note: If none are listed then we don't need to check the documentation home page ( _oik_doc_home ) 
 * since this page should itself have its _plugin_ref field set.
 * 
 * @return integer|null Number of documentation pages or null
 */
function count_documentation() {
	$count = null;
	if ( $this->is_field_registered( "page", "_plugin_ref" ) ) {
		oik_require( "includes/bw_posts.php" );
		$atts = array( "post_type" => "page"
								 , "meta_key" => "_plugin_ref"
								 , "meta_value" => $this->post_id
								 , "post_parent" => "."
								 );
		$posts = bw_get_posts( $atts );
		if ( $posts ) {
			$count = count( $posts );
		}
	}	
	return $count ;
}

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
 *
 * We may display these for WP-a2z
 * 
 */
function additional_content_links( $post, $current_tab ) {
	$tabs = $this->additional_content_tabs( $post ); 
	$valid = bw_array_get( $tabs, $current_tab, false );
	if ( !$valid ) { 
		return( $valid );
	}						 
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
function additional_content( $post, $slug=null ) {
	$this->post = $post;
	$this->post_id = $post->ID;
	$this->slug = $slug;
  $oik_tab = bw_array_get( $_REQUEST, "oik-tab", "description" ); 
  $additional_content = $this->additional_content_links( $post, $oik_tab );
  if ( $oik_tab ) {
    $tabs = array( "description" => "display_description"
                 , "faq" => "display_faq"
                 , "screenshots" => "display_screenshots"
                 , "changelog" => "tabulate_pluginversion" 
                 , "shortcodes" => "display_shortcodes"
	            , "blocks" => "display_blocks"
                 , "apiref" => "display_apiref"
                 , "documentation" => "display_documentation" 
                 );
    $oik_tab_function = bw_array_get( $tabs, $oik_tab, "display_unknown" );
    if ( $oik_tab_function ) {
      if ( is_callable( array( $this, $oik_tab_function ) ) ) {
        $additional_content .= $this->$oik_tab_function( $post, $slug );
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
function tabulate_pluginversion( $post ) {
  $version_type = get_post_meta( $post->ID, "_oikp_type", true );
  
  // $versions = array( null, null, "oik_pluginversion", "oik_premiumversion", null, null, "oik_pluginversion" );
  $versions = bw_plugin_post_types();
  $post_type = bw_array_get( $versions, $version_type, null ); 
  if ( $post_type ) {
    //$additional_content = "<!--nextpage-->";
    $additional_content = "[bw_table";
    $additional_content .= kv( "post_type", $post_type );
    
    $additional_content .= kv( "fields", "title,excerpt,date,required_version,compatible_up_to" );
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
 * Display output for a potentially unknown tab
 *
 * If there's a shortcode for it then we'll use that
 */
function display_unknown( $post, $slug ) {
	$oik_tab = bw_array_get( $_REQUEST, "oik-tab", "description" ); 
	if ( shortcode_exists( $oik_tab ) ) {
		$ret = "[$oik_tab]" ;
  } else {
		$oik_tab = esc_html( $oik_tab );
		$ret = "Invalid request: $oik_tab. Shortcode is not registered";
		bw_trace2( $ret, "ret", true, BW_TRACE_ERROR );
	}
	return( $ret );
	

}

/**
 * Display the description of the plugin 
 *
 * @param object $post - the post object
 * @return string - the post content - shortcode will be expanded later
 */
function display_description( $post ) {
  return( $post->post_content );
}

/**
 * Display the FAQ's for the plugin
 */
function display_faq( $post ) {
  $id = $post->ID;
  return( "[bw_accordion post_type=oik-faq meta_key=_plugin_ref meta_value=$id format=TEM numberposts=-1]" );
} 
 
/**
 * Display the screenshots for the plugin
 *
 * This uses the nivo shortcode. 
 * We should probably test if it's available.
 * If not then we need to do what?
 * 
 */
function display_screenshots( $post, $slug ) {
  $additional_content = "[nivo post_type=screenshot:$slug caption=n link=n]";
  return( $additional_content ); 
}

/**
 * Display the shortcodes for the plugin
 * 
 * Uses the [codes] shortcode which determines the plugin automatically
 *
 */
function display_shortcodes( $post, $slug ) {
  $additional_content = "[codes posts_per_page=.]";
  return( $additional_content ); 
}

/**
 * Display the blocks for the plugin
 * @param $post
 * @param $slug
 *
 * @return string
 */
function display_blocks( $post, $slug ) {
	$additional_content = "[blocks posts_per_page=.]";
	return $additional_content;
}

/**
 * Display the API reference for the plugin
 * 
 * Uses the [apiref] shortcode which determines the plugin automatically
 * This assumes the shortcode is defined. 
 *
 */
function display_apiref( $post, $slug ) {
	
  $additional_content = "[apiref]";
  return( $additional_content ); 
}

/**
 * Display the documentation for the plugin
 *
 * Only use _oik_doc_home if 
 * - the field is defined for the post type
 * - the value is non null
 * - it's a valid page
 * 
 * Otherwise - make it up using the bw_related shortcode
 * 
 * This is how it used to be when displayed in the sidebar widget area
 * `
 * Pages
 * [bw_related post_type=page meta_key=_plugin_ref posts_per_page=5 ] - temporarily disabled 2015/03/15
 * 
 * [clear]
 * 
 * Posts
 * [bw_related post_type=post meta_key=_plugin_ref posts_per_page=5 ] - temporarily disabled 2015/03/15
 * `
 */
function display_documentation( $post, $slug ) {
	$field_names = bw_get_field_names( $post->ID );
	//bw_trace2( $field_names, "field_names" );
	if ( bw_array_get( bw_assoc( $field_names) , "_oik_doc_home", false ) ) {
		$post_id = get_post_meta( $post->ID, "_oik_doc_home", true );
		if ( $post_id ) {
			oik_require( "includes/bw_posts.php" );
			$post = bw_get_post( $post_id, "page" );
			if ( !$post ) {
				bw_trace2( $post_id, "Invalid ID for _oik_doc_home" );
				$post_id = null;
			}
		}
	} else {
		$post_id =  null;
	}
	bw_trace2( $post_id, "post_id for _oik_doc_home", false );
  if ( $post_id ) {
		
    $additional_content = "[bw_tree post_type=page post_parent=$post_id posts_per_page=.]";
  } else {
    $additional_content = "[bw_related post_type='page,post' meta_key=_plugin_ref posts_per_page=. orderby=title order=asc ]";
  }    
  return( $additional_content ); 
}

} 
  
