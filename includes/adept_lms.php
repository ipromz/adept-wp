<?php
$wp_adept_lms = new WP_Adept_LMS();

global $wpdb;
$post_table = $wpdb->prefix . "posts";
$postmeta_table = $wpdb->prefix . "postmeta";
$table_name = $wpdb->prefix . "api_credential";
$table_name1 = $wpdb->prefix . "term_taxonomy";
$table_name2 = $wpdb->prefix . "terms";
$myrows = $wpdb->get_results("SELECT access_token FROM " . $table_name);
$access_token = $myrows[0]->access_token;
$myapi_urlrows = $wpdb->get_results("SELECT api_url FROM " . $table_name);
$api_url = $myapi_urlrows[0]->api_url;
$site_url = get_site_url();

// All category array
//$categories = get_terms( 'category', 'orderby=count&hide_empty=0' );

$check_category_slug = $wpdb->get_results("SELECT slug FROM " . $wpdb->prefix . "terms");
if (!empty($check_category_slug)) {
    foreach ($check_category_slug as $k => $v) {
        $all_category_slug_array[] = $v->slug;
    }
}

if (isset($_POST['import_categories'])) {


    if ($access_token == '') {
        $error = "Please enter authentication detail";
    } else {

        $ch = curl_init($api_url . 'course_categories_api?access_token=' . $access_token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
                )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);

        $temp = json_decode($result);

        foreach ($temp as $_temp) {
            foreach ($_temp as $_temp1) {
                $name = $_temp1->name;
                $slug = $_temp1->id . '_' . $name;


                $description = $_temp1->description;
                $access_token = $myrows[0]->access_token;
                // Not to insert duplicate category

                if (!in_array($slug, $all_category_slug_array)) {
                    $data = $wpdb->insert($table_name2, array(
                        "name" => $name,
                        "slug" => $slug
                    ));

                    $lastid = $wpdb->insert_id;

                    $wpdb->insert(
                            $table_name1, array(
                        'term_id' => $lastid,
                        'taxonomy' => 'genre',
                        'description' => $description
                            )
                    );
                }
            }
        }
        if (!in_array($slug, $all_category_slug_array)) {
            $success = 'Course category imported successfully';
        } else {
           $error = 'Duplicate course category found';
        }
    }
}


//course import code 

