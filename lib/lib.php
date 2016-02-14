<?php
Class WP_Lib {
	
	function postdata($url,$data) {
		
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
		if($temp){
		foreach ($temp as $_temp) {
            foreach ($_temp as $_temp1) {
                $name = $_temp1->name;
				$description = $_temp1->description;
                $slug = $_temp1->id . '_' .sanitize_title($name);
				$data  = wp_insert_term(
				   $name, // the term 
				  'genre', // the taxonomy
				  array(
					'description'=> $description,
					'parent'=> 0,
					'slug' => $slug 
				  )
				);
            }
        }
		return $data->errors['term_exists'][0];
		} return "No Categories for import";
    }
	
	function import_course($url) {
		global $wpdb;
		$temp = $this->getdata($url);
		$adept_author_value = get_option( 'adept_author' );
		if($temp){
		foreach ($temp as $_temp) {
            foreach ($_temp as $_temp1) {
                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");
				if($_temp1->teasere == ''){ $_temp1->teasere = $_temp1->description;}
				
				$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " .$wpdb->prefix . "postmeta". " where meta_key='_post_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;
				if ($postid == '') {
				// Gather post data.
				$my_post = array(
					"post_author" => $adept_author_value,
					"post_date" => $_temp1->created_at,
					"post_date_gmt" => $_temp1->created_at,
					"post_content" => $_temp1->description,
					"post_excerpt" => $_temp1->teasere,
					"post_title" =>$_temp1->course_title,
					"post_status" => 'publish',
					"comment_status" => 'closed',
					"ping_status" => 'closed',
					"post_name" => sanitize_title($_temp1->course_title),
					"post_modified" => $_temp1->updated_at,
					"post_modified_gmt" => $_temp1->updated_at,
					"menu_order" => '0',
					"post_type" => 'courses',
					'guid'     => ''
				);
				// Insert the post into the database.
				$post_id = wp_insert_post( $my_post, $wp_error );
				$data = wp_set_post_terms( $post_id, $check_term_id_slug[0]->term_id, 'genre' );
				add_post_meta( $post_id, '_post_id',$_temp1->id );
				add_post_meta( $post_id, '_tags',$_temp1->tags );
				add_post_meta( $post_id, '_is_featured',$_temp1->is_featured );
				add_post_meta( $post_id, '_course_fee',$_temp1->course_fee );
				add_post_meta( $post_id, '_sku',$_temp1->sku );
				add_post_meta( $post_id, '_tax_category',$_temp1->tax_category );
				add_post_meta( $post_id, '_allow_discounts',$_temp1->allow_discounts );
				add_post_meta( $post_id, '_subscription',$_temp1->subscription );
				add_post_meta( $post_id, '_booking_count',$_temp1->booking_count );
				}
            }
			return "Courses imported successfully";
        }
		}    return "No Courses for import";
    }
	
	function update_course($url) {
		global $wpdb;
		$temp = $this->getdata($url);
		$adept_author_value = get_option( 'adept_author' );
		if($temp){
		foreach ($temp as $_temp) {
            foreach ($_temp as $_temp1) {
                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");
				if($_temp1->teasere == ''){ $_temp1->teasere = $_temp1->description;}
				
				$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " .$wpdb->prefix . "postmeta". " where meta_key='_post_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;
				if ($postid != '') {
				// Gather post data.
				$my_post = array(
					"ID"        => $postid,
					"post_author" => $adept_author_value,
					"post_date" => $_temp1->created_at,
					"post_date_gmt" => $_temp1->created_at,
					"post_content" => $_temp1->description,
					"post_excerpt" => $_temp1->teasere,
					"post_title" =>$_temp1->course_title,
					"post_name" => sanitize_title($_temp1->course_title),
					"post_modified" => $_temp1->updated_at,
					"post_modified_gmt" => $_temp1->updated_at,
					"post_type" => 'courses'
				);
				// Insert the post into the database.
				$post_id =  wp_update_post( $my_post, $wp_error );
				$data = wp_set_post_terms( $post_id, $check_term_id_slug[0]->term_id, 'genre' );
				update_post_meta( $post_id, '_post_id',$_temp1->id );
				update_post_meta( $post_id, '_tags',$_temp1->tags );
				update_post_meta( $post_id, '_is_featured',$_temp1->is_featured );
				update_post_meta( $post_id, '_course_fee',$_temp1->course_fee );
				update_post_meta( $post_id, '_sku',$_temp1->sku );
				update_post_meta( $post_id, '_tax_category',$_temp1->tax_category );
				update_post_meta( $post_id, '_allow_discounts',$_temp1->allow_discounts );
				update_post_meta( $post_id, '_subscription',$_temp1->subscription );
				update_post_meta( $post_id, '_booking_count',$_temp1->booking_count );
				}
            }
			return "Courses Updated successfully";
        }
		}   return "No Courses for Update";
    }
	
	function import_meeting($url) {
		global $wpdb;
		$temp = $this->getdata($url);
		$adept_author_value = get_option( 'adept_author' );
		if($temp){
		foreach ($temp as $_temp) {
            foreach ($_temp as $_temp1) {
                $check_term_id_slug = $wpdb->get_results("SELECT term_id FROM " . $wpdb->prefix . "terms" . " WHERE slug LIKE '" . $_temp1->course_category_id . "_%'");
				if($_temp1->teasere == ''){ $_temp1->teasere = $_temp1->description;}
				
				$get_existing_post_id = $wpdb->get_results("SELECT post_id FROM " .$wpdb->prefix . "postmeta". " where meta_key='_meeting_id' AND meta_value =" . $_temp1->id . " ORDER BY post_id DESC LIMIT 0,1 ");
                $postid = $get_existing_post_id[0]->post_id;
				if ($postid == '') {
				// Gather post data.
				$my_post = array(
					"post_author" => $adept_author_value,
					"post_date" => $_temp1->created_at,
					"post_date_gmt" => $_temp1->created_at,
					"post_content" => $_temp1->description,
					"post_excerpt" => $_temp1->teasere,
					"post_title" =>$_temp1->course_title,
					"post_status" => 'publish',
					"comment_status" => 'closed',
					"ping_status" => 'closed',
					"post_name" => sanitize_title($_temp1->course_title),
					"post_modified" => $_temp1->updated_at,
					"post_modified_gmt" => $_temp1->updated_at,
					"menu_order" => '0',
					"post_type" => 'meetings',
					'guid'     => ''
				);
				// Insert the post into the database.
				$post_id = wp_insert_post( $my_post, $wp_error );
				
				add_post_meta( $post_id, '_meeting_id',$_temp1->id );
				add_post_meta( $post_id, '_date',$_temp1->date );
				add_post_meta( $post_id, '_start_time',$_temp1->start_time );
				add_post_meta( $post_id, '_end_time',$_temp1->end_time );
				add_post_meta( $post_id, '_status',$_temp1->status );
				add_post_meta( $post_id, '_web_conference',$_temp1->web_conference );
				add_post_meta( $post_id, '_address',$_temp1->address );
				add_post_meta( $post_id, '_class_id',$_temp1->class_id );
				add_post_meta( $post_id, '_check_address',$_temp1->check_address );
				add_post_meta( $post_id, '_group_id_value',$_temp1->group_id_value );
				}
            }
			return "Meetings imported successfully";
        }
		}	return "No Meetings for import";
    }
	
	function import_instructors($url) {
		$temp = $this->getdata($url);
		if($temp){
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
        }   return "Intructors imported successfully";
		}   return "No Categories for import";
    }
	
}



?>