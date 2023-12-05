<?php 
/*
Plugin Name: oik user
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-user
Description: oik lazy smart shortcodes by user ID/name
Version: 0.9.0
Author: bobbingwide
Author URI: https://bobbingwide.com/about-bobbing-wide
Text Domain: oik-user
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2013-2023 Bobbing Wide (email : herb@bobbingwide.com )

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
 * Implement the "oik_loaded" action for oik-user
 */
function oiku_loaded() {
  add_action( "admin_bar_menu", "oiku_admin_bar_menu", 30 );
	add_action( "oik_add_shortcodes", "oiku_add_shortcodes" );
	add_action( "oik_admin_menu", "oiku_admin_menu" );
}

/**
 * Implement "oik_add_shortcodes" action for oik-user
 */
function oiku_add_shortcodes() { 
  $path = oik_path( "shortcodes/oik-user.php", "oik-user" );
  bw_add_shortcode( "bw_user", "oiku_user", $path , false );
  bw_add_shortcode( "bw_users", "oiku_users", $path, false );
}

/**
 * Implement the "oik_admin_menu" action for oik-user
 */
function oiku_admin_menu() {
  oik_register_plugin_server( __FILE__ );
  oik_require( "admin/oik-user.php", "oik-user" );
  oiku_lazy_admin_menu();
}

/**
 * Implement the "admin_notices" action for oik-user
 * 
 * - oik-user is dependent upon the oik base plugin v2.0 or higher
 * - Since v0.5 oik-user has been dependent upon oik-fields
 * - v0.5.1 is now dependent upon oik v2.5 or higher and oik-fields v1.40.1
 * - v0.5.2 is now dependent upon oik v2.6 or higher and oik-fields v1.40.2
 * - v0.6.0 is now dependent upon oik v3.1 or higher and oik-fields v1.40.4
 */ 
function oiku_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-user/oik-user.php", "oiku_activation" );
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      require_once( "admin/oik-activation.php" );
    }  
  }  
  $depends = "oik:3.1,oik-fields:1.40.4";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Implement "wpmem_admin_style_list" to provide our own styling for WP-members sign in and register forms 
 * 
 * @param array $list - List of stylesheets
 * @return array - the list with our addition(s)
 */
function oiku_wpmem_admin_style_list( $list ) {
  $list['oik-user'] = oik_url( "wp-members.css", "oik-user" );
  return( $list ); 
}

/**
 * Implement "wpmem_post_password" for Artisteer themes v3.1 and higher
 * 
 * Fixes password spoof - the plugin sets a post password to protect
 * the comment form.  In themes where this causes the WordPress password protected post dialog to display, 
 * returning an empty post password should correct the problem.
 * @see post_password_required()
 * 
 * @link http://rocketgeek.com/plugins/wp-members/users-guide/filter-hooks/wpmem_post_password/
 *
 *  
 */
function oiku_wpmem_post_password( $password ) {
  // Check Artisteer version **?**
  return( null );
}

/**
 * Implements "oik_pre_theme_field" action 
 *
 * The user field is only required when we intend to actually display the field
 *
 */
function oiku_pre_theme_field() {
  oik_require( "includes/oik-user.inc", "oik-user" );
} 

/**
 * Implements "oik_pre_form_field" action
 *
 * The user field is only required when we intend to actually to set a new value for the field
 *
 */
function oiku_pre_form_field() {
  oik_require( "includes/oik-user.inc", "oik-user" );
} 

/**
 * Validate a user field
 * 
 * @param string $value - the field value
 * @param string $field - the field name
 * @param array $data - array of data about the field   
 */
function oiku_field_validation_userref( $value, $field, $data ) {
  // bw_trace2();
  $numeric = is_numeric( $value );
  if ( !$numeric ) {
    $text = sprintf( __( "Invalid %s" ), $data['#title'] );     
    bw_issue_message( $field, "non_numeric", $text, "error" );
  }     
  return( $value );   
}

/**
 * Implement "oik_query_field_types" to return the field types supported by oik-user
 * 
 * @param array $field_types - array of field types
 * @return array - updated with our values 
 */
function oiku_query_field_types( $field_types ) {
  $field_types['userref'] = __( 'User' );
  return( $field_types );
} 

