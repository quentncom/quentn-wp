<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Cron
{
    /**
     * Constructor method.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'set_quentn_cron' ) );
        add_action( 'quentn_cron_hook', array( $this, 'quentn_cron_exec' ) );
    }

    public function set_quentn_cron()
    {
        if ( ! wp_next_scheduled( 'quentn_cron_hook' ) ) {
            wp_schedule_event( time(), 'daily', 'quentn_cron_hook' );
        }
    }

    /**
     * Delete expired data
     *
     * @return void
     */
    public function quentn_cron_exec() {
        //Delete all expired vu dates from wp_usermeta table
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $wpdb->usermeta WHERE
             meta_key = %s and meta_value < %d",
            'quentn_reset_pwd_vu',
            time()
        ));

		//delete logs
	    $expiry_days = get_option( 'quentn_log_expire_days', 365 );
	    $expiry_days_ago = time() - ( $expiry_days * 86400 ); // Calculate days as a Unix timestamp
	    $table_log = $wpdb->prefix . 'qntn_log';
		$wpdb->query($wpdb->prepare(
            "DELETE FROM $table_log WHERE created_at < %d",
			$expiry_days_ago
        ));
    }

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new self;
        }

        return $instance;
    }
}

Quentn_Wp_Cron::get_instance();