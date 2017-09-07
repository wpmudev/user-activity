<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

delete_site_option( 'user_activity_version' );
