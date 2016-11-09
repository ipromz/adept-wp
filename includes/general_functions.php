<?php 

if(!function_exists("pre")) {
	function pre($arr) {
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
}

if(!function_exists("get_val")) {

	function get_val($key) {
		return (isset($_GET[$key])) ? $_GET[$key] : "";
	}

}

if(!function_exists("post_val")) {
	function post($key) {
		return (isset($_POST[$key])) ? $_POST[$key] : "";
	}
}



function wpa_add_post_language($post_id, $post_type, $lang, $title, $desc = "" , $excerpt="") {
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

function wpa_translate_copy($post_id , $new_post_id) {
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

	$wpdb->insert($wpdb->prefix . "term_relationships" , array(
		"object_id" => $new_post_id,
		"term_taxonomy_id" => $relation->term_taxonomy_id
	));


}


function wpa_duplicate_meta($metas , $post_id , $post_id_new) {
	foreach($metas as $meta) {
		$val = get_post_meta($post_id , $meta , true);
		update_post_meta($post_id_new , $meta , $val);	
	}
}

function wpa_update_post_content($post_id , $content) {
	
	$my_post = array(
      'ID'           => $post_id,
      'post_content' => $content,
  	);
	wp_update_post( $my_post );

}

function wpa_get_cron_url() {
	return plugins_url("cron.php" , WPA_PLUGIN_FILE);
}

function wpa_get_cron_meeting_url() {
	return plugins_url("cron-meetings.php" , WPA_PLUGIN_FILE);
}



function get_wp_id($post_id , $post_type) {
	global $wpdb;
	return $wpdb->get_col("select ID from {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta m where p.ID = m.post_id and post_type='$post_type' and meta_key='_adept_api_id' and meta_value='$post_id ' ");	    
}

function cron_check_is_authenticated($url) {
	
	$lib = new WP_Lib();

	$data = $lib->getdata($url);

	if(isset($data->data) && is_string($data->data) &&  $data->data == "Unauthorised") {
		wp_die("Access token incorrect or expired, please reauthenticate." , "Unauthorised" , array( "response" => 502 ) );
		
	} 

}