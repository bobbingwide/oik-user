<?php // (C) Copyright Bobbing Wide 2013,2014

/**
 * Implements "oik_admin_menu" action for oik-user
 *
 * Adds the actions and filters needed to allow oik options to be defined for individual users
 *
 */
function oiku_lazy_admin_menu() {
  add_action( 'show_user_profile', "oiku_show_user_profile_selected", 9, 1 );
  add_action( 'show_user_profile', "oiku_show_user_profile" );
  //add_action( 'edit_user_profile', "oiku_show_user_profile_selected", 9, 1 );
  add_action( 'edit_user_profile', "oiku_edit_user_profile" );
  add_action( 'personal_options_update', "oiku_personal_options_update" );
  add_action( 'edit_user_profile_update', "oiku_edit_user_profile_update" );
  add_filter( 'user_contactmethods', 'oiku_user_contactmethods', 10, 1 );
  add_filter( 'user_contactmethods', 'oiku_user_contactmethods_selected', 20, 1 );
  add_action( "oik_menu_box", "oiku_menu_box" );
  add_filter( 'manage_users_columns', 'oiku_manage_user_columns' );
  add_filter( 'manage_users_custom_column', 'oiku_manage_users_custom_column', 10, 3 );
  register_setting( 'oik_user_options', 'bw_user_options', 'oik_user_options_validate' ); 
  register_setting( 'oik_user_filters', 'bw_user_filters', 'oik_user_options_validate' ); 
  add_submenu_page( 'oik_menu', __( 'oik user admin', 'oik') , __( "oik user admin", 'oik'), 'manage_options', 'oik_user_admin', "oik_user_admin_page" );
}

/**
 * Validate the oik user options fields
 * 
 * @param $input 
 * @return $input 
 *
 * Note: Checkboxes don't need validating
 * and there's little point validating the text since we allow (X)HTML and shortcodes
 * AND if the user chooses to change a list start field to something else
 * it may not be necessary to check the list end is the right tag.
 * Of course, we're assuming the user is reasonably web savvy
 */
function oik_user_options_validate( $input ) {
  return( $input ); 
}

/**
 * Display the oik user admin page
 * 
 * We display a set of checkboxes reflecting the fields that could be displayed on the user profile
 * We allow user_contactmethods fields to be turned off individually
 * For other fields we have less control
 * 
 * 
 */
