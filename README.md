# oik plugins server 
![banner](assets/oik-plugins-banner-772x250.jpg)
* Contributors: bobbingwide
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: plugins, server, FREE, premium, shortcodes
* Requires at least: 5.0.0
* Tested up to: 6.4-RC1
* Stable tag: 1.21.2
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
oik-plugins server for FREE and Premium oik-plugins

New in v1.18.0
* Added Blocks tab

Changes in v1.16.0
* See change log

Changes in v1.15.9
* See change log

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
[oik FAQ](https://www.oik-plugins.com/oik/oik-faq)

## Screenshots 
1. Fields for the oik-plugin custom post type
2. Fields for the oik_pluginversion custom post type (prior to removal of "Requires" and "Tested to")
3. Fields for a Premium oik-plugin showing the Purchasable product

## Upgrade Notice 
# 1.21.2 
Upgrade for an improved plugin version template and support for PHP 8.1 and PHP 8.2

## Changelog 
# 1.21.2 
* Changed: Support PHP 8.1 and PHP 8.2 #25
* Changed: Improve Plugin version template #26
* Tested: With WordPress 6.4-RC1 and WordPress Multisite
* Tested: With PHP 8.0, PHP 8.1 and PHP 8.2
* Tested: With Gutenberg 16.8.1
* Tested: With PHPUnit 9.6



## Further reading 
If you want to read more about the oik plugins then please visit the
[oik plugin](https://www.oik-plugins.com/oik)
**"the oik plugin - for often included key-information"**
