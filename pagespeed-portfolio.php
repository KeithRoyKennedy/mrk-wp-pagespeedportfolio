<?php
/**
 * Plugin Name:       PageSpeed Portfolio
 * Plugin URI:        https://wordpress.org/plugins/pagespeed-portfolio/
 * Description:       Showcase your agency's website portfolio with live Google PageSpeed Insights scores. Display Performance, Accessibility, Best Practices, and SEO metrics.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Involved Marketing
 * Author URI:        https://involvedmarketing.co.za/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pagespeed-portfolio
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 */
define( 'PSP_VERSION', '1.0.0' );
define( 'PSP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PSP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Include required files.
 */
require_once PSP_PLUGIN_DIR . 'includes/class-psp-db.php';
require_once PSP_PLUGIN_DIR . 'includes/class-psp-activator.php';
require_once PSP_PLUGIN_DIR . 'includes/class-psp-deactivator.php';
require_once PSP_PLUGIN_DIR . 'includes/class-psp-post-type.php';
require_once PSP_PLUGIN_DIR . 'includes/class-psp-api.php';
require_once PSP_PLUGIN_DIR . 'includes/class-psp-cron.php';
require_once PSP_PLUGIN_DIR . 'includes/class-psp-shortcode.php';
require_once PSP_PLUGIN_DIR . 'admin/class-psp-admin.php';
require_once PSP_PLUGIN_DIR . 'admin/class-psp-settings.php';
require_once PSP_PLUGIN_DIR . 'admin/class-psp-metabox.php';
require_once PSP_PLUGIN_DIR . 'public/class-psp-public.php';

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, array( 'PSP_Activator', 'activate' ) );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, array( 'PSP_Deactivator', 'deactivate' ) );

/**
 * Initialize the plugin.
 */
function psp_init() {
	PSP_Post_Type::init();
	PSP_Cron::init();
	PSP_Shortcode::init();
	PSP_Admin::init();
	PSP_Settings::init();
	PSP_Metabox::init();
	PSP_Public::init();
}
add_action( 'plugins_loaded', 'psp_init' );