function oik_user_admin_page() {
  remove_filter( "user_contactmethods", "oiku_user_contactmethods_selected", 20, 1 );
  remove_filter( "show_user_profile", "oiku_show_user_profile_selected", 9, 1 );
  oik_menu_header( "user admin", "w60pc" );
  oik_box( null, null, "Check <b>Contact Info</b> fields to display on User Profile", "oik_user_profile_fields" );
  oik_box( null, null, "Check hooks to run for the 'show_user_profile' action", "oik_user_profile_filters" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Return an array of possible user contact methods fields to display
 *
 * Where do these come from?
 * 
 */
function oik_user_list_metadata() {
  $fields = array();
  //$fields = apply_filters( "user_contactmethods", $fields,  );
  $fields = wp_get_user_contact_methods();
  return( $fields );
}

/**
 * Display checkboxes for fields that could be displayed
 *
 * Store the information as an array of fields to be shown keyed by fields name
 * 
 */ 
function oik_user_profile_fields() {
  $fields = oik_user_list_metadata();
  $option = "bw_user_options"; 
  $options = bw_form_start( $option, "oik_user_options" );
  foreach ( $fields as $field => $data ) {
    bw_trace2( $data );
    $options[$field] = bw_array_get( $options, $field, true );
    bw_checkbox_arr( $option, $data, $options, $field );  
  }
  etag( "table" );   
  p( isubmit( "ok", "Update", null, "button-primary" ) );
  etag( "form" );
  bw_flush();
}

/**
 * Return the function name as a string
 *
 * @param array|string $function - the function or method name
 * @return string - the method name as class::method or original function name
 */
function oiku_get_function_as_string( $function ) {
  if ( is_array( $function ) ) {
    $class = $function[0];
    if ( is_object( $class ) ) {
      $class = get_class( $class );
    }
    $func = $function[1];
    $funcname = $class . '::' . $func;
  } else {
    // It's OK already
    $funcname = $function;
  }
  return( $funcname );
}

/**
 * Return the function name as an array if necessary 
 * 
 * If the function is in the form class::method
 * then we need to pass it as an array to remove_filters()
 *
 */
function oiku_get_function_as_array( $function ) {
  if ( false !== strpos( $function, "::" ) ) {
    list( $class, $method ) = explode( "::", $function );
    $function = array( $class, $method ); 
  }  
  return( $function );
} 

/**
 * Identify the other action hooks to remove
 *
 * The action invoked to display other fields is "show_user_profile".
 *
 * During run time execution we need to run oiku_show_user_profile_selected() first
 * so that it can disable the hooks that we don't want to have run.
 *
 * This would include:
 * - WordPress SEO settings
 * - oik user options
 * - WP-Members additional fields
 * - others that I'm not yet aware of
 * 
 * We may also want to remove
 * - Additional Capabilities
 *
 * 
 */
function oik_user_profile_filters() {
  global $wp_filter;
  $fields = array();
  $filters = bw_array_get( $wp_filter, "show_user_profile", null );
  foreach ( $filters as $priority => $hooks ) {
    foreach ( $hooks as $key => $hook ) {
      bw_trace2( $hook, "hook", false );
      $function = oiku_get_function_as_string( $hook['function'] ); 
      $fields["$priority $function"] = $hook['function'];
    }
  } 
  $option = "bw_user_filters"; 
  $options = bw_form_start( $option, "oik_user_filters" );
  foreach ( $fields as $field => $data ) {
    //bw_trace2( $data );
    $options[$field] = bw_array_get( $options, $field, true );
    bw_checkbox_arr( $option, $field, $options, $field );  
  }
  etag( "table" ); 
  p( "This table lists the action hooks that plugins have registered to be run when displaying the user profile" );
  p( "Uncheck the action hooks that you do not want to have run, then click on Update." );
    
  p( isubmit( "ok", "Update", null, "button-primary" ) );
  etag( "form" );
  bw_flush();
}

/**
 * Implement actions or filters that we haven't understood yet! 
 *
 * **?** Dummy function not expected to be called except during development/debugging
 */
function oiku_action() {
  bw_backtrace();
  gobang();
}

/** 
 * Add a contact method if not already listed
 * 
 * WordPress SEO adds Google+ (googleplus), Twitter (twitter) and now (1.4.10) Facebook URL facebook)
 *
 * Notes: We can alter the label using the "user_$name_label" filter
 * echo apply_filters('user_'.$name.'_label', $desc); ?> 
 * We can do this for twitter, facebook and googleplus later if we want
 * OR just do it here by updating the label? 
 *
 * @param array $contact_methods - array of contact methods to filter
 * @param string $key - contact method to check for
 * @param string $label - translatable part of the label
 * @param string $shortcode - oik shortcode suffix - with leading space - non translatable
 * @return array of contact methods 
 */
function oiku_user_contactmethod( $contact_methods, $key, $label, $shortcode ) {
  $key_present = bw_array_get( $contact_methods, $key, false );
  if ( !$key_present ) {
    $contact_methods[$key] = __( $label ) . $shortcode;
  } else {
    $contact_methods[$key] = $key_present . $shortcode;
  }
  return( $contact_methods );
}

/**
 * Implement "user_contactmethods" filter for oik-user - 1st pass 
 * 
 * Add contact methods not already included.
 * Note that the User Contact fields are stored individually in wp_usermeta
 * whereas the oik User Profile fields are less granular, being stored in a serialised structure
 * @uses oiku_user_contactmethod
 * 
 * @param array $contact_methods - the array of "Contact methods"
 * @return array - the filtered contact methods
 */
function oiku_user_contactmethods( $contact_methods ) {
  $contact_methods = oiku_user_contactmethod( $contact_methods, "twitter", "Twitter"," [bw_twitter]" );
  $contact_methods = oiku_user_contactmethod( $contact_methods, "facebook", "Facebook URL"," [bw_facebook]" );
  $contact_methods = oiku_user_contactmethod( $contact_methods, "linkedin", "LinkedIn URL"," [bw_linkedin]" );
  $contact_methods = oiku_user_contactmethod( $contact_methods, "googleplus", "Google+ URL"," [bw_googleplus]" );
  $contact_methods = oiku_user_contactmethod( $contact_methods, "youtube", "YouTube URL"," [bw_youtube]" );
  $contact_methods = oiku_user_contactmethod( $contact_methods, "flickr", "Flickr URL"," [bw_flickr]" );
  $contact_methods = oiku_user_contactmethod( $contact_methods, "picasa", "Picasa URL"," [bw_picasa]" );
  $contact_methods = oiku_user_contactmethod( $contact_methods, "skype", "Skype"," [bw_skype]" );
  return( $contact_methods ); 
}

/**
 * Implement "user_contactmethods" filter for oik-user - 2nd pass
 *
 * Here we remove the contact methods that the administrator has decided should not be displayed
 * on the "oik user admin" page.
 * 
 * @param array $contact_methods - array of possible contact methods and their labels 
 * @return array filtered contact methods
 */
function oiku_user_contactmethods_selected( $contact_methods ) {
  $oik_user_options = get_option( "bw_user_options" );
  //bw_trace2( $oik_user_options, "bw_user_options" );
  if ( $oik_user_options && count( $oik_user_options ) ) {
    foreach ( $oik_user_options as $key => $value ) {
      //bw_trace2( $key, $value );
      if ( !$value ) {
        unset( $contact_methods[ $key ]);
      }  
    }
  }  
  return( $contact_methods );
}

/**
 *
 * Return the user meta data for user $ID and key $key
 * 
 * @param integer $ID - User ID of the user
 * @param string $key - field name. Note: This could be a serialized array
 * @return string $user_meta - NOT deserialised
 * 
 */
function bw_get_user_meta( $ID, $key="bw_options" ) {
  $user_meta = get_user_meta( $ID, $key, true );
  bw_trace2( $user_meta, "user_meta" );
  return( $user_meta );
}

/**
 * Update user meta data 
 *
 * @param integer $ID - user ID
 * @param string $key - the meta_key of the user_metadata
 * @param mixed $new_value - the new value
 * @param mixed $old_value - the old value
 *
 * @uses update_user_meta()  
 */
function bw_update_user_meta( $ID, $key="bw_options", $new_value, $old_value=null ) {
  $success = update_user_meta( $ID, $key, $new_value, $old_value );
  if ( $success === false ) {
    // Anything to do here? 
  }
  bw_trace2( $success, "update_user_meta" );
}

/**
 * Implement "edit_user_profile" action for oik-user to display another user's fields
 *
 * @param user $user - user object
 *
 * @uses oiku_show_user_profile()
 */
function oiku_edit_user_profile( $user ) {
  oiku_show_user_profile( $user ); 
}

/**
 * Remove a filter defined for an object
 *
 * This function is necessary when you know the name of the method but don't have access to the specific instance of the class
 * We have to work directly against the $wp_filter array
 *
 * Code copied from 
 * @link https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
 *
 * @param string $hook_name - the action/filter hook to remove
 * @param string $class_name - the class name e.g. WPSEO_Admin
 * @param string $method_name - the method name e.g. user_profile
 * @param integer $priority - the hook priority
 */
function oiku_remove_filters_for_anonymous_class( $hook_name='', $class_name='', $method_name='', $priority=0 ) {
	global $wp_filter;
	// Take only filters on right hook name and priority
	if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
		return false;

	// Loop on filters registered
	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
			// Test if object is a class, class and method is equal to param !
			if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name ) {
				unset($wp_filter[$hook_name][$priority][$unique_id]);
			}
		}
	}
	return false;
}

