<?php
/**
 * Plugin Name: HNRK User Activity Log
 * Plugin URI:  https://hnrkagency.se
 * Description: Logs Subscriber logins and page visits per session.
 * Version:     1.0
 * Author: Henrik Pettersson at HNRK Labs
 * Author URI: https://hnrkagency.se
 * Text Domain: hnrk-user-activity-log
 * Domain Path: /languages
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * License: GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package HNRK_User_Activity_Log
 */

// Include necessary files.
require plugin_dir_path( __FILE__ ) . 'includes/admin.php';
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/visits-by-page.php';

// Hook for loading plugin translations.
add_action( 'plugins_loaded', 'hnrk_load_textdomain' );

// Hook for logging when a Subscriber logs in.
add_action( 'wp_login', 'hnrk_log_user_login', 10, 2 );

// Hook for logging page visits for Subscribers.
add_action( 'template_redirect', 'hnrk_log_page_visits' );

// Hook for creating the admin menu.
add_action( 'admin_menu', 'hnrk_create_admin_menu' );

// Hook to enqueue custom CSS.
add_action( 'admin_enqueue_scripts', 'hnrk_enqueue_custom_css' );

/**
 * Load plugin translations.
 */
function hnrk_load_textdomain() {
	load_plugin_textdomain( 'hnrk-user-activity-log', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Enqueue custom CSS for admin pages.
 *
 * @param string $hook The current admin page hook.
 */
function hnrk_enqueue_custom_css( $hook ) {
	if ( 'toplevel_page_hnrk-user-activity-log' === $hook || 'activity-log_page_hnrk-sort-by-page' === $hook ) {
		wp_enqueue_style( 'hnrk-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '1.0.0' );
	}
}
