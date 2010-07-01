<?php
/*
Plugin Name: User Activity
Plugin URI: 
Description:
Author: Andrew Billits
Version: 1.0.2
Author URI:
WDP ID: 3
*/

/* 
Copyright 2007-2009 Incsub (http://incsub.com)

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

//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
add_action('init', 'user_activity_init');
function user_activity_init() {
	user_activity_global_db_sync();
}
if ($_GET['page'] == 'user_activity_main'){
	user_activity_install();
	user_activity_upgrade();
}
add_action('admin_menu', 'user_activity_plug_pages');
add_action('admin_footer', 'user_activity_global_db_sync');
add_action('wp_footer', 'user_activity_global_db_sync');
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
function user_activity_upgrade() {
	global $wpdb;
	if (get_site_option( "user_activity_version" ) == '') {
		add_site_option( 'user_activity_version', '0.0.0' );
	}
	
	if (get_site_option( "user_activity_version" ) == "1.0.0") {
		// do nothing
	} else {
		//upgrade code goes here
		//update to current version
		update_site_option( "user_activity_version", "1.0.0" );
	}
}

function user_activity_install() {
	global $wpdb;
	if (get_site_option( "user_activity_installed" ) == '') {
		add_site_option( 'user_activity_installed', 'no' );
	}
	
	if (get_site_option( "user_activity_installed" ) == "yes") {
		// do nothing
	} else {
	
		$user_activity_table1 = "CREATE TABLE `" . $wpdb->base_prefix . "user_activity` (
  `active_ID` bigint(20) unsigned NOT NULL auto_increment,
  `user_ID` bigint(35) NOT NULL default '0',
  `last_active` bigint(35) NOT NULL default '0',
  PRIMARY KEY  (`active_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;";
		$wpdb->query( $user_activity_table1 );
		update_site_option( "user_activity_installed", "yes" );
	}
}

function user_activity_global_db_sync() {
	global $wpdb, $wp_roles, $current_user;
	if ($current_user->ID == ''){
		//houston... we have a problem. ABORT!!! ABORT!!! Ok, so it's not that dramatic.
	} else {
		$tmp_user_activity_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE user_ID = '" . $current_user->ID . "'");
		if ($tmp_user_activity_count == '0') {
				$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "user_activity (user_ID, last_active) VALUES ( '" . $current_user->ID . "', '" . time() . "' )" );
		} else {
				$wpdb->query( "UPDATE " . $wpdb->base_prefix . "user_activity SET last_active = '" . time() . "' WHERE user_ID = '" . $current_user->ID . "'" );
		}
		/*
		$tmp_user_activity_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE user_ID = '1'");
		if ($tmp_nationality_count == '0') {
				$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "user_activity (user_ID, last_active) VALUES ( '1', '" . time() . "' )" );
		} else {
				$wpdb->query( "UPDATE " . $wpdb->base_prefix . "user_activity SET last_active = '" . time() . "' WHERE user_ID = '1'" );
		}
		*/
	}
}

function display_user_activity($tmp_period) {
	global $wpdb, $wp_roles, $current_user;
	if ($tmp_period == '' || $tmp_period == 0){
		$tmp_period = 1;
	}
	$tmp_period = $tmp_period * 60;
	$user_current_stamp = time();
	$tmp_stamp = $user_current_stamp - $tmp_period;
	$tmp_output = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE last_active > '" . $tmp_stamp . "'");
	
	echo $tmp_output;
}

function user_activity_plug_pages() {
	global $wpdb, $wp_roles, $current_user;
	if ( is_site_admin() ) {
		add_submenu_page('ms-admin.php', 'User Activity', 'User Activity', 10, 'user_activity_main', 'user_activity_page_main_output');
	}
}

//------------------------------------------------------------------------//
//---Output Functions-----------------------------------------------------//
//------------------------------------------------------------------------//

