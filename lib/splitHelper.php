<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wpadept_split_helper {

	function new_batch($data) {

		$SIZE_OF_CHUNK = 200;

		$meeting_chunks = array_chunk($data, $SIZE_OF_CHUNK); 

		update_option("adept_meetings_batch" , $meeting_chunks);

	}

	function has_incomplete_batch() {
		$data = get_option("adept_meetings_batch");
		if(is_array($data) && count($data)) {
			return true;
		}
		else {
			false;
		}
	}

	function get_next_batch() {
		$data = get_option("adept_meetings_batch");
		if(is_array($data) && count($data)) {
			$return = $data[0];
			
			unset($data[0]);
			
			$data = array_values($data);
			update_option("adept_meetings_batch"  , $data);
			
			return $return;
		}
		else {
			update_option("adept_meetings_batch" , "");		
			return false;
		}

	}


}