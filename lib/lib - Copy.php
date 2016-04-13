<?php
require( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        $resultdata = json_decode($result);

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
        $result = curl_exec($ch);
        $resultdata = json_decode($result);

        return $resultdata;
    }

    function import_category($url) {
        $temp = $this->getdata($url);
        if ($temp) {
            foreach ($temp->data as $_temp1) {
                    $name = $_temp1->name;
                    $description = $_temp1->name;
                    $slug = $_temp1->id . '_' . sanitize_title($name);
                    $data = wp_insert_term(
                            $name, // the term 
                            'genre', // the taxonomy
                            array(
                        'description' => $description,
                        'parent' => 0,
                        'slug' => $slug
                            )
                    );
					wpml_add_translatable_content('tax_genre', $data['term_id'], 'fr');
            }
            return $data->errors['term_exists'][0];
        } return "No Categories for import";
    }
	
	function update_course_to_live($url,$data) {
        $temp = $this->postdata($url,$data);
        return "Update course to live site";
    }

    function import_course($url) {
        global $wpdb, $sitepress;

        //$sitepress->set_element_language_details($ru_post_id, 'post_post', $def_trid, 'ru');
        // Static entry for the course 18 - 2 -2016//

       $static_json = '{
  "data": [
    {
      "id": 4,
      "course_title": "Deploma",
      "teaser": "aaaaaaa",
      "description": "<p>helooooooooooooo</p>\r\n",
      "tags": "",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T08:00:49.383Z",
      "updated_at": "2016-02-17T08:00:49.383Z",
      "translation": [
        {
          "id": 2,
          "course_id": 4,
          "locale": "en",
          "created_at": "2016-02-17T08:00:49.567Z",
          "updated_at": "2016-02-17T08:00:49.567Z",
          "course_title": "Deploma",
          "teaser": "aaaaaaa",
          "description": "<p>helooooooooooooo</p>\r\n",
          "tags": "",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 5,
      "course_title": "be",
      "teaser": "sss",
      "description": "<p>ssssssssss</p>\r\n",
      "tags": "a",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T08:56:58.966Z",
      "updated_at": "2016-02-17T08:56:58.966Z",
      "translation": [
        {
          "id": 3,
          "course_id": 5,
          "locale": "en",
          "created_at": "2016-02-17T08:56:58.974Z",
          "updated_at": "2016-02-17T08:56:58.974Z",
          "course_title": "be",
          "teaser": "sss",
          "description": "<p>ssssssssss</p>\r\n",
          "tags": "a",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 6,
      "course_title": "Fr maths",
      "teaser": "new lang",
      "description": "<p>etsdf</p>\r\n",
      "tags": "sasasa",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T09:19:14.238Z",
      "updated_at": "2016-02-17T09:19:14.238Z",
      "translation": [
        {
          "id": 4,
          "course_id": 6,
          "locale": "en",
          "created_at": "2016-02-17T09:19:14.244Z",
          "updated_at": "2016-02-17T09:19:14.244Z",
          "course_title": "Fr maths",
          "teaser": "new lang",
          "description": "<p>etsdf</p>\r\n",
          "tags": "dasd",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 7,
      "course_title": "School",
      "teaser": "Beta school",
      "description": "test",
      "tags": "a",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T09:23:05.970Z",
      "updated_at": "2016-02-17T09:23:05.970Z",
      "translation": [
        {
          "id": 5,
          "course_id": 7,
          "locale": "en",
          "created_at": "2016-02-17T09:23:05.975Z",
          "updated_at": "2016-02-17T09:23:05.975Z",
          "course_title": "School",
          "teaser": "Beta school",
          "description": "test",
          "tags": "a",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 9,
      "course_title": "Post title sample",
      "teaser": "adasdsdsa",
      "description": "dasdsdssdsd",
      "tags": "adsasdsdasdasd",
      "categories": null,
      "sku": null,
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 3,
      "created_at": "2016-02-17T09:56:10.699Z",
      "updated_at": "2016-02-17T09:56:10.699Z",
      "translation": [
        {
          "id": 7,
          "course_id": 9,
          "locale": "ru",
          "created_at": "2016-02-17T09:56:10.726Z",
          "updated_at": "2016-02-17T09:56:10.726Z",
          "course_title": "rissian course Post title sample",
          "teaser": "ddddd",
          "description": "<p>dddddddddddddd</p>\r\n",
          "tags": "",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 8,
      "course_title": "asdsd",
      "teaser": "asdasd",
      "description": "dadsdsd",
      "tags": "adsdsdad",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T09:37:20.688Z",
      "updated_at": "2016-02-17T10:09:32.486Z",
      "translation": [
        {
          "id": 8,
          "course_id": 8,
          "locale": "en",
          "created_at": "2016-02-17T10:09:32.535Z",
          "updated_at": "2016-02-17T10:09:32.535Z",
          "course_title": "dadsdsa",
          "teaser": "dads",
          "description": "dasdasd",
          "tags": "",
          "categories": null,
          "sku": "",
          "status": "active"
        },
        {
          "id": 6,
          "course_id": 8,
          "locale": "ja",
          "created_at": "2016-02-17T09:37:20.830Z",
          "updated_at": "2016-02-17T09:37:20.830Z",
          "course_title": "???????",
          "teaser": "sddddd",
          "description": "<p>ccccccc</p>\r\n",
          "tags": "b",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    }
  ]
}';

        $all_courses_list = json_decode($static_json);
        
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;

        if (!empty($all_courses_list->data)) {

            foreach ($all_courses_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");
                if ($v->teaser == '') {
                    $v->teaser = $v->description;
                }

                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND meta_value ='" . $site_default_language."_".$v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;

                if (trim($postid) == "") {

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
					
                    $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                    add_post_meta($post_id, '_post_id', $site_default_language."_".$v->id);
                    add_post_meta($post_id, '_tags', $v->tags);
                    add_post_meta($post_id, '_is_featured', $v->is_featured);
                    add_post_meta($post_id, '_course_fee', $v->course_fee);
                    add_post_meta($post_id, '_sku', $v->sku);
                    add_post_meta($post_id, '_tax_category', $v->tax_category);
                    add_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
                    add_post_meta($post_id, '_subscription', $v->subscription);
                    add_post_meta($post_id, '_booking_count', $v->booking_count);

                    // Insert category id in courses
                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");

                    $wpdb->insert($wpdb->prefix . "term_relationships", array(
                        "object_id" => $post_id,
                        "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                    ));

                    $_POST['icl_post_language'] = $language_code = $site_default_language;
                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                    
					 // Multi translations
					if (!empty($v->translation)) {
						foreach ($v->translation as $a => $b) {
							$adept_author_value = get_option('adept_author');
							$check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");
							if ($b->teaser == '') {
								$b->teaser = $b->description;
							}

							$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $b->locale.'_'.$b->course_id.'_'.$b->id."'  ORDER BY post_id DESC LIMIT 0,1 ");
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
								
								add_post_meta($post_id, '_post_id', $b->locale.'_'.$b->course_id.'_'.$b->id);
								add_post_meta($post_id, '_tags', $b->tags);
								add_post_meta($post_id, '_is_featured', $v->is_featured);
								add_post_meta($post_id, '_course_fee', $v->course_fee);
								add_post_meta($post_id, '_sku', $b->sku);
								add_post_meta($post_id, '_tax_category', $v->tax_category);
								add_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
								add_post_meta($post_id, '_subscription', $v->subscription);
								add_post_meta($post_id, '_booking_count', $v->booking_count);

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
			return "Courses imported successfully";
        }


        // Commented code from the url //

        //$temp = $this->getdata($url);
        //$adept_author_value = get_option('adept_author');
        /*
          if ($temp) {
          foreach ($temp as $_temp) {
          foreach ($_temp as $_temp1) {
          $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");
          if ($_temp1->teasere == '') {
          $_temp1->teasere = $_temp1->description;
          }

          $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
          $postid = $get_existing_post_id[0]->post_id;
          if ($postid == '') {
          // Gather post data.
          $my_post = array(
          "post_author" => $adept_author_value,
          "post_date" => $_temp1->created_at,
          "post_date_gmt" => $_temp1->created_at,
          "post_content" => $_temp1->description,
          "post_excerpt" => $_temp1->teasere,
          "post_title" => $_temp1->course_title,
          "post_status" => 'publish',
          "comment_status" => 'closed',
          "ping_status" => 'closed',
          "post_name" => sanitize_title($_temp1->course_title),
          "post_modified" => $_temp1->updated_at,
          "post_modified_gmt" => $_temp1->updated_at,
          "menu_order" => '0',
          "post_type" => 'courses',
          'guid' => ''
          );
          // Insert the post into the database.
          $post_id = wp_insert_post($my_post, $wp_error);
          $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
          add_post_meta($post_id, '_post_id', $_temp1->id);
          add_post_meta($post_id, '_tags', $_temp1->tags);
          add_post_meta($post_id, '_is_featured', $_temp1->is_featured);
          add_post_meta($post_id, '_course_fee', $_temp1->course_fee);
          add_post_meta($post_id, '_sku', $_temp1->sku);
          add_post_meta($post_id, '_tax_category', $_temp1->tax_category);
          add_post_meta($post_id, '_allow_discounts', $_temp1->allow_discounts);
          add_post_meta($post_id, '_subscription', $_temp1->subscription);
          add_post_meta($post_id, '_booking_count', $_temp1->booking_count);


          // Insert category id in courses
          $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");

          $wpdb->insert($wpdb->prefix . "term_relationships", array(
          "object_id" => $post_id,
          "term_taxonomy_id" => $check_term_id_slug[0]->term_id
          ));
          }
          }
          return "Courses imported successfully";
          }
          }
         */
        return "No Courses for import";
    }


    function update_course($url) {
        global $wpdb;
        $temp = $this->getdata($url);
        $adept_author_value = get_option('adept_author');
		
		 $static_json = '{
  "data": [
    {
      "id": 4,
      "course_title": "Deploma",
      "teaser": "aaaaaaa",
      "description": "<p>helooooooooooooo</p>\r\n",
      "tags": "",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T08:00:49.383Z",
      "updated_at": "2016-02-17T08:00:49.383Z",
      "translation": [
        {
          "id": 2,
          "course_id": 4,
          "locale": "en",
          "created_at": "2016-02-17T08:00:49.567Z",
          "updated_at": "2016-02-17T08:00:49.567Z",
          "course_title": "Deploma",
          "teaser": "aaaaaaa",
          "description": "<p>helooooooooooooo</p>\r\n",
          "tags": "",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 5,
      "course_title": "be",
      "teaser": "sss",
      "description": "<p>ssssssssss</p>\r\n",
      "tags": "a",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T08:56:58.966Z",
      "updated_at": "2016-02-17T08:56:58.966Z",
      "translation": [
        {
          "id": 3,
          "course_id": 5,
          "locale": "en",
          "created_at": "2016-02-17T08:56:58.974Z",
          "updated_at": "2016-02-17T08:56:58.974Z",
          "course_title": "be",
          "teaser": "sss",
          "description": "<p>ssssssssss</p>\r\n",
          "tags": "a",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 6,
      "course_title": "Fr maths",
      "teaser": "new lang",
      "description": "<p>etsdf</p>\r\n",
      "tags": "sasasa",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T09:19:14.238Z",
      "updated_at": "2016-02-17T09:19:14.238Z",
      "translation": [
        {
          "id": 4,
          "course_id": 6,
          "locale": "en",
          "created_at": "2016-02-17T09:19:14.244Z",
          "updated_at": "2016-02-17T09:19:14.244Z",
          "course_title": "Fr maths",
          "teaser": "new lang",
          "description": "<p>etsdf</p>\r\n",
          "tags": "dasd",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 7,
      "course_title": "School",
      "teaser": "Beta school",
      "description": "test",
      "tags": "a",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T09:23:05.970Z",
      "updated_at": "2016-02-17T09:23:05.970Z",
      "translation": [
        {
          "id": 5,
          "course_id": 7,
          "locale": "en",
          "created_at": "2016-02-17T09:23:05.975Z",
          "updated_at": "2016-02-17T09:23:05.975Z",
          "course_title": "School",
          "teaser": "Beta school",
          "description": "test",
          "tags": "a",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 9,
      "course_title": "Post title sample",
      "teaser": "adasdsdsa",
      "description": "dasdsdssdsd",
      "tags": "adsasdsdasdasd",
      "categories": null,
      "sku": null,
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 3,
      "created_at": "2016-02-17T09:56:10.699Z",
      "updated_at": "2016-02-17T09:56:10.699Z",
      "translation": [
        {
          "id": 7,
          "course_id": 9,
          "locale": "ru",
          "created_at": "2016-02-17T09:56:10.726Z",
          "updated_at": "2016-02-17T09:56:10.726Z",
          "course_title": "rissian course Post title sample",
          "teaser": "ddddd",
          "description": "<p>dddddddddddddd</p>\r\n",
          "tags": "",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    },
    {
      "id": 8,
      "course_title": "asdsd",
      "teaser": "asdasd",
      "description": "dadsdsd",
      "tags": "adsdsdad",
      "categories": null,
      "sku": "",
      "taxable": null,
      "allow_discounts": true,
      "subscription": false,
      "published": true,
      "booking_count": null,
      "course_category_id": 2,
      "created_at": "2016-02-17T09:37:20.688Z",
      "updated_at": "2016-02-17T10:09:32.486Z",
      "translation": [
        {
          "id": 8,
          "course_id": 8,
          "locale": "en",
          "created_at": "2016-02-17T10:09:32.535Z",
          "updated_at": "2016-02-17T10:09:32.535Z",
          "course_title": "dadsdsa",
          "teaser": "dads",
          "description": "dasdasd",
          "tags": "",
          "categories": null,
          "sku": "",
          "status": "active"
        },
        {
          "id": 6,
          "course_id": 8,
          "locale": "ja",
          "created_at": "2016-02-17T09:37:20.830Z",
          "updated_at": "2016-02-17T09:37:20.830Z",
          "course_title": "???????",
          "teaser": "sddddd",
          "description": "<p>ccccccc</p>\r\n",
          "tags": "b",
          "categories": null,
          "sku": "",
          "status": "active"
        }
      ],
      "status": "200"
    }
  ]
}';

		$all_courses_list = json_decode($static_json);
        
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;

        if (!empty($all_courses_list->data)) {

            foreach ($all_courses_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");
                if ($v->teaser == '') {
                    $v->teaser = $v->description;
                }

                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND meta_value ='" . $site_default_language."_".$v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;

                if (trim($postid) == "") {

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
					
                    $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                    add_post_meta($post_id, '_post_id', $site_default_language."_".$v->id);
                    add_post_meta($post_id, '_tags', $v->tags);
                    add_post_meta($post_id, '_is_featured', $v->is_featured);
                    add_post_meta($post_id, '_course_fee', $v->course_fee);
                    add_post_meta($post_id, '_sku', $v->sku);
                    add_post_meta($post_id, '_tax_category', $v->tax_category);
                    add_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
                    add_post_meta($post_id, '_subscription', $v->subscription);
                    add_post_meta($post_id, '_booking_count', $v->booking_count);

                    // Insert category id in courses
                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $v->course_category_id . "_%'");

                    $wpdb->insert($wpdb->prefix . "term_relationships", array(
                        "object_id" => $post_id,
                        "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                    ));

                    $_POST['icl_post_language'] = $language_code = $site_default_language;
                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                    
					 // Multi translations
					if (!empty($v->translation)) {
						foreach ($v->translation as $a => $b) {
							$adept_author_value = get_option('adept_author');
							$check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");
							if ($b->teaser == '') {
								$b->teaser = $b->description;
							}

							$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $b->locale.'_'.$b->course_id.'_'.$b->id."'  ORDER BY post_id DESC LIMIT 0,1 ");
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
								
								add_post_meta($post_id, '_post_id', $b->locale.'_'.$b->course_id.'_'.$b->id);
								add_post_meta($post_id, '_tags', $b->tags);
								add_post_meta($post_id, '_is_featured', $v->is_featured);
								add_post_meta($post_id, '_course_fee', $v->course_fee);
								add_post_meta($post_id, '_sku', $b->sku);
								add_post_meta($post_id, '_tax_category', $v->tax_category);
								add_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
								add_post_meta($post_id, '_subscription', $v->subscription);
								add_post_meta($post_id, '_booking_count', $v->booking_count);

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
                }else{
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
					
                    $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                    update_post_meta($post_id, '_post_id', $site_default_language."_".$v->id);
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
                    /*$wpdb->insert($wpdb->prefix . "term_relationships", array(
                        "object_id" => $post_id,
                        "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                    ));*/

                    $_POST['icl_post_language'] = $language_code = $site_default_language;
                    //wpml_add_translatable_content('post_post', $post_id, $language_code);
                    
					 // Multi translations
					if (!empty($v->translation)) {
						foreach ($v->translation as $a => $b) {
							$adept_author_value = get_option('adept_author');
							$check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");
							if ($b->teaser == '') {
								$b->teaser = $b->description;
							}

							$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND  meta_value ='" . $b->locale.'_'.$b->course_id.'_'.$b->id."'  ORDER BY post_id DESC LIMIT 0,1 ");
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
								$data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
								
								update_post_meta($post_id, '_post_id', $b->locale.'_'.$b->course_id.'_'.$b->id);
								update_post_meta($post_id, '_tags', $b->tags);
								update_post_meta($post_id, '_is_featured', $v->is_featured);
								update_post_meta($post_id, '_course_fee', $v->course_fee);
								update_post_meta($post_id, '_sku', $b->sku);
								update_post_meta($post_id, '_tax_category', $v->tax_category);
								update_post_meta($post_id, '_allow_discounts', $v->allow_discounts);
								update_post_meta($post_id, '_subscription', $v->subscription);
								update_post_meta($post_id, '_booking_count', $v->booking_count);
								
								// Insert category id in courses
								$check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $b->course_category_id . "_%'");
								$data = wp_set_post_terms($postid, $check_term_id_slug[0]->term_id, 'genre');	
								/*$wpdb->insert($wpdb->prefix . "term_relationships", array(
									"object_id" => $post_id,
									"term_taxonomy_id" => $check_term_id_slug[0]->term_id
								));*/

								$_POST['icl_post_language'] = $language_code = $b->locale;
								//wpml_add_translatable_content('post_post', $post_id, $language_code);
								
							}
						}
					}
				} 
            }
			return "Courses updated successfully";
        }

        /*if ($temp) {
            foreach ($temp as $_temp) {
                foreach ($_temp as $_temp1) {
                    $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");
                    if ($_temp1->teasere == '') {
                        $_temp1->teasere = $_temp1->description;
                    }

                    $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_post_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                    $postid = $get_existing_post_id[0]->post_id;

                    if ($postid != '') {
                        // Gather post data.
                        $my_post = array(
                            "ID" => $postid,
                            "post_author" => $adept_author_value,
                            "post_date" => $_temp1->created_at,
                            "post_date_gmt" => $_temp1->created_at,
                            "post_content" => $_temp1->description,
                            "post_excerpt" => $_temp1->teasere,
                            "post_title" => $_temp1->course_title,
                            "post_name" => sanitize_title($_temp1->course_title),
                            "post_modified" => $_temp1->updated_at,
                            "post_modified_gmt" => $_temp1->updated_at,
                            "post_type" => 'courses'
                        );
                        // Insert the post into the database.
                        $post_id = wp_update_post($my_post, $wp_error);
                        $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                        update_post_meta($post_id, '_post_id', $_temp1->id);
                        update_post_meta($post_id, '_tags', $_temp1->tags);
                        update_post_meta($post_id, '_is_featured', $_temp1->is_featured);
                        update_post_meta($post_id, '_course_fee', $_temp1->course_fee);
                        update_post_meta($post_id, '_sku', $_temp1->sku);
                        update_post_meta($post_id, '_tax_category', $_temp1->tax_category);
                        update_post_meta($post_id, '_allow_discounts', $_temp1->allow_discounts);
                        update_post_meta($post_id, '_subscription', $_temp1->subscription);
                        update_post_meta($post_id, '_booking_count', $_temp1->booking_count);
                    } else {
                        $my_post_insert = array(
                            "post_author" => $adept_author_value,
                            "post_date" => $_temp1->created_at,
                            "post_date_gmt" => $_temp1->created_at,
                            "post_content" => $_temp1->description,
                            "post_excerpt" => $_temp1->teasere,
                            "post_title" => $_temp1->course_title,
                            "post_status" => 'publish',
                            "comment_status" => 'closed',
                            "ping_status" => 'closed',
                            "post_name" => sanitize_title($_temp1->course_title),
                            "post_modified" => $_temp1->updated_at,
                            "post_modified_gmt" => $_temp1->updated_at,
                            "menu_order" => '0',
                            "post_type" => 'courses',
                            'guid' => ''
                        );
						
                        // Insert the post into the database.
                        $post_id = wp_insert_post($my_post_insert, $wp_error);
                        $data = wp_set_post_terms($post_id, $check_term_id_slug[0]->term_id, 'genre');
                        add_post_meta($post_id, '_post_id', $_temp1->id);
                        add_post_meta($post_id, '_tags', $_temp1->tags);
                        add_post_meta($post_id, '_is_featured', $_temp1->is_featured);
                        add_post_meta($post_id, '_course_fee', $_temp1->course_fee);
                        add_post_meta($post_id, '_sku', $_temp1->sku);
                        add_post_meta($post_id, '_tax_category', $_temp1->tax_category);
                        add_post_meta($post_id, '_allow_discounts', $_temp1->allow_discounts);
                        add_post_meta($post_id, '_subscription', $_temp1->subscription);
                        add_post_meta($post_id, '_booking_count', $_temp1->booking_count);

                        // Insert category id in courses
                        $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");

                        $wpdb->insert($wpdb->prefix . "term_relationships", array(
                            "object_id" => $post_id,
                            "term_taxonomy_id" => $check_term_id_slug[0]->term_id
                        ));
                        
                    }
                }
                return "Courses Updated successfully";
            }
        } 
		*/
		return "No Courses for Update";
    }

    function import_meeting($url) {
        global $wpdb;
		 $static_json = '{
  	"data": [
    		{
      		"id": 1,
     	 	"title": "first meeting",
      		"comment": "aaaaaa",
     	 	"date": "2016-02-15T00:00:00.000Z",
      		"start_time": "2016-02-15T00:00:00.000Z",
     	 	"end_time": "2016-02-15T00:00:00.000Z",
      		"status": null,
     		 "web_conference": null,
     		 "address": null,
      		"class_id": null,
      		"created_by": null,
      		"modified_by": null,
      		"check_address": null,
     		 "group_id": 1,
      		"user_id": 2,
      		"created_at": "2016-02-15T05:36:39.254Z",
     		 "updated_at": "2016-02-15T05:36:39.254Z",
     		 "kind": null,
      		"video_conference_account_id": 1,
     		 "video_conference_url": null,
     		 "video_conference_uid": null,
		"translation": [
        			{
          				"id": 1,
          				"meeting_id": 1,
          				"locale": "en",
          				"created_at": "2016-02-19T00:00:00.000Z",
         				 "updated_at": "2016-02-19T00:00:00.000Z",
         				 "title": "aaa",
         				 "comment": "aaa",
          				"status": "1",
          				"address": "aaaaa"
        			}
     		 ]
  	  }
	]
}
';

        $all_meeting_list = json_decode($static_json);
		
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;

        if (!empty($all_meeting_list->data)) {

            foreach ($all_meeting_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND meta_value ='" . $site_default_language."_".$v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
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
					
                    
                    add_post_meta($post_id, '_meeting_id', $site_default_language."_".$v->id);
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
					if (!empty($v->translation)) {
						foreach ($v->translation as $a => $b) {
							$adept_author_value = get_option('adept_author');
							
							$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND  meta_value ='" . $b->locale.'_'.$b->meeting_id.'_'.$b->id."'  ORDER BY post_id DESC LIMIT 0,1 ");
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
								
								add_post_meta($post_id, '_meeting_id', $b->locale.'_'.$b->meeting_id.'_'.$b->id);
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
			return "Meetings imported successfully";
        }
        /*$temp = $this->getdata($url);
        
        $adept_author_value = get_option('adept_author');
        if ($temp) {
           foreach ($temp as $_temp) {
            foreach ($temp as $_temp1) {
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;
                if ($postid == '') {
                    // Gather post data.
                    $my_post = array(
                        "post_author" => $adept_author_value,
                        "post_date" => $_temp1->created_at,
                        "post_date_gmt" => $_temp1->created_at,
                        "post_content" => $_temp1->comment,
                        "post_excerpt" => $_temp1->comment,
                        "post_title" => $_temp1->title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($_temp1->title),
                        "post_modified" => $_temp1->updated_at,
                        "post_modified_gmt" => $_temp1->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'meetings',
                        'guid' => ''
                    );
                    // Insert the post into the database.
                    $post_id = wp_insert_post($my_post, $wp_error);

                    add_post_meta($post_id, '_meeting_id', $_temp1->id);
                    add_post_meta($post_id, '_date', $_temp1->date);
                    add_post_meta($post_id, '_start_time', $_temp1->start_time);
                    add_post_meta($post_id, '_end_time', $_temp1->end_time);
                    add_post_meta($post_id, '_status', $_temp1->status);
                    add_post_meta($post_id, '_web_conference', $_temp1->web_conference);
                    add_post_meta($post_id, '_address', $_temp1->address);
                    add_post_meta($post_id, '_check_address', $_temp1->check_address);
                    add_post_meta($post_id, '_group_id', $_temp1->group_id);
                    add_post_meta($post_id, '_user_id', $_temp1->user_id);
                    add_post_meta($post_id, '_kind', $_temp1->kind);
                    add_post_meta($post_id, '_video_conference_account_id', $_temp1->video_conference_account_id);
                    add_post_meta($post_id, '_video_conference_url', $_temp1->video_conference_url);
                    add_post_meta($post_id, '_video_conference_uid', $_temp1->video_conference_uid);
                }
              }
            }
            return "Meetings imported successfully";
        } */
		return "No Meetings for import";
    }
	
	function update_meeting($url) {
        global $wpdb;
		$static_json = '{
  	"data": [
    		{
      		"id": 1,
     	 	"title": "first meeting",
      		"comment": "aaaaaa",
     	 	"date": "2016-02-15T00:00:00.000Z",
      		"start_time": "2016-02-15T00:00:00.000Z",
     	 	"end_time": "2016-02-15T00:00:00.000Z",
      		"status": null,
     		 "web_conference": null,
     		 "address": null,
      		"class_id": null,
      		"created_by": null,
      		"modified_by": null,
      		"check_address": null,
     		 "group_id": 1,
      		"user_id": 2,
      		"created_at": "2016-02-15T05:36:39.254Z",
     		 "updated_at": "2016-02-15T05:36:39.254Z",
     		 "kind": null,
      		"video_conference_account_id": 1,
     		 "video_conference_url": null,
     		 "video_conference_uid": null,
		"translation": [
        			{
          				"id": 1,
          				"meeting_id": 1,
          				"locale": "en",
          				"created_at": "2016-02-19T00:00:00.000Z",
         				 "updated_at": "2016-02-19T00:00:00.000Z",
         				 "title": "aaa",
         				 "comment": "aaa",
          				"status": "1",
          				"address": "aaaaa"
        			}
     		 ]
  	  }
	]
}
';

        $all_meeting_list = json_decode($static_json);
		
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;

        if (!empty($all_meeting_list->data)) {

            foreach ($all_meeting_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND meta_value ='" . $site_default_language."_".$v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
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
					
                    
                    add_post_meta($post_id, '_meeting_id', $site_default_language."_".$v->id);
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
					if (!empty($v->translation)) {
						foreach ($v->translation as $a => $b) {
							$adept_author_value = get_option('adept_author');
							
							$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND  meta_value ='" . $b->locale.'_'.$b->meeting_id.'_'.$b->id."'  ORDER BY post_id DESC LIMIT 0,1 ");
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
								
								add_post_meta($post_id, '_meeting_id', $b->locale.'_'.$b->meeting_id.'_'.$b->id);
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
                }else{
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
					
                    
                    update_post_meta($post_id, '_meeting_id', $site_default_language."_".$v->id);
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
					if (!empty($v->translation)) {
						foreach ($v->translation as $a => $b) {
							$adept_author_value = get_option('adept_author');
							
							$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND  meta_value ='" . $b->locale.'_'.$b->meeting_id.'_'.$b->id."'  ORDER BY post_id DESC LIMIT 0,1 ");
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
								
								update_post_meta($post_id, '_meeting_id', $b->locale.'_'.$b->meeting_id.'_'.$b->id);
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
			return "Meetings imported successfully";
        }
        /*$temp = $this->getdata($url);
        $adept_author_value = get_option('adept_author');
        if ($temp) {
            foreach ($temp as $_temp) {
                foreach ($_temp as $_temp1) {
                    $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_meeting_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                    $postid = $get_existing_post_id[0]->post_id;

                    if ($postid != '') {
                        // Gather post data.
                        $my_post = array(
							"post_author" => $adept_author_value,
							"post_date" => $_temp1->created_at,
							"post_date_gmt" => $_temp1->created_at,
							"post_content" => $_temp1->comment,
							"post_excerpt" => $_temp1->comment,
							"post_title" => $_temp1->title,
							"post_status" => 'publish',
							"comment_status" => 'closed',
							"ping_status" => 'closed',
							"post_name" => sanitize_title($_temp1->title),
							"post_modified" => $_temp1->updated_at,
							"post_modified_gmt" => $_temp1->updated_at,
							"menu_order" => '0',
							"post_type" => 'meetings',
							'guid' => ''
						);
                        // Insert the post into the database.
                        $post_id = wp_update_post($my_post, $wp_error);
                        
						update_post_meta($post_id, '_meeting_id', $_temp1->id);
						update_post_meta($post_id, '_date', $_temp1->date);
						update_post_meta($post_id, '_start_time', $_temp1->start_time);
						update_post_meta($post_id, '_end_time', $_temp1->end_time);
						update_post_meta($post_id, '_status', $_temp1->status);
						update_post_meta($post_id, '_web_conference', $_temp1->web_conference);
						update_post_meta($post_id, '_address', $_temp1->address);
						update_post_meta($post_id, '_check_address', $_temp1->check_address);
						update_post_meta($post_id, '_group_id', $_temp1->group_id);
						update_post_meta($post_id, '_user_id', $_temp1->user_id);
						update_post_meta($post_id, '_kind', $_temp1->kind);
						update_post_meta($post_id, '_video_conference_account_id', $_temp1->video_conference_account_id);
						update_post_meta($post_id, '_video_conference_url', $_temp1->video_conference_url);
						update_post_meta($post_id, '_video_conference_uid', $_temp1->video_conference_uid);
                    } else {
						$my_post = array(
							"post_author" => $adept_author_value,
							"post_date" => $_temp1->created_at,
							"post_date_gmt" => $_temp1->created_at,
							"post_content" => $_temp1->comment,
							"post_excerpt" => $_temp1->comment,
							"post_title" => $_temp1->title,
							"post_status" => 'publish',
							"comment_status" => 'closed',
							"ping_status" => 'closed',
							"post_name" => sanitize_title($_temp1->title),
							"post_modified" => $_temp1->updated_at,
							"post_modified_gmt" => $_temp1->updated_at,
							"menu_order" => '0',
							"post_type" => 'meetings',
							'guid' => ''
						);

                        // Insert the post into the database.
                        $post_id = wp_insert_post($my_post_insert, $wp_error);
                        add_post_meta($post_id, '_meeting_id', $_temp1->id);
						add_post_meta($post_id, '_date', $_temp1->date);
						add_post_meta($post_id, '_start_time', $_temp1->start_time);
						add_post_meta($post_id, '_end_time', $_temp1->end_time);
						add_post_meta($post_id, '_status', $_temp1->status);
						add_post_meta($post_id, '_web_conference', $_temp1->web_conference);
						add_post_meta($post_id, '_address', $_temp1->address);
						add_post_meta($post_id, '_check_address', $_temp1->check_address);
						add_post_meta($post_id, '_group_id', $_temp1->group_id);
						add_post_meta($post_id, '_user_id', $_temp1->user_id);
						add_post_meta($post_id, '_kind', $_temp1->kind);
						add_post_meta($post_id, '_video_conference_account_id', $_temp1->video_conference_account_id);
						add_post_meta($post_id, '_video_conference_url', $_temp1->video_conference_url);
						add_post_meta($post_id, '_video_conference_uid', $_temp1->video_conference_uid);
                        
                    }
                }
                return "Meetings Updated successfully";
            }
        }*/ 
		return "No Meetings for Update";
    }


    function import_groups($url) {
        global $wpdb;
		 $static_json = '{
  		"data": [
   		 {
      			"id": 1,
     			 "group_title": "first",
      			"description": null,
     			 "tags": null,
     			 "course_fee": "10000.0",
     			 "taxable": true,
     			 "published": true,
      			"allow_bookings": true,
      			"start_date": null,
     			 "end_date": null,
      			"reg_date": null,
      			"seats": null,
      			"hide_if_full": true,
     			 "show_seats_left": null,
     			 "lessons": null,
      			"status": null,
      			"subscription_plan_id": null,
      			"created_at": "2016-02-15T00:00:00.000Z",
      			"updated_at": "2016-02-15T00:00:00.000Z",
			"translation": [
        				{
          					"id": 1,
         					 "group_id": 3,
         					 "locale": "en",
          					"created_at": "2016-02-17T08:59:31.723Z",
          					"updated_at": "2016-02-17T08:59:31.723Z",
         					 "group_title": "group A",
          					"description": "<p>sssssss</p>\r\n",
          					"tags": "a",
          					"status": "active"
        				}
     			 ]
   		 }
 		 ]
  		
	}

';

        $all_courses_list = json_decode($static_json);
		
        $get_all_languages = $this->get_languages();
        $site_default_language = $get_all_languages->default_language;

        if (!empty($all_courses_list->data)) {

            foreach ($all_courses_list->data as $k => $v) {

                $adept_author_value = get_option('adept_author');
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND meta_value ='" . $site_default_language."_".$v->id . "' ORDER BY post_id DESC LIMIT 0,1 ");
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
					
                    
					add_post_meta($post_id, '_group_id', $site_default_language."_".$v->id);
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
					if (!empty($v->translation)) {
						foreach ($v->translation as $a => $b) {
							$adept_author_value = get_option('adept_author');
							
							$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND  meta_value ='" . $b->locale.'_'.$b->_group_id.'_'.$b->id."'  ORDER BY post_id DESC LIMIT 0,1 ");
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
								
								add_post_meta($post_id, '_group_id', $b->locale.'_'.$b->_group_id.'_'.$b->id);
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
			return "Groups imported successfully";
        }
        /*$temp = $this->getdata($url);
        $adept_author_value = get_option('adept_author');

        if ($temp) {
           foreach ($temp as $_temp) {
            foreach ($temp as $_temp1) {
                $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_group_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;

                if ($postid == '') {
                    // Gather post data.
                    $my_post = array(
                        "post_author" => $adept_author_value,
                        "post_date" => $_temp1->created_at,
                        "post_date_gmt" => $_temp1->created_at,
                        "post_content" => $_temp1->description,
                        "post_excerpt" => $_temp1->description,
                        "post_title" => $_temp1->group_title,
                        "post_status" => 'publish',
                        "comment_status" => 'closed',
                        "ping_status" => 'closed',
                        "post_name" => sanitize_title($_temp1->group_title),
                        "post_modified" => $_temp1->updated_at,
                        "post_modified_gmt" => $_temp1->updated_at,
                        "menu_order" => '0',
                        "post_type" => 'groups',
                        'guid' => ''
                    );
                    // Insert the post into the database.
                    $post_id = wp_insert_post($my_post, $wp_error);

                    add_post_meta($post_id, '_group_id', $_temp1->id);
                    add_post_meta($post_id, '_tags', $_temp1->tags);
                    add_post_meta($post_id, '_course_fee', $_temp1->course_fee);
                    add_post_meta($post_id, '_taxable', $_temp1->taxable);
                    add_post_meta($post_id, '_published', $_temp1->published);
                    add_post_meta($post_id, '_allow_bookings', $_temp1->allow_bookings);
                    add_post_meta($post_id, '_start_date', $_temp1->start_date);
                    add_post_meta($post_id, '_end_date', $_temp1->end_date);
                    add_post_meta($post_id, '_reg_date', $_temp1->reg_date);
                    add_post_meta($post_id, '_seats', $_temp1->seats);
                    add_post_meta($post_id, '_hide_if_full', $_temp1->hide_if_full);
                    add_post_meta($post_id, '_show_seats_left', $_temp1->show_seats_left);
                    add_post_meta($post_id, '_lessons', $_temp1->lessons);
                    add_post_meta($post_id, '_status', $_temp1->status);
                    add_post_meta($post_id, '_subscription_plan_id', $_temp1->subscription_plan_id);
                }
              }
            }
            return "Groups imported successfully";
        } */
		return "No Groups for import";
    }

    function import_instructors($url) {
        $temp = $this->getdata($url);
        $adept_author_value = get_option('adept_author');
        if ($temp) {
            foreach ($temp as $_temp) {
                foreach ($_temp as $_temp1) {
                    $get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "postmeta" . " where meta_key='_instructor_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                    $postid = $get_existing_post_id[0]->post_id;
                    if ($postid == '') {
                        // Gather post data.
                        $my_post = array(
                            "post_author" => $adept_author_value,
                            "post_date" => $_temp1->created_at,
                            "post_date_gmt" => $_temp1->created_at,
                            "post_content" => $_temp1->email,
                            "post_excerpt" => $_temp1->email,
                            "post_title" => $_temp1->email,
                            "post_status" => 'publish',
                            "comment_status" => 'closed',
                            "ping_status" => 'closed',
                            "post_name" => sanitize_title($_temp1->email),
                            "post_modified" => $_temp1->updated_at,
                            "post_modified_gmt" => $_temp1->updated_at,
                            "menu_order" => '0',
                            "post_type" => 'instructors',
                            'guid' => ''
                        );
                        // Insert the post into the database.
                        $post_id = wp_insert_post($my_post, $wp_error);

                        add_post_meta($post_id, '_instructor_id', $_temp1->id);
                        add_post_meta($post_id, '_email', $_temp1->email);
                        add_post_meta($post_id, '_privacy_policy', $_temp1->privacy_policy);
                        add_post_meta($post_id, '_provider', $_temp1->provider);
                        add_post_meta($post_id, '_uid', $_temp1->uid);
                        add_post_meta($post_id, '_system_admin', $_temp1->system_admin);
                        add_post_meta($post_id, '_created_at', $_temp1->created_at);
                        add_post_meta($post_id, '_updated_at', $_temp1->updated_at);
                    }
                }
                return "Instructors imported successfully";
            }
        }
        return "No instructors for import";
        /* if ($temp) {
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

          $user->remove_role('subscriber');
          $user->add_role('instructor');
          }
          return "Intructors imported successfully";
          } */
    }
	function get_languages() {
        $all_languages_json = '{
  		"data": {
   			 "en": "English",
    			"nl": "Dutch",
    			"fr": "French",
    			"ja": "Japanese",
    			"pt": "Portuguese",
    			"ru": "Russian",
   			 "es": "Spanish"
  		},
  		"default_language": "nl",
  		"status": 200
		}';
        $all_languages = json_decode($all_languages_json);
        return $all_languages;
    }

}

?>