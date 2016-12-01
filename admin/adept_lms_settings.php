<?php
include_once MY_PLUGIN_PATH . "lib/lib.php";
$adept = new WP_Lib();
$wp_adept_lms = new WP_Adept_LMS();
//$plugin1 = 'sitepress-multilingual-cms/sitepress.php';
//$plugin2 = 'wpml-translation-management/plugin.php';

//if(is_plugin_active($plugin1) && is_plugin_active($plugin2)){
if (isset($_POST['save_code'])) {
       
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
            add_option('adept_cron', $cron, '', 'yes');
            
            //update_option('adept_filter_enabled', $adept_filter_enabled);
            //update_option('adept_cat_filter', $adept_cat_filter);

            register_activation_hook(__FILE__, 'my_activation');            
            $success = "Api authenticated succeeded";
        } else {
            wp_cache_delete('alloptions', 'options');
            update_option('adept_api_url', $url);
            update_option('adept_email', $email);
            update_option('adept_password', md5($password));
            update_option('adept_account_id', $account_id);
            update_option('adept_access_token', $access_token);
            //update_option('adept_language', $language);
            update_option('adept_author', $author);
            update_option('adept_cron', $cron);

            //update_option('adept_filter_enabled', $adept_filter_enabled);
            //update_option('adept_cat_filter', $adept_cat_filter);

            $success = "User details updated";
        }
    } else {
        $error = "Api authenticated failed";
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
$adept_language = get_option('adept_language', "en");

if ($cron == '1') {
    $select = 'checked="checked"';
}
if ($cron == '0') {
    $unselect = 'checked="checked"';
}

$adept_filter_enabled = get_option("adept_filter_enabled");
$adept_cat_filter = get_option("adept_cat_filter" , array());

echo "<script> var adept_cat_filter =  ".json_encode($adept_cat_filter)." </script>";

?>
<title>Adept LMS Plugin Settings</title>
<h1>Adept LMS Plugin Settings</h1>
<div class="wrap">
    <form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
        <?php if (isset($success)) { ?>
            <div class="updated notice notice-success is-dismissible" id="message"><p><?php echo $success; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <div class="updated notice notice-error error is-dismissible" id="message"><p><?php echo $error; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php } ?>
        <table width="1004" class="form-table">
            <tbody>

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
                        <input type="radio" name="cron" value="1" <?php checked($cron , 1) ?> class="widefat" /> True &nbsp;&nbsp;&nbsp;
                        <input type="radio" name="cron" value="0" <?php checked($cron , 0) ?>  class="widefat" /> False
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Set Author') ?> </th>
                    <td>
                        <?php wp_dropdown_users(array('name' => 'author', 'selected' => $author)); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Filter Categories'); ?></th>
                    <td>

                        <input type="checkbox" name="adept_filter_enabled" id="filter_enabled" value="1" <?php checked($adept_filter_enabled , "1"); ?>>
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
                    <th>Choose language</th>
                    <td>
                        <select name="adept_language" >
                            <option value="en" <?php selected($adept_language , "en"); ?> > English</option> 
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
                            <input type="submit" class="button-primary" value="Save Authentication" name="save_code" />
                        </p>
                    </td>

            <br/><p>Only admin user accounts will be permitted</p>
            </tr>
            </tbody>
        </table>
    </form>
</div>
