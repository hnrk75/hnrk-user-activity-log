<?php
/**
 * Visits by Page for the HNRK User Activity Log plugin.
 *
 * @package HNRK_User_Activity_Log
 */

/**
 * Display the submenu page content for visits by page.
 */
function hnrk_display_visits_by_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'hnrk-user-activity-log' ) );
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'User Activity Log - Visits by Page', 'hnrk-user-activity-log' ); ?></h1>
		<p><b><?php esc_html_e( 'If you need more information about the user, click on the username', 'hnrk-user-activity-log' ); ?></b></p>

		<div class="hnrk-logs-container">
			<div class="hnrk-log-header">
				<div class="hnrk-log-cell"><?php esc_html_e( 'Page', 'hnrk-user-activity-log' ); ?></div>
				<div class="hnrk-log-cell"><?php esc_html_e( 'Users', 'hnrk-user-activity-log' ); ?></div>
			</div>
			<div class="hnrk-log-body">
				<?php hnrk_display_logs_grouped_by_page(); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Display logs grouped by pages.
 */
function hnrk_display_logs_grouped_by_page() {
	$subscribers = get_users( array( 'role' => 'subscriber' ) );
	$page_visits = array();

	foreach ( $subscribers as $subscriber ) {
		$user_id   = $subscriber->ID;
		$user_name = $subscriber->user_login;
		$logins    = get_user_meta( $user_id, 'login_times', true );

		if ( $logins ) {
			foreach ( $logins as $login ) {
				if ( isset( $login['pages'] ) ) {
					foreach ( $login['pages'] as $visit ) {
						$page       = $visit['page'];
						$visit_time = $visit['time'];

						if ( ! isset( $page_visits[ $page ] ) ) {
							$page_visits[ $page ] = array();
						}

						$page_visits[ $page ][] = array(
							'user'             => $user_name,
							'user_profile_url' => get_edit_user_link( $user_id ),
							'time'             => $visit_time,
						);
					}
				}
			}
		}
	}

	foreach ( $page_visits as $page => &$visits ) {
		usort(
			$visits,
			function ( $a, $b ) {
				return strtotime( $b['time'] ) - strtotime( $a['time'] );
			}
		);
	}

	uksort(
		$page_visits,
		function ( $a, $b ) use ( $page_visits ) {
			$latest_visit_a = max( array_column( $page_visits[ $a ], 'time' ) );
			$latest_visit_b = max( array_column( $page_visits[ $b ], 'time' ) );
			return strtotime( $latest_visit_b ) - strtotime( $latest_visit_a );
		}
	);

	foreach ( $page_visits as $page_url => $visits ) {
		echo '<div class="hnrk-log-row">';
		echo '<div class="hnrk-log-cell"><a href="' . esc_url( $page_url ) . '" target="_blank">' . esc_html( $page_url ) . '</a></div>';
		echo '<div class="hnrk-log-cell">';
		echo '<ul>';
		foreach ( $visits as $visit ) {
			echo '<li><a href="' . esc_url( $visit['user_profile_url'] ) . '" target="_blank">' . esc_html( $visit['user'] ) . '</a> (' . esc_html( $visit['time'] ) . ')</li>';
		}
		echo '</ul>';
		echo '</div>';
		echo '</div>';
	}
}
