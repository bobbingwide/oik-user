<?php // (C) Copyright Bobbing Wide 2013, 2014

/**
 * Return the most appropriate field name given the value that the user typed
 * 
 * Note: We don't need to prefix some fields with user_ as get_the_author_meta does that
 *
 * We could either attach our own filters for each of the fields OR leave it to format_meta **?**
 * 
 * @link http://codex.wordpress.org/Function_Reference/the_author_meta
 * 
 * User fields according to above link include: 
 *
 *  user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key,
 *  user_status, display_name, nickname, first_name, last_name, description, jabber, aim, yim
 *  user_level, user_firstname, user_lastname, user_description, rich_editing, comment_shortcuts,
 *  admin_color, plugins_per_page, plugins_last_view, ID
 * 
 * Then there's the additional fields added by tools such as WP-member: dob, sex, FBconnect, Twitterconnect ... which are managed by what? **?**
 */
function oiku_map_field( $field ) {
  static $fields = array( "bio" => "description" 
                        , "name" => "display_name"
                        , "forename" => "first_name"
                        , "surname" => "last_name"
                        , "site" => "url" 
                        );
  $name = bw_array_get( $fields, $field, $field );
  oiku_register_field( $name ); 
  return( $name );
}

/**
 * Register a field if the name matches
 *
 */
function bw_mayberegister_field( $name, $field, $type, $title ) {
  if ( $name == $field ) {
    bw_register_field( $field, $type, $title );
  }
}

/**
 * Register a field named $name
 *
 * Given a field name register the field that matches the name.
 * This is so we can format the field according to the field type.
 * e.g. Create an email link for an email address, a link for an URL.
 * 
 *
 */  
function oiku_register_field( $name ) {
  bw_mayberegister_field( $name, "display_name", "text", "User name" );
  bw_mayberegister_field( $name, "description", "text", "Description" );
  bw_mayberegister_field( $name, "email", "email", "Email" );
  bw_mayberegister_field( $name, "url", "URL", "Website" );
}

/**
 * Format some fields for a user
 *
 * @param ID $user - ID of the user
 * @param array $atts - array of name value pairs
 */
function oiku_format_fields( $user, $atts ) {  
  $fields = bw_array_get( $atts, "fields", "name,bio,email" );
  if ( $fields ) {
    $field_arr = explode( ",", $fields ); 
    $field_arr = bw_assoc( $field_arr );
    //bw_trace2( $field_arr, "field_arr", false );
    foreach ( $field_arr as $field ) {
      $name = oiku_map_field( $field );
      //e( $name );
      $user_meta = get_the_author_meta( $name, $user );
      //e ( "User meta: $user_meta!" );
      $customfields = array( $name => $user_meta ); 
      sdiv( $name );
      //bw_backtrace();
      bw_format_meta( $customfields );
      ediv( $name );
    }
  } else { 
    p( "Invalid fields= parameter for bw_user shortcode" );
  } 
}

/** 
 * Implements the [bw_user] shortcode
 *
 * Display the selected fields for a user
 *
 */
function oiku_user( $atts=null, $content=null, $tag=null ) {
  $id = bw_array_get_dcb( $atts, "user", false, "bw_default_user", null );
  // e( "Current id is: $id " );
  $user = bw_get_user( $id );
  if ( $user ) {
    oiku_format_fields( $id, $atts );
  } else {
    bw_trace2( $id, "User not found" );
    //e( "User not found: $id " );
  }
  return( bw_ret() );
}

/**
 * Implement help hook for [bw_user]
 */
function bw_user__help( $shortcode="bw_user" ) {
  return( "Display information about a user" );
}

/**
 * Implement syntax hook for [bw_user]
 */
function bw_user__syntax( $shortcode="bw_user" ) {
  $syntax = array( "user" => bw_skv( bw_default_user(), "<i>id</i>|<i>email</i>|<i>slug</i>|<i>login</i>", "Value to identify the user" )
                 , "fields" => bw_skv( "name,bio,email", "<i>field1,field2</i>", "Comma separated list of fields" )
                 );
  return( $syntax );
}

/**
 * Implement example hook for [bw_user] 
 *
 */
function bw_user__example( $shortcode="bw_user" ) {
  $id = bw_default_user( true ); 
  $example = "user=$id"; 
  $text = __( "Display default information for user: $id " );
  bw_invoke_shortcode( $shortcode, $example, $text );
}

/**
 * Implement snippet hook for [bw_user] 
 */
function bw_user__snippet( $shortcode="bw_user" ) {
  $id = bw_default_user( true ); 
  $example = "user=$id"; 
  _sc__snippet( $shortcode, $example ); 
}


