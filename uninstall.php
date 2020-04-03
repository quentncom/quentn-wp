<?php
/**
 * Trigger this file on Plugin uninstall
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-quentn-wp-uninstall.php';

$uninstall_handler = new Quentn_Wp_Uninstall();
$uninstall_handler->quentn_uninstall();
