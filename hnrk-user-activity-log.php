<?php
/**
 * Plugin Name: HNRK User Activity Log
 * Plugin URI:  https://www.hnrkagency.se
 * Description: Logs Subscriber logins and page visits per session.
 * Version:     1.0
 * Author:      Henrik Pettersson
 * Author URI:  https://www.hnrkagency.se
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package HNRK_User_Activity_Log
 */

// Hook for logging when a Subscriber logs in.
add_action('wp_login', 'hnrk_log_user_login', 10, 2);

// Hook for logging page visits for Subscribers.
add_action('template_redirect', 'hnrk_log_page_visits');

// Hook for creating the admin menu.
add_action('admin_menu', 'hnrk_create_admin_menu');

// Hook to enqueue custom CSS.
add_action('admin_enqueue_scripts', 'hnrk_enqueue_custom_css');

// Include necessary files.
include(plugin_dir_path(__FILE__) . 'includes/admin.php');
include(plugin_dir_path(__FILE__) . 'includes/functions.php');
include(plugin_dir_path(__FILE__) . 'includes/visits-by-page.php');

// Enqueue custom CSS for admin pages.
function hnrk_enqueue_custom_css($hook) {
	if ($hook === 'toplevel_page_hnrk-user-activity-log' || $hook === 'activity-log_page_hnrk-sort-by-page') {
		wp_enqueue_style('hnrk-admin-style', plugin_dir_url(__FILE__) . 'css/style.css');
	}
}
