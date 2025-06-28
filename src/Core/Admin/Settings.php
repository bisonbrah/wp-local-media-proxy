<?php

namespace LocalMediaProxy\Core\Admin;

/**
 * Class Settings
 *
 * Adds the plugin settings page to the WordPress admin, registers the plugin option,
 * and renders the settings page form.
 */
class Settings
{
    /**
     * @var string The option name stored in the wp_options table.
     */
    private string $option_name = 'lmcdn_remote_base_url';

    /**
     * Registers the admin menu page and the plugin settings with WordPress.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Adds the new management page to the WordPress admin dashboard.
     *
     * @return void
     */
    public function add_menu_page(): void
    {
        add_management_page(
            __('Local Media Proxy Settings', 'wp-local-media-proxy'),
            __('Local Media Proxy', 'wp-local-media-proxy'),
            'manage_options',
            'local-media-proxy',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Registers the plugin option, settings section, and field using the Settings API.
     *
     * @return void
     */
    public function register_settings(): void
    {
        register_setting('lmcdn_settings_group', $this->option_name, [
            'sanitize_callback' => [$this, 'sanitize'],
        ]);

        add_settings_section(
            'lmcdn_main_section',
            __('Remote Media Settings', 'wp-local-media-proxy'),
            [$this, 'render_section_description'],
            'local-media-proxy'
        );

        add_settings_field(
            $this->option_name,
            __('Remote Media Base URL', 'wp-local-media-proxy'),
            [$this, 'render_field'],
            'local-media-proxy',
            'lmcdn_main_section'
        );
    }

    /**
     * Outputs a description for the main settings section.
     *
     * @return void
     */
    public function render_section_description(): void
    {
        echo '<p>' . esc_html__(
                'Enter the base URL of your remote media server or CDN. When a local file is missing, media URLs will point here.',
                'wp-local-media-proxy'
            ) . '</p>';
    }

    /**
     * Renders the input field for the CDN base URL.
     *
     * @return void
     */
    public function render_field(): void
    {
        $value = esc_url(get_option($this->option_name, ''));
        printf(
            '<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />',
            esc_attr($this->option_name),
            esc_attr($value)
        );
    }

    /**
     * Sanitizes the input before saving it to the database.
     *
     * @param string $input The raw input from the form.
     * @return string The sanitized URL, or an empty string on validation failure.
     */
    public function sanitize(string $input): string
    {
        $sanitized = esc_url_raw(trim($input));

        if (!filter_var($sanitized, FILTER_VALIDATE_URL)) {
            add_settings_error(
                $this->option_name,
                'lmcdn_invalid_url',
                __('Please enter a valid URL.', 'wp-local-media-proxy'),
                'error'
            );
            return '';
        }

        return $sanitized;
    }

    /**
     * Renders the full settings page in the WordPress admin.
     *
     * @return void
     */
    public function render_settings_page(): void
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Local Media Proxy Settings', 'wp-local-media-proxy'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('lmcdn_settings_group');
                do_settings_sections('local-media-proxy');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
