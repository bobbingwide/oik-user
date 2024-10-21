=== oik user shortcodes ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 4.9.8
Tested up to: 6.7-beta3
Stable tag: 0.9.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
The oik-user plugin delivers two shortcodes to display user based information 
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

* googleplus - Google+ - deprecated by Google in April 2019
* twitter - Twitter account
* x - X account
* facebook - Facebook account
* linkedin - LinkedIn account
* youtube - YouTube account
* flickr - Flickr account
* picasa - picasa account
* skype - Skype account
* github - GitHub account
* wordpress - WordPress.org profile
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
[oik FAQ](https://www.oik-plugins.com/oik/oik-faq)

== Screenshots ==
1. User - contact info fields
2. User - oik user options
3. [bw_user] in action
4. [bw_users] in action 

== Upgrade Notice ==
= 0.9.2 =
Fixes a message produced for PHP 8.3

== Changelog ==
= 0.9.2 =
* Fixed: Don't trim( null ) #8
* Tested: With WordPress 6.7-beta3 and WordPress Multisite
* Tested: With PHP 8.3
* Tested: With PHPUnit 9.6
== Further reading ==
If you want to read more about the oik plugins then please visit the
[oik plugin](https://www.oik-plugins.com/oik) 
**"the oik plugin - for often included key-information"**

