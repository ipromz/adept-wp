<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS api_crendential" );
	exit();
}
    


?>