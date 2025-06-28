<?php

namespace LocalMediaProxy\Core;

use LocalMediaProxy\Core\Admin\Admin;
use LocalMediaProxy\Features\Proxy;

/**
 * Class Core
 *
 * This class is responsible for defining the core functionalities of the plugin.
 * It initializes its components, defines hooks for administrative and public-facing
 * functionalities, and manages plugin lifecycle actions such as activation and deactivation.
 */
class Core
{
    /**
     * @var Loader $loader The loader instance responsible for managing WordPress hooks.
     */
    protected Loader $loader;

    /**
     * @var string $plugin_name The unique identifier for the plugin.
     */
    protected string $plugin_name = 'local-media-proxy';

    /**
     * @var string $plugin_basename The basename of the plugin's main file.
     */
    protected string $plugin_basename = '';

    /**
     * @var string $plugin_name_dir The directory path of the plugin.
     */
    protected string $plugin_name_dir = '';

    /**
     * @var string $plugin_name_url The URL path of the plugin.
     */
    protected string $plugin_name_url = '';

    /**
     * @var string $version The current version of the plugin.
     */
    protected string $version = '0.1.0';

    /**
     * @var string $plugin_text_domain The text domain for internationalization.
     */
    protected string $plugin_text_domain = 'local-media-proxy';

    /**
     * @var string $asset_url The url for plugin assets.
     */
    protected string $asset_url = '';

    /**
     * @var bool $logging_enabled Easy check to turn on/off logs.
     */
    private bool $logging_enabled = false;

    /**
     * Initializes the plugin by setting up its properties and defining hooks.
     *
     * @param string $plugin_file The path to the main plugin file.
     * @return void
     */
    public function __construct(string $plugin_file)
    {
        $this->plugin_basename = plugin_basename($plugin_file);
        $this->plugin_name_dir = plugin_dir_path($plugin_file);
        $this->plugin_name_url = plugin_dir_url($plugin_file);
        $this->asset_url = plugin_dir_url($plugin_file) . 'assets/';
        $this->load_dependencies();
        $this->set_locale();
        $this->loader->add_action('plugins_loaded', $this, 'init'); // Hook debug registration
    }

    /**
     * Loads the required dependencies by initializing the loader.
     *
     * @return void
     */
    private function load_dependencies(): void
    {
        $this->loader = new Loader();
    }

    /**
     * Sets the plugin's locale by initializing the internationalization class
     * and registering the action to load the text domain.
     *
     * @return void
     */
    private function set_locale(): void
    {
        $plugin_i18n = new Internationalization($this->plugin_text_domain);

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Initializes debug features after plugins are loaded.
     *
     * @return void
     */
    public function init(): void
    {
        // Register plugin settings, fields, and options page in the WordPress admin
        (new Admin())->register();

        // Enable the media replacement proxy
        (new Proxy())->register();
    }

    /**
     * Activates the plugin and initializes the CRON
     *
     * @return void
     */
    public function activate(): void
    {
        if ($this->logging_enabled) {
            error_log('Local Media Proxy: Plugin activated successfully');
        }
    }

    /**
     * Deactivates the plugin.
     *
     * @return void
     */
    public function deactivate(): void
    {
        if ($this->logging_enabled) {
            error_log('Local Media Proxy: Plugin deactivated successfully');
        }
    }

    /**
     * Executes the run method of the loader.
     *
     * @return void
     */
    public function run(): void
    {
        $this->loader->run();
    }

    /**
     * Retrieves the plugin name.
     *
     * @return string The name of the plugin.
     */
    public function get_plugin_name(): string
    {
        return $this->plugin_name;
    }

    /**
     * Retrieves the loader instance.
     *
     * @return Loader The loader instance.
     */
    public function get_loader(): Loader
    {
        return $this->loader;
    }

    /**
     * Retrieves the version of the plugin or application.
     *
     * @return string The current version.
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /**
     * Retrieves the text domain of the plugin.
     *
     * @return string The text domain used for internationalization.
     */
    public function get_plugin_text_domain(): string
    {
        return $this->plugin_text_domain;
    }
}
