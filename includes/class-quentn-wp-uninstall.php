<?php

/**
 * Fired contains definition of tables section
 *
 * @link       https://quentn.com/
 * @since      1.0.0
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 */

/**
 *
 * @since      1.0.0
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 * @author     Quentn Team < info@quentn.com>
 */
class Quentn_Wp_Uninstall {


    public function quentn_uninstall( ) {
        global $wpdb;
        if ( is_multisite() ) {
            // get ids of all sites
            // Retrieve all site IDs from all networks (WordPress >= 4.6 provides easy to use functions for that).
            if ( function_exists( 'get_sites' ) ) {
                $site_ids = get_sites( array( 'fields' => 'ids' ) );
            } else {
                $site_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs;" );
            }
            foreach ( $site_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                $this->uninstall();
                restore_current_blog();
            }
        } else {

            $this->uninstall();
        }
    }

    public function uninstall() {

        global $wpdb;
        //todo use table named with constant
        $table_qntn_restrictions = $wpdb->prefix . 'qntn_restrictions';
        $table_qntn_user_data = $wpdb->prefix . 'qntn_user_data';
        $table_log = $wpdb->prefix . 'qntn_log';

        //delete quentn table
        $wpdb->query(
            "Drop table IF EXISTS ". $table_qntn_restrictions.",".$table_qntn_user_data.",".$table_log
        );


        //delete quentn options
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $wpdb->options WHERE
			 option_name like %s and option_name <> %s",
            '%quentn%',
            'quentn_unique_id'
        ));

        //delete user meta
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $wpdb->usermeta WHERE
			 meta_key like %s",
            '%quentn%'
        ));

        //delete quentn meta data
        delete_post_meta_by_key( '_quentn_post_restrict_meta' );

        //unschedule cron tasks
        $timestamp = wp_next_scheduled( 'quentn_cron_hook' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'quentn_cron_hook' );
        }
    }

}


