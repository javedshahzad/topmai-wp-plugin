<?php 
/* 
Plugin Name: Topmai Plugin
Plugin URI: http://www.google.com
Description: Top mai backend rest api plugin
Author: Javed Shahzad
Author URI: http://www.google.com
Version:1.1.0
*/
register_activation_hook(__FILE__,'form_data_activation');
register_deactivation_hook(__FILE__,'form_data_deactivation');

function form_data_activation()
{
global $wpdb;
$database=$wpdb;
$table = $wpdb->prefix."user_data";
// print_r($database);
// exit;
$sql="CREATE TABLE $table (
`id` int(150) NOT NULL,
`name` varchar(150) NOT NULL,
`class` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$sql1="ALTER TABLE $table
ADD PRIMARY KEY (`id`);";
$sql2="ALTER TABLE $table
MODIFY `id` int(150) NOT NULL AUTO_INCREMENT;";
$wpdb->query($sql);
$wpdb->query($sql1);
$wpdb->query($sql2);
}
function form_data_deactivation()
{
global $wpdb;
$table = $wpdb->prefix."user_data";
$sql="DROP TABLE $table";
$wpdb->query($sql);

}
function owt_add_custom_menu(){
add_menu_page("Top Mai","Top Mai","manage_options","my-menu","my_menu_function","dashicons-admin-site",20);
add_submenu_page("my-menu","Dashboard","Dashboard","manage_options","topmai","owt_submenu_fn");
add_submenu_page("my-menu","About","About","manage_options","topmai-about","owt_submenu_About");
add_submenu_page("my-menu","Contact","Contact","manage_options","topmai-contact","owt_submenu_Contact");
add_submenu_page("my-menu","Services","Services","manage_options","topmai-services","owt_submenu_Services");
}

add_action('admin_menu','owt_add_custom_menu');
function my_menu_function(){
include('dashboard.php');

}
function owt_submenu_Services(){
include('services.php');
}
function owt_submenu_Contact(){
include('contact.php');
}

function owt_submenu_fn(){
echo "This Is my custom menu";
}
function owt_submenu_About(){
include('about.php');
}

function owt_cpt(){
$args=array(
'public'=>true,
'label'=>"OWT custom"
);
register_post_type('owt_book',$args);

}

add_action("init","owt_cpt");

add_shortcode('my_shortcode222', 'create_shortcode');
function create_shortcode(){
include('dashboard.php');
//return "<h2>Hello world !</h2>";
}
define("wp_plugin_dir",plugin_dir_url(__FILE__));

function wp_plugin_script(){
	echo "oooooo";
	wp_enqueue_style("wp-style_css",wp_plugin_dir.'assets/css/style.css');
	wp_enqueue_script('wp-js',wp_plugin_dir.'assets/js/script.js','jQuery','1.0.0',true);
	   wp_localize_script( 'wp-ajax', 'wp_ajax_my_url',
	    array( 'ajax_url_admin' => admin_url( 'admin-ajax.php' )));
}

add_action("wp_enqueue_scripts","wp_plugin_script");
function book_my_schedule_admin_js(){
	   wp_localize_script( 'wpac-ajax', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
}

 add_action('admin_enqueue_scripts','book_my_schedule_admin_js' );

function wp_ajax_function(){
	         global $wpdb;
            $table_name = $wpdb->prefix . "users";
             $data = $wpdb->get_results("SELECT * FROM $table_name");
             // $data1 = [];
              array_push($data);
              echo json_encode($data);
                wp_die();

}
add_action("wp_ajax_wp_ajax_function","wp_ajax_function");
add_action("wp_ajax_nopriv_wp_ajax_function","wp_ajax_function");
   include('restapi.php');
?>