# WP Local Media Proxy
> **⚠️ Work in Progress:**
> This plugin is in early development, is not intended for production use, and may change at any time. Play around, experiment, and feel free to open issues or share feedback!

A lightweight WordPress plugin that proxies missing local media from a remote CDN or production domain — perfect for local development environments that don’t have a full media library.

---

## Features

- Automatically rewrites URLs for missing media to pull from your configured remote site
- Supports local/staging “rewrite mode” and production “proxy mode”
- Uses a shared secret key for secure proxying
- Lightweight and optimized for performance

---

## Description

When working on a local or staging site, you often have some media files but not the complete uploads library. Missing images can break layouts or slow down your workflow. Downloading the entire media directory just to fix a few missing files can be time-consuming and wasteful.

**This plugin solves that problem!** It automatically rewrites missing media URLs to fetch them from your live production site or CDN, letting you develop with confidence — no massive downloads required.

WP Local Media Proxy lets you specify a remote base URL (usually your production server). If WordPress tries to load an image or file that’s missing locally, this plugin dynamically rewrites the URL so the file is served from your remote server instead — saving time, disk space, and headaches.

For production environments, the plugin exposes a secure REST API endpoint that can serve media to local/staging sites, requiring a shared secret key for protection.

---

## Usage Instructions

1. Install and activate the plugin on both your local site and your remote/production site.
2. In your WordPress admin under **Tools → Local Media Proxy**, configure:
    - On your local site:
        - Set the **Remote Media Base URL** to your production domain’s root URL (e.g., `https://yoursite.com`).
        - Enable **Rewrite Mode** (for local/staging).
        - Enter the **Shared Secret Key** (must match the key on your production site).
    - On your production site:
        - Enable **Proxy Mode** (for production).
        - Enter the same **Shared Secret Key** as your local site.
3. That’s it! Missing media files on your local site will load transparently from production.
4. **Log Verbosity** defaults to `'basic'`. More features and use cases coming soon.

---

## Useful WP-CLI Commands

```
# Generate a random 32-character key (use this for your shared secret) - helper
wp eval 'echo wp_generate_password(32, false) . "\n";'

# Zip up the plugin - helper 
zip -r wp-local-media-proxy.zip wp-local-media-proxy -x "*.git*" -x "*.DS_Store*" -x "node_modules/*" -x "*.log"
```

## Dev Notes: To-Do List

### Features
- Add support for batching image requests to optimize performance when many files are missing.
- Implement a log viewer in the admin to track which images were served via proxy.
- Add a counter in the admin dashboard showing how many images were served remotely during development.
- Support multiple remote base URLs to handle other assets (e.g., theme or plugin resources).
- ~~Add more robust error handling and user-friendly admin notices when the proxy fails.~~

### Performance
- Add caching for missing file lookups to reduce repeated proxy requests for truly missing files.
- Optimize REST API response headers (e.g., caching, CORS) for better performance.

### Developer Experience
- Add automated tests (unit/integration) for the rewrite and proxy logic.
- Create a CLI command to reset plugin options to default state.
- ~~Implement logging for proxy failures with adjustable verbosity levels.~~

### Local/Admin Improvements
- Add a button in the plugin admin page to generate and save a new secret key.
- Build a settings validation routine to alert if the remote base URL or secret key are misconfigured.

### General Enhancements
- Add internationalization support for admin notices and UI.
- ~~Update the uninstall script (`uninstall.php`) to clean up plugin options and database entries on removal.~~
- Develop a database upgrade routine to handle future changes or new features requiring schema updates.
- Add a field on the production side of the plugin to specify approved domains for proxy requests (additional security).

### Documentation
- Improve documentation and include screenshots of the settings page.

## Contributing

This is my first public plugin, so I'm learning as I go!  
Ideas, feedback, and contributions from everyone (especially other beginners) are very welcome.

- [Open an issue](https://github.com/bisonbrah/wp-local-media-proxy/issues) to discuss bugs or feature ideas.
- Fork the repo and submit a pull request with your proposed changes.
- If you're new to contributing (like me!), don't hesitate to ask questions in the issues or discusssions — let's figure it out together!

---

## License

This plugin is open source, released under the [GPL v3](LICENSE.txt).
