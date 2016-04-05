<?php
$wp_adept_lms = new WP_Adept_LMS();

include_once MY_PLUGIN_PATH . "lib/lib.php";

$adept = new WP_Lib();

global $wpdb;
$post_table = $wpdb->prefix . "posts";
$postmeta_table = $wpdb->prefix . "postmeta";
$table_name = $wpdb->prefix . "api_credential";
$table_name1 = $wpdb->prefix . "term_taxonomy";
$table_name2 = $wpdb->prefix . "terms";

$adept_access_token_value = get_option('adept_access_token');
$adept_api_url_value = get_option('adept_api_url');
$adept_account_id_value = get_option('adept_account_id');

$langdata= $adept->get_languages();
$default_language = $langdata->default_language;//course unpublish code if (isset($_POST['unpublish_courses'])) {    if ($adept_access_token_value == '') {        $error = "Please enter authentication detail";    } else {       $url = $adept_api_url_value . 'unpublished_courses?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;        $result = $adept->unpublished_courses($url);        $success = $result;    }}
if (isset($_POST['import_categories'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'course_categories?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->import_category($url);
        if ($result) {
            $error = $result;
        } else {
            $success = 'Course category imported successfully';
        }
    }
}

//course import code 

if (isset($_POST['import_course'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
		$finalResult = array();
        $url = $adept_api_url_value . 'courses?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
		$result = $adept->import_course($url);
		$success = 'Course imported successfully';
		
    }
}

//unpublished course import code 

if (isset($_POST['unpublish_courses'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
		$finalResult = array();
        $url = $adept_api_url_value . 'unpublished_courses?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
		$result = $adept->unpublished_courses($url);
		$success = 'Unpublished courses imported successfully';
		
    }
}


//course update code 

if (isset($_POST['course_update'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'recent_course_updates?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->update_course($url);
        $success = $result;
    }
}

//meeting import code 

if (isset($_POST['class_meeting'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'meetings?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->import_meeting($url);
        $success = $result;
    }
}//meeting import code 

if (isset($_POST['update_meeting'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'recent_meeting_updates?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->update_meeting($url);
        $success = $result;
    }
}


if (isset($_POST['class_group'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'groups?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->import_groups($url);
        $success = $result;
    }
}

if (isset($_POST['update_group'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'recent_group_updates?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->update_groups($url);
        $success = $result;
    }
}

//Import course instructors

if (isset($_POST['import_instructors'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'instructors?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->import_instructors($url);
        $success = $result;
    }
}

//Map WPML language

if (isset($_POST['map_language'])) {
		
		global $wpdb, $sitepress, $sitepress_settings;
		$langdata= $adept->get_languages();
		//$lang_code = '';
		unset($lang_codes);		if(count($langdata->data) > 0){
		foreach($langdata->data as $k => $d){
			if($k == 'pt'){
				$k = 'pt-PT';
			}
			$lang_codes[] = $k;
		}
		//$lang_code = implode(',',$lang_codes);
		//$lang_codes = explode(',',$lang_codes);
		$setup_instance = wpml_get_setup_instance();
		$setup_instance->set_active_languages ( $lang_codes ) ;		}
        $success = 'Languages Map successfully';
}$plugin1 = 'sitepress-multilingual-cms/sitepress.php';$plugin2 = 'wpml-translation-management/plugin.php';
?>
<title>Adept LMS</title>
<h1>Adept LMS</h1>
<div class="wrap">
    <form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>&lang=<?php echo $default_language;?>" method="post" name="settings_form" id="settings_form">
        <?php if ($success != '') { ?>
            <div class="updated notice notice-success is-dismissible" id="message"><p><?php echo $success; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <?php if ($error != '') { ?>
            <div class="updated notice notice-error error is-dismissible" id="message"><p><?php echo $error; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <table width="1004" class="form-table">
            <tbody>			<?php if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){ ?>
				<tr>
                    <th width="115"><?php esc_html_e('Map Language:') ?></th>
                    <td width="877">
                        <input type="submit" name="map_language" value="Map Language"/>
                    </td>
                </tr>			<?php } ?>
                <tr>
                    <th width="115"><?php esc_html_e('Import Categories:') ?></th>
                    <td width="877">
                        <input type="submit" name="import_categories" value="Import Categories"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Import Course:') ?></th>
                    <td width="877">
                        <input type="submit" name="import_course" value="Import Course"/>
                    </td>
                </tr>								
				<tr>                    
					<th width="115"><?php esc_html_e('Unpublish Course:') ?></th>
                    <td width="877">
					<input type="submit" name="unpublish_courses" value="Unpublish Course"/>
                    </td>
				</tr>
                <tr>
                    <th width="115"><?php esc_html_e('Import Intructors:') ?></th>
                    <td width="877">
                        <input type="submit" name="import_instructors" value="Import Instructors"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Course Update:') ?></th>
                    <td width="877">
                        <input type="submit" name="course_update" value="Course Update"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Import Group:') ?></th>
                    <td width="877">
                        <input type="submit" name="class_group" value="Class Group"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Update Group:') ?></th>
                    <td width="877">
                        <input type="submit" name="update_group" value="Update Group"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Class Meeting:') ?></th>
                    <td width="877">
                        <input type="submit" name="class_meeting" value="Class Meeting"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Update Meeting:') ?></th>
                    <td width="877">
                        <input type="submit" name="update_meeting" value="Update Meeting"/>
                    </td>
                </tr>
                <tr>
                    <th width="115"><?php esc_html_e('Short Code for Meeting:') ?></th>
                    <td width="877">
                        <?php esc_html_e('meetings') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>