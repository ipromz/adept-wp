

var ADEPT_SYNC_OPTIONS = [
							"import_categories",
							"import_instructors",
							"class_group",
							//"update_group",
							"import_course",
							//"unpublish_courses",
							//"course_update",
							"class_meeting",
							//"update_meeting"
							];
var ADEPT_SYNC_COUNTER = 0;

jQuery(document).ready(function(){

	adept_sync_btn_click();
	jQuery(".group_select, .instructors_select , .wpaselect2").select2();

});

function adept_sync_btn_click() {
	jQuery("#adept_sync_btn").click(function(e) {
		e.preventDefault();
		jQuery(".adept_logs_inner").html("");
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
	}).error(function() {
		as_log("error occured");
		//dont increment, let the same step run again 
		//adept_sync_next_step();
	});


}



function as_log(msg) {
	jQuery(".adept_logs_inner").append("<br>"+msg);
	jQuery(".adept_logs_inner").animate({ scrollTop: jQuery('.adept_logs_inner').prop("scrollHeight")}, 1000);
}