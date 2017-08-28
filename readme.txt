=== BP Share Posts ===
Contributors: Zipline, sjregan
Author: sjregan
Tags: wordpress, plugin, template
Requires at least: 4.0
Tested up to: 4.8.1
Stable tag: 1.0a
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows members to share posts on their Buddypress activity wall.

== Description ==

Allows templates to display a share button underneath posts. Clicking the share button will create an entry in the current user's activity stream.

== Installation ==

Installing "BP Share Posts" can be done either by searching for "BP Share Posts" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why is the share button not displaying? =

Currently, the share button requires a template to render the button:
`<?php echo do_shortcode( '[bp-share-post-button]' ); ?>`

== Changelog ==

= 1.0 =
* 2017-08-18
* Initial release

== Upgrade Notice ==

= 1.0 =
* 2017-08-18
* Initial release
