<?php 


require_once "../../../wp-load.php";

set_time_limit(300); 

global $wpdb; 

$post_table = $wpdb->prefix . "posts";
$postmeta_table = $wpdb->prefix . "postmeta";
$table_name = $wpdb->prefix . "api_credential";
$table_name1 = $wpdb->prefix . "term_taxonomy";
$table_name2 = $wpdb->prefix . "terms";

$adept_access_token_value = get_option('adept_access_token');
$adept_api_url_value = get_option('adept_api_url');
$adept_account_id_value = get_option('adept_account_id');


//importing meetings
$url = $adept_api_url_value . 'meetings?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;

cron_check_is_authenticated($url);


$result = $adept->import_meeting($url);
$success = $result;
echo 'class meeting imported successfully <br><br>';

