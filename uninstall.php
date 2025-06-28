<?php

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete any settings from database
delete_option('lightweight_debug_settings');
