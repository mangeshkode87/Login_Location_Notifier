<?php
/*
Plugin Name: Login Location Notifier
Plugin URI:  http://www.clariontechnologies.co.in
Description: Plugin will notify user with its login location
Version:     0.1
Author:      Mangesh Kode,Clarionwpdeveloper
Author URI:  http://www.clariontechnologies.co.in
*/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}                   
define('LLN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LLN_PLUGIN_DIRPATH',plugin_dir_path(__FILE__));
if (!class_exists('Login_Location_Notifier')) {
    require_once 'classes/login_location_notifier.php';
}
new Login_Location_Notifier();