<?php // (C) Copyright Bobbing Wide 2013-2019

/**
 * Register the user field if required
 * 
 * Find the most appropriate field name given the field name that the user typed
 * determine its field type ( if not text ) and the suggested label
 * and register the field if it's necessary.
 *
 *
 * @param string $field field name to map
 * @return string mapped field name 
 */
function oiku_map_field( $field ) {
  $name = oiku_get_field_name( $field );
	$type = oiku_get_field_type( $name );
	$label = oiku_get_field_label( $field );
	if ( $type || $label ) {
		oiku_register_field( $name, $type, $label ); 
	}	
  return( $name );
}

/**
 * Get the field name
 * 
 * Given a field name used in the shortcode return the actual field name required for get_the_author_meta()
 * 
 * Note: We don't need to prefix some fields with user_ as get_the_author_meta() will do that for us.
 * 
 *
 * @param string $field the field name used in the shortcode
 * @return string the name of the field it maps to 
 */
function oiku_get_field_name( $field ) {
	static $fields = array( "bio" => "description"
												, "name" => "display_name"
												, "about" => "display_name" 
												, "forename" => "first_name"
												, "surname" => "last_name"
												, "site" => "url"
												);
	$name = bw_array_get( $fields, $field, $field );
	return( $name );
}

/**
 * Get the field type
 *
 * Returns the field type to register if it's not just 'text'
 *
 */
function oiku_get_field_type( $name ) {
	static $types = array( "description" => "sctext" 
	                     , "url" => "url"
											 , "email" => "email"
                       );
	$type = bw_array_get( $types, $name, null );
	return( $type );
}

/**
 * Get the field label
 *
 * Note: For backward compatibility, we retain the original labels for the default field names of the bw_user shortcode; name, bio and email.
 *
 * If you want to use bw_user for an author-box use `[bw_user fields="gravatar,about,bio" class="author-box"]`
 * then use CSS to hide the labels and separators for `gravatar` and `bio` , and maybe not for `about` .
 * 
 * @param string $field the field name
 * @return string the label/title for the field
 */
function oiku_get_field_label( $field ) {	
	$labels = array( "name" => __( "User name" , "oik-user" )
								 , "about" => __( "About", "oik-user" )
								 , "bio" => __( "Description", "oik-user" )
								 , "email" => __( "Email", "oik-user" )
								 , "url" => __( "Website", "oik-user" )
								 );
	$label = bw_array_get( $labels, $field, null ); 
	return( $label );
}																					

/**
 * Register a field named $name
 *
 * Given a field name register the field that matches the name.
 * This is so we can format the field according to the field type.
 * e.g. Create an email link for an email address, a link for an URL.
 * 
 * @param string $name 
 * @param string $type
 * @param string $label
 */  
function oiku_register_field( $name, $type, $label ) {
	bw_trace2();
	bw_register_field( $name, $type, $label );
}

/**
 * Format some fields for a user
 *
 * @param ID $user - ID of the user
 * @param array $atts - array of name value pairs
 */
