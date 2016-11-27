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
		$userlist = bw_user_list();
		$args['#options'] = $userlist;
		bw_select( $name, $title, $value, $args );
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

