<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://quentn.com/
 * @since             1.0.0
 * @package           Quentn_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       Quentn WP
 * Plugin URI:        https://docs.quentn.com/de/beta-quentn-wordpress-plugin/installieren-und-verbinden
 * Description:       This plugin allows you to restrict access to specific pages, create custom access links and create dynamic page countdowns. Optionally, you can connect your Quentn account to your WordPress installation to share contacts and manage access restrictions through Quentn.
 * Version:           1.2.12
 * Author:            Quentn.com GmbH
 * Author URI:        https://quentn.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       quentn-wp
 * Domain Path:       /languages
 * Elementor tested up to: 3.9.0
 * Elementor Pro tested up to: 3.9.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( "QUENTN_WP_PLUGIN_DIR", plugin_dir_path( __FILE__ ) );
define( "QUENTN_WP_PLUGIN_URL", plugin_dir_url(  __FILE__ ) );
define( 'QUENTN_WP_VERSION', '1.2.12' );
define( 'QUENTN_WP_DB_VERSION', '1.1' );

define( "TABLE_QUENTN_RESTRICTIONS", 'qntn_restrictions' );
define( "TABLE_QUENTN_USER_DATA", 'qntn_user_data' );
define( "TABLE_QUENTN_LOG", 'qntn_log' );
define( 'QUENTN_WP_ACCESS_ADDED_BY_API', 1 );
define( 'QUENTN_WP_ACCESS_ADDED_MANUALLY', 2 );

define( 'QUENTN_WP_ACCESS_REVOKED_BY_API', 1 );
define( 'QUENTN_WP_ACCESS_REVOKED_MANUALLY', 2 );

define( 'QUENTN_WP_LOGIN_URL_ALREADY_USED', 1 );
define( 'QUENTN_WP_LOGIN_SECURITY_FAILURE', 2 );
define( 'QUENTN_WP_LOGIN_URL_EXPIRED', 3 );




/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-quentn-wp-activator.php
 */
function activate_quentn_wp( $is_network_wide ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-quentn-wp-activator.php';
    $activator = new Quentn_Wp_Activator( $is_network_wide );
    $activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-quentn-wp-deactivator.php
 */
function deactivate_quentn_wp( $is_network_wide ) {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-quentn-wp-deactivator.php';
    $deactivator = new Quentn_Wp_Deactivator( $is_network_wide );
    $deactivator->deactivate();
}

register_activation_hook( __FILE__, 'activate_quentn_wp' );
register_deactivation_hook( __FILE__, 'deactivate_quentn_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-quentn-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_quentn_wp() {

	$plugin = new Quentn_Wp();
	$plugin->run();

}
run_quentn_wp();
