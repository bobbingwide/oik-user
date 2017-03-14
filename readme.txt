=== oik user shortcodes ===
Contributors: bobbingwide
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 4.2
Tested up to: 4.7
Stable tag: 0.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
The oik-user plugin delivers two shortcode to display user based information 
plus the ability to define often included key information for each user,
which is displayed by the shortcodes provided by the oik base plugin.

Use the [bw_user] shortcode to display a user's information. 
Use the [bw_users] shortcode to display a table of user's information.

Default values displayed for the selected user are:
* name (display_name)
* bio (description)
* email

Other fields you can choose to display are:
* url - website
* aim - AIM        - depends on when WordPress was initially installed
* yim - Yahoo IM   - depends on when WordPress was initially installed
* jabber - Jabber / Google Talk - depends on when WordPress was initially installed

* googleplus - Google+ 
* twitter - Twitter account
* facebook - Facebook account
* linkedin - LinkedIn account
* youtube - YouTube account
* flickr - Flickr account
* picasa - picasa account
* skype - Skype account
* and any other field that may have been defined by plugins such as WP-Members

For the [bw_user] shortcode the user is specified using the user= parameter.  
The possible values being:
* ID - the unique ID of the user 
* email - the user's registered email address  
* slug - the user's login slug 
* login - the user's login name 

If the user= parameter is NOT specified then the information displayed is based on the author of the global post.
* To display the company information from oik options > Options specify user=0
* To display the company information from oik options > More Options specify user=0 alt=1
* To display the company information from oik options > More Options 2 specify user=0 alt=2

oik base shortcodes which support the user= parameter include:
* [bw_contact]
* [bw_mailto] 
* [bw_email]
* [bw_telephone]
* [bw_mobile]
* [bw_fax]
* [bw_emergency]
* [bw_follow_me] and related shortcodes
* [bw_skype]
* [bw_address]
* [bw_show_googlemap]
* [bw_geo]
* [bw_directions]

oik-user also supports the oik-fields plugin, providing a field of type "userref" 
this can be used to create metadata that refers to a specific user

For the [bw_users] shortcode use the parameters to get_user() to select the users to display.

Note: This plugin can be used in addition to the oik third contact plugin.

== Installation ==
1. Upload the contents of the oik-user plugin to the `/wp-content/plugins/oik-user' directory
1. Activate the oik-user plugin through the 'Plugins' menu in WordPress
1. For each user who has authored content ( posts, pages or CP T) define their oik information
1. If required update any shortcodes which should be displaying company information but which are now displaying user specific information

== Frequently Asked Questions ==
= Where is the FAQ? =
[oik FAQ](http://www.oik-plugins.com/oik/oik-faq)

== Screenshots ==
1. User - contact info fields
2. User - oik user options
3. [bw_user] in action
4. [bw_users] in action 

== Upgrade Notice ==
= 0.6.0 = 
Tested with WordPress 4.7. Depends on oik v3.1 and oik-fields v1.40.4

= 0.5.2 =
Fixes problem when oik is not active. Tested with WordPress 4.5

= 0.5.1 =
Update for pinterest and instagram contact methods

= 0.5 = 
Required for RNGS.org.uk - to allow the Match Manager field to be optional. i.e. None

= 0.4 = 
Required for RNGS.org.uk. Now supported on WordPress 3.9 and above 

= 0.3 =
For oik-fields v1.20 and oik v2.1-alpha.1103 or higher

= 0.2 = 
Required for Custom Post Types requiring a "userref" type field

= 0.1.627 =
Dependent upon oik v2.0

= 0.1.0329 =
Dependent upon oik v2.0-alpha.0329 and oik-fields 

= 0.1.0326 =
Dependent upon oik v2.0-alpha.0326 - for "oik_menu_box" action

= 0.1.0325 = 
Dependent upon oik v2.0-alpha.0322 and oik-fields v1.18.0325

= 0.1.0322 =
Dependent upon oik v2.0-alpha.0322

= 0.1.0303 = 
oik-user is dependent upon oik base plugin v2.0-alpha

== Changelog ==
= 0.6.0 = 
* Added: Add gravatar virtual field and author-box styling [github bobbingwide oik-user issue 3]
* Added: GitHub contact method
* Changed: Allow bw_user's fields parameter to be the first positional parameter [github bobbingwide oik-user issue 4]
* Tested: With WordPress 4.7 and WordPress Multisite

= 0.5.2 =
* Fixed: Uncaught Error: Call to undefined function bw_get_user_field() when oik deactivated [github bobbingwide oik-user issue 2]
* Tested: With WordPress 4.5 and WordPress MultiSite

= 0.5.1 =
* Added: pinterest and instagram contact methods ( Issue #1 )
* Changed: Dependent upon oik-fields v1.40.1 and oik v2.5 or higher
* Tested: With WordPress 4.4-beta1

= 0.5 = 
* Added: "oik user admin" page where an Admin user can control what a normal user sees when viewing their User Profile
* Added: Individual users may replace "Howdy," with their own string. 
* Added: Logic to allow admin user to decide which action hooks are run to display other User Profile information
* Added: Logic to allow admin user to decide which user contact fields are displayed
* Added: Syntax help for [bw_users]
* Added: Userref fields can now be defined as #optional.
* Changed: Removed unnecessary functions developed for first version of [bw_users] shortcode. 
* Fixed: oiku_field_validation_userref() 

= 0.4=
* Added: [bw_users] shortcode, similar to [bw_table]
* Changed: Now responds to "oik_add_shortcodes" action hook
* Changed: bw_theme_field_userref() displays the first item from an array of fields 

= 0.3 =
* Changed: bw_theme_field_userref() handles an array of $values - chooses first
* Changed: oiku_user() changed to support oik i18n changes

= 0.2 = 
* Added: Support for "userref" field - allowing a field to reference a user

= 0.1.0627 =
* Added: Display of user registration date, authentication key and Active flag ( from wp-members )

= 0.1.0329 =
* Changed: Altered copy functionality to cater for User contact fields such as twitter and facebook

= 0.1.0326 = 
* Added: Functionality to copy oik options data to specific users. Uses "oik_menu_box" action

= 0.1.0325 = 
* Added: Registers certain fields when referenced: display_name, description (bio), email, url

= 0.1.0322 = 
* Changed: [bw_contact_form] functions now in the oik base
* Added: support using oik base options ( with user=0 parameter) 

= 0.1.0303 =
* Added: Initial logic [bw_user] shortcode to display user profile information

== Further reading ==
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik) 
**"the oik plugin - for often included key-information"**

