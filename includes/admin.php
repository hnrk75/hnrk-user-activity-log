<?php
/**
 * Admin options page for the HNRK User Activity Log plugin.
 *
 * @package HNRK_User_Activity_Log
 */

/**
 * Create an admin menu item and submenu for the HNRK User Activity Log plugin.
 */
function hnrk_create_admin_menu() {
	add_menu_page(
		__( 'User Activity Log', 'hnrk-user-activity-log' ),
		__( 'Activity Log', 'hnrk-user-activity-log' ),
		'manage_options',
		'hnrk-user-activity-log',
		'hnrk_display_logs_page',
		'dashicons-visibility',
		2
	);

	add_submenu_page(
		'hnrk-user-activity-log',
		__( 'Visits by Page', 'hnrk-user-activity-log' ),
		__( 'Visits by Page', 'hnrk-user-activity-log' ),
		'manage_options',
		'hnrk-sort-by-page',
		'hnrk_display_visits_by_page'
	);
}

/**
 * Display the logs page for the HNRK User Activity Log plugin.
 */
function hnrk_display_logs_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'hnrk-user-activity-log' ) );
	}

	$selected_user_id = 0;
	if ( isset( $_GET['user_id'] ) ) {
		check_admin_referer( 'hnrk_filter_logs' );
		$selected_user_id = intval( $_GET['user_id'] );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'User Activity Log', 'hnrk-user-activity-log' ); ?></h1>
		<p><b><?php esc_html_e( 'If you need more information about the user, click on the username', 'hnrk-user-activity-log' ); ?></b></p>

		<form method="get" action="">
			<input type="hidden" name="page" value="hnrk-user-activity-log">
			<?php wp_nonce_field( 'hnrk_filter_logs' ); ?>
			<div>
				<label for="user_id"><?php esc_html_e( 'Filter by user:', 'hnrk-user-activity-log' ); ?></label>
				<select name="user_id" id="user_id" onchange="this.form.submit()">
					<option value=""><?php esc_html_e( 'All Users', 'hnrk-user-activity-log' ); ?></option>
					<?php foreach ( get_users( array( 'role' => 'subscriber' ) ) as $subscriber ) : ?>
						<option value="<?php echo esc_attr( $subscriber->ID ); ?>" <?php selected( $selected_user_id, $subscriber->ID ); ?>>
							<?php echo esc_html( $subscriber->user_login ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</form>

		<div class="hnrk-logs-container">
			<div class="hnrk-log-header">
				<div class="hnrk-log-cell"><?php esc_html_e( 'User', 'hnrk-user-activity-log' ); ?></div>
				<div class="hnrk-log-cell"><?php esc_html_e( 'Login', 'hnrk-user-activity-log' ); ?></div>
				<div class="hnrk-log-cell"><?php esc_html_e( 'Pages visited during logged in session', 'hnrk-user-activity-log' ); ?></div>
			</div>
			<div class="hnrk-log-body">
				<?php
				if ( $selected_user_id ) {
					hnrk_display_user_logs( $selected_user_id );
				} else {
					hnrk_display_all_logs();
				}
				?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Display logs for all users.
 */
function hnrk_display_all_logs() {
	$subscribers = get_users( array( 'role' => 'subscriber' ) );

	$all_logs = array();

	foreach ( $subscribers as $subscriber ) {
		$user_id          = $subscriber->ID;
		$user_profile_url = get_edit_user_link( $user_id );
		$logins           = get_user_meta( $user_id, 'login_times', true );

		if ( $logins ) {
			foreach ( $logins as $login ) {
				$all_logs[] = array(
					'user_login'       => $subscriber->user_login,
					'login_time'       => $login['time'],
					'pages'            => $login['pages'],
					'user_profile_url' => $user_profile_url,
				);
			}
		}
	}

	usort(
		$all_logs,
		function ( $a, $b ) {
			return strtotime( $b['login_time'] ) - strtotime( $a['login_time'] );
		}
	);

	foreach ( $all_logs as $log ) {
		echo '<div class="hnrk-log-row">';
		echo '<div class="hnrk-log-cell"><a href="' . esc_url( $log['user_profile_url'] ) . '" target="_blank">' . esc_html( $log['user_login'] ) . '</a></div>';
		echo '<div class="hnrk-log-cell">' . esc_html( $log['login_time'] ) . '</div>';
		echo '<div class="hnrk-log-cell">';
		if ( isset( $log['pages'] ) ) {
			echo '<ul>';
			foreach ( $log['pages'] as $visit ) {
				echo '<li><a href="' . esc_url( $visit['page'] ) . '" target="_blank">' . esc_html( $visit['page'] ) . '</a></li>';
			}
			echo '</ul>';
		} else {
			echo esc_html__( 'No pages visited yet.', 'hnrk-user-activity-log' );
		}
		echo '</div>';
		echo '</div>';
	}
}

/**
 * Display logs for a specific user.
 *
 * @param int $user_id The user ID to display logs for.
 */
function hnrk_display_user_logs( $user_id = 0 ) {
	if ( ! $user_id ) {
		return;
	}

	$subscriber = get_user_by( 'id', $user_id );
	if ( ! $subscriber ) {
		echo '<p>' . esc_html__( 'User not found.', 'hnrk-user-activity-log' ) . '</p>';
		return;
	}

	$user_profile_url = get_edit_user_link( $user_id );
	$logins           = get_user_meta( $user_id, 'login_times', true );

	if ( ! $logins ) {
		echo '<p>' . esc_html__( 'No log entries for this user.', 'hnrk-user-activity-log' ) . '</p>';
		return;
	}

	usort(
		$logins,
		function ( $a, $b ) {
			return strtotime( $b['time'] ) - strtotime( $a['time'] );
		}
	);

	foreach ( $logins as $login ) {
		echo '<div class="hnrk-log-row">';
		echo '<div class="hnrk-log-cell"><a href="' . esc_url( $user_profile_url ) . '" target="_blank">' . esc_html( $subscriber->user_login ) . '</a></div>';
		echo '<div class="hnrk-log-cell">' . esc_html( $login['time'] ) . '</div>';
		echo '<div class="hnrk-log-cell">';
		if ( isset( $login['pages'] ) ) {
			echo '<ul>';
			foreach ( $login['pages'] as $visit ) {
				echo '<li><a href="' . esc_url( $visit['page'] ) . '" target="_blank">' . esc_html( $visit['page'] ) . '</a></li>';
			}
			echo '</ul>';
		} else {
			echo esc_html__( 'No pages visited yet.', 'hnrk-user-activity-log' );
		}
		echo '</div>';
		echo '</div>';
	}
}
