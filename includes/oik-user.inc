<?php // (C) Copyright Bobbing Wide 2013,2014

/**
 * Implement bw_form_field_ hook for "userref"
 *
 */
function bw_form_field_userref( $name, $type, $title, $value, $args ) {
  //$lab = label( $name, $title );
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
  //bw_backtrace();   
  bw_trace2();
  span( $key );
  // e( $value );
  if ( is_array( $value ) ) {
    $value = bw_array_get( $value, 0, null );
  }
  if ( $value ) {
    oik_require( "shortcodes/oik-user.php", "oik-user" );
    e( oiku_user( array( "user" => $value, "fields" => "name,email" ) ) );
    
  }  
  epan();
  // e( $value );
} 

