<?php
  $wp_adept_lms = new WP_Adept_LMS();
  
  if($_POST){
	  if(trim($_POST['email']) == ''){
		  $error = 'Please enter email';
	  }else{
		  $email = $_POST['email'];
	  }
	  if(trim($_POST['password']) == ''){
		  $error = 'Please enter password';
	  }else{
		  $password = $_POST['password'];
	  }
  }
  
?>
 
<h1>Adept LMS Plugin Settings</h1>
<div class="wrap">
<span><?php echo $error;?></span>
 <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
    <table width="1004" class="form-table">
      <tbody>
        <tr>
          <th width="115"><?php esc_html_e( 'Email:' )?></th>
              <td width="877">
                <input type="text" name="email" value="<?php echo $email;?>" style="width:450px;"/>   
              </td>
        </tr>
        <tr>
              <th><?php esc_html_e('password:')?> </th>
              <td>
                <input type="password" name="password" value="<?php echo $password;?>" style="width:450px;"/>
              </td>
        </tr>
        <tr>
          <th></th>
          <td>
            <p class="submit">
              <input type="submit" class="button-primary" value="Save Authentication" name="save_code" />
            </p>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
