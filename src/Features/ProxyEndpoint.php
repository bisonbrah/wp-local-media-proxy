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
        // Get and sanitize the 'key' parameter from the request
        $key = sanitize_text_field($request->get_param('key'));

        // Retrieve the expected proxy secret key from plugin options
        $expected_key = get_option('lmcdn_proxy_secret', '');

        // Validate the key: if missing or does not match, log and return 403 Unauthorized
        if (empty($expected_key) || $key !== $expected_key) {
            set_transient('lmcdn_last_proxy_error', 'Proxy request failed: Invalid key.', 60);
            $this->log_attempt(false, 'Invalid key', $request);
            return new \WP_REST_Response(['error' => 'Unauthorized'], 403);
        }

        // Get and sanitize the 'path' parameter from the request
        $path = sanitize_text_field($request->get_param('path'));

        // Validate the path: it must not be empty
        if (!$path) {
            set_transient('lmcdn_last_proxy_error', 'Proxy request failed: No path specified.', 60);
            $this->log_attempt(false, 'Missing path', $request);

            return new \WP_REST_Response(['error' => 'No path specified'], 400);
        }

        // Get the absolute base path to the uploads directory
        $uploads = wp_get_upload_dir();
        $allowed_base = realpath($uploads['basedir']);

        // Resolve the absolute path of the requested file inside uploads
        $resolved = realpath(trailingslashit($uploads['basedir']) . ltrim($path, '/'));

        // Validate resolved path:
        // - Must resolve successfully
        // - Must be inside the uploads base directory (prevents path traversal)
        // - Must exist on disk
        if (!$resolved || strpos($resolved, $allowed_base) !== 0 || !file_exists($resolved)) {
            set_transient('lmcdn_last_proxy_error', 'Proxy request failed: File not found or outside uploads.', 60);
            $this->log_attempt(false, 'Invalid or missing file', $request);

            return new \WP_REST_Response(['error' => 'File not found'], 404);
        }

        // Placeholder for rate limiting logic in the future

        // Log successful attempt
        $this->log_attempt(true, 'Served file', $request);

        // Determine the MIME type of the file; default to binary if unknown
        $mime = wp_check_filetype($resolved)['type'] ?: 'application/octet-stream';

        // Send the Content-Type header so the client knows the file type
        header('Content-Type: ' . $mime);

        // Send caching headers to allow long-term browser caching
        header('Cache-Control: public, max-age=31536000');

        // Clear the error transient before serving the file
        delete_transient('lmcdn_last_proxy_error');

        // Output the file content directly to the response
        readfile($resolved);

        // Stop script execution after serving the file
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
