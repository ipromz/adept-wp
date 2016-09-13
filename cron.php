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

//importing courses categories
$url = $adept_api_url_value . 'course_categories?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
   	
$result = $adept->import_category($url);
if ($result) {
    echo $result;
} else {
    echo 'Categories category imported successfully <br><br>';
}

//importing courses
$url = $adept_api_url_value . 'courses?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
$result = $adept->import_course($url);
echo 'Course imported successfully <br><br>';


//importing meetings
$url = $adept_api_url_value . 'meetings?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
$result = $adept->import_meeting($url);
$success = $result;
echo 'class meeting imported successfully <br><br>';


//importing groups
$url = $adept_api_url_value . 'groups?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
$result = $adept->import_groups($url);
$success = $result;
echo 'class group imported successfully <br><br>';

//importing instructors
$url = $adept_api_url_value . 'instructors?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
$result = $adept->import_instructors($url);
$success = $result;
echo 'instructor updated successfully <br><br>';