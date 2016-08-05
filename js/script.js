

var ADEPT_SYNC_OPTIONS = [
							"import_categories",
							"import_course",
							"unpublish_courses",
							"import_instructors",
							//"course_update",
							"class_group",
							"update_group",
							"class_meeting",
							"update_meeting"
							];
var ADEPT_SYNC_COUNTER = 0;

jQuery(document).ready(function(){

	adept_sync_btn_click();

});

function adept_sync_btn_click() {
	jQuery("#adept_sync_btn").click(function() {
		ADEPT_SYNC_COUNTER = 0;
		adept_sync_next_step();
	});
}


function adept_sync_next_step() {
	
	if(ADEPT_SYNC_COUNTER == 0) {
		as_log("initializing")	
	}
	
	as_log("running: "+ADEPT_SYNC_OPTIONS[ADEPT_SYNC_COUNTER]);
	var data = {
		step: ADEPT_SYNC_OPTIONS[ADEPT_SYNC_COUNTER],
		action: "adept_sync"
	};
	jQuery.get(ajaxurl , data , function(res) {
		
		as_log("----------------server response----------------");
		as_log(res);
		as_log("----------------server response ends----------------");
		
		ADEPT_SYNC_COUNTER++;

		if(ADEPT_SYNC_COUNTER < ADEPT_SYNC_OPTIONS.length) {
			adept_sync_next_step();
		}
		else {
			as_log("sync complete");		
		}
	});


}



function as_log(msg) {
	jQuery(".adept_logs_inner").append("<br>"+msg);
}