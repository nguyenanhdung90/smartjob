<?php
/*
Plugin Name: menuphaisingle
Plugin URI: http://on1.vn
Description: widget single menu phai
Author: nguyen anh dung
Version: 1.0
Author URI: on1.vn
*/
/*
 * Khởi tạo widget item
 */
if (!class_exists('My_First_Plugin_Demo')) {
    class My_First_Plugin_Demo
    {
        function __construct()
        {
			//wp_mail('nadungnd@gmail.com', 'The subject nguyen anh dung', 'The message noi dung tin nhan');

			register_activation_hook(__FILE__, 'my_activation');
			
add_action('my_hourly_event', 'do_this_hourly');
			/*
            if (!function_exists('add_shortcode')) {
                return;
            }
            add_shortcode('hello', array(&$this, 'hello_func'));
			*/
        }



function my_activation() {
	wp_schedule_event(time(), 'hourly', 'my_hourly_event');
}


function do_this_hourly() {
	// do something every hour
		global $wpdb;
	$wpdb->query("INSERT INTO  bang (name) VALUES ('dung')");
}


		
		/*

        function hello_func($atts = array(), $content = null)
        {
            extract(shortcode_atts(array('name' => 'World'), $atts));
            return '<div><p>Hello ' . $name . '!!!</p></div>';
        }
		*/
    }
}
function mfpd_load()
{
    global $mfpd;
    $mfpd = new My_First_Plugin_Demo();
}

add_action('plugins_loaded', 'mfpd_load');