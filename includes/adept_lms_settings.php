<?php
include_once MY_PLUGIN_PATH . "lib/lib.php";
$adept = new WP_Lib();
$wp_adept_lms = new WP_Adept_LMS();
$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
$plugin2 = 'wpml-translation-management/plugin.php';

//if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
if (isset($_POST['save_code'])) {
    if ($_POST) {

        /*if (trim($_POST['api_url']) == '') {
            $error = 'Please enter API URL';
        } else {
            $url = $_POST['api_url'];
        }*/
        if (trim($_POST['email']) == '') {
            $error = 'Please enter email';
        } else {
            $email = $_POST['email'];
        }
        if (trim($_POST['password']) == '') {
            $error = 'Please enter password';
        } else {
            $password = $_POST['password'];
        }
        if (trim($_POST['account_id']) == '') {
            $error = 'Please enter Account Id';
        } else {
            $account_id = $_POST['account_id'];
        }

        if (trim($_POST['author']) == '') {
            $error = 'Please enter Author';
        } else {
            $author = $_POST['author'];
        }

        if (trim($_POST['cron']) == '') {
            $error = 'Please enter CRON';
        } else {
            $cron = $_POST['cron'];
        }
    }
	$url = $account_id.'.adeptlms.com/api/v1/'; 
    $curl = $url . "authentication";
    $data = "email=" . $email . "&password=" . $password;
    $temp = $adept->postdata($curl, $data);
    $access_token = $temp->access_token;
    $language = $temp->language;
    $date = date('Y-m-d h:i:s', time());
	
    if ($temp->status == 200 || $temp->status == 'OK') {

        $adept_access_token_value = get_option('adept_access_token');

        if (!$adept_access_token_value) {

            add_option('adept_api_url', $url, '', 'yes');
            add_option('adept_email', $email, '', 'yes');
            add_option('adept_password', md5($password), '', 'yes');
            add_option('adept_account_id', $account_id, '', 'yes');
            add_option('adept_access_token', $access_token, '', 'yes');
            add_option('adept_language', $language, '', 'yes');
            add_option('adept_author', $author, '', 'yes');
            add_option('adept_cron', $cron, '', 'yes');
			register_activation_hook(__FILE__, 'my_activation');			// added for set cron start /*				function my_activation() {					wp_schedule_event(time(), 'hourly', 'my_hourly_event');				}				add_action('my_hourly_event', 'do_this_hourly');				function do_this_hourly() {					// do something every hour				}				register_deactivation_hook(__FILE__, 'my_deactivation');				function my_deactivation() {					wp_clear_scheduled_hook('my_hourly_event');				}*/// added for set cron end
            $success = "User details inserted successfully";
        } else {
            wp_cache_delete('alloptions', 'options');
            update_option('adept_api_url', $url);
            update_option('adept_email', $email);
            update_option('adept_password', md5($password));
            update_option('adept_account_id', $account_id);
            update_option('adept_access_token', $access_token);
            update_option('adept_language', $language);
            update_option('adept_author', $author);
            update_option('adept_cron', $cron);
            $success = "User details updated";
        }
    } else {
        $error = "Entered invalid credentials";
    }
}
/*}else{
      $error = "Please Install  wpml-translation-management & sitepress-multilingual-cms Plugin to access this page";
}*/
$url = get_option('adept_api_url');
$email = get_option('adept_email');
$password = get_option('adept_password');
$account_id = get_option('adept_account_id');
$access_token = get_option('adept_access_token');
$author = get_option('adept_author');
$cron = get_option('adept_cron');

if ($cron == '1') {
    $select = 'checked="checked"';
}
if ($cron == '0') {
    $unselect = 'checked="checked"';
}
?>
<title>Adept LMS Plugin Settings</title>
<h1>Adept LMS Plugin Settings</h1>
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

                <!-- <tr>
                    <th width="115"><?php //esc_html_e('API URL') ?></th>
                    <td width="877">
                        <input type="text" name="api_url" value="<?php //echo $url; ?>" style="width:450px;"/>  
                        <br/> i.e. xxx.adeptlms.com/api/v1/
                    </td>
                </tr> -->

                <tr>
                    <th width="115"><?php esc_html_e('Email') ?></th>
                    <td width="877">
                        <input type="text" name="email" value="<?php echo $email; ?>" style="width:450px;"/>   
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Password') ?> </th>
                    <td>
                        <input type="password" name="password" value="" style="width:450px;"/>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Account ID') ?> </th>
                    <td>
                        <input type="text" name="account_id" value="<?php echo $account_id; ?>" style="width:450px;"/>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Set CRON') ?> </th>
                    <td>
                        <input type="radio" name="cron" value="1" <?php echo $select; ?> class="widefat" /> True &nbsp;&nbsp;&nbsp;
                        <input type="radio" name="cron" value="0" <?php echo $unselect; ?>  class="widefat" /> False
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Set Author') ?> </th>
                    <td>
                        <?php wp_dropdown_users(array('name' => 'author', 'selected' => $author)); ?>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <p class="submit">
                            <input type="submit" class="button-primary" value="Save Authentication" name="save_code" />
                        </p>
                    </td>

            <br/><p>Only admin user accounts will be permitted</p>
            </tr>
            </tbody>
        </table>
    </form>
</div>
