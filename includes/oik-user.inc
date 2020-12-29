<?php // (C) Copyright Bobbing Wide 2013-2016

/**
 * Implement bw_form_field_ hook for "userref"
 *
 * @param string $name 
 * @param string $type
 * @param string $title 
 * @param string $value
 * @param array $args
 */
function bw_form_field_userref( $name, $type, $title, $value, $args ) {
	$users = bw_get_users( array( "number" => "", 'orderby' => 'name' ) ) ;
	$userlist = bw_form_field_userref_options( $users );
	$args['#options'] = $userlist;
	bw_select( $name, $title, $value, $args );
}

/**
 * Builds a user options array.
 *
 * This is different from bw_user_array(), which only shows the display name.
 * Since users can have multiple roles they are all shown.
 * This will not uniquely identify users who have the same name eg John Smith.
 *
 * @param array $user - array of user objects
 * @return array - associative array of user ID to  and roles
 */
function bw_form_field_userref_options( $users )  {
	$options = array();
	foreach ($users as $user ) {
		$roles = implode( ',', $user->roles );
		$options[$user->ID] = $user->display_name . ' ( ' . $roles . ' )';
	}
	//bw_trace2( $options );
	return $options;
}

/**
 * Theme a userref field for display
 *
 * When displayed the field is read only.
 *
 * @param string $key - field name e.g. _userref
 * @param mixed $value - post metadata value
 * @param array $field - the field structure if defined using bw_register_field()
 */
function bw_theme_field_userref( $key, $value, $field ) {
	//bw_trace2();
	span( $key );
	if ( is_array( $value ) ) {
		$value = bw_array_get( $value, 0, null );
	}
	if ( $value ) {
	 oik_require( "shortcodes/oik-user.php", "oik-user" );
		e( oiku_user( array( "user" => $value, "fields" => "name,email" ) ) );
		
	}  
	epan();
}

/**
 * Return the value of the virtual field Gravatar
 * 
 * i.e. Retrieve the avatar `<img>` tag for chosen user.
 *
 * @param ID $parms the current post ID
 
 * @return false|string An `<img>` tag for the user's avatar. False on failure.
 * @return string the Gravatar image tag... `<img>` tag for the user's avatar. False on failure.
 */ 
function oiku_get_gravatar( $parms ) {
	//bw_trace2();
	$id = bw_default_user( $parms );
	$gravatar = get_avatar( $id );
	return( $gravatar );
}

/**
 * Returns the value of the virtual field follow_me
 *
 * Displays the follow me social links for the given user.
 * The parameters to bw_follow_me are passed through the $atts array
 *
 * @param $parms - post ID of the post being displayed
 * @return string
 */
function oiku_get_follow_me( $parms ) {
	oik_require( "shortcodes/oik-follow.php");
	$atts = oiku_atts();
	bw_push();
	$follow_me = bw_follow_me( $atts );
	bw_pop();
	return $follow_me;
}