/**
 * Implement "show_user_profile" action for oik-user
 * 
 * Filter out the user profile actions that we don't want to run leaving only those that we do.
 *
 * @TODO: Make this work for "edit_user_profile" as well by checking the current filter.
 * 
 * @param WP_User $user - WP_User object
 *
 */
function oiku_show_user_profile_selected( $user ) {
  $oik_user_filters = get_option( "bw_user_filters" );
  bw_trace2( $oik_user_filters, "bw_user_filters" );
  if ( $oik_user_filters && count( $oik_user_filters ) ) {
    foreach ( $oik_user_filters as $key => $value ) {
      if ( !$value ) {
        list( $priority, $function ) = explode( " ", $key ); 
        $function = oiku_get_function_as_array( $function );
        bw_trace2( $function, "function" );
        if ( is_array( $function ) ) {
          oiku_remove_filters_for_anonymous_class( "show_user_profile", $function[0], $function[1], $priority );  
        } else { 
          remove_filter( "show_user_profile", $function, $priority ); 
        }
      }  
    }
  }  
} 

/**
 * Implement "show_user_profile" action for oik-user to display user's fields
 *
 * Note: When using a membership plugin such as WP-Members then you may not want 
 * to display oik-user's fields. Use the "oik user admin" to control what the user sees.
 * 
 * @param user $user - a user object
 *
 */
