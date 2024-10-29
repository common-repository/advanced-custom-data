<?php
/*
Plugin Name: Advanced Custom Data
Plugin URI: https://docs.photoboxone.com/advanced-custom-data.html
Description: The Advanced Custom Data to display anywhere. Supporting to Contact Form 7, Advanced Custom Fields, ...
Author: DevUI
Author URI: https://photoboxone.com/donate/?developer=devui
Text Domain: acd
Version: 1.0.21
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('ABSPATH') or die();

function acd_plugin_index()
{
	return __FILE__;
}

require( __DIR__. '/includes/functions.php');

require( __DIR__. '/includes/custom-post.php');

// Contact Form 7
require( __DIR__. '/includes/cf7.php');

// Advanced Custom Fields
require( __DIR__. '/includes/acf.php');