<?php

/*
  Plugin Name: Adept LMS Plugin
  Plugin URI: http://adeptlms.com/
  Description: Plugin for Adept LMS
  Author: promz
  Version: 1.0.1
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*error_reporting(E_ALL); 
ini_set('display_errors', 1);*/

define('WPADEPT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define("WPADEPT_PLUGIN_FILE" , __FILE__);

include_once WPADEPT_PLUGIN_PATH . "/lib/lib.php";
include_once WPADEPT_PLUGIN_PATH . "admin/admin_sync_page.php";
include_once WPADEPT_PLUGIN_PATH . "includes/general_functions.php";
include_once WPADEPT_PLUGIN_PATH . "includes/ajax-functions.php";
include_once WPADEPT_PLUGIN_PATH . "lib/splitHelper.php";
include_once WPADEPT_PLUGIN_PATH . "cron.php";

class WPadept_LMS {

    // Constructor
    function __construct() {

        add_action('admin_menu', array($this, 'wpa_add_menu'));
        register_activation_hook(__FILE__, array($this, 'wpa_install'));
        register_deactivation_hook(__FILE__, array($this, 'wpa_uninstall'));
        register_activation_hook(__FILE__, array($this, 'wpa_role_instructor'));
    }

    /*
     * Add role intructors
     */

    function wpa_role_instructor() {
        add_role('instructor', 'Instructor', array('read' => true, 'level_0' => true));
    }

    /*
     * Actions perform at loading of admin menu
     */

    function wpa_add_menu() {

        /*add_wmenu_page('Adept LMS', 'Adept LMS', 'manage_options', 'adept_lms', array(
            __CLASS__,
            'wpa_page_file_path'
                ), "", '2.2.9');*/
        $icon = plugins_url( "images/logo-icon-2.png", __FILE__ );
        
        add_menu_page( 'Adept LMS','Adept LMS', 'manage_options', 'adept_lms', array( $this, 'wpa_page_file_path1') , $icon , 2);
        


    }

    /*
     * Actions perform on loading of menu pages
     */

    function wpa_page_file_path() {

        foreach (glob(plugin_dir_path(__FILE__) . "admin/adept_lms.php") as $file) {
            include_once $file;
        }
    }

    /*
     * Actions perform on loading of menu pages
     */

    function wpa_page_file_path1() {

        foreach (glob(plugin_dir_path(__FILE__) . "admin/adept_lms_settings.php") as $file) {
            include_once $file;
        }
    }

    /*
     * Actions perform on activation of plugin
     */

    function wpa_install() {
        
    }

    /*
     * Actions perform on de-activation of plugin
     */

    function wpa_uninstall() {

        $args = array(
            'numberposts' => 500000,
            'post_type' => 'courses'
        );
        $posts = get_posts($args);
        if (is_array($posts)) {
            foreach ($posts as $post) {
                // what you want to do;
                wp_delete_post($post->ID, true);
                //delete_post_meta($post_id, $meta_key, $meta_value);
            }
        }
        $args = array(
            'numberposts' => 500000,
            'post_type' => 'meetings'
        );
        $posts = get_posts($args);
        if (is_array($posts)) {
            foreach ($posts as $post) {
                // what you want to do;
                wp_delete_post($post->ID, true);
            }
        }

        delete_option('adept_api_url');
        delete_option('adept_email');
        delete_option('adept_password');
        delete_option('adept_account_id');
        delete_option('adept_access_token');
        delete_option('adept_author');
        delete_option('adept_cron');


        $terms = get_terms('genre', array('fields' => 'ids', 'hide_empty' => false));
        foreach ($terms as $value) {
            wp_delete_term($value, 'genre');
        }

        remove_role('instructor');
    }

}

new WPadept_LMS();

add_action('init', 'wpadept_create_post_type');

function wpadept_create_post_type() {
    register_post_type('courses', array(
        'labels' => array(
            'name' => __('Courses'),
            'singular_name' => __('Course'),
            'menu_name' => _x('Courses', 'admin menu', 'Course'),
            'name_admin_bar' => _x('Course', 'add new on admin bar', 'Course'),
            'add_new' => _x('Add New Course', 'Course', 'Course'),
            'add_new_item' => __('Add New Course', 'Course'),
            'new_item' => __('New Course', 'Course'),
            'edit_item' => __('Edit Course', 'Course'),
            'view_item' => __('View Course', 'Course'),
            'all_items' => __('All Course', 'Course'),
            'search_items' => __('Search Course', 'Course'),
            'parent_item_colon' => __('Parent Course:', 'Course'),
            'not_found' => __('No Course found.', 'Course'),
            'not_found_in_trash' => __('No Course found in Trash.', 'Course')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'excerpt', 'author'),
        'register_meta_box_cb' => 'wpadept_add_course_metaboxes'
            )
    );
}

add_action('add_meta_boxes', 'wpadept_add_course_metaboxes');

// Add the Course Meta Boxes

function wpadept_add_course_metaboxes() {
    add_meta_box('wpt_course_fields', 'Course Other details', 'wpadept_course_fields', 'courses', 'normal', 'high');
}

function wpadept_course_fields() {
    global $post, $wpdb;
    $adept = new Wpadept_Lib();
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="coursemeta_noncename" id="coursemeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Get the tags data if its already been entered
    $tags = get_post_meta($post->ID, '_tags', true);
    // Echo out the field
    $tags = esc_attr($tags);
    echo '<b>Tags : </b><input type="text" name="_tags" value="' . $tags . '" class="widefat" /><br/><br/>';

    // Get the is_featured data if its already been entered
    $is_featured = get_post_meta($post->ID, '_is_featured', true);
    // Echo out the field

   
    echo '<b>Is Featured :</b> <input type="radio" name="_is_featured" ' . checked($is_featured , '1' , false) . ' value="true" class="widefat" /> True ';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_is_featured" ' .  checked($is_featured , '0', false) . ' value="false" class="widefat" /> False <br/><br/>';

    // Get the course_fee data if its already been entered
    $course_fee = get_post_meta($post->ID, '_course_fee', true);
    $course_fee = esc_attr($course_fee );
    // Echo out the field
    echo '<b>Course Fee :</b> <input type="text" name="_course_fee" value="' . $course_fee . '" class="widefat" /><br/><br/>';


    // Get the sku data if its already been entered
    $sku = get_post_meta($post->ID, '_sku', true);
    $sku = esc_attr($sku);
    // Echo out the field
    echo '<b>SKU :</b> <input type="text" name="_sku" value="' . $sku . '" class="widefat" /><br/><br/>';

	echo '<b>Course Groups :</b><br/><br/>';	
	$all_groups = wpadept_get_all_of_post_type_2( 'groups' );

  	$linked_group_ids = get_post_meta(  $post->ID,'_group_ids', true ) ;
    
    if(empty($linked_group_ids)) {
        $linked_group_ids = array();
    }


    //pre($linked_group_ids); exit;
    if ( 0 == count($all_groups) ) {
        $choice_block = '<p>No Group found in the system.</p>';
    } else {
        $html = array();
        $html[] = "<select name='group_ids[]'  multiple='multiple' class='group_select'>";
        foreach ( $all_groups as $group ) {
            $selected = ( in_array( $group->ID, $linked_group_ids ) ) ? ' selected="selected"' : '';

            $display_name = esc_attr( $group->post_title );
            $html[] = <<<HTML
                <option  value="{$group->ID}" {$selected}> {$display_name}</option>
HTML;

        }
        $html[] = '</select>';
        $choice_block = implode("\r\n", $html);
    }

    echo $choice_block."</br></br>";
	// Instructor select box	
	echo '<b>Course Instructors  :</b><br/><br/>';	
	$all_instructors = $wpdb->get_results(" select * from {$wpdb->prefix}posts where post_type='dt_team' and post_status in ('publish', 'draft') ");
  	$linked_instructor_ids = get_post_meta(  $post->ID,'_instructor_ids', true ) ;
    if(empty($linked_instructor_ids)) {
        $linked_instructor_ids = array();
    }
    if ( 0 == count($all_instructors) ) {
        $choice_block = '<p>No Instructor found in the system.</p>';
    } 
    else {
        $choices = array();
        $choices[] = "<select name='instructor_ids[]' multiple='multiple' class='instructors_select'>";			
        foreach ( $all_instructors as $instructor ) {
            $selected = ( in_array( $instructor->ID, $linked_instructor_ids ) ) ? ' selected="selected"' : '';

            $display_name = esc_attr( $instructor->post_title );
            $choices[] = <<<HTML
<option value="{$instructor->ID}" {$selected} > {$display_name}</option>
HTML;

        }
        $choices[] = "</select>";
        $choice_block = implode("\r\n", $choices);
    }

        echo $choice_block."</br></br>";
    
	// Get the subscription data if its already been entered
    $subscription = get_post_meta($post->ID, '_subscription', true);
    // Echo out the field
    $checked = "";
    $unchecked = "";
    if ($subscription == '1') {
        $checked = "checked='checked'";
    } else {
        $unchecked = "checked='checked'";
    }
    echo '<b>Subscription :</b> <input type="radio" name="_subscription" ' . $checked . ' value="true" class="widefat" /> True';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_subscription" ' . $unchecked . ' value="false" class="widefat" /> False  <br/><br/>';

	// Get Image url 
    $image_url = get_post_meta($post->ID, '_image_url', true);
    $image_url = esc_attr($image_url);
    // Echo out the field
    echo '<b>Image Url :</b> <input type="text" name="_image_url" value="' . $image_url . '" class="widefat" /><br/><br/>';


    $course_url = get_post_meta($post->ID, '_course_url', true);
    $course_url = esc_attr($course_url);
    // Echo out the field
    echo '<b>Course Url :</b> <input type="text" name="_course_url" value="' . $course_url . '" class="widefat"  readonly /><br/><br/>';
	
    $location = get_post_meta($post->ID, '_group_locations', true);
    $location = $adept->unstringify($location);
    $location = esc_attr($location);
    echo '<b>Location:</b> <input type="text" name="_group_locations" value="' . $location . '" class="widefat"   /><br/><br/>';
    
    $level = get_post_meta($post->ID, '_group_level', true);
    $level = esc_attr($level);
    echo '<b>Level:</b> <input type="text" name="_group_level" value="' . $level . '" class="widefat"   /><br/><br/>';

}

function wpadept_get_all_of_post_type( $type_name = '') {
    $items = array();
    if ( !empty( $type_name ) ) {
        $args = array(
            'post_type' => "{$type_name}",
            'posts_per_page' => -1,
            'order' => 'ASC',
            'orderby' => 'title'
        );
        $results = new WP_Query( $args );
        if ( $results->have_posts() ) {
            while ( $results->have_posts() ) {
                $items[] = $results->next_post();
            }
        }
    }
    return $items;
}

function wpadept_get_all_of_post_type_2($post_type) {
    global $wpdb;
    
    $element_type = "post_".$post_type;
    //because we want dont want one post to appear many times,
    $qry = $wpdb->prepare(" select * from {$wpdb->prefix}posts where ID in (SELECT element_id FROM `{$wpdb->prefix}icl_translations` where element_type = '%s' group by trid )" , $element_type );
    $posts = $wpdb->get_results($qry);

    return $posts; 

}

function wpadept_save_course_meta($post_id, $post) {
    global $wpdb;
    
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if(!isset($_POST['coursemeta_noncename'])) return;
    if (!wp_verify_nonce($_POST['coursemeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;



    $adept_access_token_value = get_option('adept_access_token');
    $postid = $post_id;
    $course_title = sanitize_text_field($_POST['post_title']);
    $teaser = wp_kses_post($_POST['post_excerpt']);
    $description = wp_kses_post($_POST['content']);
    $tags = sanitize_text_field($_POST['_tags']);
    $course_fee = sanitize_text_field($_POST['_course_fee']);
    $sku = sanitize_text_field($_POST['_sku']);
    $subscription = sanitize_text_field($_POST['_subscription']);
    $course_category_id = sanitize_text_field($_POST['tax_input']['tax_input'][0]);
    //include_once WPADEPT_PLUGIN_PATH . "lib/lib.php";
    $adept = new Wpadept_Lib();
    $adept_api_url_value = get_option('adept_api_url');

    $qry = $wpdb->prepare("select meta_value from " . $wpdb->prefix . "postmeta" . " where post_id=%d AND meta_key='_post_id'" , $post->ID);
    $get_existing_post_id = $wpdb->get_results($qry);
    //var_dump($get_existing_post_id);
    $oripostidStr = $get_existing_post_id[0]->meta_value;
    $oripostidArray = explode('_',$oripostidStr);
    $originalPostId = $oripostidArray[1];
    $curl = $adept_api_url_value . 'update_course/' .$originalPostId;
    $data = "id=" . $email . "&access_token=" . $adept_access_token_value . "&course[course_title]=" . $course_title
            . "&course[teaser]=" . $teaser . "&course[description]=" . $description
            . "&course[tags]=" . $tags . "&course[course_fee]=" . $course_fee ."&course[sku]=" . $sku 
            . "&course[subscription]=" . $subscription
            . "&course[course_category_id]=" . $course_category_id;
    //$data = "access_token=fa547f76ea1ebedbceb6b1ab674040bf&course[course_title]=test123456&course[teaser]=test";
    //echo $data; die();
   // $temp = $adept->postdata($curl, $data);
    $ch = curl_init($curl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //uncomment this line
    //$result = curl_exec($ch);
//var_dump($result); die();
    
    //  $resultdata = json_decode($result);
//var_dump($resultdata); die();
    
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.

    $course_meta['_tags']               = sanitize_text_field($_POST['_tags']);
    $course_meta['_is_featured']        = sanitize_text_field($_POST['_is_featured']);
    $course_meta['_course_fee']         = sanitize_text_field($_POST['_course_fee']);
    $course_meta['_sku']                = sanitize_text_field($_POST['_sku']);
    $course_meta['_subscription']       = sanitize_text_field($_POST['_subscription']);
    $course_meta['_image_url']          = sanitize_text_field($_POST['_image_url']);
    $course_meta['_group_locations']    = sanitize_text_field($adept->stringify($_POST['_group_locations']));
//include_once WPADEPT_PLUGIN_PATH . "lib/splitHelper.php";
    $course_meta['_group_level']        = sanitize_text_field($_POST['_group_level']);
    $groups                             = sanitize_text_field($_POST['group_ids']);
    $instructors                        = sanitize_text_field($_POST['instructor_ids']);
    
    
    delete_post_meta( $post->ID , '_group_ids');
    delete_post_meta( $post->ID , '_instructor_ids');
    
    // Add values of $course_meta as custom fields
    if ( 0 < count($groups) ) {
            foreach ( $groups as $group_id ) {
                # We use add post meta with 4th parameter false to let us link
                # books to as many authors as we want.              
                add_post_meta( $post->ID, '_group_ids',$group_id );             
            }
        }
        
    // Add values of $course_meta as custom fields
    if ( 0 < count($instructors) ) {
            foreach ( $instructors as $instructor_id ) {
                # We use add post meta with 4th parameter false to let us link
                # books to as many authors as we want.              
                add_post_meta( $post->ID, '_instructor_ids',$instructor_id );               
            }
        }


    foreach ($course_meta as $key => $value) { // Cycle through the $course_meta array!
        if ($post->post_type == 'revision')
            return; // Don't store custom data twice
        $value = implode(',', (array) $value); // If $value is an array, make it a CSV (unlikely)
        if (get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
            update_post_meta($post->ID, $key, $value);
        } else { // If the custom field doesn't have a value
            add_post_meta($post->ID, $key, $value);
        }
        if (!$value)
            delete_post_meta($post->ID, $key); // Delete if blank
    }
}

add_action('save_post', 'wpadept_save_course_meta', 1, 2); // save the custom fields

add_action('init', 'wpadept_create_course_category');

function wpadept_create_course_category() {
    $labels = array(
        'name' => _x('Course Category', 'Course Category'),
        'singular_name' => _x('Course Category', 'Course Category'),
        'search_items' => __('Search Course Category'),
        'all_items' => __('All Course Category'),
        'parent_item' => __('Parent Course Category'),
        'parent_item_colon' => __('Parent Course Category:'),
        'edit_item' => __('Edit Course Category'),
        'update_item' => __('Update Course Category'),
        'add_new_item' => __('Add New Course Category'),
        'new_item_name' => __('New Course Category Name'),
        'menu_name' => __('Course Category'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'genre'),
    );
    register_taxonomy('genre', array('courses'), $args);
}

add_action('init', 'wpadept_create_meetings');

function wpadept_create_meetings() {
    register_post_type('meetings', array(
        'labels' => array(
            'name' => __('Meetings'),
            'singular_name' => __('Meeting'),
            'menu_name' => _x('Meetings', 'admin menu', 'Meeting'),
            'name_admin_bar' => _x('Meeting', 'add new on admin bar', 'Meeting'),
            'add_new' => _x('Add New Meeting', 'Meeting', 'Meeting'),
            'add_new_item' => __('Add New Meeting', 'Meeting'),
            'new_item' => __('New Meeting', 'Meeting'),
            'edit_item' => __('Edit Meeting', 'Meeting'),
            'view_item' => __('View Meeting', 'Meeting'),
            'all_items' => __('All Meeting', 'Meeting'),
            'search_items' => __('Search Meeting', 'Meeting'),
            'parent_item_colon' => __('Parent Meeting:', 'Meeting'),
            'not_found' => __('No Meeting found.', 'Meeting'),
            'not_found_in_trash' => __('No Meeting found in Trash.', 'Meeting')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'register_meta_box_cb' => 'wpadept_add_meeting_metaboxes'
            )
    );
}

add_action('add_meta_boxes', 'wpadept_add_meeting_metaboxes');

// Add the Course Meta Boxes

function wpadept_add_meeting_metaboxes() {
    add_meta_box('wpt_meeting_fields', 'Meetings Other details', 'wpadept_meeting_fields', 'meetings', 'normal', 'high');
}

function wpadept_meeting_fields() {
    global $post, $wpdb;

    $adeptlib = new Wpadept_Lib();
    $wpdb->show_errors();
    
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="meetingmeta_noncename" id="meetingmeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    
    // Get the group_id data if its already been entered
    $meeting_id = get_post_meta($post->ID, '_meeting_id', true);
    $meeting_id = esc_attr($meeting_id);
    // Echo out the field
    echo '<input type="hidden" name="_meeting_id" value="' . $meeting_id . '" class="widefat" /><br/><br/>';

    
    // Get the date data if its already been entered
    //$date = get_post_meta($post->ID, '_date', true);
    // Echo out the field
    //echo '<b>Date :</b> <input type="text" name="_date" value="' . $date . '" class="widefat" /><br/><br/>';

    // Get the start_time data if its already been entered
    $start_time = get_post_meta($post->ID, '_start_time', true);
    $start_time = esc_attr($start_time );
    //echo $start_time; exit;
    // Echo out the field
    echo '<b>Start Time : </b><input type="text" name="_start_time" value="' . $start_time . '" class="widefat" /><br/><br/>';

    // Get the start_time data if its already been entered
    $duration = get_post_meta($post->ID, '_duration', true);
    // Echo out the field
    $duration = esc_attr($duration);
    echo '<b>Duration : </b><input type="text" name="_duration" value="' . $duration . '" class="widefat" /><br/><br/>';

    $hour_length = get_post_meta($post->ID, '_hour_length', true);
    $hour_length = esc_attr($hour_length);
    echo '<b>Hour Length : </b><input type="text" name="_hour_length" value="' . $hour_length . '" class="widefat" /><br/><br/>';

    $linked_instructor_id = get_post_meta($post->ID, '_instructor', true);
    $all_instructors = $wpdb->get_results("select p.* , pm.meta_value as instructor_id from {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta pm where pm.post_id=p.ID and pm.meta_key='_adept_api_id' and post_type='dt_team' and post_status in ('publish', 'draft') ");
    
    if(empty($linked_instructor_ids)) {
        $linked_instructor_ids = 0;
    }
    $html_option = ""; 
    
    foreach($all_instructors as $ins ) {
        $selected = "";
        if( $linked_instructor_id == $ins->instructor_id ) {
            $selected = " selected ";
        }
        $ins->ID = esc_attr($ins->ID );
        $ins->post_title = esc_attr($ins->post_title );
        $html_option .= "<option $selected value='{$ins->ID}'> {$ins->post_title}</option>";
    }



    echo '<b>Instructor: </b>
            <select name="_instructor"  class="" >
                '.$html_option.'
            </select>
    <br/><br/>';

    // Get the status data if its already been entered
    $status = get_post_meta($post->ID, '_status', true);
    // Echo out the field
    $status = esc_attr($status );
    echo '<b>Status :</b> <input type="text" name="_status" value="' . $status . '" class="widefat" /><br/><br/>';

    // Get the web_conference data if its already been entered
    /*$web_conference = get_post_meta($post->ID, '_web_conference', true);
    // Echo out the field
    echo '<b>Web Conference :</b> <input type="radio" name="_web_conference" value="true" class="widefat" /> True';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_web_conference" value="false" class="widefat" /> False  <br/><br/>';*/

    // Get the address data if its already been entered
    $address = get_post_meta($post->ID, '_address', true);
    // Echo out the field
    $address = esc_attr($address );
    echo '<b>Address :</b> <textarea  name="_address" class="widefat">' . $address . '</textarea><br/><br/>';
    
    $category = get_post_meta($post->ID, '_category', true);
    $category = esc_attr($category);
    echo '<b>Category :</b> <input type="text" name="_category" class="widefat" value="'.$category.'" /><br/><br/>';


    $meta = get_post_meta($post->ID);
    $meetings_url = get_post_meta($post->ID, '_meeting_url', true);
    $str = esc_attr($meetings_url);
    echo '<b>Meeting\'s Url:</b> <input type="text" name="_meeting_url" value="' . $str . '" class="widefat" /><br/><br/>';

    $group_id_wp = get_post_meta($post->ID, '_group_id', true);
    $str = $adeptlib->unstringify($group_id_wp);
    $str = esc_attr($str);
    echo '<b>Group Id(WordPress):</b> <input type="text" name="_group_id" value="' . $str . '" class="widefat" /><br/><br/>';
    
    $group_id_adept = get_post_meta($post->ID, '_group_id_adept', true);
    $group_id_adept = esc_attr( $group_id_adept );
    echo "<b>Group Id(Adept):</b> $group_id_adept <br/><br/>";

}

function wpadept_save_meeting_meta($post_id, $post) {
    global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if(!isset($_POST['meetingmeta_noncename'])) return;
    if (!wp_verify_nonce($_POST['meetingmeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;

    $adeptlib = new Wpadept_Lib();

    $adept_access_token_value = get_option('adept_access_token');
    $postid = $post_id;
    $meeting_title = sanitize_text_field($_POST['post_title'] );
    $description = sanitize_text_field($_POST['content']);
    $date = sanitize_text_field($_POST['_date']);
    $start_time = sanitize_text_field($_POST['_start_time']);
    $end_time = sanitize_text_field($_POST['_end_time']);
    //$web_conference = $_POST['_web_conference'];
    $address = sanitize_text_field($_POST['_address']);
    $meeting_url = sanitize_text_field($_POST['_meeting_url']);
    $meeting_id = sanitize_text_field($_POST['_meeting_id']);
    $status = sanitize_text_field($_POST['_status']);
    $group_id = sanitize_text_field($_POST['_group_id']);
    $group_id = sanitize_text_field($adeptlib->stringify($group_id));
    $check_address = sanitize_text_field($_POST['_check_address']);
    $user_id = sanitize_text_field($_POST['_user_id']);
    $kind = sanitize_text_field($_POST['_kind']);
    $video_conference_account_id = sanitize_text_field($_POST['_video_conference_account_id']);
    $video_conference_url = sanitize_text_field($_POST['_video_conference_url']);
    $video_conference_uid = sanitize_text_field($_POST['_video_conference_uid']);


    //include_once WPADEPT_PLUGIN_PATH . "lib/lib.php";
    $adept = new Wpadept_Lib();
    $adept_api_url_value = get_option('adept_api_url');
    
    $get_existing_post_id = $wpdb->get_results("select meta_value from " . $wpdb->prefix . "postmeta" . " where post_id=".$post->ID." AND meta_key='_meeting_id'");
    // var_dump($get_existing_post_id); die();
    $originalPostId = $get_existing_post_id[0]->meta_value;;
    $curl = $adept_api_url_value . 'update_meeting/'.$originalPostId;
    //echo $curl; die();
    $data = "id=" . $post_id . "&access_token=" . $adept_access_token_value . "&meeting[title]=" . $course_title
            . "&meeting[comment]=" . $description . "&meeting[date]=" . $date
            . "&meeting[start_time]=" . $start_time . "&meeting[end_time]=" . $end_time
            . "&meeting[address]=" . $allow_discounts . "&meeting[class_id]=" . $meeting_id
            . "&meeting[created_by]=" . $user_id . "&meeting[modified_by]=" . $user_id
            . "&meeting[check_address]=" . $check_address . "&meeting[group_id]=" . $group_id
            . "&meeting[user_id]=" . $user_id . "&meeting[kind]=" . $kind
            . "&meeting[video_conference_account_id]=" . $video_conference_account_id . "&meeting[video_conference_url]=" . $video_conference_url. "&meeting[video_conference_uid]=" . $video_conference_uid;
    
    $ch = curl_init($curl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //not executing curl, disabled until testing done
    //$result = curl_exec($ch);
    //var_dump($result); die();
    
    $resultdata = json_decode($result);
    
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.

    $meeting_meta['_date'] = sanitize_text_field($_POST['_date']);
    $meeting_meta['_start_time'] = sanitize_text_field($_POST['_start_time']);
    $meeting_meta['_end_time'] = sanitize_text_field($_POST['_end_time']);
    $meeting_meta['_address'] = sanitize_text_field($_POST['_address']);
    $meeting_meta['_meeting_url'] = sanitize_text_field($_POST['_meeting_url']);
    $meeting_meta['_meeting_id'] = sanitize_text_field($_POST['_meeting_id']);
    $meeting_meta['_status'] = sanitize_text_field($_POST['_status']);
    $meeting_meta['_group_id'] = sanitize_text_field($_POST['_group_id']);
    $meeting_meta['_check_address'] = sanitize_text_field($_POST['_check_address']);
    $meeting_meta['_user_id'] = sanitize_text_field($_POST['_user_id']);
    $meeting_meta['_kind'] = sanitize_text_field($_POST['_kind']);
    $meeting_meta['_video_conference_account_id'] = sanitize_text_field($_POST['_video_conference_account_id']);
    $meeting_meta['_video_conference_url'] = sanitize_text_field($_POST['_video_conference_url']);
    $meeting_meta['_video_conference_uid'] = sanitize_text_field($_POST['_video_conference_uid']);


    // Add values of $course_meta as custom fields

    foreach ($meeting_meta as $key => $value) { // Cycle through the $course_meta array!

        if ($post->post_type == 'revision')
            return; // Don't store custom data twice
        
        update_post_meta($post->ID, $key, $value);

    }
}

add_action('save_post', 'wpadept_save_meeting_meta', 1, 2); // save the custom fields


add_action('init', 'wpadept_create_instructors');

function wpadept_create_instructors() {
   
    //get list of all the post_types
    $post_types = get_post_types();
    //if dt_team is already defined then return without doing anything
    if(in_array("dt_team", $post_types)) {
        return;
    }

    register_post_type('dt_team', array(
        'labels' => array(
            'name' => __('Instructors'),
            'singular_name' => __('Instructor'),
            'menu_name' => _x('Instructors', 'admin menu', 'Instructor'),
            'name_admin_bar' => _x('Instructor', 'add new on admin bar', 'Instructor'),
            'add_new' => _x('Add New Instructor', 'Instructor', 'Instructor'),
            'add_new_item' => __('Add New Instructor', 'Instructor'),
            'new_item' => __('New Instructor', 'Instructor'),
            'edit_item' => __('Edit Instructor', 'Instructor'),
            'view_item' => __('View Instructor', 'Instructor'),
            'all_items' => __('All Instructor', 'Instructor'),
            'search_items' => __('Search Instructor', 'Instructor'),
            'parent_item_colon' => __('Parent Instructor:', 'Instructor'),
            'not_found' => __('No Instructor found.', 'Instructor'),
            'not_found_in_trash' => __('No Instructor found in Trash.', 'Instructor')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'register_meta_box_cb' => 'add_instructor_metaboxes'
            )
    );
}

add_action('add_meta_boxes', 'wpadept_add_instructor_metaboxes' );

// Add the Course Meta Boxes

function wpadept_add_instructor_metaboxes() {
    add_meta_box('wpt_instructor_fields', 'Instructor Other details', 'wpadept_instructor_fields', 'dt_team', 'normal', 'high');
}

function wpadept_instructor_fields() {
    global $post;

    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="instructormeta_noncename" id="instructormeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Get the email data if its already been entered
    //$email = get_post_meta($post->ID, '_dt_teammate_options_mail', true);
    // Echo out the field
    //echo '<b>Email :</b> <input type="text" name="_dt_teammate_options_mail" class="widefat" value="' . $email . '" /><br/><br/>';

    // Get the group_id data if its already been entered
    $instructor_id = get_post_meta($post->ID, '_instructor_id', true);
    // Echo out the field
    $instructor_id = esc_attr($instructor_id);
    echo '<input type="hidden" name="_instructor_id" value="' . $instructor_id . '" class="widefat" /><br/><br/>';

    
    // Get the avatar data if its already been entered
    $avatar = get_post_meta($post->ID, '_avatar', true);
    $avatar = esc_attr($avatar);
    // Echo out the field
    echo '<b>Avatar : </b><input type="text" name="_avatar" value="' . $avatar . '" class="widefat" /><br/><br/>';

    
    echo '<b>Instructor Courses :</b><br/><br/>';   
    $all_courses = wpadept_get_all_of_post_type_2( 'courses' );

    $linked_group_ids = get_post_meta( $post->ID , '_course_ids' , true );
    if(empty($linked_group_ids)) {
        $linked_group_ids = array();
    }
    //pre($linked_group_ids);
    //pre($all_courses);

        if ( 0 == count($all_courses) ) {
            $choice_block = '<p>No Course found in the system.</p>';
        } else {
            $choices = array();
            $choices[] = "<select class='wpaselect2' name='course_ids[]' multiple>";
            foreach ( $all_courses as $course ) {
                $selected = ( in_array( $course->ID, $linked_group_ids ) ) ? ' selected="selected"' : '';

                $display_name = esc_attr( $course->post_title );
                $choices[] = <<<HTML
<option  value="{$course->ID}" {$selected} > {$display_name}</option>
HTML;

            }
            $choices[] = "</select>";
            $choice_block = implode("\r\n", $choices);
        }

        
        echo $choice_block."</br></br>";
    
    // Get the Bio data if its already been entered
    //$uid = get_post_meta($post->ID, '_bio', true);
    // Echo out the field
    //echo '<b>Bio : </b><input type="text" name="_bio" value="' . $bio . '" class="widefat" /><br/><br/>';

    
}

function wpadept_save_instructor_meta($post_id, $post) {

    global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if(!isset($_POST['instructormeta_noncename'])) return;
    if (!wp_verify_nonce($_POST['instructormeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;

    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.
    $adept_access_token_value = get_option('adept_access_token');
    $postid = $post->ID;
    $email = sanitize_text_field($_POST['_email']);
    $full_name = sanitize_text_field($_POST['_full_name']);
    $avatar = sanitize_text_field($_POST['_avatar']);
    $bio = sanitize_text_field($_POST['_bio']);
    
    //include_once WPADEPT_PLUGIN_PATH . "lib/lib.php";
    $adept = new Wpadept_Lib();
    $adept_api_url_value = get_option('adept_api_url');
    $qry = $wpdb->prepare("select meta_value from " . $wpdb->prefix . "postmeta" . " where post_id=%d  AND meta_key='_post_id'" , $post->ID);
    $get_existing_post_id = $wpdb->get_results($qry);
    $oripostidStr = $get_existing_post_id[0]->meta_value;
    $oripostidArray = explode('_',$oripostidStr);
    $originalPostId = $oripostidArray[1];
    //echo $originalPostId; die;
    $curl = $adept_api_url_value . 'update_instructor/'.$originalPostId;
    $data = "id=" . $postid . "&access_token=" . $adept_access_token_value . "&instructor[email]=" . $email
            . "&instructor[full_name]=" . $full_name . "&instructor[avatar]=" . $avatar
            . "&instructor[bio]=" . $bio;

    //$ch = curl_init($curl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //$result = curl_exec($ch);
    //var_dump($result); die();
    
    $resultdata = json_decode($result);
    //var_dump($resultdata); die();
    
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.

    $instructor_meta['_instructor_id'] = sanitize_text_field($_POST['_instructor_id']);
    $instructor_meta['_dt_teammate_options_mail'] = sanitize_text_field($_POST['_dt_teammate_options_mail']);

    $instructor_meta['_avatar'] = sanitize_text_field($_POST['_avatar']);
  

    // Add values of $course_meta as custom fields
    
    foreach ($instructor_meta as $key => $value) { // Cycle through the $course_meta array!
        if ($post->post_type == 'revision')
            return; // Don't store custom data twice

        $value = implode(',', (array) $value); // If $value is an array, make it a CSV (unlikely)
        if (get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
            update_post_meta($post->ID, $key, $value);
        } else { // If the custom field doesn't have a value
            add_post_meta($post->ID, $key, $value);
        }
        if (!$value)
            delete_post_meta($post->ID, $key); // Delete if blank
    }
}


add_action('save_post', 'wpadept_save_instructor_meta', 1, 2); // save the custom fields


add_action('init', 'wpadept_create_groups');

function wpadept_create_groups() {
    register_post_type('groups', array(
        'labels' => array(
            'name' => __('Groups'),
            'singular_name' => __('Group'),
            'menu_name' => _x('Groups', 'admin menu', 'Group'),
            'name_admin_bar' => _x('Group', 'add new on admin bar', 'Group'),
            'add_new' => _x('Add New Group', 'Group', 'Group'),
            'add_new_item' => __('Add New Group', 'Group'),
            'new_item' => __('New Group', 'Group'),
            'edit_item' => __('Edit Group', 'Group'),
            'view_item' => __('View Group', 'Group'),
            'all_items' => __('All Group', 'Group'),
            'search_items' => __('Search Group', 'Group'),
            'parent_item_colon' => __('Parent Group:', 'Group'),
            'not_found' => __('No Group found.', 'Group'),
            'not_found_in_trash' => __('No Group found in Trash.', 'Group')
        ),
        'public' => true,
        'publicly_queryable' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'register_meta_box_cb' => 'wpadept_add_group_metaboxes'
            )
    );
}

add_action('add_meta_boxes', 'wpadept_add_group_metaboxes');

// Add the Course Meta Boxes

function wpadept_add_group_metaboxes() {
    add_meta_box('wpt_group_fields', 'Group Other details', 'wpadept_group_fields', 'groups', 'normal', 'high');
}

function wpadept_group_fields() {
    global $post;

    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="groupmeta_noncename" id="instructormeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Get the email data if its already been entered
    $tags = get_post_meta( $post->ID, '_tags', true );
    $tags = implode( ", ", $tags );
    // Echo out the field
    $tags = esc_attr($tags);
    echo '<b>Tags :</b> <input type="text" name="_tags" class="widefat" value="' . $tags . '" /><br/><br/>';
    
    // Get the group_id data if its already been entered
    $group_id = get_post_meta($post->ID, '_group_id', true);
    // Echo out the field
    $group_id = esc_attr($group_id);
    echo '<input type="hidden" name="_group_id" value="' . $group_id . '" class="widefat" /><br/><br/>';


    // Get the course_fee data if its already been entered
    $course_fee = get_post_meta($post->ID, '_course_fee', true);
    $course_fee = esc_attr($course_fee);
    echo '<b>Course Fee :</b> <input type="text" name="_course_fee" value="' . $course_fee . '" class="widefat" /><br/><br/>';
    
    echo '<b>Group Courses :</b><br/><br/>';    
    $all_courses = wpadept_get_all_of_post_type( 'courses' );

    $linked_group_ids = get_post_meta(  $post->ID,'_course_ids' ) ;;

        if ( 0 == count($all_courses) ) {
            $choice_block = '<p>No Course found in the system.</p>';
        } else {
            $choices = array();
            foreach ( $all_courses as $course ) {
                $checked = ( in_array( $course->ID, $linked_group_ids ) ) ? ' checked="checked"' : '';

                $display_name = esc_attr( $course->post_title );
                $choices[] = <<<HTML
<label><input type="checkbox" name="course_ids[]" value="{$course->ID}" {$checked}/> {$display_name}</label><br/>
HTML;

            }
            $choice_block = implode("\r\n", $choices);
        }

        
        echo $choice_block."</br></br>";
    
    // Get the start_date data if its already been entered
    $start_date = get_post_meta($post->ID, '_start_date', true);
    // Echo out the field
    $start_date = esc_attr($start_date);
    echo '<b>Start Date :</b> <input type="text" name="_start_date" value="' . $start_date . '" class="widefat" /><br/><br/>';

    // Get the end_date data if its already been entered
    $end_date = get_post_meta( $post->ID, '_end_date', true );
    $end_date = esc_attr($end_date);
    echo '<b>End Date :</b> <input type="text" name="_end_date" value="' . $end_date . '" class="widefat" /><br/><br/>';

    // Get the reg_date data if its already been entered
    $reg_date = get_post_meta( $post->ID, '_reg_date', true );
    $reg_date = esc_attr($reg_date);
    echo '<b>Reg Date:</b> <input type="text" name="_reg_date" value="' . $reg_date . '" class="widefat" /><br/><br/>';

    // Get the seats data if its already been entered
    $seats = get_post_meta($post->ID, '_seats', true);
    $seats = esc_attr($seats);
    echo '<b>Seats :</b> <input type="text" name="_seats" value="' . $seats . '" class="widefat" /><br/><br/>';

    // Get the seats data if its already been entered
    $address = get_post_meta($post->ID, '_address', true);
    $address = esc_attr($address);
    echo '<b>Address :</b> <input type="text" name="_address" value="' . $address . '" class="widefat" /><br/><br/>';

    // Get the lessons data if its already been entered
    $lessons = get_post_meta($post->ID, '_lessons', true);
    $lessons = esc_attr($lessons);
    echo '<b>Lessons :</b> <input type="text" name="_lessons" value="' . $lessons . '" class="widefat" /><br/><br/>';

    // Get the status data if its already been entered
    $status = get_post_meta($post->ID, '_status', true);
    $status = esc_attr($status);
    echo '<b>Status :</b> <input type="text" name="_status" value="' . $status . '" class="widefat" /><br/><br/>';
    
    $location = get_post_meta($post->ID, '_group_locations', true);
    $adept = new Wpadept_Lib();
    $location = $adept->unstringify($location);
    echo '<b>Location:</b> <input type="text" name="_group_locations" value="' . $location . '" class="widefat"   /><br/><br/>';
    
    $level = get_post_meta($post->ID, '_group_level', true);
    $level = esc_attr( $level );
    echo '<b>Level:</b> <input type="text" name="_group_level" value="' . $level . '" class="widefat"   /><br/><br/>';

}


function wpadept_save_group_meta($post_id, $post) {
    global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if(!isset($_POST['groupmeta_noncename'])) return;
    if (!wp_verify_nonce($_POST['groupmeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;

    $adept_access_token_value = get_option('adept_access_token');
    $postid = $post->ID;
    $group_title = sanitize_text_field( $_POST['post_title'] );
    $description = wp_kses_post( $_POST['post_content']);
    $tags = sanitize_text_field( $_POST['_tags']);
    $course_fee = sanitize_text_field( $_POST['_course_fee']);
    $start_date = sanitize_text_field( $_POST['_start_date']);
    $end_date = sanitize_text_field( $_POST['_end_date']);
    $reg_date = sanitize_text_field( $_POST['_reg_date']);
    $seats = sanitize_text_field( $_POST['_seats']);
    $lessons = sanitize_text_field( $_POST['_lessons']);
    $status = sanitize_text_field( $_POST['_status']);

    //include_once WPADEPT_PLUGIN_PATH . "lib/lib.php";
    $adept = new Wpadept_Lib();


    $adept_api_url_value = get_option('adept_api_url');
    
    $qry = $wpdb->prepare( "select meta_value from " . $wpdb->prefix . "postmeta  where post_id=%d AND meta_key='_group_id'" , $post->ID );
    $get_existing_post_id = $wpdb->get_results( $qry );
    //var_dump($get_existing_post_id);
    $oripostidStr = $get_existing_post_id[0]->meta_value;
    $oripostidArray = explode('_',$oripostidStr);
    $originalPostId = $oripostidArray[1];
    //echo $originalPostId; die();
    $curl = $adept_api_url_value . 'update_group/' .$originalPostId;
    //echo $curl; die(); 
    $data = "id=" . $email . "&access_token=" . $adept_access_token_value . "&group[group_title]=" . $group_title
            . "&group[description]=" . $description . "&group[tags]=" . $tags
            . "&group[course_fee]=". "&group[start_date]=" . $start_date . "&group[end_date]=" . $end_date
            . "&group[reg_date]=" . $reg_date .  "&group[address]=" . $address . "&group[seats]=" . $seats
            . "&group[lessons]=" . $lessons . "&group[status]=" . $status;

    //$data = "access_token=fa547f76ea1ebedbceb6b1ab674040bf&course[course_title]=test123456&course[teaser]=test";
//echo $data; die();
   // $temp = $adept->postdata($curl, $data);
    $ch = curl_init($curl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //not executing curl
    //$result = curl_exec($ch);
    //var_dump($result); die();
    
    $resultdata = json_decode($result);
    //var_dump($resultdata); die();
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.
    
    
    $group_meta['_group_id'] = sanitize_text_field($_POST['_group_id']);
    $group_meta['_tags'] = sanitize_text_field($_POST['_tags']);
    $group_meta['_course_fee'] = sanitize_text_field($_POST['_course_fee']);
    /*
    $course_meta['_taxable'] = $_POST['_taxable'];
    $course_meta['_published'] = $_POST['_published'];
    $course_meta['_allow_bookings'] = $_POST['_allow_bookings'];
     * 
     */
    $group_meta['_start_date'] = sanitize_text_field($_POST['_start_date']);
    $group_meta['_end_date'] = sanitize_text_field($_POST['_end_date']);
    $group_meta['_reg_date'] = sanitize_text_field($_POST['_reg_date']);
    $group_meta['_address'] = sanitize_text_field($_POST['_address']);
    $group_meta['_seats'] = sanitize_text_field($_POST['_seats']);
    /*
    $course_meta['_hide_if_full'] = $_POST['_hide_if_full'];
    $course_meta['_show_seats_left'] = $_POST['_show_seats_left'];
     * 
     */
    $group_meta['_lessons'] = sanitize_text_field($_POST['_lessons']);
    $group_meta['_status'] = sanitize_text_field($_POST['_status']);

    $group_meta['_group_locations'] = $adept->stringify($_POST['_group_locations']);
    $group_meta['_group_level'] = sanitize_text_field($_POST['_group_level']);

    $courses = $_POST['course_ids'];
        delete_post_meta( $post->ID , '_course_ids');
    // Add values of $course_meta as custom fields
    if ( 0 < count($courses) ) {
            foreach ( $courses as $course_id ) {
                # We use add post meta with 4th parameter false to let us link
                # books to as many authors as we want.
                $course_id = sanitize_text_field( $course_id );
                add_post_meta( $post->ID , '_course_ids', $course_id );
            }
    }
    // Add values of $course_meta as custom fields

    foreach ($group_meta as $key => $value) { // Cycle through the $course_meta array!
        if ($post->post_type == 'revision')
            return; // Don't store custom data twice
        $value = implode(',', (array) $value); // If $value is an array, make it a CSV (unlikely)
        if (get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
            update_post_meta($post->ID, $key, $value);
        } else { // If the custom field doesn't have a value
            add_post_meta($post->ID, $key, $value);
        }
        if (!$value)
            delete_post_meta($post->ID, $key); // Delete if blank
    }
}

add_action('save_post', 'wpadept_save_group_meta', 1, 2); // save the custom fields

function wpadept_add_wmenu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {

    global $menu, $admin_page_hooks, $_registered_pages, $_parent_pages;

    $menu_slug = plugin_basename($menu_slug);

    $admin_page_hooks[$menu_slug] = sanitize_title($menu_title);

    $hookname = get_plugin_page_hookname($menu_slug, '');

    if (!empty($function) && !empty($hookname) && current_user_can($capability))
        add_action($hookname, $function);

    if (empty($icon_url)) {
        $icon_url = 'dashicons-admin-generic';
        $icon_class = 'menu-icon-generic ';
    } else {
        $icon_url = set_url_scheme($icon_url);
        $icon_class = '';
    }

    $new_menu = array($menu_title, $capability, $menu_slug, $page_title, 'menu-top ' . $icon_class . $hookname, $hookname, $icon_url);

    if (null === $position) {
        $menu[] = $new_menu;
    } elseif (isset($menu["$position"])) {
        $position = $position + substr(base_convert(md5($menu_slug . $menu_title), 16, 10), -5) * 0.00001;
        $menu["$position"] = $new_menu;
    } else {
        $menu[$position] = $new_menu;
    }

    $_registered_pages[$hookname] = true;

    // No parent as top level
    $_parent_pages[$menu_slug] = false;

    return $hookname;
}

function wpadept_add_publish_confirmation(){

    global $post;

    if(is_object($post)) {
        
        if ( 'courses' === $post->post_type  ||  'groups' === $post->post_type ||  'dt_team' === $post->post_type ||  'meetings' === $post->post_type ) {     
        $confirmation_message = "Content will be updated on LMS,Are you sure?";      
        echo '<script type="text/javascript">';    
        echo 'var publish = document.getElementById("publish");';     
        echo 'if (publish !== null){';    
        echo 'publish.onclick = function(){ return confirm("'.$confirmation_message.'"); };';     
        echo '}';     
        echo '</script>'; } 
    }
}

//add_action('admin_footer', 'wpadept_add_publish_confirmation');



//error nag for sitepress plugin
add_action("init" , "wpadept_sitepress_plugin_notice");
function wpadept_sitepress_plugin_notice() {
    
    
    if(!wpadept_is_wpml_installed())
    {

        //add_action( 'admin_notices', 'wpadept_sitepress_plugin_notice_nag' );
    }
    
    add_action( 'admin_notices', 'wpadept_nags' );
}

function wpadept_sitepress_plugin_notice_nag() {

    ?>
    <div class="notice notice-error ">
        <p>Adept LMS Plugin require WPML Multilingual CMS. Please install WPML Multilingual CMS</p>
    </div>
    <?php

}


function wpadept_nags() {
    global $pagenow;
    global $page;
    if($pagenow == "admin.php") {
        if(isset($_GET["page"]) ) {
            if($_GET["page"] == "adept_lms" || $_GET["page"] == "adept_lms_sync")  {

                echo '<div class="notice notice-error ">
                        Please ensure that you have setup server cron manually for the given URLs: <em>'.wpadept_get_cron_url().' , '.wpadept_get_cron_meeting_url().' </em>
                    </div>';
            }
        }
    }

}

//enqueing styles and scripts
function wpadept_custom_wp_admin_style() {
    wp_enqueue_script( "adeptwp", plugins_url("js/script.js" , __FILE__), "jquery");
    wp_enqueue_script( 'select2-js',  plugins_url("js/select2.js" , __FILE__) , array("jquery") );
    wp_enqueue_style("select2-css" , plugins_url("css/select2.css" , __FILE__) );
    wp_enqueue_style("adept" , plugins_url("css/style.css" , __FILE__) );
}
add_action( 'admin_enqueue_scripts', 'wpadept_custom_wp_admin_style' );


//adding necesary js variables in the footer
add_action("admin_footer" , "wpadept_footer_js");

function wpadept_footer_js() {
    echo "<script>
        var ADEPT_SITE_URL = '".site_url()."';
    </script>";
}




function wpadept_is_wpml_installed() {

    $option = get_option("active_plugins");    
    //print_r($option); exit;
    return in_array( "sitepress-multilingual-cms/sitepress.php", $option );

}


//todo
//x rename WP_LIB
//x check error_reporting
//x check metabox issue
//x sanitize
//x escape
//x wpdb->prepare
//x change cron endpoints 
//x sanitization in backend setting page
?>