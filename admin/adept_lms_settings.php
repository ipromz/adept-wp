<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


//include_once WPADEPT_PLUGIN_PATH . "lib/lib.php";
$adept = new Wpadept_Lib();
$wp_adept_lms = new Wpadept_LMS();


if (isset($_POST['wpadept_save_code'])) {
       
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

   



    $adept_filter_enabled = 0;
    $adept_cat_filter = array();
    $adept_language = "en";
    if(isset($_POST["adept_language"])) {
        $adept_language = $_POST["adept_language"];
        update_option('adept_language', $adept_language);
    }
    if(isset($_POST["adept_filter_enabled"])) {
        $adept_filter_enabled = $_POST["adept_filter_enabled"];
    }
    update_option('adept_filter_enabled', $adept_filter_enabled);

   if(isset($_POST["adept_cat_filter"])) {
        $adept_cat_filter = $_POST["adept_cat_filter"];
        update_option('adept_cat_filter', $adept_cat_filter);
    }

    
    $url = "https://".$account_id.'.adeptlms.com/api/v1/';
        
    $curl = $url . "authentication";
    $data = "email=" . $email . "&password=" . $password;

    $temp = $adept->postdata($curl, $data);
    //pre($temp); exit;
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
            //add_option('adept_language', $language, '', 'yes');
            add_option('adept_author', $author, '', 'yes');
            
            //update_option('adept_filter_enabled', $adept_filter_enabled);
            //update_option('adept_cat_filter', $adept_cat_filter);

            register_activation_hook(__FILE__, 'my_activation');            
            $success = "Api authenticated succeeded";
        } else {
            update_option('adept_api_url', $url);
            update_option('adept_email', $email);
            update_option('adept_password', md5($password));
            update_option('adept_account_id', $account_id);
            update_option('adept_access_token', $access_token);
            //update_option('adept_language', $language);
            update_option('adept_author', $author);

            //update_option('adept_filter_enabled', $adept_filter_enabled);
            //update_option('adept_cat_filter', $adept_cat_filter);

            $success = "User details updated";
        }
    } else {
        $error = "Api authenticated failed";
    }
}

$url = get_option('adept_api_url');
$email = get_option('adept_email');
$password = get_option('adept_password');
$account_id = get_option('adept_account_id');
$access_token = get_option('adept_access_token');
$author = get_option('adept_author');
$adept_language = get_option('adept_language', "en");


$adept_filter_enabled = get_option("adept_filter_enabled");
$adept_cat_filter = get_option("adept_cat_filter" , array());

echo "<script> var adept_cat_filter =  ".json_encode($adept_cat_filter)." </script>";

?>
<h1>Adept LMS Plugin Settings</h1>
<div class="wrap">
    <form action="" method="post" name="settings_form" id="settings_form">
        <?php if (isset($success)) { ?>
            <div class="updated notice notice-success is-dismissible" id="message"><p><?php echo $success; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <div class="updated notice notice-error error is-dismissible" id="message"><p><?php echo $error; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <table width="1004" class="form-table">
            <tbody>

                <tr>
                    <th width="115"><label for="adept_setting_email"><?php esc_html_e('Email') ?></label></th>
                    <td width="877">
                        <input type="text" id="adept_setting_email" name="email" value="<?php echo esc_attr( $email ); ?>" style="width:450px;"/>   
                    </td>
                </tr>
                <tr>
                    <th><label for="adept_setting_pass"> <?php esc_html_e('Password') ?> </label></th>
                    <td>
                        <input type="password" name="password" value="" style="width:450px;" id="adept_setting_pass"/>
                    </td>
                </tr>
                <tr>
                    <th><label for="adept_setting_account_id"> <?php esc_html_e('Account ID') ?> </label></th>
                    <td>
                        <input type="text" id="adept_setting_account_id" name="account_id" value="<?php echo esc_attr( $account_id ); ?>" style="width:450px;"/>
                    </td>
                </tr>
                <tr>
                    <th><label for="adept_settings_author"> <?php esc_html_e('Set Author') ?></label></th>
                    <td>
                        <?php wp_dropdown_users( array('name' => 'author', 'selected' => $author , 'id' => "adept_settings_author") ); ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="adept_filter_enabled"><?php esc_html_e('Filter Categories'); ?></label></th>
                    <td>
                        <input type="checkbox" name="adept_filter_enabled" id="adept_filter_enabled" value="1" <?php checked($adept_filter_enabled , "1"); ?>>
                    </td>
                </tr>
                <tr id="category_filters" class="hidden">
                    <th>Choose Categories</th>
                    <td>
                        <div class="adept_loader">
                            <img src="<?php echo site_url(); ?>/wp-admin/images/spinner-2x.gif">
                        </div>
                        <div class="adept_cat_filter_select" class="hidden">
                            
                        </div>
                    </td>
                </tr>
                <tr id="chooselanguage" >
                    <th><label for="adept_settings_language">Choose language</label> </th>
                    <td>
                        <select name="adept_language" id="adept_settings_language">
                            <option value="en" <?php selected($adept_language , "en"); ?>> English</option> 
                            <option value="da" <?php selected($adept_language , "da"); ?>> Danish</option>
                            <option value="no" <?php selected($adept_language , "no"); ?>> Norwegian</option>
                            <option value="es" <?php selected($adept_language , "es"); ?>> Spanish</option>
                            <option value="sv" <?php selected($adept_language , "sv"); ?>> Swedish</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th></th>
                    <td>
                        <p class="submit">
                            <input type="submit" class="button-primary" value="Save Authentication" name="wpadept_save_code" />
                        </p>
                    </td>

                </tr>
            </tbody>
        </table>
    </form>
</div>
