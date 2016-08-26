

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
	jQuery("#filter_enabled").change(filter_enabled_fn);
	filter_enabled_fn();
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

var ADEPT_CATEGORIES_LIST = [];

function filter_enabled_fn() {

	if(jQuery("#filter_enabled").prop("checked")) {
		jQuery("#category_filters").removeClass("hidden");
		adept_cat_filter_ajax();
	}
	else {
		jQuery("#category_filters").addClass("hidden");
	}
  
}

function adept_cat_filter_ajax() {
	if(!ADEPT_CATEGORIES_LIST.length) {
		
		var data = {
			action: "adept_get_cats"
		};

		jQuery.get(ajaxurl , data , function(res) {
			try{

				res = JSON.parse(res);
				adept_populate_category_filter_select(res.data);
				ADEPT_CATEGORIES_LIST = res.data;
				
			}catch(ex) {
				console.log("something went wrong!"+ex.message);		
			}
		})

	}
	else {
		adept_populate_category_filter_select(ADEPT_CATEGORIES_LIST); 
	}
}

function adept_populate_category_filter_select(cats) {
	
	var parent = jQuery(".adept_cat_filter_select");
	jQuery(".adept_loader").addClass("hidden");
	parent.removeClass("hidden");
	parent.html(""); 
	

	var html = "<select class='adept_cat_filter' multiple name='adept_cat_filter[]'>";	
	for(i in cats) {
		var cat = cats[i];
		var selected = "";
		console.log("promz: "+cat.id+": "+adeptInArray(cat.id, adept_cat_filter));
		if(adeptInArray(cat.id, adept_cat_filter)) {
			selected = " selected='selected' ";
		}
		html +=  "<option value='"+cat.id+"' "+selected+">"+cat.name+"</option>";
	}
	html += "</select>";
	parent.html(html);

	jQuery(".adept_cat_filter").select2();

}

function adeptInArray(val , arr ) {
	for( i in arr ) {
		if(arr[i] == val) {
			return true;
		}
	}
	return false;
}