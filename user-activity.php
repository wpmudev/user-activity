<?php
/*
Plugin Name: User Activity
Plugin URI: http://premium.wpmudev.org/project/user-activity
Description: Collects user activity data and makes it available via a tab under the Site Admin
Author: Andrew Billits, Ulrich Sossou
Version: 1.0.5
Network: true
Text Domain: user_activity
Author URI: http://premium.wpmudev.org/
WDP ID: 3
*/

/*
Copyright 2007-2011 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Plugin main class
 **/
class User_Activity {

	/**
	 * Current version of the plugin
	 **/
	private $current_version = '1.0.5';

	/**
	 * PHP 4 constructor
	 **/
	function User_Activity() {
		__construct();
	}

	/**
	 * PHP 5 constructor
	 **/
	function __construct() {
		add_action( 'admin_init', array( &$this, 'init' ) );
		if ( is_multisite() ) {
			add_action( 'admin_menu', array( &$this, 'pre_3_1_network_admin_page' ) );
			add_action( 'network_admin_menu', array( &$this, 'network_admin_page' ) );
		} else {
			add_action( 'admin_menu', array( &$this, 'admin_page' ) );
		}
		add_action( 'admin_footer', array( &$this, 'global_db_sync' ) );
		add_action( 'wp_footer', array( &$this, 'global_db_sync' ) );
	}

	/**
	 * PHP 5 constructor
	 **/
	function init() {
		$current_version = get_site_option( 'user_activity_version' );
		if ( ! $current_version )
			update_site_option( 'user_activity_version', $this->current_version );

		if ( $current_version != $this->current_version ) 
			$this->install();
	}

	/**
	 * Create plugin tables
	 **/
	function install() {
		global $wpdb;

		if( @is_file( ABSPATH . '/wp-admin/includes/upgrade.php' ) )
			include_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		else
			die( __( 'We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'', 'user_activity' ) );

		// choose correct table charset and collation
		$charset_collate = '';
		if( $wpdb->supports_collation() ) {
			if( !empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if( !empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}

		$user_activity_table = "CREATE TABLE `{$wpdb->base_prefix}user_activity` (
			`active_ID` bigint(20) unsigned NOT NULL auto_increment,
			`user_ID` bigint(35) NOT NULL default '0',
			`last_active` bigint(35) NOT NULL default '0',
			PRIMARY KEY  (`active_ID`)
		) $charset_collate;";
	
		dbDelta( $user_activity_table );
	}

	/**
	 * Create or update current user activity entry
	 **/
	function global_db_sync() {
		global $wpdb, $current_user;

		if ( '' !== $current_user->ID ) {
			$tmp_user_activity_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->base_prefix}user_activity WHERE user_ID = '%d'", $current_user->ID ) );

			if( '0' == $tmp_user_activity_count )
				$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->base_prefix}user_activity ( user_ID, last_active ) VALUES ( '%d', '%d' )", $current_user->ID, time() ) );
			else
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->base_prefix}user_activity SET last_active = '%d' WHERE user_ID = '%d'", time(), $current_user->ID ) );
		}
	}

	/**
	 * Get activity from db for a set period of type
	 **/
	function get_activity( $tmp_period ) {
		global $wpdb, $current_user;

		$tmp_period = ( $tmp_period == '' || $tmp_period == 0 ) ? 1 : $tmp_period;
		$tmp_period = $tmp_period * 60;
		$user_current_stamp = time();
		$tmp_stamp = $user_current_stamp - $tmp_period;
		$tmp_output = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->base_prefix}user_activity WHERE last_active > '%d'", $tmp_stamp ) );

