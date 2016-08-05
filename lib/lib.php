<?php

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

				/* if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
                foreach ($_temp1->translation as $_lang) {
                    if ($site_default_language != $_lang->locale) {



                        $name = $_lang->name;
                        $description = $_lang->name;
                        $slug = $_lang->locale . '_' . $_lang->course_category_id . '_' . sanitize_title($name);

                        $_POST['icl_tax_' . $taxonomy . '_language'] = $_lang->locale;
                        $catdata = get_term_by('slug', $slug, 'genre');

                        if (!isset($catdata->term_id)) {
                            $fi_category1 = wp_insert_term(
                                    $name, // the term 
                                    'genre', // the taxonomy
                                    array(
                                'description' => $description,
                                'parent' => 0,
                                'slug' => $slug
                                    )
                            );

                            // Fetching WPML's trid
                            if ($fi_category1->errors['term_exists'][0] == '') {

                                $trid = $sitepress->get_element_trid($fi_category1['term_taxonomy_id'], 'tax_' . $taxonomy);

                                // Updating icl_translations table to connect the two terms

                                $updates = array(
                                    'trid' => $trid,
                                    'language_code' => $_lang->locale
                                );
                                $where = array(
                                    'element_type' => 'tax_' . $taxonomy,
                                    'element_id' => $fi_category1['term_taxonomy_id']
                                );

                                $wpdb->update($wpdb->prefix . 'icl_translations', $updates, $where);
                            }
                        }
                    }
                }
				} */
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
        //pre($all_courses_list); exit;

		$site_default_language = $get_all_languages->default_language;
        if($site_default_language == "no" ) {
            $site_default_language = "nb";
        }
        
        
		if (!empty($all_courses_list->data)) {
           
			foreach ($all_courses_list->data as $k => $v) {
                $adept_author_value = get_option('adept_author');
                
                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");                              
                if ($v->teaser == '') {
                    $v->teaser = $v->description;
                }

                $get_existing_post_id = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND meta_value ='" . $site_default_language . "_" . $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");

                if (trim($get_existing_post_id) == "") {
                    $my_post = array(
                        "post_author" => $adept_author_value,
                        //"post_date" => $v->created_at,
                        //"post_date_gmt" => $v->created_at,
                        "post_content" => $v->description,
                        "post_excerpt" => $v->teaser,
                        "post_title" => $v->course_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->course_title),
                        //"post_modified" => $v->updated_at,
                        //"post_modified_gmt" => $v->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'courses',
                        'guid' => ''
                    );

                    
                    // Insert the post into the database.
                    //$post_id = wp_insert_post($my_post); 

                    //add norverian language
                    //wpa_add_post_language($post_id , "courses" , "nb");
                    
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

				    //print_r($v->groups); die();
					if(count($v->groups)>0){
						foreach($v->groups as $key => $value){
							$get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='en_" . $value->group_id."' LIMIT 0,1 ");

							$groupid = $get_group_id[0]->post_id;
							add_post_meta( $post_id , '_group_ids', $groupid );

						}
					}
					
					
					if(count($v->instructors)>0){
                        $instructor_ids = array();
                        foreach($v->instructors as $key => $value){
							$instructor_ids[] = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND  meta_value ='" . $value->instructor_id."' LIMIT 0,1 ");
                        }
                        update_post_meta($post_id , '_instructor_ids', $instructor_ids);
						//pre($instructor_ids);
					}
					

                    $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                    add_post_meta($post_id, '_post_id', $site_default_language . "_" . $v->id);
                    add_post_meta($post_id, '_tags', $v->tags);
                    add_post_meta($post_id, '_is_featured', $v->is_featured);
                    add_post_meta($post_id, '_course_fee', $v->course_fee);
                    add_post_meta($post_id, '_sku', $v->sku);
                    add_post_meta($post_id, '_tax_category', $v->tax_category);
                    add_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
                    add_post_meta($post_id, '_subscription', $v->subscription);
                    add_post_meta($post_id, '_booking_count', $v->booking_count);
					add_post_meta($post_id, '_image_url', $v->image_url);
                    add_post_meta($post_id, '_course_url', $v->course_url);
					echo $post_id; pre($v->id);
                    add_post_meta($post_id, '_adept_course_id', $v->id);
					
					
                    // Insert category id in courses
                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");

                    $wpdb->insert($wpdb->prefix . "term_relationships", array(
                        "object_id" => $post_id,
                        "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                    ));

                    foreach($new_post_id as $id) {
                     wpa_translate_copy($post_id , $id);
                    }

                    // Multi translations
					$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
					$plugin2 = 'wpml-translation-management/plugin.php';

                }
                unset($post_id);

            }
            //$this->unpublished_courses($all_courses_list->data);
            return "Courses imported successfully";
        }

        return "No Courses for import";
    
    }

    function update_course($url) {
       // echo "here"; exit;
        global $wpdb;
		$adept_author_value = get_option('adept_author');

        $all_courses_list = $this->getdata($url);

        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;

        if (!empty($all_courses_list->data)) {
            //pre($all_courses_list); exit;
            foreach ($all_courses_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");
                if ($v->teaser == '') {
                    $v->teaser = $v->description;
                }	

                $get_existing_post_id = $wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND meta_value ='" . $site_default_language . "_" . $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
                //$postid = $get_existing_post_id[0]->post_id;

                if (trim($get_existing_post_id) == "") {

                    $my_post = array(
                        "post_author" => $adept_author_value,
                        "post_date" => $v->created_at,
                        "post_date_gmt" => $v->created_at,
                        "post_content" => $v->description,
                        "post_excerpt" => $v->teaser,
                        "post_title" => $v->course_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->course_title),
                        "post_modified" => $v->updated_at,
                        "post_modified_gmt" => $v->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'courses',
                        'guid' => ''
                    );


                    // Insert the post into the database.
                    $post_id = wp_insert_post($my_post, $wp_error);
					if(count($v->groups)>0){
						delete_post_meta( $post_id , '_group_ids');
						foreach($v->groups as $key => $value){
							$get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $value->group_id."' LIMIT 0,1 ");
                            $groupid = $get_group_id[0]->post_id;
							add_post_meta( $post_id , '_group_ids', $groupid );
						}
					}
					
					if(count($v->instructors)>0){
						foreach($v->instructors as $key => $value){
							$get_instructor_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND  meta_value ='" . $value->instructor_id."' LIMIT 0,1 ");
                            $instructorid = $get_instructor_id[0]->post_id;
							add_post_meta( $post_id , '_instructor_ids', $instructorid );
						}
					}

                    $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                    add_post_meta($post_id, '_post_id', $site_default_language . "_" . $v->id);
                    add_post_meta($post_id, '_tags', $v->tags);
                    add_post_meta($post_id, '_is_featured', $v->is_featured);
                    add_post_meta($post_id, '_course_fee', $v->course_fee);
                    add_post_meta($post_id, '_sku', $v->sku);
                    add_post_meta($post_id, '_tax_category', $v->tax_category);
                    add_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
                    add_post_meta($post_id, '_subscription', $v->subscription);
                    add_post_meta($post_id, '_booking_count', $v->booking_count);
					add_post_meta($post_id, '_image_url', $v->image_url);

                    // Insert category id in courses
                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");

                    $wpdb->insert($wpdb->prefix . "term_relationships", array(
                        "object_id" => $post_id,
                        "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                    ));

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
                                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");
                                if ($b->teaser == '') {
                                    $b->teaser = $b->description;
                                }

                                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $b->locale . '_' . $b->course_id . '_' . $b->id . "'  ORDER BY post_id DESC LIMIT 0,1 ");
                                $postid = $get_existing_post_id[0]->post_id;

                                if (trim($postid) == "") {

                                    $my_post = array(
                                        "post_author" => $adept_author_value,
                                        "post_date" => $b->created_at,
                                        "post_date_gmt" => $b->created_at,
                                        "post_content" => $b->description,
                                        "post_excerpt" => $b->teaser,
                                        "post_title" => $b->course_title,
                                        "post_status" => 'publish',
                                        "comment_status" => 'closed',
                                        "ping_status" => 'closed',
                                        "post_name" => sanitize_title($b->course_title),
                                        "post_modified" => $b->updated_at,
                                        "post_modified_gmt" => $b->updated_at,
                                        "menu_order" => '0',
                                        "post_type" => 'courses',
                                        'guid' => ''
                                    );


                                    // Insert the post into the database.
                                    $post_id = wp_insert_post($my_post, $wp_error);
                                    $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
									if(count($v->groups)>0){
										delete_post_meta( $post_id , '_group_ids');
										foreach($v->groups as $key => $value){
											$get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $site_default_language . "_"  . $value->group_id."' LIMIT 0,1 ");
											$groupid = $get_group_id[0]->post_id;
											add_post_meta( $post_id , '_group_ids', $groupid );
										}
									}
									
									if(count($v->instructors)>0){
										delete_post_meta( $post_id , '_instructor_ids');
										foreach($v->instructors as $key => $value){
											$get_instructor_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND  meta_value ='" . $value->instructor_id."' LIMIT 0,1 ");
											$instructorid = $get_group_id[0]->post_id;
											add_post_meta( $post_id , '_instructor_ids', $instructorid );
										}
									}
									
                                    add_post_meta($post_id, '_post_id', $b->locale . '_' . $b->course_id . '_' . $b->id);
                                    add_post_meta($post_id, '_tags', $b->tags);
                                    add_post_meta($post_id, '_is_featured', $v->is_featured);
                                    add_post_meta($post_id, '_course_fee', $v->course_fee);
                                    add_post_meta($post_id, '_sku', $b->sku);
                                    add_post_meta($post_id, '_tax_category', $v->tax_category);
                                    add_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
                                    add_post_meta($post_id, '_subscription', $v->subscription);
                                    add_post_meta($post_id, '_booking_count', $v->booking_count);
									add_post_meta($post_id, '_image_url', $v->image_url);

                                    // Insert category id in courses
                                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");

                                    $wpdb->insert($wpdb->prefix . "term_relationships", array(
                                        "object_id" => $post_id,
                                        "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                                    ));

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
                        "post_excerpt" => $v->teaser,
                        "post_title" => $v->course_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($v->course_title),
                        "post_modified" => $v->updated_at,
                        "post_modified_gmt" => $v->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'courses',
                        'guid' => ''
                    );


                    // Insert the post into the database.
                    $post_id = wp_update_post($my_post, $wp_error);
					if(count($v->groups)>0){
						delete_post_meta( $post_id , '_group_ids');
						foreach($v->groups as $key => $value){
							$get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $value->group_id."' LIMIT 0,1 ");
							$groupid = $get_group_id[0]->post_id;
							add_post_meta( $post_id , '_group_ids', $groupid );
						}
					}
					
					if(count($v->instructors)>0){
						delete_post_meta( $post_id , '_instructor_ids');
						foreach($v->instructors as $key => $value){
							$get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND  meta_value ='" . $value->instructor_id."' LIMIT 0,1 ");
							$instructorid = $get_instructor_id[0]->post_id;
							add_post_meta( $post_id , '_instructor_ids', $instructorid );
						}
					}
					
					

                    $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                    update_post_meta($post_id, '_post_id', $site_default_language . "_" . $v->id);
                    update_post_meta($post_id, '_tags', $v->tags);
                    update_post_meta($post_id, '_is_featured', $v->is_featured);
                    update_post_meta($post_id, '_course_fee', $v->course_fee);
                    update_post_meta($post_id, '_sku', $v->sku);
                    update_post_meta($post_id, '_tax_category', $v->tax_category);
                    update_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
                    update_post_meta($post_id, '_subscription', $v->subscription);
                    update_post_meta($post_id, '_booking_count', $v->booking_count);

                    // Insert category id in courses
                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");
                    $data = wp_set_post_terms($postid, $check_term_id_slug[0]->term_id, 'genre');
                    /* $wpdb->insert($wpdb->prefix . "term_relationships", array(
                      "object_id" => $post_id,
                      "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                      )); */

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
                                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");
                                    if ($b->teaser == '') {
                                        $b->teaser = $b->description;
                                    }

                                    $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $b->locale . '_' . $b->course_id . '_' . $b->id . "'  ORDER BY post_id DESC LIMIT 0,1 ");
                                    $postid = $get_existing_post_id[0]->post_id;

                                    if (trim($postid) == "") {

                                        $my_post = array(
                                            "ID" => $postid,
                                            "post_author" => $adept_author_value,
                                            "post_date" => $b->created_at,
                                            "post_date_gmt" => $b->created_at,
                                            "post_content" => $b->description,
                                            "post_excerpt" => $b->teaser,
                                            "post_title" => $b->course_title,
                                            "post_status" => 'publish',
                                            "comment_status" => 'closed',
                                            "ping_status" => 'closed',
                                            "post_name" => sanitize_title($b->course_title),
                                            "post_modified" => $b->updated_at,
                                            "post_modified_gmt" => $b->updated_at,
                                            "menu_order" => '0',
                                            "post_type" => 'courses',
                                            'guid' => ''
                                        );


                                        // Insert the post into the database.
                                        $post_id = wp_update_post($my_post, $wp_error);
    									if(count($b->groups)>0){
    										delete_post_meta( $post_id , '_group_ids');
    										foreach($b->groups as $key => $value){
    											$get_group_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $site_default_language . "_" . $value->group_id."' LIMIT 0,1 ");
    											$groupid = $get_group_id[0]->post_id;
    											add_post_meta( $post_id , '_group_ids', $groupid );
    										}
    									}
    									
    									if(count($b->instructors)>0){
    										delete_post_meta( $post_id , '_instructor_ids');
    										foreach($b->instructors as $key => $value){
    											$get_instructor_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND  meta_value ='" . $value->instructor_id."' LIMIT 0,1 ");
    											$instructorid = $get_instructor_id[0]->post_id;
    											add_post_meta( $post_id , '_instructor_ids', $instructorid );
    										}
    									}
    									
                                        $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');

                                        update_post_meta($post_id, '_post_id', $b->locale . '_' . $b->course_id . '_' . $b->id);
                                        update_post_meta($post_id, '_tags', $b->tags);
                                        update_post_meta($post_id, '_is_featured', $v->is_featured);
                                        update_post_meta($post_id, '_course_fee', $v->course_fee);
                                        update_post_meta($post_id, '_sku', $b->sku);
                                        update_post_meta($post_id, '_tax_category', $v->tax_category);
                                        update_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
                                        update_post_meta($post_id, '_subscription', $v->subscription);
                                        update_post_meta($post_id, '_booking_count', $v->booking_count);
    									update_post_meta($post_id, '_image_url', $v->image_url);

                                        // Insert category id in courses
                                        $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");
                                        $data = wp_set_post_terms($postid, $check_term_id_slug[0]->term_id, 'genre');
                                        /* $wpdb->insert($wpdb->prefix . "term_relationships", array(
                                          "object_id" => $post_id,
                                          "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                                          )); */

                                        $_POST['icl_post_language'] = $language_code = $b->locale;
                                        //wpml_add_translatable_content('post_post', $post_id, $language_code);
                                    }
                                }
                            }
                        }
    				}
                }
            }
            return "Courses updated successfully";
        }

        return "No Courses for Update";
    }

	function unpublished_courses($courses) {

        global $wpdb, $sitepress;

        $adept_ids = array();
        $active_ids = array();
        $all_ids = array();

        //get all the courses which are  active
        foreach($courses as $course) {
            $adept_ids[] =  $course->id;
        }
        pre($adept_ids);

        $query = new WP_Query(
            array(
                'post_type' => 'courses',
                'post_status' => 'any',
                'meta_query' => 
                array(
                    array(
                        'key'     => '_adept_course_id',
                        'value'   => $adept_ids,
                        'compare' => 'IN',
                    )
                )
            )
        );

        pre($query); exit;  

        while($query->have_posts()){
            $query->the_post();
            $active_ids[] = get_the_ID();
        }
        
        pre( $active_ids);

    }
	
	
    function import_meeting($url) {
		//echo $url; die();
	  global $wpdb;
        $adept_author_value = get_option('adept_author');
        $all_meeting_list = $this->getdata($url);
        //pre($all_meeting_list); exit;
        //echo "<pre>";
		//print_r($all_meeting_list);
		//exit();
        
        
        //$get_all_languages = $this->get_languages();
        //$site_default_language = $get_all_languages->default_language;

        if (!empty($all_meeting_list->data)) {
        
             foreach ($all_meeting_list->data as $k => $v) {
                
                
                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND meta_value ='". $v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
				
				$postid = $get_existing_post_id[0]->post_id;
	
                if (trim($postid) == "") {
				//	echo "<pre>";
				//	print_r($v->meetings);
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
					//echo "<pre>";
					//print_r($my_post); die();
                    // Insert the post into the database.
                    $post_id = wp_insert_post($my_post);
					//echo $post_id; die();
				
                    add_post_meta($post_id, '_meeting_id', $value->id);
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
            return "Meetings imported successfully";
        }

        return "No Meetings for import";
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
		//echo $url; exit;
        global $wpdb,$sitepress;

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
            return "Groups imported successfully";
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
		//pre($temp); exit;
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
            add_post_meta($post_id, '_email', $_temp1->email);
            //add_post_meta($post_id, '_full_name', $_temp1->full_name);
            add_post_meta($post_id, '_avatar', $_temp1->avatar);
            //add_post_meta($post_id, '_bio', $_temp1->bio);
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

?>