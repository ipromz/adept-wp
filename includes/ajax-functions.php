<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action("wp_ajax_adept_get_cats" , "adept_get_cats_ajax");
function adept_get_cats_ajax() {

	$adept = new Wpadept_Lib();
	$adept_access_token_value = get_option('adept_access_token');
	$adept_api_url_value = get_option('adept_api_url');
	$adept_account_id_value = get_option('adept_account_id');
	$url = $adept_api_url_value . 'course_categories?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	$data = $adept->getdata($url);
	echo json_encode($data);
	exit;
}