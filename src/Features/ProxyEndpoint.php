<?php

namespace LocalMediaProxy\Features;

/**
 * Class ProxyEndpoint
 *
 * Registers a REST API endpoint on production to serve missing media files
 * to development environments running the Local Media Proxy plugin.
 */
class ProxyEndpoint
{
    /**
     * Registers the proxy endpoint if proxy mode is enabled.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'register_endpoint']);
    }

    /**
     * Registers the REST API route for the proxy endpoint.
     *
     * @return void
     */
    public function register_endpoint(): void
    {
        $is_proxy_enabled = get_option('lmcdn_act_as_proxy', false);
        if (!$is_proxy_enabled) {
            return;
        }

        register_rest_route('lmcdn/v1', '/proxy', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_request'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handles incoming proxy requests, validates them, and serves the requested file.
     *
     * @param \WP_REST_Request $request The incoming REST request.
     * @return \WP_REST_Response|void The REST response object or exits on success.
     */
    public function handle_request(\WP_REST_Request $request)
    {
        $key = sanitize_text_field($request->get_param('key'));
        $expected_key = get_option('lmcdn_proxy_secret', '');
        if (empty($expected_key) || $key !== $expected_key) {
            $this->log_attempt(false, 'Invalid key', $request);
            return new \WP_REST_Response(['error' => 'Unauthorized'], 403);
        }

        $path = sanitize_text_field($request->get_param('path'));
        if (!$path) {
            $this->log_attempt(false, 'Missing path', $request);
            return new \WP_REST_Response(['error' => 'No path specified'], 400);
        }

        $uploads = wp_get_upload_dir();
        $allowed_base = realpath($uploads['basedir']);
        $resolved = realpath(trailingslashit($uploads['basedir']) . ltrim($path, '/'));

        // Validate file path is inside uploads dir and exists
        if (!$resolved || strpos($resolved, $allowed_base) !== 0 || !file_exists($resolved)) {
            $this->log_attempt(false, 'Invalid or missing file', $request);
            return new \WP_REST_Response(['error' => 'File not found'], 404);
        }

        // TODO: Add rate limiting here in future.

        $this->log_attempt(true, 'Served file', $request);

        $mime = wp_check_filetype($resolved)['type'] ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($resolved);
        exit;
    }

    /**
     * Logs each request attempt for auditing purposes.
     *
     * @param bool $success Whether the request was successful.
     * @param string $reason The reason for success or failure.
     * @param \WP_REST_Request $request The REST request object.
     * @return void
     */
    protected function log_attempt(bool $success, string $reason, \WP_REST_Request $request): void
    {
        error_log(sprintf(
            '[LMCDN Proxy] %s | %s | Path: %s | IP: %s',
            $success ? 'SUCCESS' : 'FAIL',
            $reason,
            sanitize_text_field($request->get_param('path')),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
    }
}