if (isset($_POST['import_course'])) {

    $myrows = $wpdb->get_results("SELECT access_token FROM " . $table_name);
    $access_token = $myrows[0]->access_token;
    if ($access_token == '') {
        $error = "Please enter authentication detail";
    } else {

        $ch = curl_init($api_url . 'courses_api?access_token=' . $access_token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
                )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);

        $temp = json_decode($result);


        foreach ($temp as $_temp) {

            foreach ($_temp as $_temp1) {

                $lastid = $wpdb->get_col("SELECT ID FROM " . $post_table . " ORDER BY ID DESC LIMIT 0 , 1");
                $latestid = $lastid[0] + 1;
                $post_id = $_temp1->id;
                $post_date = $_temp1->created_at;
                $post_update_date = $_temp1->updated_at;
                $post_title = $_temp1->course_title;
                $description = $_temp1->description;
                $meta_teaser_value = $_temp1->teaser;
                $meta_tags_value = $_temp1->tags;
                $meta_is_featured_value = $_temp1->is_featured;
                $meta_status_value = $_temp1->status;
                $meta_course_fee_value = $_temp1->course_fee;
                $meta_sku_value = $_temp1->sku;
                $meta_tax_category_value = $_temp1->tax_category;
                $meta_allow_discounts_value = $_temp1->allow_discounts;
                $meta_subscription_value = $_temp1->subscription;
                $meta_booking_count_value = $_temp1->booking_count;
                $meta_course_category_id_value = $_temp1->course_category_id;


                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $postmeta_table . " where meta_key='_post_id' AND meta_value =" . $post_id . " ORDER BY post_id DESC LIMIT 0,1 ");

                $courseid = $get_existing_post_id[0]->post_id;

                if (empty($courseid)) {

                    $wpdb->insert($post_table, array(
                        "post_author" => '1',
                        "post_date" => $post_date,
                        "post_date_gmt" => $post_date,
                        "post_content" => $description,
                        "post_excerpt" => $meta_teaser_value,
                        "post_title" => $post_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => $post_title,
                        "post_modified" => $post_update_date,
                        "post_modified_gmt" => $post_update_date,
                        "menu_order" => '0',
                        "post_type" => 'courses',
                        "guid" => get_option('siteurl').'/?post_type=courses&#038;p=' . $latestid
                            //"guid"=>$site_url.'/?post_type=courses&#038;p='.$latestid
                    ));
                    $id = $wpdb->insert_id;

                    if ($post_id != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_post_id',
                            "meta_value" => $post_id
                        ));
                    }

                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $table_name2 . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");

                    $wpdb->insert($wpdb->prefix . "term_relationships", array(
                        "object_id" => $id,
                        "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                    ));



                    /* if($meta_teaser_value != '')
                      {
                      $wpdb->insert($postmeta_table, array(
                      "post_id" => $id,
                      "meta_key" => '_teaser',
                      "meta_value" => $meta_teaser_value

                      ));
                      } */

                    if ($meta_tags_value != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_tags',
                            "meta_value" => $meta_tags_value,
                        ));
                    }

                    if ($meta_is_featured_value != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_is_featured',
                            "meta_value" => $meta_is_featured_value,
                        ));
                    }


                    if ($meta_course_fee_value != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_course_fee',
                            "meta_value" => $meta_course_fee_value,
                        ));
                    }


                    if ($meta_sku_value != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_sku',
                            "meta_value" => $meta_sku_value,
                        ));
                    }

                    if ($meta_tax_category_value != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_tax_category',
                            "meta_value" => $meta_tax_category_value,
                        ));
                    }

                    if ($meta_allow_discounts_value != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_allow_discounts',
                            "meta_value" => $meta_allow_discounts_value,
                        ));
                    }

                    if ($meta_subscription_value != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_subscription',
                            "meta_value" => $meta_subscription_value,
                        ));
                    }
                }
            }
        }
        $success = "Courses imported successfully";
    }
}

//course update code 

if (isset($_POST['course_update'])) {

    $myrows = $wpdb->get_results("SELECT access_token FROM " . $table_name);
    $access_token = $myrows[0]->access_token;
    if ($access_token == '') {
        $error = "Please enter authentication detail";
    } else {
        $ch = curl_init($api_url . 'course_updates?access_token=' . $access_token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
                )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);

        $temp = json_decode($result);

        foreach ($temp as $_temp) {

            foreach ($_temp as $_temp1) {
                $post_id = $_temp1->id;
                $post_date = $_temp1->created_at;
                $post_update_date = $_temp1->updated_at;
                $post_title = $_temp1->course_title;
                $description = $_temp1->description;
                $meta_teaser_value = $_temp1->teaser;
                $meta_tags_value = $_temp1->tags;
                $meta_is_featured_value = $_temp1->is_featured;
                $meta_status_value = $_temp1->status;
                $meta_course_fee_value = $_temp1->course_fee;
                $meta_sku_value = $_temp1->sku;
                $meta_tax_category_value = $_temp1->tax_category;
                $meta_allow_discounts_value = $_temp1->allow_discounts;
                $meta_subscription_value = $_temp1->subscription;
                $meta_booking_count_value = $_temp1->booking_count;
                $meta_course_category_id_value = $_temp1->course_category_id;


                $myrows = $wpdb->get_results("SELECT post_id FROM " . $postmeta_table . " where meta_key='_post_id' AND meta_value =" . $post_id);
                $courseid = $myrows[0]->post_id;


                if ($courseid) {
                    $wpdb->update($post_table, array(
                        "post_date" => $post_date,
                        "post_date_gmt" => $post_date,
                        "post_content" => $description,
                        "post_title" => $post_title,
                        "post_name" => $post_title,
                        "post_modified" => $post_update_date,
                        "post_modified_gmt" => $post_update_date,
                        "post_type" => 'courses'
                            ), array('ID' => $courseid));

                    if ($meta_teaser_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_teaser_value
                                ), array("post_id" => $courseid,
                            "meta_key" => '_teaser'));
                    }

                    if ($meta_tags_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_tags_value), array("post_id" => $courseid,
                            "meta_key" => '_tags'));
                    }

                    if ($meta_is_featured_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_is_featured_value), array("post_id" => $courseid,
                            "meta_key" => '_is_featured'));
                    }


                    if ($meta_course_fee_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_course_fee_value), array("post_id" => $courseid,
                            "meta_key" => '_course_fee'));
                    }


                    if ($meta_sku_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_sku_value), array("post_id" => $courseid,
                            "meta_key" => '_sku'));
                    }

                    if ($meta_tax_category_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_tax_category_value), array("post_id" => $courseid,
                            "meta_key" => '_tax_category'));
                    }

                    if ($meta_allow_discounts_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_allow_discounts_value), array("post_id" => $courseid,
                            "meta_key" => '_allow_discounts'));
                    }

                    if ($meta_subscription_value != '') {
                        $wpdb->update($postmeta_table, array(
                            "meta_value" => $meta_subscription_value), array("post_id" => $courseid,
                            "meta_key" => '_subscription'));
                    }
                }
            }
        }
        $success = "Courses Updated successfully";
    }
}