function oiku_show_user_profile( $user ) {
  $ID = $user->ID;
  bw_trace2(); 
  $option = "bw_options"; 
  $options = bw_get_user_meta( $ID, $option ); 
  h3( __("oik user options") );
  //p( "ID:" . $user->ID );
  //p( "Login: " . $user->user_login );
  //p( "User name: " . $user->user_nicename ); 
  stag( "table" );
  oik_require( "bobbforms.inc" );
  bw_textfield_arr( $option, "Contact [bw_contact user=$ID]", $options, 'contact', 50 );
  bw_textfield_arr( $option, "Telephone [bw_telephone user=$ID]", $options, 'telephone', 50 );
  bw_textfield_arr( $option, "Mobile [bw_mobile user=$ID]", $options, 'mobile', 50 );
  bw_textfield_arr( $option, "Fax [bw_fax user=$ID]", $options, 'fax', 50 );
  bw_textfield_arr( $option, "Emergency [bw_emergency user=$ID]", $options, 'emergency', 50 ); 
  bw_textfield_arr( $option, "Company [bw_company user=$ID]", $options, 'company', 50 );    
  bw_textfield_arr( $option, "Extended-address [bw_address user=$ID]", $options, 'extended-address', 50 );
  bw_textfield_arr( $option, "Street-address", $options, 'street-address', 50 );
  bw_textfield_arr( $option, "Locality", $options, 'locality', 50 );
  bw_textfield_arr( $option, "Region", $options, 'region', 50 );
  bw_textfield_arr( $option, "Post Code", $options, 'postal-code', 50 );
  bw_textfield_arr( $option, "Country name", $options, 'country-name', 50 );
  bw_textarea_arr( $option, "Google Maps introductory text for [bw_show_googlemap user=$ID]", $options, 'gmap_intro', 50 );
  bw_textfield_arr( $option, "Latitude [bw_geo user=$ID] [bw_directions user=$ID]", $options, 'lat', 50 );
  bw_textfield_arr( $option, "Longitude [bw_show_googlemap user=$ID]", $options, 'long', 50 );
  bw_textfield_arr( $option, "'Howdy,' replacement string", $options, 'howdy', 50 );
  
  etag( "table" ); 
  // oiku_display_activation_status( $user );			
  bw_flush();
  wp_nonce_field( 'oiku_user_profile_update', 'oiku_user_profile' );
}

/**
 * Display user Activated information 
 * 
 * Relevant fields are:
 * <code>
 * [user_registered] => 2013-03-07 15:25:39
 * [user_activation_key] => 
 * [user_status] => 0 
 * </code>
 * 
 * We should only display this information to admin users - current_user_can( ? )
 * The user_status field has been deprecated for quite a while, but some plugins still refer to it
 * 
 * The user_activation_key is set when the user has to be (re)activated 
 
 */
function oiku_display_activation_status( $user ) {
  bw_trace2();
  p( $user->user_registered );
  p( $user->user_activation_key );
  p( $user->user_status );
  

   
  //bw_textfield( "Status", 3, "Status"
} 

/**
 * Implement "personal_options_update" for oik-user 
 *
 * This action is invoked if IS_PROFILE_PAGE is true
 */
