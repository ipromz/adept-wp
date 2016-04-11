<?php

/*
  Plugin Name: Adept LMS Plugin
  Plugin URI: http://www.orangecreative.net
  Description: Plugin for Adept LMS
  Author: Viral Sonawala
  Version: 1.0
 */

class WP_Adept_LMS {

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

        add_wmenu_page('Adept LMS', 'Adept LMS', 'manage_options', 'adept_lms', array(
            __CLASS__,
            'wpa_page_file_path'
                ), "", '2.2.9');

        add_submenu_page('adept_lms', 'Adept LMS Settings', '<b style="color:#f9845b">Settings</b>', 'manage_options', 'adept_lms_settings', array(
            __CLASS__,
            'wpa_page_file_path1'
        ));
    }

    /*
     * Actions perform on loading of menu pages
     */

    function wpa_page_file_path() {

        foreach (glob(plugin_dir_path(__FILE__) . "includes/adept_lms.php") as $file) {
            include_once $file;
        }
    }

    /*
     * Actions perform on loading of menu pages
     */

    function wpa_page_file_path1() {

        foreach (glob(plugin_dir_path(__FILE__) . "includes/adept_lms_settings.php") as $file) {
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

add_action('init', 'create_post_type');

function create_post_type() {
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
        'register_meta_box_cb' => 'add_course_metaboxes'
            )
    );
}

add_action('add_meta_boxes', 'add_course_metaboxes');

// Add the Course Meta Boxes

function add_course_metaboxes() {
    add_meta_box('wpt_course_fields', 'Course Other details', 'wpt_course_fields', 'courses', 'normal', 'high');
}

function wpt_course_fields() {
    global $post;

    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="coursemeta_noncename" id="coursemeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Get the tags data if its already been entered
    $tags = get_post_meta($post->ID, '_tags', true);
    // Echo out the field
    echo '<b>Tags : </b><input type="text" name="_tags" value="' . $tags . '" class="widefat" /><br/><br/>';

    // Get the is_featured data if its already been entered
    $is_featured = get_post_meta($post->ID, '_is_featured', true);
    // Echo out the field

    if ($is_featured == '1') {
        $checked = "checked='checked'";
    } else {
        $unchecked = "checked='checked'";
    }
    echo '<b>Is Featured :</b> <input type="radio" name="_is_featured" ' . $checked . ' value="true" class="widefat" /> True ';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_is_featured" ' . $unchecked . ' value="false" class="widefat" /> False <br/><br/>';

    // Get the course_fee data if its already been entered
    $course_fee = get_post_meta($post->ID, '_course_fee', true);
    // Echo out the field
    echo '<b>Course Fee :</b> <input type="text" name="_course_fee" value="' . $course_fee . '" class="widefat" /><br/><br/>';


    // Get the sku data if its already been entered
    $sku = get_post_meta($post->ID, '_sku', true);
    // Echo out the field
    echo '<b>SKU :</b> <input type="text" name="_sku" value="' . $sku . '" class="widefat" /><br/><br/>';

    // Get the tax_category data if its already been entered
    $tax_category = get_post_meta($post->ID, '_tax_category', true);
    // Echo out the field
    echo '<b>Taxable :</b> <input type="text" name="_tax_category" value="' . $tax_category . '" class="widefat" /><br/><br/>';


    // Get the allow_discounts data if its already been entered
    $allow_discounts = get_post_meta($post->ID, '_allow_discounts', true);

    if ($allow_discounts == '1') {
        $checked = "checked='checked'";
    } else {
        $unchecked = "checked='checked'";
    }
    // Echo out the field
    echo '<b>Allow Discounts : </b><input type="radio" name="_allow_discounts" ' . $checked . ' value="true" class="widefat" /> True ';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_allow_discounts" ' . $unchecked . ' value="false" class="widefat" /> False <br/><br/>';

    // Get the subscription data if its already been entered
    $subscription = get_post_meta($post->ID, '_subscription', true);
    // Echo out the field

    if ($subscription == '1') {
        $checked = "checked='checked'";
    } else {
        $unchecked = "checked='checked'";
    }
    echo '<b>Subscription :</b> <input type="radio" name="_subscription" ' . $checked . ' value="true" class="widefat" /> True';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_subscription" ' . $unchecked . ' value="false" class="widefat" /> False  <br/><br/>';

    // Get the booking_count data if its already been entered
    $booking_count = get_post_meta($post->ID, '_booking_count', true);
    // Echo out the field
    echo '<b>Booking Count :</b> <input type="text" name="_booking_count" value="' . $booking_count . '" class="widefat" /><br/><br/>';
	
	// Get Image url 
    $image_url = get_post_meta($post->ID, '_image_url', true);
    // Echo out the field
    echo '<b>Image Url :</b> <input type="text" name="_image_url" value="' . $image_url . '" class="widefat" /><br/><br/>';
	
}

