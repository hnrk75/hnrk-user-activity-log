<?php
/**
 * Uninstall script for the HNRK User Activity Log plugin.
 *
 * Runs automatically when the plugin is deleted via the WordPress admin.
 * Removes all plugin data from the database.
 *
 * @package HNRK_User_Activity_Log
 */

// Exit if not called from WordPress uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove login_times meta from all users.
$users = get_users( array( 'fields' => 'ID' ) );
foreach ( $users as $user_id ) {
	delete_user_meta( $user_id, 'login_times' );
}