function oiku_personal_options_update( $ID ) {
  oiku_edit_user_profile_update( $ID );
}

/**
 * Implement "edit_user_profile_update" for oik-user 
 */ 
function oiku_edit_user_profile_update( $ID ) {
  bw_trace2();
  $option = "bw_options"; 
  $options = bw_get_user_meta( $ID, $option ); 
  $new_options = bw_array_get( $_REQUEST, $option, null );
  if ( $new_options ) {
    $new_options = oik_set_latlng( $new_options );
    bw_update_user_meta( $ID, $option, $new_options, $options );
  }
}

/*  
function oiku_extra_usage_notes_2() {
  oik_require( "includes/oik-sc-help.inc" );
  p( "Use the shortcodes in your pages, widgets and titles. e.g." );
  bw_invoke_shortcode( "bw_contact", "alt=2", "Display your alternative contact name." );
  bw_invoke_shortcode( "bw_email", "alt=2 prefix=e-mail", "Display your alternative email address, with a prefix of 'e-mail'." );
  bw_invoke_shortcode( "bw_telephone", "alt=2", "Display your alternative telephone number." );
  bw_invoke_shortcode( "bw_address", "alt=2", "Display your alternative address." );
  bw_invoke_shortcode( "bw_show_googlemap", "alt=2", "Display a Googlemap for your alternative address." );
  bw_invoke_shortcode( "bw_directions", "alt=2", "Display directions to the alternative address." );
  bw_flush();
}
*/

/**
 * Implement "oik_menu_box" action for oik-user 
 */
function oiku_menu_box() {
  oik_box( NULL, NULL, "oik user settings", "oiku_user_settings" );
}

/** 
 * Display/process the Copy options form
 
 */
function oiku_user_settings() {
  p( "Use this form to copy oik options values to a user of your choice." );
  p( "You should only need to do this once per user" );
  oiku_copy_user_settings();
  bw_form();
  stag( "table", "widefat" );
  $alts = array( 0 => "options"
               , 1 => "more options (alt=1)"
               , 2 => "more options 2 ( alt=2)" 
               ); 
  bw_select( "oiku_alt", "Copy from", null, array( "#options" => $alts ) );
  $users = bw_user_list();
  bw_select( "oiku_user", "Target user", null, array( "#options" =>  $users ) );
  etag( "table" );
  p( isubmit( "_oiku_copy_options", "Copy options to user", null, "button-secondary" ) );
  etag( "form" );
}

/**
 * Handle Copy options form
 */
function oiku_copy_user_settings() {
  $copy = bw_array_get( $_REQUEST, "_oiku_copy_options", null );
  if ( $copy ) {
    $alt = bw_array_get( $_REQUEST, "oiku_alt", null );
    $user_id = bw_array_get( $_REQUEST, "oiku_user", null );
    if ( $user_id && $alt != null ) {
      oiku_copy_settings_to_user( $alt, $user_id );
    } else {
      p( "Please choose source and target" );
      p( "alt=$alt" );
      p( "user=$user_id" );
    }
  }
}

/**
 * Copy oik options for alt= to user= 
 * 
 * This function is used during the initial setup of oik-user 
 * Some options that don't (yet) apply to the user may get copied but we don't consider this to be a problem. 
 * There is no checking that the user options are not already set. 
 * So this function can override the user's settings. 
 * 
 * @param string $alt - source options 
 * @param integer $user_id - user ID
 */
function oiku_copy_settings_to_user( $alt, $user_id ) {
  p( "Copying settings from alt=$alt to user=$user_id" );
  $sets = array ( "bw_options" 
                , "bw_options1"
                , "bw_options2" 
                );
  $set = $sets[$alt]; 
  $new_options = get_option( $set );
  $options = bw_get_user_meta( $user_id );  
  
  /**
   * Deal with the fields that get stored separately as "User contact" fields
   * by extracting them from the $new_options structure
   */
  $new_options['twitter'] = oiku_simplify_social( $new_options, 'twitter' );
  
  oiku_set_user_contact_user_meta( $user_id, $new_options, "twitter" );
  oiku_set_user_contact_user_meta( $user_id, $new_options, "facebook" );
  oiku_set_user_contact_user_meta( $user_id, $new_options, "linkedin" );
  oiku_set_user_contact_user_meta( $user_id, $new_options, "googleplus" );
  oiku_set_user_contact_user_meta( $user_id, $new_options, "youtube" );
  oiku_set_user_contact_user_meta( $user_id, $new_options, "flickr" );
  oiku_set_user_contact_user_meta( $user_id, $new_options, "picasa" );
  oiku_set_user_contact_user_meta( $user_id, $new_options, "skype" );
  
  
  bw_update_user_meta( $user_id, "bw_options", $new_options, $options );
  

  // p( "Options copied. See "Users" to verify/update values );
}


