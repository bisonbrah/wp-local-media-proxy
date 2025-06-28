<?php
/**
 * Plugin Name: Local Media Proxy
 * Plugin URI:  https://evanghenry.com
 * Description: Transparently proxy missing local WordPress media from a remote CDN or production site while working in development.
 * Version:     0.1.0
 * Author:      Evan Henry
 * Author URI:  https://evanghenry.com
 * Text Domain: wp-local-media-proxy
 * License:     GPLv2 or later
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Autoload Classes
require 'vendor/autoload.php';

// Initialize Core
use LocalMediaProxy\Core\Core;

// Minimum PHP Version
$min_php = '7.4';

// Check the minimum required PHP version and run the plugin.
if (version_compare(PHP_VERSION, $min_php, '>=')) {
    $core = new Core(__FILE__); // Pass __FILE__ for correct paths
    register_activation_hook(__FILE__, [$core, 'activate']);
    register_deactivation_hook(__FILE__, [$core, 'deactivate']);
    $core->run();
}
