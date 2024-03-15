<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://quentn.com/
 * @since      1.0.0
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/public
 * @author     Quentn Team < info@quentn.com>
 */
use QuentnWP\Admin\Utility\Helper;

class Quentn_Wp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Quentn_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Quentn_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


        wp_register_style( 'quentn.flipclock.css', plugin_dir_url( __FILE__ ) . 'css/flipclock.css' );
        wp_enqueue_style( 'quentn.flipclock.css' );


		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/quentn-wp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Quentn_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Quentn_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/quentn-wp-public.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( 'flipclock.min.js', plugin_dir_url( __FILE__ ) . 'js/flipclock.min.js', array( 'jquery' ), $this->version, true );

        if( Helper::is_elementor_plugin_enabled() && Helper::is_elementor_pro_plugin_enabled() ) {
            wp_enqueue_script( 'quentn-elementor-form-autofill.js', plugin_dir_url( __FILE__ ) . 'js/quentn-elementor-form-autofill.js', array( 'jquery' ), $this->version, true );
        }

	}

	public function load_quentn_web_tracking() {
        if( get_option('quentn_web_tracking_code') ) {
            echo get_option('quentn_web_tracking_code');
        }
    }

	/**
	 * Update database if required
	 *
	 * @since    1.2.8
	 */
	public function quentn_update_db_check() {
		if ( version_compare( get_option( 'quentn_db_version', '1.0' ), QUENTN_WP_DB_VERSION, '<' ) ) {

			global $wpdb;
			$table_qntn_log = $wpdb->prefix. TABLE_QUENTN_LOG;
			$charset_collate = $wpdb->get_charset_collate();

			$sql_create_table_log = "CREATE TABLE IF NOT EXISTS $table_qntn_log (
    	  id int NOT NULL AUTO_INCREMENT,
    	  event TINYINT  NOT NULL,
          email varchar(150),
          page_id int,
    	  created_at int NOT NULL,
          context mediumtext,                
          PRIMARY KEY  (id)
        )  $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta( $sql_create_table_log );

			update_option( "quentn_db_version", QUENTN_WP_DB_VERSION );
		}
	}

}
