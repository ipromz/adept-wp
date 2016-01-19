<?php
  $wp_adept_lms = new WP_Adept_LMS();
?>
  
<div class="wrap">
  <span class='opt-title'><span id='icon-options-general' class='analytics-options'><img src="<?php echo plugins_url('custom/images/wp-logo.png');?>" alt=""></span>
    <?php echo __( 'Adept LMS Plugin Settings', 'wp-Adept_LMS'); ?>


  <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
    <table width="1004" class="form-table">
      <tbody>
        <tr>
          <th width="115"><?php esc_html_e( 'Authentication:' )?></th>
              <td width="877">
                   
              </td>
        </tr>
        <tr>
              <th><?php esc_html_e('Your Access Code:')?> </th>
              <td>
                <input type="text" name="access_code" value="" style="width:450px;"/>
              </td>
        </tr>
        <tr>
          <th></th>
          <td>
            <p class="submit">
              <input type="submit" class="button-primary" value="Save Changes" name="save_code" />
            </p>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
</div>
</div>