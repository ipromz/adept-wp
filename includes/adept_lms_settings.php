<?php
  $wp_adept_lms = new WP_Adept_LMS();
if(isset($_POST['save_code']))
{
$ch = curl_init('http://adeptlms.com/api/v1/authentication');
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
  
$data = "email=".$email."&password=".$password."";

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded',
    'Content-Length: ' . strlen($data))
);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

//execute post
$result = curl_exec($ch);
//echo $result;
$temp = json_decode($result);
//print_r($temp->status);
$access_token = $temp->access_token;
echo $access_token;
$date = date('Y-m-d h:i:s', time());
//echo $date;

if ($temp->status==200 || $temp->status=='OK')
{
		
	global $wpdb;
	$table_name = "api_crendential";
	$myrows = $wpdb->get_results( "SELECT email FROM ".$table_name );
	$useremail = $myrows[0]->email;
	$wpdb->get_results( 'SELECT COUNT(*) FROM '.$table_name );
	$count = $wpdb->num_rows;
	echo $count;


	if($count==0)
	{
	$wpdb->insert( 
	$table_name, 
	array( 
		'email' => $email, 
		'password' => $password,
		'access_token' => $access_token,
		'addeddatetime' => $date
	) 
	 
);
	}
	else if ($count == 1)
	{
			if ($email == $useremail)
			{
				$wpdb->query($wpdb->prepare("UPDATE $table_name SET access_token= '$access_token' WHERE email='$useremail'"));
				echo "user already exists in system";
			}
			else{
		
				$wpdb->query($wpdb->prepare("UPDATE $table_name SET email='$email',password='$password',access_token= '$access_token' WHERE email='$useremail'"));
				echo "user details are updated";
	
			}
	}
	
	else{
		echo "invalid credentials";
	}
	
	//echo $useremail;
	
	
	
}
else{
	echo "invalid credentials";
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
