<?php
/*
Plugin Name: Alumier Links
Description: Generate link from text embraced by {{ and }}.
Version: 1.1
Author: Huey Zhou
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
require_once 'alumierlink-class.php';
$alumierLink = new AlumierLink;
// admin
add_action('admin_menu', array(&$alumierLink, 'alumierlink_menu'));
// front-end
add_action('the_content', array(&$alumierLink, 'alumierlink_interpreter'));
