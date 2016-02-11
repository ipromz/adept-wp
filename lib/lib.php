<?php
Class WP_Lib{
	// Constructor
    function __construct() {
	global $wpdb;
	
    $post_table = $wpdb->prefix . "posts";
	$postmeta_table = $wpdb->prefix . "postmeta";
	$table_name = $wpdb->prefix . "api_credential";
	$table_name1 = $wpdb->prefix . "term_taxonomy";
	$table_name2 = $wpdb->prefix . "terms";
    }
	
	
	
}



?>