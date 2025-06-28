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
}