function user_activity_output($tmp_minutes = '5',$tmp_limit = '10',$tmp_global_before = '',$tmp_before = '',$tmp_global_after = '',$tmp_after = '',$tmp_avatars = 'yes',$tmp_avatar_size = '32') {
	global $wpdb;
	
	$user_activity_current_stamp = time();
	$user_activity_seconds = $tmp_minutes * 60;
	$user_activity_stamp = $user_activity_current_stamp - $user_activity_seconds;
	$query = "SELECT * FROM " . $wpdb->base_prefix . "user_activity WHERE last_active > '" . $user_activity_stamp . "' LIMIT " . $tmp_limit;
	$tmp_active_users = $wpdb->get_results( $query, ARRAY_A );
	if ( count( $tmp_active_users ) > 0) {
		echo $tmp_global_before;
		foreach ($tmp_active_users as $tmp_active_user){
			echo $tmp_before;
			//=========================================================//
			$tmp_username = $wpdb->get_var("SELECT user_login FROM " . $wpdb->users . " WHERE ID = '" . $tmp_active_user['user_ID'] . "'");
			$tmp_display_name = $wpdb->get_var("SELECT display_name FROM " . $wpdb->users . " WHERE ID = '" . $tmp_active_user['user_ID'] . "'");
			if ($tmp_display_name == ''){
				$tmp_display_name = $tmp_username;
			}
			$tmp_primary_blog_ID = get_usermeta( $tmp_active_user['user_ID'], "primary_blog" );
			$tmp_blog_domain = $wpdb->get_var("SELECT domain FROM $wpdb->blogs WHERE blog_id = '" . $tmp_primary_blog_ID . "'");
			$tmp_blog_path = $wpdb->get_var("SELECT path FROM $wpdb->blogs WHERE blog_id = '" . $tmp_primary_blog_ID . "'");
			//=========================================================//
			if ($tmp_avatars == 'yes') {
				if ($tmp_primary_blog_ID != '') {
					echo get_avatar($tmp_active_user['user_ID'],$tmp_avatar_size,get_option('avatar_default')) . ' <a href="http://' . $tmp_blog_domain . $tmp_blog_path . '" style="text-decoration:none;border:none;">' . $tmp_display_name . '</a>';
				} else {
					echo get_avatar($tmp_active_user['user_ID'],$tmp_avatar_size,get_option('avatar_default')) . ' ' . $tmp_display_name;
				}
			} else {
				if ($tmp_primary_blog_ID != '') {
					echo '<a href="http://' . $tmp_blog_domain . $tmp_blog_path . '">' . $tmp_display_name . '</a>';
				} else {
					echo $tmp_display_name;
				}
			}
			//=========================================================//
			echo $tmp_after;
		}
		echo $tmp_global_after;
	}
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//

function user_activity_page_main_output() {
	global $wpdb, $wp_roles, $current_user;
	
	if(!current_user_can('manage_options')) {
		echo "<p>Nice Try...</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			$user_activity_current_stamp = time();
			
			$user_activity_current_five_minutes = $user_activity_current_stamp - 300;
			$user_activity_current_hour = $user_activity_current_stamp - 3600;
			$user_activity_current_day = $user_activity_current_stamp - 86400;
			$user_activity_current_week = $user_activity_current_stamp - 604800;
			$user_activity_current_month = $user_activity_current_stamp - 2592000;
			
			$user_activity_five_minutes = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE last_active > '" . $user_activity_current_five_minutes . "'");
			$user_activity_hour = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE last_active > '" . $user_activity_current_hour . "'");
			$user_activity_day = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE last_active > '" . $user_activity_current_day . "'");
			$user_activity_week = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE last_active > '" . $user_activity_current_week . "'");
			$user_activity_month = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "user_activity WHERE last_active > '" . $user_activity_current_month . "'");
			?>
			<h2><?php _e('User Activity') ?></h2>
			<h3>Active users in the last:</h3>
			<p>Five Minutes: <?php echo $user_activity_five_minutes; ?><br />
			Hour: <?php echo $user_activity_hour; ?><br />
			Day: <?php echo $user_activity_day; ?><br />
			Week: <?php echo $user_activity_week; ?><br />
			Month*: <?php echo $user_activity_month; ?><br />
			</p>
			<p>*Month = 30 days<br />
            Note: It will take a full thirty days for all of this data to be accurate. For example, if the plugin has been installed for only a day then only "day", "hour", and "five minutes" will contain accurate data.
            </p>
			<?php
		break;
		//---------------------------------------------------//
		case "remove":
		break;
		//---------------------------------------------------//
		case "temp":
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

?>