/**
 * Set the value of a user contact field from the $options array
 * 
 * @param ID $user_id - user ID 
 * @param array $options - reference to the options array
 * @param string $field - the name of the field to extract
 */
function oiku_set_user_contact_user_meta( $user_id, &$options, $field ) {
  $value = bw_array_get( $options, $field, null );
  if ( $value ) {
    unset( $options[$field] );
    bw_update_user_meta( $user_id, $field, $value );
  }
} 

/**
 * Return the block of text after the last slash, if there is one, for the selected $social field in $options
 *
 * This routine saves us getting confused with parse_url() returning strange results on URLs such as http://www.twitter.com/!#/twittername
 * when all we actually want is the user's twittername (without the @ )
 * @param array $options - array of fields
 * @param string $social - the index of the field from which to extract the value
 * @return string - the simplified value
 */
function oiku_simplify_social( $options, $social ) {
  $option = bw_array_get( $options, $social, null ); 
  if ( $option ) {
    $splits = explode( "/", $option );
    $option = array_pop( $splits);
  }
  return( $option );
}

/**
 * Implement "manage_user_columns" for oik-user
 * 
 * Add Registered for user's registration date (user_registered)
 * Add Status for user_status
 * Add Activation key for the user activation key
 * 
 * Notes: This field is set when the user requests a password change
 * The user gets an email with the activation key in the link
 *
 <pre>
 
Someone requested that the password be reset for the following account:

http://qw/wordpress/

Username: mick

If this was a mistake, just ignore this email and nothing will happen.

To reset your password, visit the following address:

<http://qw/wordpress/wp-login.php?action=rp&key=MYBXay77rGcyZ58DxPm5&login=mick>

</pre>
Once the password has been reset the Activation key is also reset.
So, there should never be any user who has an Activation key... 
otherwise this can be used to reset someone's password.

What happens when WP-Members holds an account for approval by admin then?

 * 
 */
function oiku_manage_user_columns( $columns ) {
  bw_trace2();
  //$columns['status'] = 'status';
  $columns['user_registered'] = "Registered";
  $columns['user_status'] = "Status";
  $columns['user_activation_key'] = "Activation key";
  $columns['active'] = "Active user?";
  // $column['regist
  return( $columns );
}

/**
 * Implement "manage_users_custom_column" for oik-user
 */
function oiku_manage_users_custom_column( $val, $column_name, $user_id ) {
  bw_trace2();
  bw_backtrace();
  
  //$user = get_userdata( $user_id );
  //$user = bw_get_user( $user_id, "id" );
  //bw_trace2( $user );
  
  //$value = bw_array_get( $user, $column_name, null );
  
  // use bw_get_user_option() to get the metadata
  
  $value = get_the_author_meta( $column_name, $user_id );
  bw_format_field( array( $column_name => $value ) );
  
  return( $value );
}


/* 
 * @TODO - someday **?**

@link http://wpquestions.com/question/show/id/8170


function status_column_sortable($columns) {
  $custom = array( 'status'    => 'status' );
  return wp_parse_args( $custom, $columns);
}

add_filter( 'manage_users_sortable_columns', 'status_column_sortable' );

function status_column_orderby( $vars ) {
  if ( isset( $vars['orderby'] ) && 'status' == $vars['orderby'] ) {
    $vars = array_merge( $vars, array( 'meta_key' => 'status', 'orderby' => 'meta_value' ) );
  }
  return $vars;
}

add_filter( 'request', 'status_column_orderby' );
  


*/





  
