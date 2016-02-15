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


if (isset($_POST['import_categories'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'course_categories_api?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
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
        $url = $adept_api_url_value . 'courses_api?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->import_course($url);
        $success = $result;
    }
}

//course update code 

if (isset($_POST['course_update'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'course_updates?access_token=' . $adept_access_token_value . '&account_id=' . $adept_account_id_value;
        $result = $adept->update_course($url);
        $success = $result;
    }
}

//meeting import code 

if (isset($_POST['class_meeting'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'group_meetings?access_token=' . $adept_access_token_value . '&id=1' . '&account_id=' . $adept_account_id_value;
        $result = $adept->import_meeting($url);
        $success = $result;
    }
}//meeting import code 

if (isset($_POST['class_group'])) {

    if ($adept_access_token_value == '') {
        $error = "Please enter authentication detail";
    } else {
        $url = $adept_api_url_value . 'groups?access_token=' . $adept_access_token_value . '&id=1' . '&account_id=' . $adept_account_id_value;
        $result = $adept->import_groups($url);
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
?>
<title>Adept LMS</title>
<h1>Adept LMS</h1>
<div class="wrap">
    <form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
        <?php if ($success != '') { ?>
            <div class="updated notice notice-success is-dismissible" id="message"><p><?php echo $success; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <?php if ($error != '') { ?>
            <div class="updated notice notice-error error is-dismissible" id="message"><p><?php echo $error; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <table width="1004" class="form-table">
            <tbody>
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
                    <th width="115"><?php esc_html_e('Class Meeting:') ?></th>
                    <td width="877">
                        <input type="submit" name="class_meeting" value="Class Meeting"/>
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