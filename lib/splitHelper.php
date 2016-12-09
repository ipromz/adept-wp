<?php 


add_action("init" , "myinit" );

function myinit() {

}

class AWP_split_helper {

	function new_batch($data) {

		$meeting_chunks = array_chunk($data, 200);

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