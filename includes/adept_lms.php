<?php
  $wp_adept_lms = new WP_Adept_LMS();

 ?>
 
 <h1>Adept LMS</h1>
 <div class="wrap">
  <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
    <table width="1004" class="form-table">
      <tbody>
        <tr>
          <th width="115"><?php esc_html_e( 'Import Categories:' )?></th>
              <td width="877">
                    <input type="button" name="import_categories" value="Import Categories"/>
              </td>
        </tr>
		<tr>
          <th width="115"><?php esc_html_e( 'Import Course:' )?></th>
              <td width="877">
                    <input type="button" name="import_course" value="Import Course"/>
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