<?php
/*
 * Plugin Name: BP Share Posts
 * Version: 1.0a
 * Plugin URI: https://wearezipline.com
 * Description: Allows members to share posts on their Buddypress activity wall.
 * Author: Zipline
 * Author URI: https://wearezipline.com
 * Requires at least: 4.0
 * Requires: BuddyPress
 * Tested up to: 4.8.1
 *
 * Text Domain: bp-share-posts
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Zipline
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-bp-share-posts.php' );
// require_once( 'includes/class-bp-share-posts-settings.php' );

// Load plugin libraries
// require_once( 'includes/lib/class-bp-share-posts-admin-api.php' );

/**
 * Returns the main instance of BP_Share_Posts to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object BP_Share_Posts
 */
function BP_Share_Posts () {
	$instance = BP_Share_Posts::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		// $instance->settings = BP_Share_Posts_Settings::instance( $instance );
	}

	return $instance;
}

BP_Share_Posts();