function oiku_format_fields( $user, $atts ) {  
  $fields = bw_array_get_from( $atts, "fields,0", "name,bio,email" );
  $field_divs = explode( "/", $fields );
  if ( count( $field_divs )) {
    foreach ( $field_divs as $key => $fields ) {
    	$field_classes = str_replace( ",", "-", $fields );
  	    sdiv( "bw_user-fields-$key $field_classes");
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
        }
	    ediv();
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
 * @param array $atts shortcode parameters
 * @param string $content - Additional data after the other fields
 * @param string $tag - shortcode name
 * @return string generated HTML
 */
function oiku_user( $atts=null, $content=null, $tag=null ) {
	//bw_trace2();
	oiku_atts( $atts );
	$id = bw_default_user( false );
	$user_id = bw_array_get( $atts, "user", null );
	$user_id = trim( $user_id );
	if ( $user_id ) {
		$user = bw_get_user( $user_id );
	} else {
		$user = bw_get_user( $id );
	}
	if ( $user ) {
		if ( $user_id ) {
			oiku_fiddle_user_in_global_post( $user->ID );
		}
		//bw_trace2( $user, "user" );
		$class = bw_array_get( $atts, "class", null );
		if ( $class ) {
			sdiv( $class );
		}
        oiku_format_fields( $user->ID, $atts );
		if ( $content ) {
			e( bw_do_shortcode( $content ));
		}
		if ( $class ) {
			ediv( $class );
		}

		if ( $user_id ) {
			oiku_fiddle_user_in_global_post( null );
		}
	} else {
		bw_trace2( $id, "User not found" );
		//e( "User not found: $id " );
	}
	return( bw_ret() );
}

/**
 * Fiddle the author of the global post
 * 
 * We want the user of the bw_user shortcode to be used for all nested shortcodes
 * This logic doesn't support nested changes of user.
 * 
 * @param $user_id - the ID of the user we've chosen, or 0 to reset
 */

function oiku_fiddle_user_in_global_post( $user_id ) {
	$post = bw_global_post();
	if ( $post ) {
		if ( $user_id ) {
			$post->saved_author = $post->post_author;
			$post->post_author = $user_id;
		} else {
			if ( isset( $post->saved_author ) ) {
				$post->post_author = $post->saved_author;
			}
			unset( $post->saved_author );
		}
	}	
}	

/**
 * Implement help hook for [bw_user]
 */
function bw_user__help( $shortcode="bw_user" ) {
  return( "Display information about a user" );
}

/** 
 * Get field list
 *
 * - The list of possible fields is infinite.
 * - Any plugin or theme can register its own fields stored in wp_usermeta
 * - Some plugins store serialized data.
 * - WordPress supports a variety of aliases
 * - And we've added a couple more, primarily so that we can use different labels
 * - but also to make it easier to choose the fields
 * 
 * 
 * Note: Fields are marked as Registered? if they need to be displayed as other than 'text', or prefixed nicely with a sensible label.
 * 
 * Note: The label and separator can be styled using CSS
 * To display an author-box a few of the labels and separators are set to display:no.
 * 
 * 
 * Field name | Alias of | Registered?
 * ----------- | --------- | -----
 * about | display_name | -
 * bio | description | sctext
 * description | user_description | sctext
 * display_name | | - 
 * email | user_email | email
 * first_name | | -
 * forename | first_name | -
 * gravatar | | virtual 
 * ID | user_id | -
 * last_name | | -
 * login | user_login | 
 * name | display_name |
 * nicename | user_nicename | -
 * nickname | | -
 * site | url | URL
 * surname | last_name | -
 * url | user_url | URL
 * 
 * Notes: 
 * - nicename ( an alias for user_nicename ) is not the same as nickname.
 * - nicename is a sanitized version of login.
 * - nickname is something the user can choose for themselves
 *
 * 
 * Fields that are better handled using shortcodes in the embedded content include:
 * 
 * Field name | Alias of 
 * ----------- | --------- 
 * facebook | 
 * flickr | 
 * googleplus |  
 * twitter | 
 * 
 * Fields you probably won't want to display to users who are not logged in
 * 
 * Field Name | Alias of
 * ---------- | --------
 * activation_key | user_activation_key 
 * admin_color | 
 * aim | 
 * comment_shortcuts | 
 * dismissed_wp_pointers | 
 * jabber | 
 * user_level | 
 * pass | user_pass
 * plugins_last_view |
 * plugins_per_page |
 * registered | user_registered | 
 * rich_editing | 
 * show_admin_bar_front | 
 * status | user_status 
 * use_ssl | 
 * wp_capabilities 
 * yim | 
 * 
 * Note: If you want to display these you may want to implement a 'get_the_author_$field_name' filter hook
 * 
 * Fields which are provided for backward compatibility
 *
 * Field Name | Alias of 
 * ---------- | -------- 
 * user_level | $wpdb_prefix . "user_level" | 
 * user_firstname| first_name 
 * user_lastname | last_name 
 * user_description | description
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
 * Then there's the additional fields added by tools such as WP-member: dob, sex, FBconnect, Twitterconnect
 * and fields from other plugins: 
 * - billing_address_*
 * - bw_options
 * - genesis_*
 * - wpseo_*
 * 
 */
function bw_user_field_list() {
	$field_list = "about|bio|description|display_name|email|first_name|forename|gravatar|ID|last_name|login|name|nicename|nickname|site|surname|url";
	return( $field_list );
}

/**
 * Implement syntax hook for [bw_user]
 * 
 * 
 */
function bw_user__syntax( $shortcode="bw_user" ) {

	$field_list = bw_user_field_list();
  $syntax = array( "user" => bw_skv( bw_default_user(), "<i>id</i>|<i>email</i>|<i>slug</i>|<i>login</i>", "Value to identify the user" )
                 , "fields" => bw_skv( "name,bio,email", "$field_list|<i>field1,field2</i>", "Comma separated list of fields" )
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
 * The WP_User Object consists of:
 * `
        (
            [data] => stdClass Object
                (
                    [ID] => 1
                    [user_login] => #########
                    [user_pass] => $###############
                    [user_nicename] => ########
                    [user_email] => ######@######
                    [user_url] => http://##########
                    [user_registered] => 2010-12-23 12:22:39
                    [user_activation_key] => ###############
                    [user_status] => #
                    [display_name] => ##########
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
	`
 *
 * @param WP_User $user - A WP_User object
 * @param array $atts - shortcode parameters
 * 
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

function oiku_atts( $atts=null ) {
	static $saved_atts = [];
	if ( null !== $atts ) {
		$saved_atts = $atts;
	}
	return $saved_atts;
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
                 , "meta_compare" => bw_skv( "EQ", "NE|GT|GE|LT|LE", "Operator to test the meta value" )
                 , 'include' => bw_skv( null, "<i>id1,id2</i>", "IDs to include" )
                 , 'exclude' => bw_skv( null, "<i>id1,id2</i>", "IDs to exclude" )
                 );
  return( $syntax );
}                          




