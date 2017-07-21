<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!function_exists("wpadept_pre")) {
	function wpadept_pre($arr) {
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
}

if(!function_exists("wpadept_get_val")) {

	function wpadept_get_val($key) {
		return (isset($_GET[$key])) ? $_GET[$key] : "";
	}

}

if(!function_exists("wpadept_post_val")) {
	function wpadept_post_val($key) {
		return (isset($_POST[$key])) ? $_POST[$key] : "";
	}
}



function wpadept_add_post_language($post_id, $post_type, $lang, $title, $desc = "" , $excerpt="") {
	global $sitepress;
	$trigid = wpml_get_content_trid('post_' . $post_type, $post_id); // Find Transalation ID function from WPML API. 
	$_POST['icl_post_language'] = $lang; // Set another language
	
	$postdata = array( 'post_title' => $title, 
						'post_type' => $post_type, 
						'post_status'=> 'publish' , 
						'post_content'=>$desc,
						'post_excerpt'=>$excerpt
						);

	//pre($postdata);

	$tpropertyid1 = wp_insert_post( $postdata ); 
	$sitepress->set_element_language_details($tpropertyid1, 'post_' . $post_type, $trigid, $lang); // Change this post translation ID to Hebrew's post id
 	return $tpropertyid1;
}

function wpadept_translate_copy($post_id , $new_post_id) {
	global $wpdb;

	$content_post = get_post($post_id)->post_content;
	$meta = get_post_meta($post_id );	

	$meta_keys = array(
				"_group_ids" ,
				"_post_id",
				"_tags",
				"_is_featured",
				"_course_fee",
				"_sku",
				"_tax_category",
				"_allow_discounts",
				"_subscription",
				"_booking_count",
				"_image_url",
				"_small_image_url",
				"_small_image_url",
				"_instructor_ids",
				"_course_url",
				"_adept_api_id",
				"_group_locations",
				"_group_level",
		);

	foreach($meta_keys as $key) {
		$val = get_post_meta($post_id, $key, true);
		update_post_meta($new_post_id , $key , $val );

	} 

	$relation = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}term_relationships where object_id = $post_id ");
	if($relation) {
		
		$wpdb->insert($wpdb->prefix . "term_relationships" , array(
			"object_id" => $new_post_id,
			"term_taxonomy_id" => $relation->term_taxonomy_id
		));
	}


}


function wpadept_duplicate_meta($metas , $post_id , $post_id_new) {
	foreach($metas as $meta) {
		$val = get_post_meta($post_id , $meta , true);
		update_post_meta($post_id_new , $meta , $val);	
	}
}

function wpadept_update_post_content($post_id , $content) {
	
	$my_post = array(
      'ID'           => $post_id,
      'post_content' => $content,
  	);
	wp_update_post( $my_post );

}

function wpadept_get_cron_url() {
	return site_url()."/?wpadept_cron";
}

function wpadept_get_cron_meeting_url() {
	return site_url()."/?wpadept_cron_meetings";
}



function wpadept_get_wp_id($post_id , $post_type) {
	global $wpdb;
	return $wpdb->get_col("select ID from {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta m where p.ID = m.post_id and post_type='$post_type' and meta_key='_adept_api_id' and meta_value='$post_id ' ");	    
}

function wpadept_cron_check_is_authenticated($url) {
	
	$lib = new Wpadept_Lib();

	$data = $lib->getdata($url);

	if(isset($data->data) && is_string($data->data) &&  $data->data == "Unauthorised") {
		wp_die("Access token incorrect or expired, please reauthenticate." , "Unauthorised" , array( "response" => 502 ) );
		
	} 

}


function wpadept_insert_post($postarr) {
	
	global $wpdb;
	$user_id = get_current_user_id();
	$defaults = array(
		'post_author' => $user_id,
		'post_content' => '',
		'post_content_filtered' => '',
		'post_title' => '',
		'post_excerpt' => '',
		'post_status' => 'publish',
		'post_type' => 'post',
		'comment_status' => '',
		'ping_status' => '',
		'post_password' => '',
		'to_ping' =>  '',
		'pinged' => '',
		'post_parent' => 0,
		'menu_order' => 0,
		'guid' => '',
		//'import_id' => 0,
		//'context' => '',
	);
	$ints = array('menu_order','post_parent' , 'comment_count' );
	if ( ! empty( $postarr['ID'] ) ) {
		//echo " -  yeah will update <br>";
		//$postarr["post_content"] = "post'content";
		$postarr["post_title"] =  esc_sql( $postarr["post_title"] );
		$postarr["post_content"] =  esc_sql( $postarr["post_content"] );
		$postarr = sanitize_post($postarr, 'db');
		unset($postarr["filter"]);
		
		$post_ID = $postarr['ID'];
		$query = " update {$wpdb->prefix}posts set ";
		foreach($postarr as $key=>$val) {
			
			if($key == "ID") continue;
			if(in_array($key, $ints)) {
				$query .= " `$key` = $val,";
			}
			else {
				$query .= " `$key` = '$val',";
				
			}
		}
		$query = rtrim($query , ",");
		$query .= " where ID = $post_ID";
		$wpdb->query($query);
		//echo $query; exit;
		return $post_ID;
	} 
	else {
		
		$postarr = wp_parse_args($postarr, $defaults);
		
		$postarr["post_title"] =  esc_sql( $postarr["post_title"] );
		$postarr["post_content"] =  esc_sql( $postarr["post_content"] );

		$postarr = sanitize_post($postarr, 'db');
		unset($postarr["filter"]);
		$query = " insert into {$wpdb->prefix}posts set ";
		foreach($postarr as $key=>$val) {
			
			if($key == "ID") continue;


			if(in_array($key, $ints)) {
				$query .= " `$key` = $val,";
				
			}
			else {
				$query .= " `$key` = '$val',";
				
			}
		}
		//echo $query; exit;
		$query = rtrim($query , ",");
		$wpdb->query($query);
		return $wpdb->insert_id;
	}
}

function wpadept_clc(){
    global $itime;
    return time() - $itime;
}