/**
 * Display the fields for the user
 *
 * @param WP_User $user - A WP_User object
 * @param array $atts - shortcode parameters
 * 
 * 
 * The WP_User Object consists of:
 
  WP_User Object
        (
            [data] => stdClass Object
                (
                    [ID] => 1
                    [user_login] => admin
                    [user_pass] => $P$BijsY7/BdZ9AzR8YdJwYVVt68FBovk0
                    [user_nicename] => admin
                    [user_email] => cweec@cwiccer.com
                    [user_url] => http://cwiccer.com
                    [user_registered] => 2010-12-23 12:22:39
                    [user_activation_key] => qLc3INyEWwBOsfFDnZeV
                    [user_status] => 0
                    [display_name] => vsgloik
                )

            [ID] => 1
            [caps] => Array of Additional Capabilities - how does this compare to allcaps
                (
                    [administrator] => 1
                    [membershipadmin] => 1
                    [gform_full_access] => 1
                    [M_add_level] => 1
                    [M_add_ping] => 1
                )

            [cap_key] => wp_capabilities
            [roles] => Array or Roles
                (
                    [0] => administrator
                )

            [allcaps] => Array of Capabilities - there are loads of these
                (
                  [capability_name] => 1
                )

            [filter] => 
        )

    
)
 */
function oiku_display_user( $user, $atts ) {
  $fields = bw_array_get( $atts, "fields", "name,bio,email" );
  if ( $fields ) {
    $field_arr = explode( ",", $fields ); 
    $field_arr = bw_assoc( $field_arr );
    stag( "tr" );
    foreach ( $field_arr as $field ) {
      $name = oiku_map_field( $field );
      $user_meta = get_the_author_meta( $name, $user->ID );
      stag( "td" );
      $customfields = array( $name => $user_meta ); 
      bw_format_field( $customfields );
      etag( "td" );
    }
    etag( "tr" );
  } else { 
    p( "Invalid fields= parameter for bw_user shortcode" );
  } 
}

/**
 * Determine the columns to be displayed in the table
 *
 * Similar to the logic for the [bw_table] shortcode
 * 
 * @param array $atts - array of NVP parameters containing "fields"
 * @return bool - true if the fields include "excerpts" - which we can't do for users! 
 */
function oiku_query_table_columns( $atts=null ) {
  global $field_arr, $title_arr;
  $field_arr = array();
  $title_arr = array();
  $field_arr = bw_assoc( bw_as_array( bw_array_get( $atts, "fields", "name,bio,email" ) ));
  $field_arr = apply_filters( "oik_table_fields_user", $field_arr, "user" );
  $title_arr = bw_default_title_arr( $field_arr ); 
  $title_arr = apply_filters( "oik_table_titles_user", $title_arr, "user", $field_arr );
  bw_table_header( $title_arr );  
  $excerpts = in_array( "excerpt", $field_arr);
  return( $excerpts );
}

/**
 * Implement [bw_users] shortcode
 *
 * Similar to bw_table, this displays information about a selected set of users
 *
 * @param array $atts - shortcode parameters
 * @param string $content - not expected
 * @param string $tag 
 * @return string generated HTML 
 */
function oiku_users( $atts=null, $content=null, $tag=null ) {
  $fields = bw_array_get( $atts, "fields", "name,bio,email" );
  $atts[ "fields" ] = "all_with_meta";
  $users = get_users( $atts );
  bw_trace2( $users, "users" );
  $atts[ "fields" ] = $fields;
  
  if ( count( $users) ) {
    oik_require( "shortcodes/oik-table.php" );
    stag( "table");
    oiku_query_table_columns( $atts, "user" );
    stag( "tbody" );
    foreach ( $users as $user ) {
      oiku_display_user( $user, $atts );
    }
    etag( "tbody" );
    etag( "table" );
  }  
  return( bw_ret() );
}

/**
 * Implement help hook for [bw_users]
 */
function bw_users__help( $shortcode="bw_users" ) {
  return( "Display information about site users" );
}

/**
 * Implement syntax hook for [bw_users]
 *
 * @link http://codex.wordpress.org/Function_Reference/get_users
 * 
  'blog_id' => $GLOBALS['blog_id'],
        'role' => '',
        'meta_key' => '',
        'meta_value' => '',
        'meta_compare' => '',
        'include' => array(),
        'exclude' => array(),
        'search' => '',
        'search_columns' => array(),
        'orderby' => 'login',
        'order' => 'ASC',
        'offset' => '',
        'number' => '',
        'count_total' => true,
        'fields' => 'all',
        'who' => ''
        
        orderby - Sort by 'ID', 'login', 'nicename', 'email', 'url', 'registered', 'display_name', 'post_count', or 'meta_value' (query must also contain a 'meta_key' - see WP_User_Query).
 */
function bw_users__syntax( $shortcode="bw_users" ) {
  $syntax = array( "role" => bw_skv( null, "", "User role" )
                 , "orderby" => bw_skv( "user_login", "ID|login|nicename|email|url|registered|display_name|post_count|meta_value", "Order by" )
                 , "order" => bw_skv( "ASC", "DESC", "Ascending or Descending sequence" )
                 , "meta_key" => bw_skv( null, "<i>field name</i>", "Meta field name" )
                 , "meta_value" => bw_skv( null, "<i>value</i>", "Meta field value" )
                 , "meta_compare" => bw_skv( "=", "!=|>|>=|<|<=", "Operator to test the meta value" )
                 , 'include' => bw_skv( null, "<i>id1,id2</i>", "IDs to include" )
                 , 'exclude' => bw_skv( null, "<i>id1,id2</i>", "IDs to exclude" )
                 );
  return( $syntax );
}                          




