<?php 
    /*
    Plugin Name: Lingu Plugin
    Plugin URI: http://www.orangecreative.net
    Description: Plugin for Lingu
    Author: Viral Sonawala
    Version: 1.0
    */


	class WP_Adept_LMS{

  // Constructor
    function __construct() {

        add_action( 'admin_menu', array( $this, 'wpa_add_menu' ));
        register_activation_hook( __FILE__, array( $this, 'wpa_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'wpa_uninstall' ) );
    }

    /*
      * Actions perform at loading of admin menu
      */
    function wpa_add_menu() {

        add_wmenu_page( 'plugins.php', 'Adept LMS', 'manage_options', 'adept_lms', array(
                          __CLASS__,
                         'wpa_page_file_path'
                        ), plugins_url('images/wp-logo.png', __FILE__),'2.2.9');

        add_submenu_page( 'adept_lms', 'Adept LMS' . ' Settings', '<b style="color:#f9845b">Settings</b>', 'manage_options', 'adept_lms_settings', array(
                              __CLASS__,
                             'wpa_page_file_path1'
                            ));
    }

    /*
     * Actions perform on loading of menu pages
     */
    function wpa_page_file_path() {

		foreach ( glob( plugin_dir_path( __FILE__ ) . "includes/adept_lms.php" ) as $file ) {
			include_once $file;
		}
    }
	
    /*
     * Actions perform on loading of menu pages
     */
    function wpa_page_file_path1(){

		foreach ( glob( plugin_dir_path( __FILE__ ) . "includes/adept_lms_settings.php" ) as $file ) {
			include_once $file;
		}
    }

    /*
     * Actions perform on activation of plugin
     */
    function wpa_install() {



    }

    /*
     * Actions perform on de-activation of plugin
     */
    function wpa_uninstall() {



    }

}

add_action( 'admin_menu', array( $this, 'wpa_add_menu' ));

add_action( 'init', 'create_post_type' );
function create_post_type() {
  register_post_type( 'courses',
	array(
	  'labels' => array(
		'name' => __( 'Courses' ),
		'singular_name' => __( 'Course' ),
		'menu_name'          => _x( 'Courses', 'admin menu', 'Course' ),
		'name_admin_bar'     => _x( 'Course', 'add new on admin bar', 'Course' ),
		'add_new'            => _x( 'Add New Course', 'Course', 'Course' ),
		'add_new_item'       => __( 'Add New Course', 'Course' ),
		'new_item'           => __( 'New Course', 'Course' ),
		'edit_item'          => __( 'Edit Course', 'Course' ),
		'view_item'          => __( 'View Course', 'Course' ),
		'all_items'          => __( 'All Course', 'Course' ),
		'search_items'       => __( 'Search Course', 'Course' ),
		'parent_item_colon'  => __( 'Parent Course:', 'Course' ),
		'not_found'          => __( 'No Course found.', 'Course' ),
		'not_found_in_trash' => __( 'No Course found in Trash.', 'Course' )
	  ),
	  'public' => true,
	  'has_archive' => true,
	  'supports' => array( 'title', 'editor', 'excerpt' )
	)
  );
}

add_action( 'init', 'create_course_category' );
function create_course_category() {
	$labels = array(
		'name'              => _x( 'Course Category', 'Course Category' ),
		'singular_name'     => _x( 'Course Category', 'Course Category' ),
		'search_items'      => __( 'Search Course Category' ),
		'all_items'         => __( 'All Course Category' ),
		'parent_item'       => __( 'Parent Course Category' ),
		'parent_item_colon' => __( 'Parent Course Category:' ),
		'edit_item'         => __( 'Edit Course Category' ),
		'update_item'       => __( 'Update Course Category' ),
		'add_new_item'      => __( 'Add New Course Category' ),
		'new_item_name'     => __( 'New Course Category Name' ),
		'menu_name'         => __( 'Course Category' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'genre' ),
	);
	register_taxonomy( 'genre', array( 'courses' ), $args );
}

function add_wmenu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null ) {
    global $menu, $admin_page_hooks, $_registered_pages, $_parent_pages;
 
    $menu_slug = plugin_basename( $menu_slug );
 
    $admin_page_hooks[$menu_slug] = sanitize_title( $menu_title );
 
    $hookname = get_plugin_page_hookname( $menu_slug, '' );
 
    if ( !empty( $function ) && !empty( $hookname ) && current_user_can( $capability ) )
        add_action( $hookname, $function );
 
    if ( empty($icon_url) ) {
        $icon_url = 'dashicons-admin-generic';
        $icon_class = 'menu-icon-generic ';
    } else {
        $icon_url = set_url_scheme( $icon_url );
        $icon_class = '';
    }
 
    $new_menu = array( $menu_title, $capability, $menu_slug, $page_title, 'menu-top ' . $icon_class . $hookname, $hookname, $icon_url );
 
    if ( null === $position ) {
        $menu[] = $new_menu;
    } elseif ( isset( $menu[ "$position" ] ) ) {
        $position = $position + substr( base_convert( md5( $menu_slug . $menu_title ), 16, 10 ) , -5 ) * 0.00001;
        $menu[ "$position" ] = $new_menu;
    } else {
        $menu[ $position ] = $new_menu;
    }
 
    $_registered_pages[$hookname] = true;
 
    // No parent as top level
    $_parent_pages[$menu_slug] = false;
 
    return $hookname;
}

new WP_Adept_LMS();

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();

$table_name = 'api_crendential';

$sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  email varchar(55) DEFAULT '' NOT NULL,
  password varchar(55) DEFAULT '' NOT NULL,
  addeddatetime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  UNIQUE KEY id (id)
) $charset_collate;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

?>