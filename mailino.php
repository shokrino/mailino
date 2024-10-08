<?php
defined( 'ABSPATH' ) || exit;
/*
Plugin Name: Mailino
Plugin URI: https://shokrino.com/mailino
Description: The most useful email subscription list plugin
Author: Shokrino Team
Version: 0.0.1
Author URI: https://shokrino.com
Textdomain: mailino
*/
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_data_name = get_file_data(__FILE__, array('Plugin Name' => 'Plugin Name'), false);
$current_theme = wp_get_theme()->get( 'Name' );
$plugin_version = $plugin_data['Version'];
$plugin_name = $plugin_data_name['Plugin Name'];
$plugin_textdomain = $plugin_data_name['Plugin Name'];
define('MILIN_NAME', $plugin_name);
define('MILIN_VERSION', $plugin_version);
define('MILIN_TEXTDOMAIN', $plugin_version);
define('MILIN_PATH' , WP_CONTENT_DIR.'/plugins'.'/mailino');
define('MILIN_URL' , plugin_dir_url( __DIR__ ).'mailino');
define('MILIN_INC' , MILIN_PATH.'/inc');
define('MILIN_TMPL' , MILIN_PATH.'/inc/templates');
define('MILIN_ASSETS' , MILIN_URL.'/assets');
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}