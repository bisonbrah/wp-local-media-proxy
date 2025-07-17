<?php

namespace LocalMediaProxy\Core;

/**
 * Class Logger
 *
 * Provides centralized logging with adjustable verbosity for
 * the Local Media Proxy plugin.
 *
 * Verbosity levels (set via the ‘lmcdn_log_level’ option):
 * - none     : no logs at all
 * - basic    : only error-level logs
 * - detailed : both error- and info-level logs
 */
class Logger
{
    /**
     * Logs a message at the given level if the current verbosity allows it.
     *
     * @param string $message The message to log.
     * @param string $level The log level: 'error' or 'info' (default 'info').
     * @return void
     */
    public static function log(string $message, string $level = 'info'): void
    {
        if (!self::shouldLog($level)) {
            return;
        }

        // Prefix with plugin tag and level, then send to PHP error log
        error_log(sprintf('[LMCDN %s] %s', strtoupper($level), $message));
    }

    /**
     * Determines whether messages at the given level should be logged,
     * based on the lmcdn_log_level option.
     *
     * @param string $level The log level – 'error' or 'info'.
     * @return bool True if we should log; false otherwise.
     */
    protected static function shouldLog(string $level): bool
    {
        // Retrieve the user’s verbosity setting (default to 'basic')
        $verbosity = get_option('lmcdn_log_level', 'basic');

        if ($verbosity === 'none') {
            // No logging at all
            return false;
        }

        if ($level === 'error') {
            // Errors always log in basic or detailed
            return in_array($verbosity, ['basic', 'detailed'], true);
        }

        // Info-level only logs in detailed mode
        return $verbosity === 'detailed';
    }
}
