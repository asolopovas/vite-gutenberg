<?php

/**
 * Plugin Name:       Gutenberg Vite Blocks Starter Kit
 * Description:       Bundle your Gutenberg blocks with Vite and use the latest JS features.
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lyn-blocks
 *
 * @package           create-block
 */

use Spatie\Ignition\Ignition;
use Lyntouch\Vite;
use Symfony\Component\Dotenv\Dotenv;

defined('ABSPATH') || exit;
define('VITGUT_BASE_PATH', plugin_dir_path(__FILE__));
define('VITGUT_BASE_URL', plugin_dir_url(__FILE__));

require_once(__DIR__ . '/src/helpers.php');
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/filters.php');

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');


if (getenv('APP_ENV') == 'development') {
    add_action('init', function () {
        error_reporting(E_ALL ^ E_DEPRECATED);
    });

    Ignition::make()->register();
}


function print_react_tag()
{
    $hotFileLoc = __DIR__ . '/static/hot';
    if (file_exists($hotFileLoc)) {
        $host = file_get_contents($hotFileLoc);
?>
        <script type="module">
            import RefreshRuntime from "<?php echo $host ?>/@react-refresh"
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => {}
            window.$RefreshSig$ = () => (type) => type
            window.__vite_plugin_react_preamble_installed__ = true
        </script>
<?php
    }
}

add_action('admin_head', 'print_react_tag', 10, 0);


function initGutenbergVite()
{
    register_block_type(__DIR__ . '/src/blocks/test');
}

add_action('init', 'initGutenbergVite');

function loadEditorAssets()
{
    (new Vite([
        'src/blocks/test/index.js',
    ]))->build()->load();
}

add_action('enqueue_block_editor_assets', 'loadEditorAssets');