//meeting import code 

if (isset($_POST['class_meeting'])) {
    $myrows = $wpdb->get_results("SELECT access_token FROM " . $table_name);
    $access_token = $myrows[0]->access_token;
    if ($access_token == '') {
        $error = "Please enter authentication detail";
    } else {
        $ch = curl_init($api_url . 'group_meetings?access_token=' . $access_token . '&id=1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
                )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);

        $temp = json_decode($result);

        foreach ($temp as $_temp) {

            foreach ($_temp as $_temp1) {
                $lastid = $wpdb->get_col("SELECT ID FROM " . $post_table . " ORDER BY ID DESC LIMIT 0 , 1");
                $latestid = $lastid[0] + 1;
				$post_id = $_temp1->id;
                $post_date = $_temp1->created_at;
                $post_update_date = $_temp1->updated_at;
                $post_title = $_temp1->title;
                $comment = $_temp1->comment;
                $meta_date_value = $_temp1->date;
                $meta_start_time_value = $_temp1->start_time;
                $meta_end_time_value = $_temp1->end_time;
                $meta_status_value = $_temp1->status;
                $meta_web_conference_value = $_temp1->web_conference;
                $meta_address_value = $_temp1->address;
                $meta_class_id_value = $_temp1->class_id;
                $meta_check_address_value = $_temp1->check_address;
                $meta_group_id_value = $_temp1->group_id;

				$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $postmeta_table . " where meta_key='_meeting_id' AND meta_value =" . $post_id . " ORDER BY post_id DESC LIMIT 0,1 ");

                $meetingid = $get_existing_post_id[0]->post_id;

                if (empty($meetingid)) {

                $wpdb->insert($post_table, array(
                    "post_author" => '1',
                    "post_date" => $post_date,
                    "post_date_gmt" => $post_date,
                    "post_content" => $comment,
                    "post_title" => $post_title,
                    "post_status" => 'publish',
                    "comment_status" => 'closed',
                    "ping_status" => 'closed',
                    "post_name" => $post_title,
                    "post_modified" => $post_update_date,
                    "post_modified_gmt" => $post_update_date,
                    "menu_order" => '0',
                    "post_type" => 'meetings',
                    "guid" => get_option('siteurl').'/?post_type=meetings&#038;p=' . $latestid
                ));
                $id = $wpdb->insert_id;

				if ($post_id != '') {
                        $wpdb->insert($postmeta_table, array(
                            "post_id" => $id,
                            "meta_key" => '_meeting_id',
                            "meta_value" => $post_id
                        ));
                    }

                if ($meta_date_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_date',
                        "meta_value" => $meta_date_value
                    ));
                }

                if ($meta_start_time_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_start_time',
                        "meta_value" => $meta_start_time_value
                    ));
                }

                if ($meta_end_time_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_end_time',
                        "meta_value" => $meta_end_time_value
                    ));
                }

                if ($meta_status_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_status',
                        "meta_value" => $meta_status_value
                    ));
                }

                if ($meta_web_conference_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_web_conference',
                        "meta_value" => $meta_web_conference_value
                    ));
                }

                if ($meta_address_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_address',
                        "meta_value" => $meta_address_value
                    ));
                }

                if ($meta_class_id_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_class_id',
                        "meta_value" => $meta_class_id_value
                    ));
                }

                if ($meta_check_address_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_check_address',
                        "meta_value" => $meta_check_address_value
                    ));
					//add_post_meta($id,'_check_address', $meta_check_address_value);
                }
                }

                if ($meta_group_id_value != '') {
                    $wpdb->insert($postmeta_table, array(
                        "post_id" => $id,
                        "meta_key" => '_group_id_value',
                        "meta_value" => $meta_group_id_value
                    ));
					//add_post_meta($id,'_group_id_value', $meta_group_id_value);
                }
				
				}
            }
        $success = "Meetings imported successfully";
    }
}

