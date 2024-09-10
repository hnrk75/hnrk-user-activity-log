<?php
/**
 * Admin options page for the HNRK User Activity Log plugin.
 *
 * @package HNRK_User_Activity_Log
 */

// Create an admin menu item for the HNRK User Activity Log plugin.
function hnrk_create_admin_menu() {
	add_menu_page(
		'User Activity Log',
		'Activity Log',
		'manage_options',
		'hnrk-user-activity-log',
		'hnrk_display_logs_page',
		'dashicons-visibility',
		2
	);
}

// Display the logs page for the HNRK User Activity Log plugin.
function hnrk_display_logs_page() {
	if (!current_user_can('manage_options')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}

	$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
	?>
	<div class="wrap">
		<h1>User Activity Log</h1>

		<form method="get" action="">
			<input type="hidden" name="page" value="hnrk-user-activity-log">
			<div>
				<label for="user_id">Filter by user:</label>
				<?php
				$subscribers = get_users(array('role' => 'subscriber'));

				function format_user_label($user) {
					return $user->user_login;
				}
				?>
				<select name="user_id" id="user_id" onchange="this.form.submit()">
					<option value="">All Users</option>
					<?php foreach ($subscribers as $subscriber): ?>
						<option value="<?php echo esc_attr($subscriber->ID); ?>" <?php selected($selected_user_id, $subscriber->ID); ?>>
							<?php echo esc_html(format_user_label($subscriber)); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</form>


		<div class="hnrk-logs-container">
			<div class="hnrk-log-header">
				<div class="hnrk-log-cell">User</div>
				<div class="hnrk-log-cell">Full name</div>
				<div class="hnrk-log-cell">Email address</div>
				<div class="hnrk-log-cell">Registration date</div>
				<div class="hnrk-log-cell">Login date & time</div>
				<div class="hnrk-log-cell">Pages visited during logged in session</div>
			</div>
			<div class="hnrk-log-body">
				<?php
				if ($selected_user_id) {
					hnrk_display_user_logs($selected_user_id);
				} else {
					hnrk_display_all_logs();
				}
				?>
			</div>
		</div>
	</div>
	<?php
}

// Display logs for all users.
function hnrk_display_all_logs() {
	$subscribers = get_users(array('role' => 'subscriber'));

	foreach ($subscribers as $subscriber) {
		$user_id = $subscriber->ID;
		$first_name = get_user_meta($user_id, 'first_name', true);
		$last_name = get_user_meta($user_id, 'last_name', true);
		$full_name = trim($first_name . ' ' . $last_name);
		$logins = get_user_meta($user_id, 'login_times', true);
		$email = $subscriber->user_email;
		$registration_date = $subscriber->user_registered;

		if ($logins) {
			foreach ($logins as $login) {
				echo '<div class="hnrk-log-row">';
				echo '<div class="hnrk-log-cell">' . esc_html($subscriber->user_login) . '</div>';
				echo '<div class="hnrk-log-cell">' . esc_html($full_name) . '</div>';
				echo '<div class="hnrk-log-cell">' . esc_html($email) . '</div>';
				echo '<div class="hnrk-log-cell">' . esc_html($registration_date) . '</div>';
				echo '<div class="hnrk-log-cell">' . esc_html($login['time']) . '</div>';
				echo '<div class="hnrk-log-cell">';
				if (isset($login['pages'])) {
					echo '<ul>';
					foreach ($login['pages'] as $visit) {
						echo '<li>' . esc_html($visit['time']) . ' - <a href="' . esc_url($visit['page']) . '" target="_blank">' . esc_html($visit['page']) . '</a></li>';
					}
					echo '</ul>';
				} else {
					echo 'No pages visited yet.';
				}
				echo '</div>';
				echo '</div>';
			}
		}
	}
}

// Display logs for a specific user.
function hnrk_display_user_logs($user_id = 0) {
	if ($user_id) {
		$subscriber = get_user_by('id', $user_id);
		if ($subscriber) {
			$first_name = get_user_meta($user_id, 'first_name', true);
			$last_name = get_user_meta($user_id, 'last_name', true);
			$full_name = trim($first_name . ' ' . $last_name);
			$logins = get_user_meta($user_id, 'login_times', true);
			$email = $subscriber->user_email;
			$registration_date = $subscriber->user_registered;

			if ($logins) {
				foreach ($logins as $login) {
					echo '<div class="hnrk-log-row">';
					echo '<div class="hnrk-log-cell">' . esc_html($subscriber->user_login) . '</div>';
					echo '<div class="hnrk-log-cell">' . esc_html($full_name) . '</div>';
					echo '<div class="hnrk-log-cell">' . esc_html($email) . '</div>';
					echo '<div class="hnrk-log-cell">' . esc_html($registration_date) . '</div>';
					echo '<div class="hnrk-log-cell">' . esc_html($login['time']) . '</div>';
					echo '<div class="hnrk-log-cell">';
					if (isset($login['pages'])) {
						echo '<ul>';
						foreach ($login['pages'] as $visit) {
							echo '<li>' . esc_html($visit['time']) . ' - <a href="' . esc_url($visit['page']) . '" target="_blank">' . esc_html($visit['page']) . '</a></li>';
						}
						echo '</ul>';
					} else {
						echo 'No pages visited yet.';
					}
					echo '</div>';
					echo '</div>';
				}
			} else {
				echo '<p>No log entries for this user.</p>';
			}
		} else {
			echo '<p>User not found.</p>';
		}
	}
}
