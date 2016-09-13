<?php
/*
error_reporting(E_ALL); 
ini_set('display_errors', 1);*/
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //debug only
        curl_setopt($ch, CURLOPT_HEADER, false);

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
        if(isset($_GET["show_data"])) {
            pre($temp); exit;
        }

        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
        $taxonomy = 'genre';
        if ($temp) {
            foreach ($temp->data as $_temp1) {
                $name = $_temp1->name;
                $description = $_temp1->name;
                $slug = "cat-".$_temp1->id . '-' . sanitize_title($name);
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
        $wpdb->hide_errors();
        include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
        $all_courses_list = $this->getdata($url);                   
        $get_all_languages = $this->get_languages();
        

        if(isset($_GET["show_data"])) {

            foreach($all_courses_list->data as &$course) {
                $course->description="nullified";
                foreach($course->translation as &$locale) {
                    $locale->description = "nullified";
                }    
            }
            pre($all_courses_list); 
            exit;
        }

        $site_default_language = $get_all_languages->default_language;
        if($site_default_language == "no" ) {
            $site_default_language = "nb";
        }

        //pre($all_courses_list); exit;
        if (!empty($all_courses_list->data)) {
           
            foreach ($all_courses_list->data as $k => $v) {
                
                if($this->should_skip_category($v->course_category_id)) {
                   continue; 
                }
                
                $adept_author_value = get_option('adept_author');
                if ($v->teaser == '') {
                    $v->teaser = $v->description;
                }


                $get_existing_post_id = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta m, {$wpdb->prefix}posts  p where p.ID = m.post_id and p.post_type='courses' and meta_key='_adept_api_id' AND meta_value ='".$v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");

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

                    if(is_array($v->translation) && count($v->translation)>0) {

                        foreach($v->translation as $locale) {
                            /*if($v->id == 6) {
                                pre($v); exit;
                            }*/

                            if($locale->locale == "no") {
                                $locale->locale = "nb";
                            }
                            
                            $_POST['icl_post_language'] = $locale->locale;
                            
                            if(!isset($post_id)) {
                                $my_post["post_title"] = $locale->course_title;
                                $my_post["post_content"] = $locale->description;
                                $my_post["post_excerpt"] = $locale->teaser;
                                $post_id = wp_insert_post($my_post);                                
                                //echo "<br>newdefining : $locale->course_title : $post_id<br> ";
                            }
                            else {
                            
                                $new_id = wpa_add_post_language($post_id , "courses" , $locale->locale , $locale->course_title , $locale->description , $locale->teaser);
                                //echo "<br>already: $post_id : $locale->course_title : $new_id<br> ";
                                //pre($locale);
                                //echo $new_id."<br><br>";

                                $new_post_id[] = $new_id;
                           
                               
                            }
                           
                        }

                    }


                    $this->course_insert_extra_information($post_id , $v );
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
        //pre($all_courses_list); 
        return "No Courses for import";
    
    }

    function should_skip_category($category_id) {
        $adept_filter_enabled = get_option("adept_filter_enabled");
        $adept_cat_filter = get_option("adept_cat_filter");
        
        if($adept_filter_enabled != "1") {
            //echo "no need of any filtering";
            return false;
        }


        if(in_array($category_id, $adept_cat_filter)) {
            return false;
        }
        else {
            return true;
        }


    }

    function course_insert_extra_information($post_id , $data ) {
        //pre($data); exit;
        global $wpdb;

        $term_id = $wpdb->get_var("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE 'cat-" . $data->course_category_id . "-%'");                              
        if(!empty($term_id)) {
            wp_set_object_terms($post_id, (int)$term_id, 'genre' , false); //false to append
        }

        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
        //pre($site_default_language);

        if(count($data->groups)>0){
            $group_ids = array();
            foreach($data->groups as $key => $value){
                $get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_common_id' AND  meta_value ='{$value->group_id}' order by post_id desc LIMIT 1 ");
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
                $instructor_ids[] = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND  meta_value ='" . $value->instructor_id."' order by post_id desc LIMIT 1");
            }
            update_post_meta($post_id , '_instructor_ids', $instructor_ids);
        }

        update_post_meta($post_id, '_post_id', $site_default_language . "_" . $data->id);
        update_post_meta($post_id, '_tags', $data->tags);
        update_post_meta($post_id, '_is_featured', $data->is_featured);
        update_post_meta($post_id, '_course_fee', $data->course_fee);
        update_post_meta($post_id, '_sku', $data->sku);
        if(isset($data->tax_category)) {
            update_post_meta($post_id, '_tax_category', $data->tax_category);
        }
        if(isset($data->allow_discounts)) {
            update_post_meta($post_id, '_allow_discounts', $data->allow_discounts);
        }
        if(isset($data->subscription)) {
            update_post_meta($post_id, '_subscription', $data->subscription);
        }
        if(isset($data->booking_count)) {
            update_post_meta($post_id, '_booking_count', $data->booking_count);
        }
        update_post_meta($post_id, '_image_url', $data->image_url);
        update_post_meta($post_id, '_course_url', $data->course_url);
        update_post_meta($post_id, '_adept_api_id', $data->id);


        update_post_meta( $post_id, '_group_locations', $this->stringify($data->group_locations) );
        update_post_meta($post_id, '_group_level', $data->level);
        
        // Insert category id in courses
        /*
        $previous_slug = $wpdb->get_var("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE 'cat-" . $data->course_category_id . "-%'");

        if(!empty($previous_slug)) {

            $wpdb->insert($wpdb->prefix . "term_relationships", array(
                "object_id" => $post_id,
                "term_taxonomy_id" => $previous_slug
            ));
        }*/

    }

    function update_course($course , $old_post) {
       
        global $sitepress;
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
                $post_id = wp_insert_post($post);
                $sitepress->set_element_language_details($post_id, 'post_' . $post_type, $trigid, $locale->locale); // Change this post 
                

            }
            if($post_id) {
                $this->course_insert_extra_information($post_id , $course);
            }
        }

       
    }

    function does_post_exists($id, $locale , $type) {
        //echo "$id, $locale"; exit;
        global $wpdb;
        //$wpdb->show_errors();
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
        1. $active_ids = get all the posts which are ('active' in adeptlms)
        2. $all_draft_ids = get all posts which are in draft
        3. $to_be_published = $active_ids [intersection] $all_draft_ids
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
        if(isset($_GET["show_data"])) {
            pre($all_meeting_list); exit;
        }

        $meetings_flat_data = $this->flatten_meetings_array($all_meeting_list->data);
            
        $this->update_meeting($meetings_flat_data);

        $this->unpublished_posts($meetings_flat_data , "meetings");   

        return "Meetings imported successfully";
       
        //return "No Meetings for import";
    }

    function flatten_meetings_array($groups) {
        $return = array();

        foreach($groups as $group) {
            foreach($group->meetings as $meeting) {
                $meeting->group_id = $group->id;
                $meeting->group_title = $group->group_title;
                $return[] = $meeting;
            }
        }

        return($return);
    }

    function update_meeting($meetings) {
        global $wpdb,$sitepress;
        //$wpdb->show_errors();
        $adept_author_value = get_option('adept_author');
        //pre($meetings);exit;
        foreach($meetings as $meeting) {
            //check if post exists
            $post_id = $wpdb->get_var("select * from {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta m where m.post_id=p.ID and p.post_type='meetings' and m.meta_key='_adept_api_id' and m.meta_value='{$meeting->id}'  ");
            //echo "<br>select * from {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta m where m.post_id=p.ID and p.post_type='meetings' and m.meta_key='_adept_api_id' and m.meta_value='{$meeting->id}'  <br>";
            //echo " post_id: $post_id";
            if($meeting->description == "" ) {
                $meeting->description = " ";
            }
            $my_post = array(    
                "post_author" => $adept_author_value,
                "post_content" => $meeting->description,
                "post_excerpt" => $meeting->description,
                "post_title" => $meeting->title,
                "comment_status" => 'closed',
                "ping_status" => 'closed',
                "post_type" => 'meetings',
                "post_name" =>  sanitize_title( $meeting->title ),
            );
            if(!empty($post_id)) {
                $my_post["ID"] = $post_id;
            }               
                    
            $post_id = wp_insert_post($my_post);
            
            update_post_meta($post_id, '_meeting_id', $meeting->id);
            update_post_meta($post_id, '_adept_api_id', $meeting->id);
            
            $gmt_offset = get_option('gmt_offset');
            $starttime_ts = strtotime($meeting->start_time);
            $starttime_ts_local = $starttime_ts + ($gmt_offset*3600);
            $date = date("d/m/Y" , $starttime_ts_local );
            //echo $date; exit;
            update_post_meta($post_id, '_start_time', $starttime_ts_local);
            
            update_post_meta($post_id, '_start_date', $date);
            update_post_meta($post_id, '_duration', $meeting->duration);
            update_post_meta($post_id, '_instructor', $meeting->instructor_id);
            update_post_meta($post_id, '_category', $meeting->category);
            if(isset($meeting->end_time)) {
                update_post_meta($post_id, '_end_time', $meeting->end_time);
            }

            update_post_meta($post_id, '_status', $meeting->status);
            update_post_meta($post_id, '_web_conference', $meeting->web_conference);
            update_post_meta($post_id, '_address', $meeting->address);
            if(isset($meeting->check_address)) {
                update_post_meta($post_id, '_check_address', $meeting->check_address);
            }
            update_post_meta($post_id, '_group_id', $meeting->group_id);
            update_post_meta($post_id, '_kind', $meeting->kind);
            update_post_meta($post_id, '_video_conference_account_id', $meeting->video_conference_account_id);
            update_post_meta($post_id, '_video_conference_url', $meeting->video_conference_url);
            update_post_meta($post_id, '_video_conference_uid', $meeting->video_conference_uid);
                 
        }

       
    }

    function import_groups($url) {
        global $wpdb,$sitepress;
        //echo $url; exit;
        $all_courses_list = $this->getdata($url);
        
        $adept_author_value = get_option('adept_author');
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
        if(isset($_GET["show_data"])) {
            pre($all_courses_list); exit;
        }
        if (!empty($all_courses_list->data)) {

            foreach ($all_courses_list->data as $k => $v) {


                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND meta_value ='" . $site_default_language . "_" . $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
                //$postid = $get_existing_post_id[0]->post_id;

                if (empty($get_existing_post_id)) {


                    
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
                                    $post_id = wp_insert_post($my_post);
                                 
                                    if(count($v->courses)>0){
                                        
                                        $courses_ids = array();
                                        foreach($v->courses as $key => $value){

                                            $get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='en_" . $value->course_id."' LIMIT 0,1 ");
                                            
                                            if(isset($get_course_id[0]->post_id)) {

                                                $courses_ids[] = $get_course_id[0]->post_id;
                                            
                                            }
                                            update_post_meta( $post_id , '_course_ids', $courses_ids );
                                        }
                                    }
                                    update_post_meta($post_id, '_group_id', $site_default_language . "_" . $v->id);
                                    update_post_meta($post_id, '_tags', $v->tags);
                                    update_post_meta($post_id, '_course_fee', $v->course_fee);
                                    if(isset($v->taxable)) {
                                        update_post_meta($post_id, '_taxable', $v->taxable);
                                    }
                                    update_post_meta($post_id, '_published', $v->published);
                                    if(isset($v->allow_bookings)) {
                                        update_post_meta($post_id, '_allow_bookings', $v->allow_bookings);
                                    }
                                    update_post_meta($post_id, '_start_date', $v->start_date);
                                    update_post_meta($post_id, '_end_date', $v->end_date);
                                    update_post_meta($post_id, '_reg_date', $v->reg_date);
                                    update_post_meta($post_id, '_address', $v->address);
                                    update_post_meta($post_id, '_seats', $v->seats);
                                    update_post_meta($post_id, '_hide_if_full', $v->hide_if_full);
                                    if(isset($v->show_seats_left)) {
                                        update_post_meta($post_id, '_show_seats_left', $v->show_seats_left);
                                    }
                                    if(isset($v->lessons)) {
                                        update_post_meta($post_id, '_lessons', $v->lessons);
                                    }
                                    update_post_meta($post_id, '_status', $v->status);
                                    if(isset($v->subscription_plan_id)) {
                                        update_post_meta($post_id, '_subscription_plan_id', $v->subscription_plan_id);
                                    }
                                    update_post_meta($post_id, '_group_common_id',  $v->id);
                                    update_post_meta($post_id, '_adept_api_id',  $v->id);
                                    update_post_meta($post_id, '_group_locations',  $v->location);
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
                                                $courseids = array();
                                                foreach($b->courses as $key => $value){
                                                    $get_course_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $value->course_id."' LIMIT 0,1 ");
                                                    $courseids[] = $get_course_id[0]->post_id;
                                                }
                                                update_post_meta( $post_id , '_course_ids', $courseids );
                                            }

                                            update_post_meta($post_id_new, '_group_id', $b->locale . '_' . $b->_group_id . '_' . $b->id);

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
                                                            "_group_locations",
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
                else {
                    $this->update_groups($v , $get_existing_post_id);
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

    function update_groups($group , $old_post) {
        global $sitepress;
      //pre($group); exit;
        $post_type = "groups";
        foreach($group->translation as $locale) {

            if($locale->locale == "no") {
                $locale->locale = "nb";
            }

            $post_exists = $this->does_post_exists($group->id , $locale->locale , $post_type);
            $post = array(  
                            "post_title" =>  $locale->group_title,
                            "post_content" => $locale->description,
                            "post_excerpt" => $locale->description,
                            "post_type" => $post_type,
                            "post_status" => "publish",
                        );

            if($post_exists) {
                //if post exists then it will be updated only
                $post["ID"] = $post_exists; 
                $post_id = wp_insert_post($post);
            }
            else {

                $trigid = wpml_get_content_trid('post_' . $post_type, $old_post); // Find Transalation ID function from WPML API. 
                $_POST['icl_post_language'] = $locale->locale; // Set another language
                $post_id = wp_insert_post($post);
                
                $sitepress->set_element_language_details($post_id, 'post_' . $post_type, $trigid, $locale->locale); // Change this post 
                

            }
            if($post_id) {
                $this->group_insert_extra_information($post_id , $group);
            }
        }

    }

    function group_insert_extra_information($post_id , $group) {
        global $wpdb; 
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;
        //pre($get_all_languages); exit;
        if(count($group->courses)>0){
            $courses_ids = array();                        
            foreach($group->courses as $key => $value){

                $courses_ids[] = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='en_" . $value->course_id."' LIMIT 0,1 ");            
            }
            update_post_meta( $post_id , '_course_ids', $courses_ids );
        }
        update_post_meta($post_id, '_group_id', $site_default_language . "_" . $group->id);
        update_post_meta($post_id, '_tags', $group->tags);
        update_post_meta($post_id, '_course_fee', $group->course_fee);
        if(isset($group->taxable)) {
            update_post_meta($post_id, '_taxable', $group->taxable);
        }
        update_post_meta($post_id, '_published', $group->published);
        if(isset($group->allow_bookings)){
            update_post_meta($post_id, '_allow_bookings', $group->allow_bookings);
        }
        update_post_meta($post_id, '_start_date', $group->start_date);
        if(isset($group->groupend_date)){
            update_post_meta($post_id, '_end_date', $group->groupend_date);
        }
        update_post_meta($post_id, '_reg_date', $group->reg_date);
        update_post_meta($post_id, '_address', $group->address);
        update_post_meta($post_id, '_seats', $group->seats);
        if(isset($group->hide_if_full)){
            update_post_meta($post_id, '_hide_if_full', $group->hide_if_full);
        }
        if(isset($group->show_seats_left)){
            update_post_meta($post_id, '_show_seats_left', $group->show_seats_left);
        }
        if(isset($group->lessons)) {
            update_post_meta($post_id, '_lessons', $group->lessons);
        }
        if(isset($group->status)) {
            update_post_meta($post_id, '_status', $group->status);
        }
        
        if(isset($group->subscription_plan_id)) {
            update_post_meta($post_id, '_subscription_plan_id', $group->subscription_plan_id);
        }
        update_post_meta($post_id, '_group_common_id',  $group->id);
        update_post_meta($post_id, '_adept_api_id',  $group->id);
        update_post_meta($post_id, '_group_locations',  $group->location);
    }


    function import_instructors($url) {
        
        $temp = $this->getdata($url);
        if(isset($_GET["show_data"])) {
            pre($temp); exit;
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
                //"post_date" => $_temp1->created_at,
                //"post_date_gmt" => $_temp1->created_at,
                "post_content" => $_temp1->bio,
                "post_excerpt" => $_temp1->bio,
                "post_title" => $_temp1->full_name,
                "post_status" => 'publish',
                "comment_status" => 'closed',
                "ping_status" => 'closed',
                "post_name" => sanitize_title($_temp1->full_name),
                //"post_modified" => $_temp1->updated_at,
                //"post_modified_gmt" => $_temp1->updated_at,
                "menu_order" => '0',
                "post_type" => 'dt_team',
                'guid' => ''
            );

            $previous_post_id = $this->check_instructor_exists($_temp1);

            if($previous_post_id) {
                $my_post["ID"] = $previous_post_id;  
            }
            // Insert the post into the database.
            $post_id = wp_insert_post($my_post);
            if(count($_temp1->courses)>0) {
                $group_ids = array(); 
                foreach($_temp1->courses as $key => $value){
                    $sql = "SELECT post_id FROM {$wpdb->prefix}postmeta m , {$wpdb->prefix}posts p where p.ID=m.post_id and meta_key='_adept_api_id' AND  meta_value ='{$value->course_id}' and post_type='courses'  ";
                    $groupid = $wpdb->get_var($sql);
                    //echo $sql."<br><br>";
                    //pre($groupid);
                    //if($groupid != "") {
                    $group_ids[] = $groupid;
                        //$group_ids = array_merge($group_ids, $groupid);
                    //}

                }
               /* if($_temp1->id == 2 ) {
                    pre($group_ids) ; exit;
                }*/
                update_post_meta( $post_id , '_course_ids', $group_ids );
            }


            update_post_meta($post_id, '_instructor_id', $_temp1->id);
            update_post_meta($post_id, '_adept_api_id', $_temp1->id);
            update_post_meta($post_id, '_dt_teammate_options_mail', $_temp1->email);
            update_post_meta($post_id, '_dt_teammate_options_position', $_temp1->position);
            update_post_meta($post_id, '_avatar', $_temp1->avatar);
            //add_post_meta($post_id, '_bio', $_temp1->bio);
        }

        //$this->unpublished_posts($temp->data , "instructors");
        if(count($temp->data) == 0 ) {
            $this->unpublish_all_posts("dt_team");
        }

        return "Instructors imported successfully";
    }

    function check_instructor_exists($instructor) {
        global $wpdb;
        $sql = "select ID from {$wpdb->prefix}posts p , {$wpdb->prefix}postmeta m  where m.post_id = p.ID and meta_key='_adept_api_id' and meta_value='{$instructor->id}'  and p.post_type='dt_team' ";
        $post_id = $wpdb->get_var($sql);
        return $post_id;

    }

    function get_languages() {

        if ( false === ( $value = get_transient( 'adept_languages' ) ) ) {
        //if ( true ) {

            $adept_access_token_value = get_option('adept_access_token');
            $adept_api_url_value = get_option('adept_api_url');
            $adept_account_id_value = get_option('adept_account_id');
            $url = $adept_api_url_value . 'list_of_languages?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
            $all_languages = $this->getdata($url);
            set_transient( 'adept_languages', $all_languages, DAY_IN_SECONDS );
            return $all_languages;
        }
        else {

            return $value;
        }
    }

    function stringify($data) {
            
        if(empty($data)) return;

        $arr = $data;
        if(is_string($data)) {

            if( strpos($arr, ',' ) === false ) {
                $arr = array($data);
            }
            else {
                $arr = explode( ",", $data );
            }

        }

        $return = "";

        foreach($arr as $a) {
            $a = trim($a);
            $return.= "@@$a";            
        }

        return $return;
    }

    function unstringify($data) {
        if(!is_string($data)) {
            return "";
        }

        $str = explode("@@", $data);    

        $str = implode(",", $str);
        $str = trim($str , ",");

        return $str;

    }


}


add_action("init" , "wpadept_clear");

function wpadept_clear() {

    global $wpdb;
    if(isset($_GET["adept_clear"])) {
        
        if(is_user_logged_in()) {

            if(current_user_can("manage_options")) {

                $posttypes = "('courses' , 'groups' , 'meetings' , 'instructors' , 'dt_team')";
                $posttypes_2 = "('post_courses' , 'post_groups' , 'post_meetings' , 'post_instructors')";
                

                $wpdb->query("delete from {$wpdb->prefix}postmeta where post_id in (select ID from wp_posts where post_type in $posttypes )");
                $wpdb->query("delete from {$wpdb->prefix}posts where post_type in $posttypes");
                $wpdb->query("delete from {$wpdb->prefix}icl_translations where element_type in $posttypes_2 ");
                
                wp_die("Cleaned.");
            }
        }

        wp_die("Not authorized.");
    }
}


?>