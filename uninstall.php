<?php
/**
 * Uninstall script for the Local Media CDN plugin.
 *
 * Cleans up all plugin options and any transients starting with 'lmcdn_' on uninstall.
 * Compatible with both single-site and multisite installations.
 */

// Exit if accessed directly or if uninstall not called from WordPress.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * List of plugin option names to remove on uninstall.
 * // TODO: Convert these options to constants
 *
 */
$options = [
    // Settings
    'lmcdn_remote_base_url',
    'lmcdn_use_proxy',
    'lmcdn_act_as_proxy',
    'lmcdn_proxy_secret',
    // Logger
    'lmcdn_log_level',
];

// Delete plugin options for single-site and multisite installations.
foreach ($options as $option) {
    delete_option($option);       // For current site
    delete_site_option($option);  // For network/global (multisite) context
}

/**
 * Delete all transients (and their timeouts) that start with 'lmcdn_'.
 * This future-proofs the cleanup for any new transients using the naming convention.
 */
global $wpdb;
$prefix = 'lmcdn_';
$like = $wpdb->esc_like($prefix) . '%';

// Clean transients and their timeouts in wp_options
$transient_options = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
        '_transient_' . $like,
        '_transient_timeout_' . $like
    )
);

foreach ($transient_options as $option) {
    delete_option($option);
}

// For multisite: Clean transients in sitemeta (rarely used, but included for completeness)
if (is_multisite()) {
    $transient_sitemeta = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT meta_key FROM $wpdb->sitemeta WHERE meta_key LIKE %s OR meta_key LIKE %s",
            '_site_transient_' . $like,
            '_site_transient_timeout_' . $like
        )
    );

    foreach ($transient_sitemeta as $meta_key) {
        delete_site_option($meta_key);
    }
}
