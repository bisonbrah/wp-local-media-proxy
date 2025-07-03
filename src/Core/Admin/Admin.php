<?php

namespace LocalMediaProxy\Core\Admin;

class Admin
{
    /**
     * Registers the settings by initializing and invoking the register method.
     *
     * @return void
     */
    public function register(): void
    {
        $settings = new Settings();
        $settings->register();
    }

    /**
     * Registers hooks for the admin area, including notices and future admin scripts/styles.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('admin_notices', [$this, 'showProxyErrors']);
    }

    /**
     * Displays an admin notice if a proxy error has been stored in a transient.
     *
     * This method checks for the 'lmcdn_last_proxy_error' transient and, if set,
     * outputs a dismissible admin error notice in the WordPress dashboard.
     *
     * @return void
     */
    public function showProxyErrors(): void
    {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'tools_page_local-media-proxy') {
            return;
        }

        $error = get_transient('lmcdn_last_proxy_error');
        if ($error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
    }
}
