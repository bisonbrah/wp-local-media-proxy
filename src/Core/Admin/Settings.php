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
     * @var string The option name for enabling rewrite mode.
     */
    private string $option_rewrite_mode = 'lmcdn_use_proxy';

    /**
     * @var string The option name for enabling proxy mode.
     */
    private string $option_proxy_mode = 'lmcdn_act_as_proxy';

    /**
     * @var string The option name for the shared secret key.
     */
    private string $option_secret_key = 'lmcdn_proxy_secret';

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
        // Register existing Remote Base URL option
        register_setting('lmcdn_settings_group', $this->option_name, [
            'sanitize_callback' => [$this, 'sanitize'],
        ]);

        // Register new options for rewrite mode, proxy mode, and secret key
        register_setting('lmcdn_settings_group', $this->option_rewrite_mode, [
            'sanitize_callback' => [$this, 'sanitize_checkbox'],
        ]);

        register_setting('lmcdn_settings_group', $this->option_proxy_mode, [
            'sanitize_callback' => [$this, 'sanitize_checkbox'],
        ]);

        register_setting('lmcdn_settings_group', $this->option_secret_key, [
            'sanitize_callback' => [$this, 'sanitize_secret_key'],
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
            [$this, 'render_remote_base_url_field'],
            'local-media-proxy',
            'lmcdn_main_section'
        );

        add_settings_field(
            $this->option_rewrite_mode,
            __('Enable Rewrite Mode (Local)', 'wp-local-media-proxy'),
            [$this, 'render_rewrite_mode_field'],
            'local-media-proxy',
            'lmcdn_main_section'
        );

        add_settings_field(
            $this->option_proxy_mode,
            __('Enable Proxy Mode (Production)', 'wp-local-media-proxy'),
            [$this, 'render_proxy_mode_field'],
            'local-media-proxy',
            'lmcdn_main_section'
        );

        add_settings_field(
            $this->option_secret_key,
            __('Shared Secret Key', 'wp-local-media-proxy'),
            [$this, 'render_secret_key_field'],
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
     * Renders the input field for the Remote Media Base URL.
     *
     * @return void
     */
    public function render_remote_base_url_field(): void
    {
        $value = esc_url(get_option($this->option_name, ''));
        printf(
            '<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />',
            esc_attr($this->option_name),
            esc_attr($value)
        );
        echo '<p class="description">' . esc_html__('Base URL of your remote media server or CDN. When a local file is missing, URLs will point here.', 'wp-local-media-proxy') . '</p>';
    }

    /**
     * Renders the checkbox for enabling rewrite mode on local environments.
     *
     * @return void
     */
    public function render_rewrite_mode_field(): void
    {
        // Always send a value: hidden input forces "0" when checkbox is unchecked
        printf(
            '<input type="hidden" name="%1$s" value="0" />',
            esc_attr($this->option_rewrite_mode)
        );

        // Render the actual checkbox
        $checked = checked(1, get_option($this->option_rewrite_mode, 0), false);
        printf(
            '<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s />',
            esc_attr($this->option_rewrite_mode),
            $checked
        );

        echo '<p class="description">' . esc_html__('Enable on local/staging environments to rewrite missing media URLs.', 'wp-local-media-proxy') . '</p>';
    }

    /**
     * Renders the checkbox for enabling proxy mode on production environments.
     *
     * @return void
     */
    public function render_proxy_mode_field(): void
    {
        $checked = checked(1, get_option($this->option_proxy_mode, 0), false);
        printf(
            '<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s />',
            esc_attr($this->option_proxy_mode),
            $checked
        );
        echo '<p class="description">' . esc_html__('Enable on production site to serve missing media via the REST proxy.', 'wp-local-media-proxy') . '</p>';
    }

    /**
     * Renders the input field for the shared secret key.
     *
     * @return void
     */
    public function render_secret_key_field(): void
    {
        $value = esc_attr(get_option($this->option_secret_key, ''));
        printf(
            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />',
            esc_attr($this->option_secret_key),
            $value
        );
        echo '<p class="description">' . esc_html__('Secure shared key for authenticating proxy requests.', 'wp-local-media-proxy') . '</p>';
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
     * Sanitizes checkbox inputs.
     *
     * @param mixed $input The raw input value; can be int, string, or null when unchecked.
     * @return int 1 if checked, 0 if unchecked.
     */
    public function sanitize_checkbox($input): int
    {
        return (!empty($input) && (int)$input === 1) ? 1 : 0;
    }

    /**
     * Sanitizes the shared secret key input.
     *
     * @param string $input The raw input value.
     * @return string The sanitized key string.
     */
    public function sanitize_secret_key(string $input): string
    {
        $sanitized = sanitize_text_field(trim($input));

        if (empty($sanitized)) {
            add_settings_error(
                $this->option_secret_key,
                'lmcdn_empty_key',
                __('Please enter a valid shared secret key.', 'wp-local-media-proxy'),
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