		echo $tmp_output;
	}

	/**
	 * Add network admin page
	 **/
	function network_admin_page() {
		add_submenu_page( 'settings.php', __( 'User Activity', 'user_activity' ), __( 'User Activity', 'user_activity' ), 'manage_network_options', 'user_activity_main', array( &$this, 'page_main_output' ) );
	}

	/**
	 * Add network admin page the old way
	 **/
	function pre_3_1_network_admin_page() {
		add_submenu_page( 'ms-admin.php', __( 'User Activity', 'user_activity' ), __( 'User Activity', 'user_activity' ), 'manage_network_options', 'user_activity_main', array( &$this, 'page_main_output' ) );
	}

	/**
	 * Add admin page for singlesite
	 **/
	function admin_page() {
		add_submenu_page( 'users.php', __( 'User Activity', 'user_activity' ), __( 'User Activity', 'user_activity' ), 'edit_users', 'user_activity_main', array( &$this, 'page_main_output' ) );
	}

	/**
	 * Admin page output.
	 **/
	function page_main_output() {
		global $wpdb, $wp_roles, $current_user;

		// Allow access for users with correct permissions only
		if ( is_multisite() && ! current_user_can( 'manage_network_options' ) )
			die( __( 'Nice Try...', 'user_activity' ) );
		elseif ( ! is_multisite() && ! current_user_can( 'manage_options' ) )
			die( __( 'Nice Try...', 'user_activity' ) );

		echo '<div class="wrap">';
		$current_stamp = time();

		$current_five_minutes = $current_stamp - 300;
		$current_hour = $current_stamp - 3600;
		$current_day = $current_stamp - 86400;
		$current_week = $current_stamp - 604800;
		$current_month = $current_stamp - 2592000;

		$five_minutes = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->base_prefix}user_activity WHERE last_active > '$current_five_minutes'" );
		$hour = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->base_prefix}user_activity WHERE last_active > '$current_hour'" );
		$day = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->base_prefix}user_activity WHERE last_active > '$current_day'" );
		$week = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->base_prefix}user_activity WHERE last_active > '$current_week'" );
		$month = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->base_prefix}user_activity WHERE last_active > '$current_month'" );

		?>
			<h2><?php _e( 'User Activity', 'user_activity' ); ?></h2>
			<div class="datagrid">
				<table>
					<thead>
						<tr>
							<th><?php _e( 'Active users in the last', 'user_activity' ); ?></th>
							<th><?php _e( 'Stats', 'user_activity' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php _e( 'Five Minutes', 'user_activity' ); ?></td>
							<td><?php echo $five_minutes; ?></td>
						</tr>
						<tr class="alt">
							<td><?php _e( 'Hour', 'user_activity' ); ?></td>
							<td><?php echo $hour; ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Day', 'user_activity' ); ?></td>
							<td><?php echo $day; ?></td>
						</tr>
						<tr class="alt">
							<td><?php _e( 'Week', 'user_activity' ); ?></td>
							<td><?php echo $week; ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Month*', 'user_activity' ); ?></td>
							<td><?php echo $month; ?></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2">
								<p><strong>* Month = 30 days</strong></p>
								<p><strong>Note:</strong> It will take a full thirty days for all of this data to be accurate. For example, if the plugin has been installed for only a day then only "day", "hour", and "five minutes" will contain accurate data.</p>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
			<style>
				.datagrid table {
					border-collapse: collapse;
					text-align: left;
					width:100%;
				}

				.datagrid {
					font: normal 12px/150% Arial, Helvetica, sans-serif;
					margin-top:15px;
					background: #fff;
					overflow: hidden;
					border: 1px solid #706D6D;
					border-radius: 3px;
					width:23%;
				}

				.datagrid table td,.datagrid table th {
					padding: 7px 5px;
				}

				.datagrid table thead th {
					background: -webkit-gradient( linear, left top, left bottom, color-stop(0.05, #8C8C8C), color-stop(1, #7D7D7D) );

					background: -webkit-linear-gradient( center top, #8C8C8C 5%, #7D7D7D 100% );
					background: -moz-linear-gradient( center top, #8C8C8C 5%, #7D7D7D 100% );
					background: -o-linear-gradient( center top, #8C8C8C 5%, #7D7D7D 100% );
					background: -ms-linear-gradient( center top, #8C8C8C 5%, #7D7D7D 100% );
					background: linear-gradient( center top, #8C8C8C 5%, #7D7D7D 100% );
					filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#8C8C8C', endColorstr='#7D7D7D');
					background-color: #8C8C8C;
					color: #FFFFFF;
					font-size: 15px;
					font-weight: bold;
					border-left: 1px solid #A3A3A3;
				}

				.datagrid table thead th:first-child {
					border: none;
				}

				.datagrid table tbody td {
					color: #7D7D7D;
					border-left: 1px solid #DBDBDB;
					font-size: 12px;
					font-weight: normal;
				}

				.datagrid table tbody .alt td {
					background: #EBEBEB;
					color: #7D7D7D;
				}

				.datagrid table tbody td:first-child {
					border-left: none;
				}

				.datagrid table tbody tr:last-child td {
					border-bottom: none;
				}
				.datagrid table tfoot {
					border-top: 1px solid #706D6D;
					background: #EBEBEB;
				}


			</style>
		<?php
	}

}

$user_activity =& new User_Activity();

/**
 * Display number of active users for a specific period of time
 **/
function display_user_activity( $tmp_period ) {
	global $user_activity;

	echo $user_activity->get_activity( $tmp_period );
}

/**
 * Display last active users
 **/
function user_activity_output( $minutes = 5, $limit = 10, $global_before = '', $before = '', $global_after = '', $after = '', $avatars = 'yes', $avatar_size = 32 ) {
	global $wpdb;

	$user_activity_current_stamp = time();
	$user_activity_seconds = $minutes * 60;
	$user_activity_stamp = $user_activity_current_stamp - $user_activity_seconds;
	$query = $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}user_activity WHERE last_active > '%d' LIMIT %d", $user_activity_stamp, (int) $limit );
	$active_users = $wpdb->get_results( $query, ARRAY_A );

	if ( count( $active_users ) > 0 ) {
		echo $global_before;

		foreach ( $active_users as $active_user ) {
			echo $before;

			$user = get_user_by( 'id', $active_user['user_ID'] );
			$display_name = empty( $user->display_name ) ? $user->display_name : $user->user_login;

			$primary_blog = get_active_blog_for_user( $active_user['user_ID'] );

			if( 'yes' == $avatars ) {
				echo get_avatar( $active_user['user_ID'], $avatar_size, get_option( 'avatar_default' ) ) . ' <a href="http://' . $primary_blog->domain . $primary_blog->path . '" style="text-decoration:none;border:none;">' . $display_name . '</a>';

			} else {
				echo '<a href="' . get_site_url( $primary_blog->blog_id, '/' ) . '">' . $display_name . '</a>';
			}

			echo $after;
		}

		echo $global_after;
	}
}

/**
 * Show notification if WPMUDEV Update Notifications plugin is not installed
 **/
if ( !function_exists( 'wdp_un_check' ) ) {
	add_action( 'admin_notices', 'wdp_un_check', 5 );
	add_action( 'network_admin_notices', 'wdp_un_check', 5 );

	function wdp_un_check() {
		if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'edit_users' ) )
			echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
	}
}
