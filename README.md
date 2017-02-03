# oik plugins server 
![banner](https://raw.githubusercontent.com/bobbingwide/oik-plugins/master/assets/oik-plugins-banner-772x250.jpg)
* Contributors: bobbingwide
* Donate link: http://www.oik-plugins.com/oik/oik-donate/
* Tags: plugins, server, FREE, premium, shortcodes
* Requires at least: 4.2
* Tested up to: 4.7.2
* Stable tag: 1.15.8
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
oik-plugins server for FREE and Premium oik-plugins

Changes in v1.15.8
* Display of the API ref tab depends on whether or not any files have been parsed for the plugin.

Changes in v1.15.7
* Display of a tab on the oik-plugin details page is now dependent upon both the plugin type and the website content.

Changes in v1.15.5
* Supports cloning of premium plugin version attached zip files

Changes in v1.15.4

* Better support for sections based on plugin type and shortcode availability

Changes in v1.15.3

* Implements filtering during cloning to ensure site specific information is not overridden


Changes in v1.15.2

* Checks for potential validity of the Documentation home page field ( _oik_doc_home )

Changes in v1.15.1

* [oikp_download] now works for the current oik_pluginversion

Changes in v1.15

The oik-plugin details are displayed in a set of tabs, similar to wordpress.org

* Description - (default tab) - shows the content of the oik-plugin
* FAQ - Accordion of oik-FAQs
* Screenshots - nivo slide show of the screenshot-n. files
* Changelog - table of oik-plugin versions
* Shortcodes - list of shortcodes
* API Ref - API Reference using [apiref] shortcode
* Documentation - List of pages and posts related through the '_plugin_ref' field

The information displayed on the plugin update page now expands shortcodes.


## Installation 
1. Upload the contents of the oik-plugins plugin to the `/wp-content/plugins/oik-plugins' directory
1. Activate the oik-plugins plugin through the 'Plugins' menu in WordPress
1. To support oik Premium plugins use oik options > Server settings to define a secure folder used to store uploaded zip files
1. Also install and activate either oik-edd or oik-woo to allow the creation of API keys

## Frequently Asked Questions 
# Where is the FAQ? 
[oik FAQ](http://www.oik-plugins.com/oik/oik-faq)

# Is there a support forum? 
Yes - please use the standard WordPress forum - http://wordpress.org/tags/oik?forum_id=10

# Can I get support? 
Yes - see above

## Screenshots 
1. Fields for the oik-plugin custom post type
2. Fields for the oik_pluginversion custom post type (prior to removal of "Requires" and "Tested to"
3. Fields for a Premium oik-plugin showing the Purchasable product


## Upgrade Notice 
# 1.15.8 
Improves display of the API ref tabl. Tested with WordPress 4.7.2 and WordPress Multisite.

# 1.15.7 
Improves display of tabs depending on content. Tested with WordPress 4.7.1 and WordPress Multisite.

# 1.15.6 
Required where there are more than 10 FAQs for a plugin. Tested with WordPress 4.6 and WordPress Multisite.

# 1.15.5 
Required for oik-clone updates. Tested with WordPress 4.5.1 and WordPress MultiSite.

# 1.15.4 
Upgrade for improved display of sections based on plugin type and shortcode options.

# 1.15.3 
Upgrade for improved cloning

# 1.15.2 
Update for better Documentation links.

# 1.15.1 
Implements changes to enable transition to the genesis-oik theme on oik-plugins.com

# 1.15 
Changes to enable performance improvements on oik-plugins.com

# 1.14 
Now checks upgrade requests for a set of plugins

# 1.13 
Tested with WordPress 4.0. Required for improved results for [bw_table]

# 1.12 
Now uses dashicons for oik-plugins, oik_pluginversion and oik_premiumversion

# 1.11 
Now supports pagination of plugin version table

# 1.10 
Improvements for GitHub repositories

# 1.9 
Improvements for WordPress update pages

# 1.8 
Required for new theme for oik-plugins.co.uk and oik-plugins.com

# 1.7 
Required for oik-plugin new theme oik410130c and it's RH sidebar for single oik-plugins display. Dependent upon oik-fields v1.33

# 1.6 
Required for cases where WordPress SEO causes the_content to be invoked a whole host of times.

# 1.5 
Required for oik-shortcodes v1.07

# 1.4 
Improvements for oik-plugins.com website - supporting bespoke plugins, improved download buttons etc.

# 1.3 
Needed to support improvements to [bw_plug] in oik v2.1

# 1.2 
Requires oik v2.1-alpha.0802 and oik-fields v1.19.0802

# 1.1.0421 
Pre-requisite to oik v2.0-beta.0421

# 1.1.0326 
# 1.1.0325 
Dependent upon oik v2.0-alpha and oik-fields v1.18.0325

# 1.1.0222 
Tested with WordPress 3.5.1

# 1.1.0115 
Depends on oik base plugin v1.17 and oik-fields v1.18

# 1.0.1108.2127 
Changes to support oik v1.17.1108.2127

# 1.0.1103.1627 
Requires oik v1.17.1103.1626 and oik-fields v1.17 or higher

# 1.0.1029.1621
Requires oik v1.17 or higher and oik-fields v1.17 or higher

# 1.0.1008.1424
Requires oik v1.17 or higher and oik-fields v1.17 or higher

# 1.0.1001.1008 
Requires oik v1.17 or higher and oik-fields v1.17 or higher

# 1.0.0927.2012 
Requires oik v1.16 or higher and oik-fields v1.17 or higher

## Changelog 
# 1.15.8 
* Changed: Improve display of the API ref tab https://github.com/bobbingwide/oik-plugins/issues/9
* Tested: With WordPress 4.7.2 and WordPress Multisite

# 1.15.7 
* Changed: Improve support for basic plugin documentation https://github.com/bobbingwide/oik-plugins/issues/9
* Tested: With WordPress 4.7.1 and WordPress Multisite

# 1.15.6 
* Changed: Allow for unlimited FAQs in the bw_accordion shortcode https://github.com/bobbingwide/oik-plugins/issues/7
* Changed: Associate the _component_version virtual field to oik-plugins https://github.com/bobbingwide/oik-plugins/issues/6
* Tested: With WordPress 4.6 and WordPress Multisite

# 1.15.5 
* Added: Implement 'oik_clone_filter_media_file' filter https://github.com/bobbingwide/oik-plugins/issues/5
* Changed: Trace levels
* Changed: Label to 'Plugins' for WP-a2z
* Fixed: Run do_action( 'oik_add_shortcodes' ) earlier https://github.com/bobbingwide/oik-plugins/issues/4
* Tested: With WordPress 4.5.1 and WordPress MultiSite

# 1.15.4 
* Changed: Improved logic to determine sections to display for oik-plugins post typee
* Changed: Added use [apiref] shortcode checkbox

# 1.15.3 
* Changed: Added implementation for 'oik_clone_filter_all_post_meta'
* Added: First version of French translation

# 1.15.2 
* Fixed: Solution for https://github.com/bobbingwide/oik-plugins/issues
* Tested: With WordPress 4.3

# 1.15.1 
* Changed: [oikp_download] shortcode now allows download of a previous plugin version
* Changed: [oikp_download] shortcode will not display "Unknown plugin" if plugin='.'
* Changed: oik-plugins.css now supports the Genesis framework
* Changed: Improved some docblock comments
* Changed: oikp_display_screenshots() add [nivo] shortcode parameters: caption=n link=n
* Changed: oikp_display_documentation() tests for '_oik_doc_home' post meta data
* Changed: Uses oik_require_lib( "bobbfunc" ) for deferred text translations - bw_dtt()
* Depends: on oik v2.6-alpha.0722 or higher

# 1.15 
* Changed: Processing of "the_content" for oik-plugins is now implemented in includes/oik-plugins-content.php
* Added: oik-tab query arg used to select the tab. Not yet implemented using rewrite rules
* Changed: Shortcodes are expanded in the response to requests for plugin info
* Fixed: Better handling of missing compatibility & required version tags.
* Added: CSS to style the tabs for oik-plugins

# 1.14 
* Changed: Added action=check-these to allow multiple plugins to be checked in one request
* Added: Implemented using oikp_perform_update_check()
* Changed: oikp_update_check() now uses the new function oikp_perform_update_check()
* Changed: Default banner image is now a .png file
* Changed: Post types now registered with additional post type support: revisions, author, publicize and home

# 1.13 
* Changed: Responses to "oik_table_fields_$post_type" filter now include "title" and "excerpt"
* Changed: No longer responds to "oik_table_titles_$post_type" filter
* Changed: Commented out some bw_trace2() and bw_backtrace() calls
* Changed: Purchasable product field now shows FREE if not set
* Fixed: oikp_load_pluginversion() to return the latest plugin version even if it's the current post.
* Changed: Added filter hook for "posts_request" to intercept the main query in certain situations

# 1.12
* Added: Dashicons

# 1.11 
* Added: Support for pagination - added posts_per_page=. parameter to [bw_table] shortcode for listing plugin versions
* Changed: oikp_lazy_redirect() sets DOING_AJAX to prevent oik-bwtrace from sending output back to the client

# 1.10 
* Added: GitHub repository field ( _oikp_git )
* Changed: 'the_content' processing invokes 'oik_add_shortcodes' action

# 1.9 
* Changed: oikp_get_tested() returns the highest level of WordPress supported. Taken from the "compatible_up_to" category.
* Changed: Returns the plugin name in the response to update-check
* Added: Returns compatibility array for a new plugin version in response to /plugins/info

# 1.8 
* Changed: [oikp_download] will produce an EDD "purchase" link for a premium plugin
* Changed: Support [oikp_download plugin=.] to detect the current plugin - for use in text widgets

# 1.7 
* Changed: [oikp_download] supports plugin=. - to select the current plugin
* Changed: oikp_the_post_oik_plugins() only adds [oikp_download] when NOT single display; assumes sidebar widget will add this
* Changed: when single plugin displayed the plugin version information is added regardless of the presence of the [oikp_download] shortcode

# 1.6
* Added: oik-plugins now has "has_archive" set
* Changed: oikp_the_post_oik_plugins() only adds [oikp_download] if not already found in the content
* Changed: oikp_the_post_oik_pluginversion() only adds [bw_fields] if [bw_field not already found in the content
* Changed: oikp_the_content() no longer checks multiple invocations

# 1.5 
* Added: API key for use with oik-shortcodes
* Added: oikp_oik_validate_apikey() filter

# 1.4 
* Changed: Plugin type 5 changed from "other" to "bespoke"
* Added: Custom taxonomy: oik_tags - to help with classification of plugin type
* Added: Translatable field hints using bw_dtt() API
* Changed: oikp_load_pluginversion() uses bw_plugin_post_types() rather than its own array
* Changed: [oikp_download] shortcode produces stylable download buttons, where applicable

# 1.3 
* Added: Responds to requests of form http://example.com/banner/plugin_slug - to display the featured image of the plugin
* Added: Download link now includes the plugin name (currently using the post_title rather than the plugin description (_oikp_desc field))
* Changed: Improved _oikp_download_version() to display download links for local and WordPress versions
* Changed: oik_plugin post_type now supports featured images and manual excerpts
* Changed: Removed _oikpv_requires and _oikpv_tested fields - having previously switch to using Custom categories

# 1.2 
* Added: "Required version" and "Compatible up to" now implemented as Custom categories
* Note: This is an intermediate version, In a future version the meta data for these fields may be removed
* Changed: Added "Plugin" to "server settings" admin page, since there is also a theme server - oik-themes plugin
* Changed: "Download count" and "Update count" will not be shown by [bw_fields]
* Changed: oik_pluginversion and oik_premiumversion automatically add [bw_fields] to the post_content during "the_content" processing
* Fixed: set download_link as well as download_url so that "Install Update Now" will appear

# 1.1.0421 
* Changed: Support for bw_user_user() in oik v2.0, for oik-user
* Changed: Share oikp_columns_and_titles() with oik-shortcodes

# 1.1.0326 
* Changed: Defined sort sequence for plugin download summary on the Server settings admin page

# 1.1.0325 
* Added: Download summary table in oik options > Server settings admin page

# 1.1.0222 
* Added: 3.6-alpha
* Tested: With WordPress 3.5.1

# 1.1.0115 
* Added: Dependency logic on oik and oik-fields
* Added: oik options > Server settings to define the Info page and target directory for premium version .zip files
* Added: Dynamic author name and author profile link
* Added: WordPress and FREE plugin - for plugins that are hosted on both WordPress AND your server

# 1.0.1108.2127
* Changed: Improvements to assist [bw_plug]
* Fixed: Removed Notification when there was file attached to the version

# 1.0.1103.1627
* Changed: Now supports direct download of WordPress plugins from downloads.wordpress.org

# 1.0.1029.1621
* Changed: The plugin 'description' is now processed through bw_excerpt() rather than just applying the 'the_excerpt' filter

# 1.0.1008.1424
* Added: automatically adds download links and tables of plugin versions to "oik-plugins" content page
* Added: Admin pages show relevant custom fields
* Added: [bw_table] plugin will show relevant custom fields
* Changed: removed _oikpv_upgrade - use the main body
* Fixed: oikp_get_FAQ() not working on a Linux server

# 1.0.1001.1008 
* Added: Support for identifying the Premium plugin's purchasable product from WooCommmerce and Easy Digital Downloads (EDD)
* Added: [oikp_download] shortcode will display a "Purchase product" link for Premium plugins
*
# 1.0.0927.2012 
* Added: Support for API keys.. though they aren't matched to the plugins! DOH **?**
* Changed: The description section is only the excerpt not the full content
* Changed: Add _oikpv_slug, _oikpv_name and _oikpv_desc meta data fields

# 1.0 
* Added: First version for use on www.oik-plugins.co.uk
* Changed:
* Fixed:

## Further reading 
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik)
**"the oik plugin - for often included key-information"**