//Import course instructors

if (isset($_POST['import_instructors'])) {
    $myrows = $wpdb->get_results("SELECT access_token, account_id FROM " . $table_name);
    $access_token = $myrows[0]->access_token;
    $account_id = $myrows[0]->account_id;


    if ($access_token == '') {
        $error = "Please enter authentication detail";
    } else {
        $ch = curl_init($api_url . 'instructors?access_token=' . $access_token . '&account_id=' . $account_id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
                )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        $temp = json_decode($result);

        foreach ($temp->data as $k => $v) {
            $random_password = wp_generate_password('pass', false);
            $user_id = wp_create_user($v->email, $random_password, $v->email);                        
            $user = get_user_by('id', $user_id);
            
            update_user_meta($user_id, 'intructor_id', $user_id);
            update_user_meta($user_id, 'privacy_policy', $v->privacy_policy);
            update_user_meta($user_id, 'provider', $v->provider);
            update_user_meta($user_id, 'uid', $v->uid);
            update_user_meta($user_id, 'system_admin', false);
            update_user_meta($user_id, 'created_at', $v->created_at);
            update_user_meta($user_id, 'updated_at', $v->updated_at);
            
            $user->remove_role( 'subscriber' );
            $user->add_role('instructor');
        }
        $success ='Intructors imported successfully';
    }    
}
?>
<title>Adept LMS</title>
<h1>Adept LMS</h1>
<div class="wrap">
    <form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
	<?php if($success != ''){ ?>
	<div class="updated notice notice-success is-dismissible" id="message"><p><?php echo $success;?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
	<?php } ?>
	<?php if($error != ''){ ?>
	<div class="updated notice notice-error error is-dismissible" id="message"><p><?php echo $error;?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
	<?php } ?>
        <table width="1004" class="form-table">
            <tbody>
                <tr>
                    <th width="115"><?php esc_html_e('Import Categories:') ?></th>
                    <td width="877">
                        <input type="submit" name="import_categories" value="Import Categories"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Import Course:') ?></th>
                    <td width="877">
                        <input type="submit" name="import_course" value="Import Course"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Import Intructors:') ?></th>
                    <td width="877">
                        <input type="submit" name="import_instructors" value="Import Instructors"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Course Update:') ?></th>
                    <td width="877">
                        <input type="submit" name="course_update" value="Course Update"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Class Meeting:') ?></th>
                    <td width="877">
                        <input type="submit" name="class_meeting" value="Class Meeting"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Short Code for Meeting:') ?></th>
                    <td width="877">
                        <?php esc_html_e('meetings') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>