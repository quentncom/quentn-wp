<?php

/**
 * Fired during plugin activation
 *
 * @link       https://quentn.com/
 * @since      1.0.0
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 * @author     Quentn Team < info@quentn.com>
 */
class Quentn_Wp_Activator {


    private $is_network_wide;

    public function __construct( $is_network_wide )
    {
        $this->is_network_wide = $is_network_wide;
    }

    /**
	 * Activate Plugin
	 *
	 * @since    1.0.0
     *
	 */
	public function activate() {

        global $wpdb;
        if ( $this->is_network_wide ) {
            // get ids of all sites
            // Retrieve all site IDs from all networks (WordPress >= 4.6 provides easy to use functions for that).
            if ( function_exists( 'get_sites' ) ) {
                $site_ids = get_sites( array( 'fields' => 'ids' ) );
            } else {
                $site_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs;" );
            }

            foreach ( $site_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                // create tables for each site
                $this->quentn_perform_activation( $blog_id );
                restore_current_blog();
            }
        } else {
            //activated on a single site
            $this->quentn_perform_activation( $wpdb->blogid );
        }
	}

    /**
     * Plugin activation process
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function quentn_perform_activation() {

        global $wpdb;
        $table_name = $wpdb->prefix. QUENTN_TABLE_NAME;

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          page_id mediumint(10) unsigned NOT NULL,
          email varchar(255) NOT NULL,
          email_hash varchar(255),
          created_at int NOT NULL,        
          PRIMARY KEY  (page_id, email)
        )";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        update_option("quentn_db_version", '1.0');
        //add unique id for quentn
        if ( ! get_option("quentn_unique_id" ) ) {
            add_option("quentn_unique_id", uniqid(), '', 'no');
        }
        // flush rewrite rules
        flush_rewrite_rules();
    }

}
