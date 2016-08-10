<?php

error_reporting(E_ALL); 
ini_set('display_errors', 1);

 include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );

Class WP_Lib {

    function postdata($url, $data) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
        
        //because of https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        
        $result = curl_exec($ch);
		//echo $result; exit;
        $resultdata = json_decode($result);
		//echo $resultdata; die();

        return $resultdata;
    }

	function putdata($url, $data) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
		
        $resultdata = json_decode($result);
		//echo $resultdata; die();

        return $resultdata;
    }

	
    function getdata($url) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
                )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $resultdata = json_decode($result);
        //pre($resultdata); exit;
		//print_r($resultdata);
        return $resultdata;
    }

    function import_category($url) {
		//echo $url; die();
        global $wpdb, $sitepress;

		$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
		$plugin2 = 'wpml-translation-management/plugin.php';
        $temp = $this->getdata($url);
        //pre($temp); exit;
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
        $taxonomy = 'genre';
        if ($temp) {
            foreach ($temp->data as $_temp1) {
                $name = $_temp1->name;
                $description = $_temp1->name;
                $slug = $_temp1->id . '_' . sanitize_title($name);
                $_POST['icl_tax_' . $taxonomy . '_language'] = $language_code = $site_default_language;
                $catdata = get_term_by('slug', $slug, 'genre');

                if (!isset($catdata->term_id)) {
                    $fi_category = wp_insert_term(
                            $name, // the term 
                            'genre', // the taxonomy
                            array(
                        'description' => $description,
                        'parent' => 0,
                        'slug' => $slug
                            )
                    );

                    // Fetching WPML's trid
                    if ($fi_category->errors['term_exists'][0] == '' && is_plugin_active($plugin1) && is_plugin_active($plugin2)) {

                        $trid = $sitepress->get_element_trid($fi_category['term_taxonomy_id'], 'tax_' . $taxonomy);

                        // Updating icl_translations table to connect the two terms

                        $updates = array(
                            'trid' => $trid,
                            'language_code' => $site_default_language
                        );
                        $where = array(
                            'element_type' => 'tax_' . $taxonomy,
                            'element_id' => $fi_category['term_taxonomy_id']
                        );

                        $wpdb->update($wpdb->prefix . 'icl_translations', $updates, $where);
                    }
                }
				
				$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
				$plugin2 = 'wpml-translation-management/plugin.php';

            }
            return $fi_category->errors['term_exists'][0];
        } return "No Categories for import";
    }

    function update_course_to_live($url, $data) {
        $temp = $this->postdata($url, $data);
        return "Update course to live site";
    }

    function import_course($url) {		
        global $wpdb, $sitepress;

        include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
        $all_courses_list = $this->getdata($url);					
        $get_all_languages = $this->get_languages();
        if(isset($_GET["show_data"])) {
            pre($all_courses_list); exit;
        }

		$site_default_language = $get_all_languages->default_language;
        if($site_default_language == "no" ) {
            $site_default_language = "nb";
        }
        
        
		if (!empty($all_courses_list->data)) {
           
			foreach ($all_courses_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                
                if ($v->teaser == '') {
                    $v->teaser = $v->description;
                }

                $get_existing_post_id = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND meta_value ='" . $site_default_language . "_" . $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");

                if (trim($get_existing_post_id) == "") {

                    $my_post = array(
                        "post_author" => $adept_author_value,
                        "post_content" => $v->description,
                        "post_excerpt" => $v->teaser,
                        "post_title" => $v->course_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->course_title),
                        "menu_order" => '0',
                        "post_type" => 'courses',
                        'guid' => ''
                    );

                    

                    
                    $new_post_id = array();
                    //code added by pramod
                    //since we have inserted the post in default languge, lets insert its translations
                    if(is_array($v->translation) && count($v->translation)>0) {
                        //pre($v->translation); exit;
                        foreach($v->translation as $locale) {
                            //pre($locale); exit;
                            if($locale->locale == "no") {
                                $locale->locale = "nb";
                            }
                            
                            $_POST['icl_post_language'] = $locale->locale;
                            
                            if(!isset($post_id)) {
                                $post_id = wp_insert_post($my_post);                                
                            }
                            else {
                            
                                $new_id = wpa_add_post_language($post_id , "courses" , $locale->locale , $locale->course_title , $locale->description , $locale->teaser);
                                //pre($locale);
                                //echo $new_id."<br><br>";

                                $new_post_id[] = $new_id;
                           
                               
                            }
                           
                        }

                    }

                    $this->course_insert_extra_information($post_id , $all_courses_list->data );
                    foreach($new_post_id as $id) {
                     wpa_translate_copy($post_id , $id);
                    }


                }
                else {
                    //echo "here"; echo $get_existing_post_id;
                    $this->update_course($v , $get_existing_post_id);
                }
                unset($post_id);

            }
            $this->unpublished_posts($all_courses_list->data , "courses");
            return "Courses imported successfully";
        }
        else {
            $this->unpublish_all_posts("courses");
        }

        return "No Courses for import";
    
    }

    function course_insert_extra_information($post_id , $data ) {
        //pre($data);
        global $wpdb;
        $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $data->course_category_id . "_%'");                              
        
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;

        if(count($data->groups)>0){
            $group_ids = array();
            foreach($data->groups as $key => $value){
                $get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_common_id' AND  meta_value ='{$value->group_id}' LIMIT 0,1 ");
                if($get_group_id) {
                    $groupid = $get_group_id[0]->post_id;
                    $group_ids[] = $groupid;
                }

            }
            update_post_meta( $post_id , '_group_ids', $group_ids );
        }
        
        
        if(count($data->instructors)>0){
            $instructor_ids = array();
            foreach($data->instructors as $key => $value){
                $instructor_ids[] = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND  meta_value ='" . $value->instructor_id."' LIMIT 0,1 ");
            }
            update_post_meta($post_id , '_instructor_ids', $instructor_ids);
        }

        wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
        add_post_meta($post_id, '_post_id', $site_default_language . "_" . $data->id);
        add_post_meta($post_id, '_tags', $data->tags);
        add_post_meta($post_id, '_is_featured', $data->is_featured);
        add_post_meta($post_id, '_course_fee', $data->course_fee);
        add_post_meta($post_id, '_sku', $data->sku);
        if(isset($data->tax_category)) {
            add_post_meta($post_id, '_tax_category', $data->tax_category);
        }
        if(isset($data->allow_discounts)) {
            add_post_meta($post_id, '_allow_discounts', $data->allow_discounts);
        }
        if(isset($data->subscription)) {
            add_post_meta($post_id, '_subscription', $data->subscription);
        }
        if(isset($data->booking_count)) {
            add_post_meta($post_id, '_booking_count', $data->booking_count);
        }
        add_post_meta($post_id, '_image_url', $data->image_url);
        add_post_meta($post_id, '_course_url', $data->course_url);
        add_post_meta($post_id, '_adept_api_id', $data->id);
        
        // Insert category id in courses
        $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $data->course_category_id . "_%'");

        $wpdb->insert($wpdb->prefix . "term_relationships", array(
            "object_id" => $post_id,
            "term_taxonomy_id" => $check_term_id_slug[0]->term_id
        ));

    }

    function update_course($course , $old_post) {
       
        //pre($course);
        $post_type = "courses";
        foreach($course->translation as $locale) {

            if($locale->locale == "no") {
                $locale->locale = "nb";
            }

            $post_exists = $this->does_post_exists($course->id , $locale->locale , "courses");
            $post = array(  
                            "post_title" =>  $locale->course_title,
                            "post_content" => $locale->description,
                            "post_excerpt" => $locale->teaser,
                            "post_type" => $post_type
                        );

            if($post_exists) {
                //if post exists then it will be updated only
                $post["ID"] = $post_exists; 
                $post_id = wp_insert_post($post);
            }
            else {

                $trigid = wpml_get_content_trid('post_' . $post_type, $old_post); // Find Transalation ID function from WPML API. 
                $_POST['icl_post_language'] = $locale->locale; // Set another language
                $sitepress->set_element_language_details($tpropertyid1, 'post_' . $post_type, $trigid, $locale->locale); // Change this post 
                
                $post_id = wp_insert_post($post);

            }
            if($post_id) {
                $this->course_insert_extra_information($post_id , $course);
            }
        }

       
    }

    function does_post_exists($id, $locale , $type) {
        //echo "$id, $locale"; exit;
        global $wpdb;
        $wpdb->show_errors();
        $post_ids = $wpdb->get_col("select post_id from {$wpdb->prefix}postmeta m, {$wpdb->prefix}posts p where p.ID = m.post_id and p.post_type='$type' and meta_key='_adept_api_id' and meta_value='$id'  ");
        if(is_array($post_ids) && count($post_ids)>0 ) {
            $post_ids = implode(",", $post_ids);
            
            return $wpdb->get_var("select element_id from {$wpdb->prefix}icl_translations where language_code='$locale' and element_id in($post_ids) ");

        }
        else {
            return false;
        }
    }

    function unpublished_posts($posts , $post_type) {
        global $wpdb, $sitepress;
        //pre($posts); exit;
        $adept_ids_arr = array();
        $all_ids = array();

        /*
        Algorithm - step 1 - unpublish missing posts:
        1. $adept_ids = get all the posts which are 'active'
        2. $all_ids = get all posts
        3. $to_be_drafted = $all_ids - $adept_ids
        */

        //all the courses which are  active
        foreach($posts as $post) {
            $adept_ids_arr[] =  $post->id;
        }
            
        $adept_ids = implode(",", $adept_ids_arr); 

        if(!empty($adept_ids)) {


            $active_ids = $wpdb->get_col("select p.ID from {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta m where p.post_type='$post_type' and m.post_id = p.ID and m.meta_key='_adept_api_id' and m.meta_value in ($adept_ids) ");

            $all_courses = $wpdb->get_col("select ID from {$wpdb->prefix}posts where post_type='$post_type'");

            $to_be_drafted =  array_diff($all_courses , $active_ids);

            $to_be_drafted = implode(",", $to_be_drafted);
            if(!empty($to_be_drafted)) {
                $wpdb->query("update {$wpdb->prefix}posts set post_status='draft' where ID in ($to_be_drafted)");
            }

        }

         /*
        =REVERSE OF ALGO 1= 
        Algorithm - step 2 - undraft posts:
        1. $adept_draft_ids = get all the posts which are ('active' in adeptlms)
        2. $all_draft_ids = get all posts which are in draft
        3. $to_be_published = $adept_ids [intersection] $all_draft_ids
        */

        $active_ids = $wpdb->get_col("select DISTINCT p.ID from {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta m where p.post_type='$post_type' and m.post_id = p.ID and m.meta_key='_adept_api_id' and m.meta_value in ($adept_ids) ");

        $all_draft_ids = $wpdb->get_col("select ID from {$wpdb->prefix}posts where post_type='$post_type' and post_status ='draft' ");
        $to_be_published =  array_intersect($active_ids, $all_draft_ids);
        if(count($to_be_published)>0) {
            $to_be_published = implode(",", $to_be_published);
            $wpdb->query("update {$wpdb->prefix}posts set post_status='publish' where ID in ($to_be_published)");
        }
    }




    function unpublish_all_posts($post_type) {
        global $wpdb;
        $wpdb->query("update {$wpdb->prefix}posts set post_status='draft' where post_type='$post_type'");
    }
    
    
    function import_meeting($url) {
        global $wpdb;
        $adept_author_value = get_option('adept_author');
        $all_meeting_list = $this->getdata($url);


        $meetings_flat_data = $this->flatten_meetings_array($all_meeting_list->data);

        if (!empty($all_meeting_list->data)) {
        
             foreach ($all_meeting_list->data as $k => $v) {
                
                
                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND meta_value ='". $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
				
				$postid = $get_existing_post_id[0]->post_id;
	
                if (trim($postid) == "") {

					foreach ($v->meetings as $key => $value) 
					{

					    $my_post = array(
                            "post_author" => $adept_author_value,
                            "post_content" => $value->description,
                            "post_excerpt" => $value->description,
                            "post_title" => $value->title,
                            "post_status" => 'publish',
                            "comment_status" => 'closed',
                            "ping_status" => 'closed',
                            "post_name" => sanitize_title($value->title),
                            "post_modified" => $value->updated_at,
                            "post_modified_gmt" => $value->updated_at,
                            "menu_order" => '0',
                            "post_type" => 'meetings',
                            'guid' => ''
                        );
					
                        $post_id = wp_insert_post($my_post);
    					//echo $post_id; die();
				
                        add_post_meta($post_id, '_meeting_id', $value->id);
                        add_post_meta($post_id, '_adept_api_id', $value->id);
                        add_post_meta($post_id, '_start_time', $value->start_time);
                        add_post_meta($post_id, '_duration', $value->duration);
    					add_post_meta($post_id, '_end_time', $value->end_time);
                        add_post_meta($post_id, '_status', $value->status);
                        add_post_meta($post_id, '_web_conference', $value->web_conference);
                        add_post_meta($post_id, '_address', $value->address);
                        add_post_meta($post_id, '_check_address', $value->check_address);
                        add_post_meta($post_id, '_group_id', $value->group_id);
                        add_post_meta($post_id, '_user_id', $value->user_id);
                        add_post_meta($post_id, '_kind', $value->kind);
                        add_post_meta($post_id, '_video_conference_account_id', $value->video_conference_account_id);
                        add_post_meta($post_id, '_video_conference_url', $value->video_conference_url);
                        add_post_meta($post_id, '_video_conference_uid', $value->video_conference_uid);
                 
                    }
				}
            }


            $this->unpublished_posts($meetings_flat_data , "meetings");   

            return "Meetings imported successfully";
        }
        else {
            $this->unpublish_all_posts("meetings");   
        }


        return "No Meetings for import";
    }

    function flatten_meetings_array($groups) {
        $return = array();

        foreach($groups as $group) {
            foreach($group->meetings as $meeting) {
                $return[] = $meeting;
            }
        }

        return($return);
    }

    function update_meeting($url) {
        global $wpdb,$sitepress;
        $all_meeting_list = $this->getdata($url);
        $adept_author_value = get_option('adept_author');
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
        if (!empty($all_meeting_list->data)) {
            foreach ($all_meeting_list->data as $k => $v) {
                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND meta_value ='" . $site_default_language . "_" . $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;
                if (trim($postid) == "") {
                    $my_post = array(
                        "post_author" => $adept_author_value,
                        "post_date" => $v->created_at,
                        "post_date_gmt" => $v->created_at,
                        "post_content" => $v->comment,
                        "post_excerpt" => $v->comment,
                        "post_title" => $v->title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->title),
                        "post_modified" => $v->updated_at,
                        "post_modified_gmt" => $v->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'meetings',
                        'guid' => ''
                    );
                    // Insert the post into the database.
                    $post_id = wp_insert_post($my_post, $wp_error);
                    add_post_meta($post_id, '_meeting_id', $site_default_language . "_" . $v->id);
                    add_post_meta($post_id, '_date', $v->date);
                    add_post_meta($post_id, '_start_time', $v->start_time);
                    add_post_meta($post_id, '_end_time', $v->end_time);
                    add_post_meta($post_id, '_status', $v->status);
                    add_post_meta($post_id, '_web_conference', $v->web_conference);
                    add_post_meta($post_id, '_address', $v->address);
                    add_post_meta($post_id, '_check_address', $v->check_address);
                    add_post_meta($post_id, '_group_id', $v->group_id);
                    add_post_meta($post_id, '_user_id', $v->user_id);
                    add_post_meta($post_id, '_kind', $v->kind);
                    add_post_meta($post_id, '_video_conference_account_id', $v->video_conference_account_id);
                    add_post_meta($post_id, '_video_conference_url', $v->video_conference_url);
                    add_post_meta($post_id, '_video_conference_uid', $v->video_conference_uid);
                    $_POST['icl_post_language'] = $language_code = $site_default_language;
                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                    // Multi translations
					$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
				$plugin2 = 'wpml-translation-management/plugin.php';

				if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
                    if (!empty($v->translation)) {
                        foreach ($v->translation as $a => $b) {
                            if ($b->locale != $site_default_language) {

                                $adept_author_value = get_option('adept_author');

                                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND  meta_value ='" . $b->locale . '_' . $b->meeting_id . '_' . $b->id . "'  ORDER BY post_id DESC LIMIT 0,1 ");
                                $postid = $get_existing_post_id[0]->post_id;

                                if (trim($postid) == "") {

                                    $my_post = array(
                                        "post_author" => $adept_author_value,
                                        "post_date" => $b->created_at,
                                        "post_date_gmt" => $b->created_at,
                                        "post_content" => $b->comment,
                                        "post_excerpt" => $b->comment,
                                        "post_title" => $b->title,
                                        "post_status" => 'publish',
                                        "comment_status" => 'closed',
                                        "ping_status" => 'closed',
                                        "post_name" => sanitize_title($b->title),
                                        "post_modified" => $b->updated_at,
                                        "post_modified_gmt" => $b->updated_at,
                                        "menu_order" => '0',
                                        "post_type" => 'meetings',
                                        'guid' => ''
                                    );


                                    // Insert the post into the database.
                                    $post_id = wp_insert_post($my_post, $wp_error);

                                    add_post_meta($post_id, '_meeting_id', $b->locale . '_' . $b->meeting_id . '_' . $b->id);
                                    add_post_meta($post_id, '_date', $b->date);
                                    add_post_meta($post_id, '_start_time', $b->start_time);
                                    add_post_meta($post_id, '_end_time', $b->end_time);
                                    add_post_meta($post_id, '_status', $b->status);
                                    add_post_meta($post_id, '_web_conference', $b->web_conference);
                                    add_post_meta($post_id, '_address', $b->address);
                                    add_post_meta($post_id, '_check_address', $b->check_address);
                                    add_post_meta($post_id, '_group_id', $b->group_id);
                                    add_post_meta($post_id, '_user_id', $b->user_id);
                                    add_post_meta($post_id, '_kind', $b->kind);
                                    add_post_meta($post_id, '_video_conference_account_id', $b->video_conference_account_id);
                                    add_post_meta($post_id, '_video_conference_url', $b->video_conference_url);
                                    add_post_meta($post_id, '_video_conference_uid', $b->video_conference_uid);

                                    $_POST['icl_post_language'] = $language_code = $b->locale;
                                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                                }
                            }
                        }
                    }
				}
                } else {
                    $my_post = array(
                        "ID" => $postid,
                        "post_author" => $adept_author_value,
                        "post_date" => $v->created_at,
                        "post_date_gmt" => $v->created_at,
                        "post_content" => $v->comment,
                        "post_excerpt" => $v->comment,
                        "post_title" => $v->title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->title),
                        "post_modified" => $v->updated_at,
                        "post_modified_gmt" => $v->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'meetings',
                        'guid' => ''
                    );


                    // Insert the post into the database.
                    $post_id = wp_update_post($my_post, $wp_error);


                    update_post_meta($post_id, '_meeting_id', $site_default_language . "_" . $v->id);
                    update_post_meta($post_id, '_date', $v->date);
                    update_post_meta($post_id, '_start_time', $v->start_time);
                    update_post_meta($post_id, '_end_time', $v->end_time);
                    update_post_meta($post_id, '_status', $v->status);
                    update_post_meta($post_id, '_web_conference', $v->web_conference);
                    update_post_meta($post_id, '_address', $v->address);
                    update_post_meta($post_id, '_check_address', $v->check_address);
                    update_post_meta($post_id, '_group_id', $v->group_id);
                    update_post_meta($post_id, '_user_id', $v->user_id);
                    update_post_meta($post_id, '_kind', $v->kind);
                    update_post_meta($post_id, '_video_conference_account_id', $v->video_conference_account_id);
                    update_post_meta($post_id, '_video_conference_url', $v->video_conference_url);
                    update_post_meta($post_id, '_video_conference_uid', $v->video_conference_uid);
                    $_POST['icl_post_language'] = $language_code = $site_default_language;
                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                    // Multi translations
					$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
				$plugin2 = 'wpml-translation-management/plugin.php';

				if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
                    if (!empty($v->translation)) {
                        foreach ($v->translation as $a => $b) {
                            if ($b->locale != $site_default_language) {

                                $adept_author_value = get_option('adept_author');

                                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND  meta_value ='" . $b->locale . '_' . $b->meeting_id . '_' . $b->id . "'  ORDER BY post_id DESC LIMIT 0,1 ");
                                $postid = $get_existing_post_id[0]->post_id;

                                if (trim($postid) == "") {

                                    $my_post = array(
                                        "ID" => $postid,
                                        "post_author" => $adept_author_value,
                                        "post_date" => $b->created_at,
                                        "post_date_gmt" => $b->created_at,
                                        "post_content" => $b->comment,
                                        "post_excerpt" => $b->comment,
                                        "post_title" => $b->title,
                                        "post_status" => 'publish',
                                        "comment_status" => 'closed',
                                        "ping_status" => 'closed',
                                        "post_name" => sanitize_title($b->title),
                                        "post_modified" => $b->updated_at,
                                        "post_modified_gmt" => $b->updated_at,
                                        "menu_order" => '0',
                                        "post_type" => 'meetings',
                                        'guid' => ''
                                    );


                                    // Insert the post into the database.
                                    $post_id = wp_update_post($my_post, $wp_error);

                                    update_post_meta($post_id, '_meeting_id', $b->locale . '_' . $b->meeting_id . '_' . $b->id);
                                    update_post_meta($post_id, '_date', $b->date);
                                    update_post_meta($post_id, '_start_time', $b->start_time);
                                    update_post_meta($post_id, '_end_time', $b->end_time);
                                    update_post_meta($post_id, '_status', $b->status);
                                    update_post_meta($post_id, '_web_conference', $b->web_conference);
                                    update_post_meta($post_id, '_address', $b->address);
                                    update_post_meta($post_id, '_check_address', $b->check_address);
                                    update_post_meta($post_id, '_group_id', $b->group_id);
                                    update_post_meta($post_id, '_user_id', $b->user_id);
                                    update_post_meta($post_id, '_kind', $b->kind);
                                    update_post_meta($post_id, '_video_conference_account_id', $b->video_conference_account_id);
                                    update_post_meta($post_id, '_video_conference_url', $b->video_conference_url);
                                    update_post_meta($post_id, '_video_conference_uid', $b->video_conference_uid);

                                    $_POST['icl_post_language'] = $language_code = $b->locale;
                                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                                }
                            }
                        }
                    }
                }
				}
            }
            return "Meetings imported successfully";
        }
        return "No Meetings for Update";
    }

    function import_groups($url) {
        global $wpdb,$sitepress;
        //echo $url; exit;
        $all_courses_list = $this->getdata($url);
        //pre($all_courses_list); exit;
        $adept_author_value = get_option('adept_author');
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
		//echo $site_default_language; exit();
        if (!empty($all_courses_list->data)) {

            foreach ($all_courses_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND meta_value ='" . $site_default_language . "_" . $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;

                if (trim($postid) == "") {


                    
					/*$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
				    $plugin2 = 'wpml-translation-management/plugin.php';*/

                    if(1){
    				//if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
                        if (!empty($v->translation)) {
                            
                            foreach ($v->translation as $a => $b) {

                                if($b->locale == "no") {
                                    $b->locale = "nb";
                                }
                                $_POST['icl_post_language'] = $language_code = $b->locale;

                                if(!isset($post_id)) {

                                    if($b->description == "") {
                                        $b->description = " ";
                                    }
                                    $my_post = array(
                                        "post_author" => $adept_author_value,
                                        "post_content" => $b->description,
                                        "post_excerpt" => $b->description,
                                        "post_title" => $v->group_title,
                                        "post_status" => 'publish',
                                        "post_name" => sanitize_title($v->group_title),
                                        "post_type" => 'groups',
                                    );

                                    // Insert the post into the database.
                                    $post_id = wp_insert_post($my_post, $wp_error);
                                 
                                    if(count($v->courses)>0){
                                        
                                        foreach($v->courses as $key => $value){

                                            $get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='en_" . $value->course_id."' LIMIT 0,1 ");
                                            //echo $value->course_id; die();    
                                            $courseid = $get_course_id[0]->post_id;
                                        
                                            add_post_meta( $post_id , '_course_ids', $courseid );
                                        }
                                    }
                                    add_post_meta($post_id, '_group_id', $site_default_language . "_" . $v->id);
                                    add_post_meta($post_id, '_tags', $v->tags);
                                    add_post_meta($post_id, '_course_fee', $v->course_fee);
                                    add_post_meta($post_id, '_taxable', $v->taxable);
                                    add_post_meta($post_id, '_published', $v->published);
                                    add_post_meta($post_id, '_allow_bookings', $v->allow_bookings);
                                    add_post_meta($post_id, '_start_date', $v->start_date);
                                    add_post_meta($post_id, '_end_date', $v->end_date);
                                    add_post_meta($post_id, '_reg_date', $v->reg_date);
                                    add_post_meta($post_id, '_address', $v->address);
                                    add_post_meta($post_id, '_seats', $v->seats);
                                    add_post_meta($post_id, '_hide_if_full', $v->hide_if_full);
                                    add_post_meta($post_id, '_show_seats_left', $v->show_seats_left);
                                    add_post_meta($post_id, '_lessons', $v->lessons);
                                    add_post_meta($post_id, '_status', $v->status);
                                    add_post_meta($post_id, '_subscription_plan_id', $v->subscription_plan_id);
                                    add_post_meta($post_id, '_group_common_id',  $v->id);
                                    add_post_meta($post_id, '_adept_api_id',  $v->id);
                                }
                                else {


                                    //if ($b->locale != $site_default_language) {

                                        $adept_author_value = get_option('adept_author');

                                        $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $b->locale . '_' . $b->_group_id . '_' . $b->id . "'  ORDER BY post_id DESC LIMIT 0,1 ");
                                        $postid = $get_existing_post_id[0]->post_id;

                                        if (trim($postid) == "") {
                                            if($b->description == "") {
                                                $b->description = " ";
                                            }
                                           

                                            // Insert the post into the database.
                                            $post_id_new = wpa_add_post_language($post_id, "groups", $b->locale, $b->group_title, $b->description , $b->description);
                                            
                                            if(count($b->courses)>0){
                                                foreach($b->courses as $key => $value){
                                                    $get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $value->course_id."' LIMIT 0,1 ");
                                                    $courseid = $get_course_id[0]->post_id;
                                                    add_post_meta( $post_id , '_course_ids', $courseid );
                                                }
                                            }

                                            add_post_meta($post_id_new, '_group_id', $b->locale . '_' . $b->_group_id . '_' . $b->id);

                                            $metas = array(
                                                            "_tags",
                                                            "_course_fee",
                                                            "_taxable",
                                                            "_published",
                                                            "_allow_bookings",
                                                            "_start_date",
                                                            "_end_date",
                                                            "_reg_date",
                                                            "_seats",
                                                            "_hide_if_full",
                                                            "_show_seats_left",
                                                            "_lessons",
                                                            "_status",
                                                            "_subscription_plan_id",
                                                            "_adept_api_id",
                                                            "_course_ids",);
                                            
                                            wpa_duplicate_meta( $metas , $post_id , $post_id_new);
                                            

                                        }
                                        
                                    //}//
                                }
                            }
                            unset($post_id);
                        }
                    }
                }
            }
            $this->unpublished_posts($all_courses_list->data , "groups" );
            return "Groups imported successfully";
        }
        else {
            $this->unpublish_all_posts("groups");
        }

        return "No Groups for import";
    }

    function update_groups($url) {
        global $wpdb;

        $all_courses_list = $this->getdata($url);
        $adept_author_value = get_option('adept_author');
        //require( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );

        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
        
        if (!empty($all_courses_list->data)) {

            foreach ($all_courses_list->data as $k => $v) {
                
                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND meta_value ='" . $site_default_language . "_" . $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;
                
                if (trim($postid) == "") {

                    $my_post = array(
                        "post_author" => $adept_author_value,
                        "post_date" => $v->created_at,
                        "post_date_gmt" => $v->created_at,
                        "post_content" => $v->description,
                        "post_excerpt" => $v->description,
                        "post_title" => $v->group_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->group_title),
                        "post_modified" => $v->updated_at,
                        "post_modified_gmt" => $v->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'groups',
                        'guid' => ''
                    );


                    // Insert the post into the database.
                    $post_id = wp_insert_post($my_post, $wp_error);
					if(count($v->courses)>0){
						delete_post_meta( $post_id , '_course_ids');
						foreach($v->courses as $key => $value){
							$get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $value->course_id."' LIMIT 0,1 ");
                            $courseid = $get_course_id[0]->post_id;
							add_post_meta( $post_id , '_course_ids', $courseid );
						}
					}

                    add_post_meta($post_id, '_group_id', $site_default_language . "_" . $v->id);
                    add_post_meta($post_id, '_tags', $v->tags);
                    add_post_meta($post_id, '_course_fee', $v->course_fee);
                    add_post_meta($post_id, '_taxable', $v->taxable);
                    add_post_meta($post_id, '_published', $v->published);
                    add_post_meta($post_id, '_allow_bookings', $v->allow_bookings);
                    add_post_meta($post_id, '_start_date', $v->start_date);
                    add_post_meta($post_id, '_end_date', $v->end_date);
                    add_post_meta($post_id, '_reg_date', $v->reg_date);
                    add_post_meta($post_id, '_seats', $v->seats);
                    add_post_meta($post_id, '_hide_if_full', $v->hide_if_full);
                    add_post_meta($post_id, '_show_seats_left', $v->show_seats_left);
                    add_post_meta($post_id, '_lessons', $v->lessons);
                    add_post_meta($post_id, '_status', $v->status);
                    add_post_meta($post_id, '_subscription_plan_id', $v->subscription_plan_id);
                    $_POST['icl_post_language'] = $language_code = $site_default_language;
                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                    // Multi translations
					$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
					$plugin2 = 'wpml-translation-management/plugin.php';

				if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
                    if (!empty($v->translation)) {
                        foreach ($v->translation as $a => $b) {
                            if ($b->locale != $site_default_language) {

                                $adept_author_value = get_option('adept_author');

                                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $b->locale . '_' . $b->_group_id . '_' . $b->id . "'  ORDER BY post_id DESC LIMIT 0,1 ");
                                $postid = $get_existing_post_id[0]->post_id;

                                if (trim($postid) == "") {

                                    $my_post = array(
                                        "post_author" => $adept_author_value,
                                        "post_date" => $b->created_at,
                                        "post_date_gmt" => $b->created_at,
                                        "post_content" => $b->description,
                                        "post_excerpt" => $b->description,
                                        "post_title" => $b->group_title,
                                        "post_status" => 'publish',
                                        "comment_status" => 'closed',
                                        "ping_status" => 'closed',
                                        "post_name" => sanitize_title($b->group_title),
                                        "post_modified" => $b->updated_at,
                                        "post_modified_gmt" => $b->updated_at,
                                        "menu_order" => '0',
                                        "post_type" => 'groups',
                                        'guid' => ''
                                    );


                                    // Insert the post into the database.
                                    $post_id = wp_insert_post($my_post, $wp_error);
									if(count($v->courses)>0){
										delete_post_meta( $post_id , '_course_ids');
										foreach($v->courses as $key => $value){
											$get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $value->course_id."' LIMIT 0,1 ");
											$courseid = $get_course_id[0]->post_id;
											add_post_meta( $post_id , '_course_ids', $courseid );
										}
									}

                                    add_post_meta($post_id, '_group_id', $b->locale . '_' . $b->_group_id . '_' . $b->id);
                                    add_post_meta($post_id, '_tags', $b->tags);
                                    add_post_meta($post_id, '_course_fee', $b->course_fee);
                                    add_post_meta($post_id, '_taxable', $b->taxable);
                                    add_post_meta($post_id, '_published', $b->published);
                                    add_post_meta($post_id, '_allow_bookings', $b->allow_bookings);
                                    add_post_meta($post_id, '_start_date', $b->start_date);
                                    add_post_meta($post_id, '_end_date', $b->end_date);
                                    add_post_meta($post_id, '_reg_date', $b->reg_date);
                                    add_post_meta($post_id, '_seats', $b->seats);
                                    add_post_meta($post_id, '_hide_if_full', $b->hide_if_full);
                                    add_post_meta($post_id, '_show_seats_left', $b->show_seats_left);
                                    add_post_meta($post_id, '_lessons', $b->lessons);
                                    add_post_meta($post_id, '_status', $b->status);
                                    add_post_meta($post_id, '_subscription_plan_id', $b->subscription_plan_id);

                                    $_POST['icl_post_language'] = $language_code = $b->locale;
                                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                                }
                            }
                        }
                    }
				}
                } else {
                    $my_post = array(
                        "ID" => $postid,
                        "post_author" => $adept_author_value,
                        "post_date" => $v->created_at,
                        "post_date_gmt" => $v->created_at,
                        "post_content" => $v->description,
                        "post_excerpt" => $v->description,
                        "post_title" => $v->group_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->group_title),
                        "post_modified" => $v->updated_at,
                        "post_modified_gmt" => $v->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'groups',
                        'guid' => ''
                    );


                    // Insert the post into the database.
                    $post_id = wp_update_post($my_post, $wp_error);
					if(count($v->courses)>0){
						delete_post_meta( $post_id , '_course_ids');
						foreach($v->courses as $key => $value){
							$get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $value->course_id."' LIMIT 0,1 ");
                            $courseid = $get_course_id[0]->post_id;
							add_post_meta( $post_id , '_course_ids', $courseid );
						}
					}

                    update_post_meta($post_id, '_group_id', $site_default_language . "_" . $v->id);
                    update_post_meta($post_id, '_tags', $v->tags);
                    update_post_meta($post_id, '_course_fee', $v->course_fee);
                    update_post_meta($post_id, '_taxable', $v->taxable);
                    update_post_meta($post_id, '_published', $v->published);
                    update_post_meta($post_id, '_allow_bookings', $v->allow_bookings);
                    update_post_meta($post_id, '_start_date', $v->start_date);
                    update_post_meta($post_id, '_end_date', $v->end_date);
                    update_post_meta($post_id, '_reg_date', $v->reg_date);
                    update_post_meta($post_id, '_seats', $v->seats);
                    update_post_meta($post_id, '_hide_if_full', $v->hide_if_full);
                    update_post_meta($post_id, '_show_seats_left', $v->show_seats_left);
                    update_post_meta($post_id, '_lessons', $v->lessons);
                    update_post_meta($post_id, '_status', $v->status);
                    update_post_meta($post_id, '_subscription_plan_id', $v->subscription_plan_id);
                    $_POST['icl_post_language'] = $language_code = $site_default_language;
                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                    // Multi translations
					$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
				    $plugin2 = 'wpml-translation-management/plugin.php';

    				if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
                        if (!empty($v->translation)) {
                            foreach ($v->translation as $a => $b) {
                                if ($b->locale != $site_default_language) {

                                    $adept_author_value = get_option('adept_author');

                                    $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $b->locale . '_' . $b->_group_id . '_' . $b->id . "'  ORDER BY post_id DESC LIMIT 0,1 ");
                                    $postid = $get_existing_post_id[0]->post_id;

                                    if (trim($postid) == "") {

                                        $my_post = array(
                                            "ID" => $postid,
                                            "post_author" => $adept_author_value,
                                            "post_date" => $b->created_at,
                                            "post_date_gmt" => $b->created_at,
                                            "post_content" => $b->description,
                                            "post_excerpt" => $b->description,
                                            "post_title" => $b->group_title,
                                            "post_status" => 'publish',
                                            "comment_status" => 'closed',
                                            "ping_status" => 'closed',
                                            "post_name" => sanitize_title($b->group_title),
                                            "post_modified" => $b->updated_at,
                                            "post_modified_gmt" => $b->updated_at,
                                            "menu_order" => '0',
                                            "post_type" => 'groups',
                                            'guid' => ''
                                        );


                                        // Insert the post into the database.
                                        $post_id = wp_update_post($my_post, $wp_error);
    									if(count($v->courses)>0){
    										delete_post_meta( $post_id , '_course_ids');
    										foreach($v->courses as $key => $value){
    											$get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $value->course_id."' LIMIT 0,1 ");
    											$courseid = $get_course_id[0]->post_id;
    											add_post_meta( $post_id , '_course_ids', $courseid );
    										}
    									}
                                        update_post_meta($post_id, '_group_id', $b->locale . '_' . $b->_group_id . '_' . $b->id);
                                        update_post_meta($post_id, '_tags', $b->tags);
                                        update_post_meta($post_id, '_course_fee', $b->course_fee);
                                        update_post_meta($post_id, '_taxable', $b->taxable);
                                        update_post_meta($post_id, '_published', $b->published);
                                        update_post_meta($post_id, '_allow_bookings', $b->allow_bookings);
                                        update_post_meta($post_id, '_start_date', $b->start_date);
                                        update_post_meta($post_id, '_end_date', $b->end_date);
                                        update_post_meta($post_id, '_reg_date', $b->reg_date);
                                        update_post_meta($post_id, '_seats', $b->seats);
                                        update_post_meta($post_id, '_hide_if_full', $b->hide_if_full);
                                        update_post_meta($post_id, '_show_seats_left', $b->show_seats_left);
                                        update_post_meta($post_id, '_lessons', $b->lessons);
                                        update_post_meta($post_id, '_status', $b->status);
                                        update_post_meta($post_id, '_subscription_plan_id', $b->subscription_plan_id);

                                        $_POST['icl_post_language'] = $language_code = $b->locale;
                                        //wpml_add_translatable_content('post_post', $post_id, $language_code);
                                    }
                                }
                            }
                        }
                    }
				}
            }
            return "Groups updated successfully";
        }
        return "No Groups for import";
    }

    function import_instructors($url) {
		
        $temp = $this->getdata($url);
        // Delete posts from post type Intructors
        $args = array(
            'numberposts' => 50,
            'post_type' => 'instructors'
        );
        $intructors_posts = get_posts($args);

        if (is_array($intructors_posts)) {
            foreach ($intructors_posts as $post) {
                wp_delete_post($post->ID, true);
            }
        }

        global $wpdb;
        $adept_author_value = get_option('adept_author');
        foreach ($temp->data as $_temp1) {
            // Gather post data.
			if($_temp1->bio == ''){
				$_temp1->bio = ' ';
			}
            $my_post = array(
                "post_author" => $adept_author_value,
                "post_date" => $_temp1->created_at,
                "post_date_gmt" => $_temp1->created_at,
                "post_content" => $_temp1->bio,
                "post_excerpt" => $_temp1->bio,
                "post_title" => $_temp1->full_name,
                "post_status" => 'publish',
                "comment_status" => 'closed',
                "ping_status" => 'closed',
                "post_name" => sanitize_title($_temp1->full_name),
                "post_modified" => $_temp1->updated_at,
                "post_modified_gmt" => $_temp1->updated_at,
                "menu_order" => '0',
                "post_type" => 'instructors',
                'guid' => ''
            );
            // Insert the post into the database.
            $post_id = wp_insert_post($my_post, $wp_error);
			if(count($v->courses)>0) {
			
				foreach($v->courses as $key => $value){
					$get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_course_ids' AND  meta_value ='" . $value->course_id."' LIMIT 0,1 ");
                    $groupid = $get_group_id[0]->post_id;
					
					add_post_meta( $post_id , '_course_ids', $groupid );
				}
			}

            add_post_meta($post_id, '_instructor_id', $_temp1->id);
            add_post_meta($post_id, '_adept_api_id', $_temp1->id);
            add_post_meta($post_id, '_email', $_temp1->email);
            //add_post_meta($post_id, '_full_name', $_temp1->full_name);
            add_post_meta($post_id, '_avatar', $_temp1->avatar);
            //add_post_meta($post_id, '_bio', $_temp1->bio);
        }

        //$this->unpublished_posts($temp->data , "instructors");
        if(count($temp->data) == 0 ) {
            $this->unpublish_all_posts("instructors");
        }

        return "Instructors imported successfully";
    }

    function get_languages() {
        $adept_access_token_value = get_option('adept_access_token');
        $adept_api_url_value = get_option('adept_api_url');
        $adept_account_id_value = get_option('adept_account_id');
        $url = $adept_api_url_value . 'list_of_languages?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $all_languages = $this->getdata($url);
        return $all_languages;
    }

}


add_action("init" , "wpadept_clear");

function wpadept_clear() {

    global $wpdb;
    if(isset($_GET["adept_clear"])) {
        
        $posttypes = "('courses' , 'groups' , 'meetings' , 'instructors')";

        $wpdb->query("delete from {$wpdb->prefix}postmeta where post_id in (select ID from wp_posts where post_type in $posttypes )");
        $wpdb->query("delete from {$wpdb->prefix}posts where post_type in $posttypes");
        
        wp_die("Cleaned.");

    }
}

?>