/**
 * Implement "admin_bar_menu" for oik-user
 *
 * This action hook runs after other action hooks to alter the "Howdy," prefix for 'my-account'
 * Note: If the main site has overridden "Howdy," then the user can't override it himself.
 *  
 * The structure we're changing is the node for 'my-account'
 * e.g.
 * `
    [id] => my-account
    [parent] => top-secondary
    [title] => Howdy, vsgloik<img alt='' onerror='this.src="http://qw/wordpress/wp-content/themes/rngs0721/images/no-avatar.jpg"' src='http://1.gravatar.com/avatar/1c32865f0cfb495334dacb5680181f2d?s=26&amp;d=http%3A%2F%2F1.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D26&amp;r=G' class='avatar avatar-26 photo' height='26' width='26' />
    [href] => http://qw/wordpress/wp-admin/profile.php
    [meta] => Array
        (
            [class] => with-avatar
            [title] => My Account
        )
 * `
 */
function oiku_admin_bar_menu( &$wp_admin_bar ) {
  $current_user = wp_get_current_user();
  $replace = bw_get_user_field( $current_user->ID, "howdy", "bw_options" );
  if ( $replace ) {
    $node = $wp_admin_bar->get_node( 'my-account' );
  	$howdy = sprintf( __('Howdy, %1$s'), $current_user->display_name );
    //bw_trace2( $node, "node" );
    $replace = $replace . " " . $current_user->display_name; 
    $node->title = str_replace( $howdy, $replace, $node->title );
    $wp_admin_bar->add_node( $node );
  }
}

/**
 * Implement "oik_fie_edit_field_type_userref" for oik-user
 *
 * Add additional fields for the "userref" field type
 *
 * Allow userref fields to be #optional. ie. Allow "None".
 * Do not yet allow userref fields to be #multiple select.
 * 
 * Note: These fields are made available by the oik-types plugin.
 * 
 */
function oiku_oik_fie_edit_field_type_userref() {
  global $bw_field;
  $argsargs = bw_array_get( $bw_field['args'], 'args', null );
  //$argsargs['#multiple'] = bw_array_get( $argsargs, "#multiple", null );
  $argsargs['#optional'] = bw_array_get( $argsargs, "#optional", null );
  // bw_form_field( "#multiple", "numeric", "Single or multiple select", $argsargs['#multiple'] );
  bw_form_field( "#optional", "checkbox", "Optional", $argsargs['#optional'] );
}

/**
 * Implement oik_fields_loaded for oik-user
 * 
 * Registers the virtual field Gravatar
 */
function oiku_oik_fields_loaded() {
	$field_args = array( "#callback" => "oiku_get_gravatar"
										 , "#parms" => "" 
										 , "#plugin" => "oik-user"
										 , "#file" => "includes/oik-user.inc"
										 , "#form" => false
										 , "#hint" => "virtual field"
										 ); 
	bw_register_field( "gravatar", "virtual", "Gravatar", $field_args );

	$field_args = array( "#callback" => "oiku_get_follow_me"
	, "#parms" => ""
	, "#plugin" => "oik-user"
	, "#file" => "includes/oik-user.inc"
	, "#form" => false
	, "#hint" => "virtual field"
	);
	bw_register_field( "follow_me", "virtual", "Follow me", $field_args );

}

/**
 * Function to run when the plugin file is loaded
 *  
 */
function oiku_plugin_loaded() {
  add_action( "oik_loaded", "oiku_loaded" );
  add_action( "admin_notices", "oiku_activation" );
  add_action( "wpmem_admin_style_list", "oiku_wpmem_admin_style_list" );
  add_filter( "wpmem_post_password", "oiku_wpmem_post_password" );
  add_filter( "oik_query_field_types", "oiku_query_field_types" );
  add_action( "oik_pre_theme_field", "oiku_pre_theme_field" );
  add_action( "oik_pre_form_field", "oiku_pre_form_field" );
  add_filter( "bw_field_validation_userref", "oiku_field_validation_userref", 10, 3 );
  add_action( "oik_fie_edit_field_type_userref", "oiku_oik_fie_edit_field_type_userref" );
	add_action( "oik_fields_loaded", "oiku_oik_fields_loaded", 10 );
}

oiku_plugin_loaded();
