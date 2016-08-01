<?php 

if(!function_exists("pre")) {
	function pre($arr) {
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
}

if(!function_exists("get_val")) {

	function get_val($key) {
		return (isset($_GET[$key])) ? $_GET[$key] : "";
	}

}

if(!function_exists("post_val")) {
	function post($key) {
		return (isset($_POST[$key])) ? $_POST[$key] : "";
	}
}
