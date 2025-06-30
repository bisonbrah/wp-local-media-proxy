<?php

namespace LocalMediaProxy\Core;

/**
 * Class Internationalization
 *
 * Handles the internationalization functionality of a plugin by loading
 * the text domain to enable translation.
 */
class Internationalization
{
    /**
     * @var string The text domain used for localization or translation purposes.
     */
    private string $text_domain;

    /**
     * Constructor method for initializing the class with a text domain.
     *
     * @param string $text_domain The text domain used for localization or translation purposes.
     * @return void
     */
    public function __construct(string $text_domain)
    {
        $this->text_domain = $text_domain;
    }

    /**
     * Loads the plugin text domain for translation.
     *
     * @return void
     */
    public function load_plugin_textdomain(): void
    {
        load_plugin_textdomain(
            $this->text_domain,
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
