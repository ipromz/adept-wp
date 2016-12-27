<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('admin_menu', 'wpadept_add_menu_sync' , 12); //priority must be more than 10

function wpadept_add_menu_sync() {
	wp_deregister_script('heartbeat');
	add_submenu_page('adept_lms', 'Sync', 'Sync', 'manage_options', 'adept_lms_sync', 'wpadept_sync_page_callback' );

}

function wpadept_sync_page_callback(){
	?>

	<div class="wrap adept_sync_wrap">
		
		<div class='adept_btn_wrap'>
			<a href='#' id="adept_sync_btn" class="button button-primary">Sync Now</a>
		</div>
		<div class="adept_logs">
			<div class='adept_logs_inner'>
			</div>
		</div>
	</div>


	<?php 

}
$itime = time();

add_action("wp_ajax_adept_sync" , "wpadept_sync_ajax");

function wpadept_sync_ajax() {
	
	set_time_limit(50); //increase max execution time

	$step = $_GET["step"];
	echo $step." - ";
	$adept = new Wpadept_Lib();
	
	global $wpdb; 

	$post_table = $wpdb->prefix . "posts";
	$postmeta_table = $wpdb->prefix . "postmeta";
	$table_name = $wpdb->prefix . "api_credential";
	$table_name1 = $wpdb->prefix . "term_taxonomy";
	$table_name2 = $wpdb->prefix . "terms";

	$adept_access_token_value = get_option('adept_access_token');
	$adept_api_url_value = get_option('adept_api_url');
	$adept_account_id_value = get_option('adept_account_id');

	switch($step) {
		case "import_categories":
			$url = $adept_api_url_value . 'course_categories?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        	
        	$result = $adept->import_category($url);
        	if ($result) {
	            echo $result;
	        } else {
	            echo 'Categories category imported successfully';
	        }
		break;		

		case "import_course";

			echo "import_course starts at - ".wpadept_clc();
			$url = $adept_api_url_value . 'courses?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        	//echo $url; exit;
        	echo $result = $adept->import_course($url);
        	echo '<br>Course import end at - '.wpadept_clc();
		break;		

		case "unpublish_courses":
	  		$url = $adept_api_url_value . 'unpublished_courses?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
			$result = $adept->unpublished_courses($url);
			echo 'Unpublished courses imported successfully';
		break;		

		case "course_update":
	        $url = $adept_api_url_value . 'recent_course_updates?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	        $result = $adept->update_course($url);
	        $success = $result;
			echo 'courses updated imported successfully';

		break;		

		case "class_meeting":
			echo 'meeting start at '.wpadept_clc()."<br>";

	        $url = $adept_api_url_value . 'meetings?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	        $result = $adept->import_meeting($url);
	        $success = $result;
			echo 'class meeting imported successfully at -'.wpadept_clc();
		break;		

		case "update_meeting":
	        $url = $adept_api_url_value . 'recent_meeting_updates?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	        $result = $adept->update_meeting($url);
	        $success = $result;
			echo 'meeting updated successfully';
		break;		

		case "class_group":
			echo 'group import start at - '.wpadept_clc()."<br>";

	        $url = $adept_api_url_value . 'groups?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	        $result = $adept->import_groups($url);
	        $success = $result;
			echo 'class group imported successfully at - '.wpadept_clc()."<br>";

		break;		

		case "update_group":
			echo 'group start at - '.wpadept_clc()."<br>";

	        $url = $adept_api_url_value . 'recent_group_updates?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	        $result = $adept->update_groups($url);
	        $success = $result;
			echo 'group updated successfully'.wpadept_clc();

		break;		

		case "import_instructors":

			echo 'instructor start at - '.wpadept_clc()."<br>";
	        //$url = $adept_api_url_value . 'instructors?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	        $url = $adept_api_url_value . 'team_members?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
	        //echo $url; exit;
	        $result = $adept->import_instructors($url);
	        $success = $result;
			echo 'instructor end at - '.wpadept_clc()."<br>";

		break;
	}
	wp_die();
}

function wpadept_clc(){
    global $itime;
    return time() - $itime;
}
