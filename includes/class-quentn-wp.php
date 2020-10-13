<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://quentn.com/
 * @since      1.0.0
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 * @author     Quentn Team < info@quentn.com>
 */

use QuentnWP\Admin\Utility\Helper;

class Quentn_Wp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Quentn_Wp_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'QUENTN_WP_VERSION' ) ) {
			$this->version = QUENTN_WP_VERSION;
		} else {
			$this->version = '1.1.0';
		}
		$this->plugin_name = 'quentn-wp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Quentn_Wp_Loader. Orchestrates the hooks of the plugin.
	 * - Quentn_Wp_i18n. Defines internationalization functionality.
	 * - Quentn_Wp_Admin. Defines all hooks for the admin area.
	 * - Quentn_Wp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-i18n.php';

        /**
         * A collection of useful static functions
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/utility/class-helper.php';

		/**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-quentn-wp-admin.php';

        /**
         * The class responsible for handling quentn api calls
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-api-handler.php';

        /**
         * The class responsible for register and handle rest api route/endpoings
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-rest-api-controller.php';

        /**
         * The class responsible to restrict access for unauthorized access to page
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-restrict-access.php';

        /**
         * The class responsible to reset user password redirect
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-reset-password.php';

        /**
         * The class responsible for cron job
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-cron.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-quentn-wp-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

        /**
         * The class responsible for handling web tracking code
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-web-tracking.php';

        /**
         * The class responsible to handle uninstallation
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-uninstall.php';

        if ( is_admin() ) {
            /**
             * The class responsible to allow admin add restriction to specific page
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-page-restriction-meta-box.php';

            /**
             * The class responsible to handle installation/activation of plugin
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-activator.php';

        }

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if( Helper::is_learndash_plugin_enabled() ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-learndash.php';
        }

        /**
         * Add classes responsible to handle elementor integration
         */
        if( defined('ELEMENTOR_VERSION' ) && defined('ELEMENTOR_PRO_VERSION' )  && ELEMENTOR_VERSION >= '2.0.0' ) {
           require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-elementor.php';
        }

		$this->loader = new Quentn_Wp_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Quentn_Wp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Quentn_Wp_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Quentn_Wp_Admin( $this->get_plugin_name(), $this->get_version() );

		//register admin scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        //register admin scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add plugin settings menu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'register_custom_menus' );

        //Add/Delete user to quentn when user is created/deleted in wp
        if ( is_multisite() ) {
            $this->loader->add_action( 'wpmu_new_blog', $plugin_admin, 'quentn_add_new_site', 7 );
            $this->loader->add_action( 'wpmu_activate_user', $plugin_admin, 'quentn_add_user' );
            $this->loader->add_action( 'add_user_to_blog', $plugin_admin, 'quentn_add_user' );
            $this->loader->add_action( 'remove_user_from_blog', $plugin_admin, 'quentn_delete_user' );
            //todo delete tags when user deleted network wide
            //$this->loader->add_action( 'wpmu_delete_user', $plugin_admin, 'quentn_delete_user' );
            $this->loader->add_action( 'delete_blog', $plugin_admin, 'action_delete_site', 10, 1 );
        } else {
            $this->loader->add_action( 'user_register', $plugin_admin, 'quentn_add_user', 100, 2  );
            $this->loader->add_action( 'delete_user', $plugin_admin, 'quentn_delete_user' );
        }

        //update user in quentn when user is updated in wp
        $this->loader->add_action( 'wp_login', $plugin_admin, 'quentn_user_login', 10, 2 );

        //update user in quentn when user is updated in wp
        $this->loader->add_action( 'profile_update', $plugin_admin, 'quentn_add_user', 100, 2 );

        //remove user tags if user role is removed in wp
        $this->loader->add_action( 'remove_user_role', $plugin_admin, 'quentn_remove_user_tags', 10, 2 );

        //add user tags if user role is added in wp
        $this->loader->add_action( 'add_user_role', $plugin_admin, 'quentn_add_user_tags', 10, 2 );

        //changed tags when user role is changed
        $this->loader->add_action( 'set_user_role', $plugin_admin, 'quentn_changed_user_roles', 10, 3 );

        //start output buffer, todo need to find a better way in future
        $this->loader->add_action( 'init', $plugin_admin, 'start_output_buffer' );

        //add settings of web tracking/quentn tags
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

        //display admin notices
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notices' );

        //when cookie notice plugin is not installed and user didn't dismiss notice, then display notice
        if( ! Helper::is_cookie_notice_plugin_enabled() and ! get_option( 'quentn_cookie_notice_dismiss' ) ) {
            $this->loader->add_action( 'admin_notices', $plugin_admin, 'display_cookie_plugin_notice' );
        }

        //when cookie notice plugin is not installed and user didn't dismiss notice, then display notice
        $this->loader->add_action( 'admin_init', $plugin_admin, 'check_member_plugin' );

        //add ajax endpoint for cookie notice dismiss
        $this->loader->add_action( 'wp_ajax_quentn_dismiss_cookie_notice', $plugin_admin, 'cookie_plugin_notice_dismiss_handler' );

        //add ajax endpoint for member plugin notice dismiss
        $this->loader->add_action( 'wp_ajax_quentn_dismiss_member_plugin_notice', $plugin_admin, 'member_plugin_notice_dismiss_ajax_handler' );

        //filter screen options
        $this->loader->add_filter( 'set-screen-option', $plugin_admin, 'set_screen_option', 10, 3 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Quentn_Wp_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        if( get_option('quentn_web_tracking_enabled') ) {
            $this->loader->add_action( 'wp_head', $plugin_public, 'load_quentn_web_tracking' );
        }
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {

		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Quentn_Wp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {

		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
