<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://quentn.com/
 * @since      1.0.0
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 * @author     Quentn Team < info@quentn.com>
 */
class Quentn_Wp_Deactivator {

    private $is_network_wide;

    public function __construct($is_network_wide)
    {
        $this->is_network_wide = $is_network_wide;
    }
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public  function deactivate() {
        global $wpdb;
        if ( $this->is_network_wide ) {
            // get ids of all sites
            // Retrieve all site IDs from all networks (WordPress >= 4.6 provides easy to use functions for that).
            if ( function_exists( 'get_sites' ) ) {
                $site_ids = get_sites( array( 'fields' => 'ids' ) );
            } else {
                $site_ids = $wpdb->get_col(  "SELECT blog_id FROM $wpdb->blogs;" );
            }
            foreach ( $site_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                $this->quentn_perform_deactivation( $blog_id );
                restore_current_blog();
            }
        } else {
            //deactivated on a single site, in a multi-site
            $this->quentn_perform_deactivation( $wpdb->blogid );
        }
    }

    private function quentn_perform_deactivation() {
        delete_option('quentn_cookie_notice_dismiss');
        delete_option('quentn_member_plugin_notice_dismiss');
        flush_rewrite_rules();
    }


}
