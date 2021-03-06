<?php

/**
 * The plugin bootstrap file
 *
 * Plugin Name:       Debug Log - Config Tool
 * Plugin URI:        #
 * Description:       Simple Debug log and Debug Toggle Tool
 * Version:           1.0.0
 * Author:            Lukman Nakib
 * Author URI:        https://nkb-bd.github.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       DebugLogConfigTool
 */
if (!defined('ABSPATH')) {
    exit;
}

define('DLCT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DLCT_PLUGIN_VERSION', '1.0.0');
define('DLCT_PLUGIN_MAIN_FILE', __FILE__);
define('DLCT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DLCT_PLUGIN_DIR', plugin_dir_path(__FILE__));

include dirname( __FILE__ ) . '/autoload.php';
add_action('plugins_loaded', function () {
    (new DebugLogConfigTool\DLCT_Bootstrap())->init();
}, 10, 1);
register_activation_hook(DLCT_PLUGIN_MAIN_FILE, function () {
    DebugLogConfigTool\DLCT_Bootstrap::activate();
});