function wpt_save_course_meta($post_id, $post) {
	global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (!wp_verify_nonce($_POST['coursemeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;



    $adept_access_token_value = get_option('adept_access_token');
    $postid = $post_id;
    $course_title = $_POST['post_title'];
    $teaser = $_POST['post_excerpt'];
    $description = $_POST['content'];
    $tags = $_POST['_tags'];
	$course_fee = $_POST['_course_fee'];
    $sku = $_POST['_sku'];
    $taxable = $_POST['_tax_category'];
    $allow_discounts = $_POST['_allow_discounts'];
    $subscription = $_POST['_allow_discounts'];
    $course_category_id = $_POST['tax_input']['tax_input'][0];
    define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
    include_once MY_PLUGIN_PATH . "lib/lib.php";
    $adept = new WP_Lib();
    $adept_api_url_value = get_option('adept_api_url');

	$get_existing_post_id = $wpdb->get_results("select meta_value from " . $wpdb->prefix . "postmeta" . " where post_id=".$post->ID." AND meta_key='_post_id'");
	//var_dump($get_existing_post_id);
	$oripostidStr = $get_existing_post_id[0]->meta_value;
	$oripostidArray = explode('_',$oripostidStr);
	$originalPostId = $oripostidArray[1];
    $curl = $adept_api_url_value . 'update_course/' .$originalPostId;
    $data = "id=" . $email . "&access_token=" . $adept_access_token_value . "&course[course_title]=" . $course_title
            . "&course[teaser]=" . $teaser . "&course[description]=" . $description
            . "&course[tags]=" . $tags . "&course[course_fee]=" . $course_fee ."&course[sku]=" . $sku . "&course[taxable]=" . $taxable
            . "&course[allow_discounts]=" . $allow_discounts . "&course[subscription]=" . $subscription
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
	$result = curl_exec($ch);
//var_dump($result); die();
	
	$resultdata = json_decode($result);
//var_dump($resultdata); die();
	
	// OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.

    $course_meta['_tags'] = $_POST['_tags'];
    $course_meta['_is_featured'] = $_POST['_is_featured'];
    $course_meta['_course_fee'] = $_POST['_course_fee'];
    $course_meta['_sku'] = $_POST['_sku'];
    $course_meta['_tax_category'] = $_POST['_tax_category'];
    $course_meta['_allow_discounts'] = $_POST['_allow_discounts'];
    $course_meta['_subscription'] = $_POST['_subscription'];
    $course_meta['_booking_count'] = $_POST['_booking_count'];
	$course_meta['_image_url'] = $_POST['_image_url'];
	
    // Add values of $course_meta as custom fields

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

add_action('save_post', 'wpt_save_course_meta', 1, 2); // save the custom fields

add_action('init', 'create_course_category');

function create_course_category() {
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

add_action('init', 'create_meetings');

function create_meetings() {
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
        'register_meta_box_cb' => 'add_meeting_metaboxes'
            )
    );
}

add_action('add_meta_boxes', 'add_meeting_metaboxes');

// Add the Course Meta Boxes

function add_meeting_metaboxes() {
    add_meta_box('wpt_meeting_fields', 'Meetings Other details', 'wpt_meeting_fields', 'meetings', 'normal', 'high');
}

function wpt_meeting_fields() {
    global $post;
	
	
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="meetingmeta_noncename" id="meetingmeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';
	
	// Get the date data if its already been entered
    $date = get_post_meta($post->ID, '_date', true);
    // Echo out the field
    echo '<b>Date :</b> <input type="text" name="_date" value="' . $date . '" class="widefat" /><br/><br/>';

    // Get the start_time data if its already been entered
    $start_time = get_post_meta($post->ID, '_start_time', true);
    // Echo out the field
    echo '<b>Start Time : </b><input type="text" name="_start_time" value="' . $start_time . '" class="widefat" /><br/><br/>';

    // Get the start_time data if its already been entered
    $end_time = get_post_meta($post->ID, '_end_time', true);
    // Echo out the field
    echo '<b>End Time : </b><input type="text" name="_end_time" value="' . $end_time . '" class="widefat" /><br/><br/>';

    // Get the status data if its already been entered
    $status = get_post_meta($post->ID, '_status', true);
    // Echo out the field
    echo '<b>Status :</b> <input type="text" name="_status" value="' . $status . '" class="widefat" /><br/><br/>';

    // Get the web_conference data if its already been entered
    $web_conference = get_post_meta($post->ID, '_web_conference', true);
    // Echo out the field
    echo '<b>Web Conference :</b> <input type="radio" name="_web_conference" value="true" class="widefat" /> True';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_web_conference" value="false" class="widefat" /> False  <br/><br/>';

    // Get the address data if its already been entered
    $address = get_post_meta($post->ID, '_address', true);
    // Echo out the field
    echo '<b>Address :</b> <textarea type="text" name="_address" class="widefat">' . $address . '</textarea><br/><br/>';

    // Get the check_address data if its already been entered
    $check_address = get_post_meta($post->ID, '_check_address', true);
    // Echo out the field
    echo '<b>Check Address :</b> <input type="radio" name="_check_address" value="true" class="widefat" /> True';
    echo '&nbsp;&nbsp;&nbsp;<input type="radio" name="_check_address" value="false" class="widefat" /> False  <br/><br/>';

    // Get the group_id data if its already been entered
    $group_id = get_post_meta($post->ID, '_group_id', true);
    // Echo out the field
    echo '<b>Group Id:</b> <input type="text" name="_group_id" value="' . $group_id . '" class="widefat" /><br/><br/>';
}

function wpt_save_meeting_meta($post_id, $post) {
	global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (!wp_verify_nonce($_POST['meetingmeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;

    $adept_access_token_value = get_option('adept_access_token');
    $postid = $post_id;
    $course_title = $_POST['post_title'];
    $description = $_POST['content'];
    $date = $_POST['_date'];
    $start_time = $_POST['_start_time'];
    $end_time = $_POST['_end_time'];
    $web_conference = $_POST['_web_conference'];
    $address = $_POST['_address'];
    $meeting_id = $_POST['_meeting_id'];
    $status = $_POST['_status'];
    $group_id = $_POST['_group_id'];
    $check_address = $_POST['_check_address'];
    $user_id = $_POST['_user_id'];
    $kind = $_POST['_kind'];
    $video_conference_account_id = $_POST['_video_conference_account_id'];
    $video_conference_url = $_POST['_video_conference_url'];
    $video_conference_uid = $_POST['_video_conference_uid'];


    define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
    include_once MY_PLUGIN_PATH . "lib/lib.php";
    $adept = new WP_Lib();
    $adept_api_url_value = get_option('adept_api_url');
    $get_existing_post_id = $wpdb->get_results("select meta_value from " . $wpdb->prefix . "postmeta" . " where post_id=".$post->ID." AND meta_key='_meeting_id'");
	//var_dump($get_existing_post_id);
	$oripostidStr = $get_existing_post_id[0]->meta_value;
	$oripostidArray = explode('_',$oripostidStr);
	$originalPostId = $oripostidArray[1];
    $curl = $adept_api_url_value . 'update_meeting/'.$originalPostId;
	$data = "id=" . $post_id . "&access_token=" . $adept_access_token_value . "&meeting[title]=" . $course_title
            . "&meeting[comment]=" . $description . "&meeting[date]=" . $date
            . "&meeting[start_time]=" . $start_time . "&meeting[end_time]=" . $end_time
            . "&meeting[address]=" . $allow_discounts . "&meeting[class_id]=" . $meeting_id
            . "&meeting[created_by]=" . $user_id . "&meeting[modified_by]=" . $user_id
            . "&meeting[check_address]=" . $check_address . "&meeting[group_id]=" . $group_id
            . "&meeting[user_id]=" . $user_id . "&meeting[kind]=" . $kind
            . "&meeting[video_conference_account_id]=" . $video_conference_account_id . "&meeting[video_conference_url]=" . $video_conference_url. "&meeting[video_conference_uid]=" . $video_conference_uid;

    $temp = $adept->putdata($curl, $data);

    $course_meta['_date'] = $_POST['_date'];
    $course_meta['_start_time'] = $_POST['_start_time'];
    $course_meta['_end_time'] = $_POST['_end_time'];
    $course_meta['_web_conference'] = $_POST['_web_conference'];
    $course_meta['_address'] = $_POST['_address'];
    $course_meta['_meeting_id'] = $_POST['_meeting_id'];
    $course_meta['_status'] = $_POST['_status'];
    $course_meta['_group_id'] = $_POST['_group_id'];
    $course_meta['_check_address'] = $_POST['_check_address'];
    $course_meta['_user_id'] = $_POST['_user_id'];
    $course_meta['_kind'] = $_POST['_kind'];
    $course_meta['_video_conference_account_id'] = $_POST['_video_conference_account_id'];
    $course_meta['_video_conference_url'] = $_POST['_video_conference_url'];
    $course_meta['_video_conference_uid'] = $_POST['_video_conference_uid'];


    // Add values of $course_meta as custom fields

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

add_action('save_post', 'wpt_save_meeting_meta', 1, 2); // save the custom fields
/*
  function wp_add_custom_user_profile_fields($user) {
  ?>
  <h3><?php _e('Extra Instructor Information', 'your_textdomain'); ?></h3>

  <table class="form-table">
  <tr>
  <th>
  <label for="intructor_id"><?php _e('Intructor Id', 'your_textdomain'); ?>
  </label></th>
  <td>
  <input type="text" name="intructor_id" id="intructor_id" value="<?php echo esc_attr(get_the_author_meta('intructor_id', $user->ID)); ?>" class="regular-text" /><br />
  <span class="description"><?php _e('Please enter your intructor id.', 'your_textdomain'); ?></span>
  </td>
  </tr>
  <tr>
  <th>
  <label for="privacy_policy"><?php _e('Privacy Policy', 'your_textdomain'); ?>
  </label></th>
  <td>
  <input type="text" name="privacy_policy" id="privacy_policy" value="<?php echo esc_attr(get_the_author_meta('privacy_policy', $user->ID)); ?>" class="regular-text" /><br />
  <span class="description"><?php _e('Please enter your Privacy Policy.', 'your_textdomain'); ?></span>
  </td>
  </tr>
  <tr>
  <th>
  <label for="provider"><?php _e('Provider', 'your_textdomain'); ?>
  </label></th>
  <td>
  <input type="text" name="provider" id="provider" value="<?php echo esc_attr(get_the_author_meta('provider', $user->ID)); ?>" class="regular-text" /><br />
  <span class="description"><?php _e('Please enter your provider.', 'your_textdomain'); ?></span>
  </td>
  </tr>
  <tr>
  <th>
  <label for="uid"><?php _e('U Id', 'your_textdomain'); ?>
  </label></th>
  <td>
  <input type="text" name="uid" id="uid" value="<?php echo esc_attr(get_the_author_meta('uid', $user->ID)); ?>" class="regular-text" /><br />
  <span class="description"><?php _e('Please enter your uid.', 'your_textdomain'); ?></span>
  </td>
  </tr>
  <tr>
  <th>
  <label for="system_admin"><?php _e('System Admin', 'your_textdomain'); ?>
  </label></th>
  <td>
  <input type="text" name="system_admin" id="system_admin" value="<?php echo esc_attr(get_the_author_meta('system_admin', $user->ID)); ?>" class="regular-text" /><br />
  <span class="description"><?php _e('Please enter your system admin.', 'your_textdomain'); ?></span>
  </td>
  </tr>
  <tr>
  <th>
  <label for="created_at"><?php _e('Created At', 'your_textdomain'); ?>
  </label></th>
  <td>
  <input type="text" name="created_at" id="created_at" value="<?php echo esc_attr(get_the_author_meta('created_at', $user->ID)); ?>" class="regular-text" /><br />
  <span class="description"><?php _e('Please enter your created at.', 'your_textdomain'); ?></span>
  </td>
  </tr>
  <tr>
  <th>
  <label for="updated_at"><?php _e('Updated At', 'your_textdomain'); ?>
  </label></th>
  <td>
  <input type="text" name="updated_at" id="updated_at" value="<?php echo esc_attr(get_the_author_meta('updated_at', $user->ID)); ?>" class="regular-text" /><br />
  <span class="description"><?php _e('Please enter your updated at.', 'your_textdomain'); ?></span>
  </td>
  </tr>
  </table>
  <?php
  }

  function wp_save_custom_user_profile_fields($user_id) {

  if (!current_user_can('edit_user', $user_id))
  return FALSE;

  update_usermeta($user_id, 'intructor_id', $_POST['intructor_id']);
  update_usermeta($user_id, 'privacy_policy', $_POST['privacy_policy']);
  update_usermeta($user_id, 'provider', $_POST['provider']);
  update_usermeta($user_id, 'uid', $_POST['uid']);
  update_usermeta($user_id, 'system_admin', $_POST['system_admin']);
  update_usermeta($user_id, 'created_at', $_POST['created_at']);
  update_usermeta($user_id, 'updated_at', $_POST['updated_at']);
  }

  add_action('show_user_profile', 'wp_add_custom_user_profile_fields');
  add_action('edit_user_profile', 'wp_add_custom_user_profile_fields');

  add_action('personal_options_update', 'wp_save_custom_user_profile_fields');
  add_action('edit_user_profile_update', 'wp_save_custom_user_profile_fields');
 */

function wp_meetings_shortcode($atts) {
	$pull_quote_atts = shortcode_atts( array(
        'group_id' => 'Group ID'
    ), $atts );
	
    $args = array(
        'offset' => 0,
        'category' => '',
        'category_name' => '',
        'orderby' => 'date',
        'order' => 'DESC',
        'post_type' => 'meetings',
        'post_status' => 'publish',
        'suppress_filters' => true,
		'meta_query' => array (
		    array (
			  'key' => '_group_id',
			  'value' => $pull_quote_atts['group_id'],
		    )
		  )
    );
    $meetings_array = get_posts($args);
    echo "<div class='meeting-loop'>";
    foreach ($meetings_array as $meeting) {
        $date = get_post_meta($meeting->ID, '_date');
        $start_time = get_post_meta($meeting->ID, '_start_time');
        $end_time = get_post_meta($meeting->ID, '_end_time');
        $status = get_post_meta($meeting->ID, '_status');
        $web_conference = get_post_meta($meeting->ID, '_web_conference');
        $address = get_post_meta($meeting->ID, '_address');
        $class_id = get_post_meta($meeting->ID, '_class_id');
        $group_id = get_post_meta($meeting->ID, '_group_id');
        $check_address = get_post_meta($meeting->ID, '_check_address');


        echo '<ul class="meeting">
			<li class="title"> ' . $meeting->post_title . '</li>
			<li class="content"> ' . $meeting->post_content . ' </li>
			<li class="date">' . $date[0] . '</li>
			<li class="start_time">' . $start_time[0] . '</li>
			<li class="end_time">' . $end_time[0] . '</li>
			<li class="status">' . $status[0] . '</li>
			<li class="web_conference">' . $web_conference[0] . '</li>
			<li class="address">' . $address[0] . '</li>
			<li class="class_id">' . $class_id[0] . '</li>
			<li class="group_id">' . $group_id[0] . ' </li>
			<li class="check_address">' . $check_address[0] . ' </li>
			</ul>
			';
    }
    echo "</div>";
}


add_shortcode('meetings', 'wp_meetings_shortcode');

add_action('init', 'create_instructors');

function create_instructors() {
    register_post_type('instructors', array(
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

add_action('add_meta_boxes', 'add_instructor_metaboxes');

// Add the Course Meta Boxes

function add_instructor_metaboxes() {
    add_meta_box('wpt_instructor_fields', 'Instructor Other details', 'wpt_instructor_fields', 'instructors', 'normal', 'high');
}

function wpt_instructor_fields() {
    global $post;

    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="instructormeta_noncename" id="instructormeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Get the email data if its already been entered
    $email = get_post_meta($post->ID, '_email', true);
    // Echo out the field
    echo '<b>Email :</b> <input type="text" name="_email" class="widefat" value="' . $email . '" /><br/><br/>';

    // Get the full_name data if its already been entered
    //$full_name = get_post_meta($post->ID, '_full_name', true);
    // Echo out the field
    //echo '<b>Full Name :</b> <input type="text" name="_full_name" value="' . $full_name . '" class="widefat" /><br/><br/>';

    // Get the avatar data if its already been entered
    $avatar = get_post_meta($post->ID, '_avatar', true);
    // Echo out the field
    echo '<b>Avatar : </b><input type="text" name="_avatar" value="' . $avatar . '" class="widefat" /><br/><br/>';

    // Get the Bio data if its already been entered
    //$uid = get_post_meta($post->ID, '_bio', true);
    // Echo out the field
    //echo '<b>Bio : </b><input type="text" name="_bio" value="' . $bio . '" class="widefat" /><br/><br/>';

    
}

function wpt_save_instructor_meta($post_id, $post) {
	global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
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
    $email = $_POST['_email'];
    $full_name = $_POST['_full_name'];
    $avatar = $_POST['_avatar'];
    $bio = $_POST['_bio'];
    
    define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
    include_once MY_PLUGIN_PATH . "lib/lib.php";
    $adept = new WP_Lib();
    $adept_api_url_value = get_option('adept_api_url');
	$get_existing_post_id = $wpdb->get_results("select meta_value from " . $wpdb->prefix . "postmeta" . " where post_id=".$post->ID." AND meta_key='_post_id'");		$oripostidStr = $get_existing_post_id[0]->meta_value;
	$oripostidArray = explode('_',$oripostidStr);
	$originalPostId = $oripostidArray[1];
        $curl = $adept_api_url_value . 'update_instructor/'.$originalPostId;
    $data = "id=" . $postid . "&access_token=" . $adept_access_token_value . "&instructor[email]=" . $email
            . "&instructor[full_name]=" . $full_name . "&instructor[avatar]=" . $avatar
            . "&instructor[bio]=" . $bio;

    $temp = $adept->putdata($curl, $data);
    $course_meta['_instructor_id'] = $_POST['_instructor_id'];
    $course_meta['_email'] = $_POST['_email'];
    //$course_meta['_full_name'] = $_POST['_full_name'];
    $course_meta['_avatar'] = $_POST['_avatar'];
    //$course_meta['_bio'] = $_POST['_bio'];

    // Add values of $course_meta as custom fields

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

add_action('save_post', 'wpt_save_instructor_meta', 1, 2); // save the custom fields


add_action('init', 'create_groups');

function create_groups() {
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
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'register_meta_box_cb' => 'add_group_metaboxes'
            )
    );
}

add_action('add_meta_boxes', 'add_group_metaboxes');

// Add the Course Meta Boxes

function add_group_metaboxes() {
    add_meta_box('wpt_group_fields', 'Group Other details', 'wpt_group_fields', 'groups', 'normal', 'high');
}

function wpt_group_fields() {
    global $post;

    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="groupmeta_noncename" id="instructormeta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    // Get the email data if its already been entered
    $email = get_post_meta($post->ID, '_tags', true);
    // Echo out the field
    echo '<b>Tags :</b> <input type="text" name="_tags" class="widefat" value="' . $tags . '" /><br/><br/>';

    // Get the course_fee data if its already been entered
    $course_fee = get_post_meta($post->ID, '_course_fee', true);
    // Echo out the field
    echo '<b>Course Fee :</b> <input type="text" name="_course_fee" value="' . $course_fee . '" class="widefat" /><br/><br/>';

    // Get the taxable data if its already been entered
    $taxable = get_post_meta($post->ID, '_taxable', true);
    // Echo out the field
    echo '<b>Taxable : </b><input type="text" name="_taxable" value="' . $taxable . '" class="widefat" /><br/><br/>';

    // Get the published data if its already been entered
    $published = get_post_meta($post->ID, '_published', true);
    // Echo out the field
    echo '<b>Published : </b><input type="text" name="_published" value="' . $published . '" class="widefat" /><br/><br/>';

    // Get the allow_bookings data if its already been entered
    $allow_bookings = get_post_meta($post->ID, '_allow_bookings', true);
    // Echo out the field
    echo '<b>Allow Bookings :</b> <input type="text" name="_allow_bookings" value="' . $allow_bookings . '" class="widefat" /><br/><br/>';

    // Get the start_date data if its already been entered
    $start_date = get_post_meta($post->ID, '_start_date', true);
    // Echo out the field
    echo '<b>Start Date :</b> <input type="text" name="_start_date" value="' . $start_date . '" class="widefat" /><br/><br/>';

    // Get the end_date data if its already been entered
    $end_date = get_post_meta($post->ID, '_end_date', true);
    // Echo out the field
    echo '<b>End Date :</b> <input type="text" name="_end_date" value="' . $end_date . '" class="widefat" /><br/><br/>';

    // Get the reg_date data if its already been entered
    $reg_date = get_post_meta($post->ID, '_reg_date', true);
    // Echo out the field
    echo '<b>Reg Date:</b> <input type="text" name="_reg_date" value="' . $reg_date . '" class="widefat" /><br/><br/>';

    // Get the seats data if its already been entered
    $seats = get_post_meta($post->ID, '_seats', true);
    // Echo out the field
    echo '<b>Seats :</b> <input type="text" name="_seats" value="' . $seats . '" class="widefat" /><br/><br/>';

    // Get the hide_if_full data if its already been entered
    $hide_if_full = get_post_meta($post->ID, '_hide_if_full', true);
    // Echo out the field
    echo '<b>Hide if full :</b> <input type="text" name="_hide_if_full" value="' . $hide_if_full . '" class="widefat" /><br/><br/>';

    // Get the show_seats_left data if its already been entered
    $show_seats_left = get_post_meta($post->ID, '_show_seats_left', true);
    // Echo out the field
    echo '<b>Show seats left :</b> <input type="text" name="_show_seats_left" value="' . $show_seats_left . '" class="widefat" /><br/><br/>';

    // Get the lessons data if its already been entered
    $lessons = get_post_meta($post->ID, '_lessons', true);
    // Echo out the field
    echo '<b>Lessons :</b> <input type="text" name="_lessons" value="' . $lessons . '" class="widefat" /><br/><br/>';

    // Get the status data if its already been entered
    $status = get_post_meta($post->ID, '_status', true);
    // Echo out the field
    echo '<b>Status :</b> <input type="text" name="_status" value="' . $status . '" class="widefat" /><br/><br/>';

    // Get the subscription_plan_id data if its already been entered
    $subscription_plan_id = get_post_meta($post->ID, '_subscription_plan_id', true);
    // Echo out the field
    echo '<b>Subscription plan id :</b> <input type="text" name="_subscription_plan_id" value="' . $subscription_plan_id . '" class="widefat" /><br/><br/>';
}

function wpt_save_group_meta($post_id, $post) {
	global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (!wp_verify_nonce($_POST['groupmeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;

    $adept_access_token_value = get_option('adept_access_token');
    $postid = $post->ID;
    $group_title = $_POST['post_title'];
    $description = $_POST['post_content'];
    $tags = $_POST['_tags'];
    $course_fee = $_POST['_course_fee'];
    $taxable = $_POST['_taxable'];
    $published = $_POST['_published'];
    $allow_bookings = $_POST['_allow_bookings'];
    $start_date = $_POST['_start_date'];
    $end_date = $_POST['_end_date'];
    $reg_date = $_POST['_reg_date'];
    $seats = $_POST['_seats'];
    $hide_if_full = $_POST['_hide_if_full'];
    $show_seats_left = $_POST['_show_seats_left'];
    $lessons = $_POST['_lessons'];
    $status = $_POST['_status'];

    define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
    include_once MY_PLUGIN_PATH . "lib/lib.php";
    $adept = new WP_Lib();
    $adept_api_url_value = get_option('adept_api_url');
    $curl = $adept_api_url_value . 'update_instructor/';
    $data = "id=" . $postid . "&access_token=" . $adept_access_token_value . "&group[group_title]=" . $group_title
            . "&group[description]=" . $description . "&group[tags]=" . $tags
            . "&group[course_fee]=" . $course_fee . "&group[taxable]=" . $taxable
            . "&group[published]=" . $published . "&group[allow_bookings]=" . $allow_bookings
            . "&group[start_date]=" . $start_date . "&group[end_date]=" . $end_date
            . "&group[reg_date=" . $reg_date . "&seats=" . $seats
            . "&hide_if_full=" . $hide_if_full . "&show_seats_left=" . $show_seats_left
            . "&lessons=" . $lessons . "&status=" . $status;

    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.

    $course_meta['_group_id'] = $_POST['_group_id'];
    $course_meta['_tags'] = $_POST['_tags'];
    $course_meta['_course_fee'] = $_POST['_course_fee'];
    $course_meta['_taxable'] = $_POST['_taxable'];
    $course_meta['_published'] = $_POST['_published'];
    $course_meta['_allow_bookings'] = $_POST['_allow_bookings'];
    $course_meta['_start_date'] = $_POST['_start_date'];
    $course_meta['_end_date'] = $_POST['_end_date'];
    $course_meta['_reg_date'] = $_POST['_reg_date'];
    $course_meta['_seats'] = $_POST['_seats'];
    $course_meta['_hide_if_full'] = $_POST['_hide_if_full'];
    $course_meta['_show_seats_left'] = $_POST['_show_seats_left'];
    $course_meta['_lessons'] = $_POST['_lessons'];
    $course_meta['_status'] = $_POST['_status'];


    // Add values of $course_meta as custom fields

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

add_action('save_post', 'wpt_save_group_meta', 1, 2); // save the custom fields

function add_wmenu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {
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
function add_publish_confirmation(){     $confirmation_message = "Content will be updated on LMS,Are you sure?";      echo '<script type="text/javascript">';    echo ' var publish = document.getElementById("publish");';     echo 'if (publish !== null){';    echo 'publish.onclick = function(){ return confirm("'.$confirmation_message.'"); };';     echo '}';     echo '</script>'; } add_action('admin_footer', 'add_publish_confirmation');
new WP_Adept_LMS();

define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));

include_once MY_PLUGIN_PATH . "lib/lib.php";

$adept = new WP_Lib();
?>