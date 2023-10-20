<?php

/**
 * @package oik-plugins
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Unit tests to load all the PHP files for PHP 8.2
 */
class Tests_load_php extends BW_UnitTestCase
{

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setUp(): void 	{
		parent::setUp();
	}

	function test_load_admin_php() {
		oik_require( 'admin/oik-activation.php', 'oik-plugins');
		oik_require( 'admin/oik-plugins.php', 'oik-plugins');
		$this->assertTrue( true );
	}

	function test_load_feed_php() {
		oik_require( 'feed/oik-banner-feed.php', 'oik-plugins' );
		oik_require( 'feed/oik-plugins-feed.php', 'oik-plugins' );
		$this->assertTrue( true );
	}

	function test_load_includes_php() {
		oik_require( 'includes/class-oik-plugins-content.php', 'oik-plugins');
		$this->assertTrue( true );
	}

	
	function test_load_shortcodes_php() {
		oik_require( 'shortcodes/oik-plugins.php', 'oik-plugins');
		$this->assertTrue( true );
	}

	function test_load_plugin_php() {
		oik_require( 'oik-plugins.php', 'oik-plugins');
		$this->assertTrue( true );
	}
}


