<?php
  $wp_adept_lms = new WP_Adept_LMS();
  if(isset($_POST['import_categories']))
  {
  global $wpdb;
	$table_name = "api_crendential";
	$myrows = $wpdb->get_results( "SELECT access_token FROM ".$table_name );
	$access_token = $myrows[0]->access_token;
	//echo $access_token;
	$ch = curl_init('adeptlms.com/api/v1/course_categories_api?access_token='.$access_token);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded',
    )
);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$result = curl_exec($ch);
//echo $result;
$temp = json_decode($result);

/* $table_name1 = "wp_term_taxonomy";
$table_name2 = "wp_term";

foreach($temp as $_temp)
{
	foreach($_temp as $_temp1)
	{
		$name = $_temp1->name;
		$description = $_temp1->description;
		
		$wpdb->insert("wp_terms", array(
		   "name" => $name,
			"slug" => $name
		));

		$lastid = $wpdb->insert_id;
		
		
	  $wpdb->insert( 
			$table_name1, 
			array( 
				'term_id' => $lastid,
				'taxonomy' => 'genre', 
				'description' => $description
			)
			);
		}
	} */
	echo "<h3>Course category imported successfully</h3>";
}


//course import code 

 if(isset($_POST['import_course']))
  {
  global $wpdb;
	$table_name = "api_crendential";
	$myrows = $wpdb->get_results( "SELECT access_token FROM ".$table_name );
	$access_token = $myrows[0]->access_token;
	//echo $access_token;
	$ch = curl_init('adeptlms.com/api/v1/courses_api?access_token='.$access_token);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded',
    )
);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$result = curl_exec($ch);

$temp = json_decode($result);
echo '<pre>';
var_dump($temp);
die();
$table_name1 = "wp_postmeta";
$table_name2 = "wp_posts";

foreach($temp as $_temp)
{
	foreach($_temp as $_temp1)
	{
		$name = $_temp1->name;
		$description = $_temp1->description;
		
		$wpdb->insert("wp_terms", array(
		   "name" => $name,
			"slug" => $name
		));

		$lastid = $wpdb->insert_id;
		
		
	  $wpdb->insert( 
			$table_name1, 
			array( 
				'term_id' => $lastid,
				'taxonomy' => 'genre', 
				'description' => $description
			)
			);
		}
	}
	echo "Course category imported successfully";
}

 ?>
 
 <h1>Adept LMS</h1>
 <div class="wrap">
  <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
    <table width="1004" class="form-table">
      <tbody>
        <tr>
			<th width="115"><?php esc_html_e( 'Import Categories:' )?></th>
              <td width="877">
                    <input type="submit" name="import_categories" value="Import Categories"/>
              </td>
        </tr>
		<tr>
          <th width="115"><?php esc_html_e( 'Import Course:' )?></th>
              <td width="877">
                    <input type="submit" name="import_course" value="Import Course"/>
              </td>
        </tr>
		<tr>
          <th width="115"><?php esc_html_e( 'Course Update:' )?></th>
              <td width="877">
                    <input type="button" name="course_update" value="Course Update"/>
              </td>
        </tr>
		<tr>
          <th width="115"><?php esc_html_e( 'Class Meeting:' )?></th>
              <td width="877">
                    <input type="button" name="class_meeting" value="Class Meeting"/>
              </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>