<?php // (C) Copyright Bobbing Wide 2017

/**
 * Unit tests for the [bw_follow_me] shortcode
 * when oik-user is active - for both GitHub and WordPress
 *
 */

class Tests_follow_me extends BW_UnitTestCase {

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 * - we need "oik_plugins" for bw_update_option 
	 * - we need oik-follow to load the functions we're testing
	 */
	function setUp() {
		parent::setUp();
		$oik_plugins = oik_require_lib( "oik_plugins" );
		bw_trace2( $oik_plugins, "oik_plugins" );
		oik_require( "shortcodes/oik-follow.php" );
	}

	/**
	 * Test the Follow me for GitHub
	 *
	 * Specifically to test the display of the new GitHub option; oik issue #47, oik-user issue #5
	 *
	 * We assume that the option value is set in the database.
	 * If not we set a dummy value.
	 */
	function test_follow_me_with_github_set() {
		$value = bw_get_option_arr( "github", null, array( "user" => 1 ) );
		if ( !$value ) {
			oik_require( "admin/oik-user.php", "oik-user" );
			$value = "dummy";
			bw_update_user_meta( 1, "github", "dummy" );
		}
		$html = bw_follow( array( "network" => "github", "user" => 1 ) );
		$this->assertStringStartsWith( '<a href="https://github.com/' . $value, $html );
	}

	/**
	 * Tests issue #5 - Option field for WordPress.org
	 *
	 * Note: user = 0 when the user is not logged in.
	 * For user = 1 you need to ensure that the value is set.
	 */
	function test_follow_me_with_wordpress_set() {
		$value = bw_get_option_arr( "wordpress", null, array( "user" => 1 ) );
		if ( !$value ) {
			oik_require( "admin/oik-user.php", "oik-user" );
			$value = "dummy";
			bw_update_user_meta( 1, "wordpress", "dummy" );
		}
		$html = bw_follow( array( "network" => "wordpress", "user" => 1 ) );
		$this->assertStringStartsWith( '<a href="https://profiles.wordpress.org/' . $value, $html );
	}

}

