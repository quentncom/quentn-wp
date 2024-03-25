<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://quentn.com/
 * @since      1.0.0
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/admin
 * @author     Quentn Team < info@quentn.com>
 */
use QuentnWP\Admin\Utility\Helper;

class Quentn_Wp_Admin {


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
     * Custom admin notices.
     *
     * @since  1.0.0
     * @access public
     * @var    array
     */

    public $notices = array();


    /**
     * Quentn API Handler
     *
     * @since  1.0.0
     * @access   private
     * @var Quentn_Wp_Api_Handler
     */

    private $api_handler;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->api_handler = Quentn_Wp_Api_Handler::get_instance();
    }

    /**
     * Register the stylesheets for the admin area.
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

        global $hook_suffix;

        if ( in_array( $hook_suffix,  array(
            'post-new.php',
            'post.php',
            'toplevel_page_quentn-dashboard',
            'quentn_page_quentn-access-pages-restrictions',
            'admin_page_quentn-page-access-overview',
        ) ) )  {
            //add quentn bootstrap
            wp_enqueue_style(  'quentn.bootstrap.css', plugin_dir_url( __FILE__ ). 'css/bootstrap-qntn.css' );

            //add fontawesome cdn
            wp_enqueue_style(  'quentn.fontawesome.css', 'https://use.fontawesome.com/releases/v5.7.0/css/all.css', array(), '' );

            //add tempusdominus-bootstrap-4
            wp_enqueue_style( 'quentn.empusdominus-bootstrap-4.min', plugin_dir_url( __FILE__ ) . 'css/tempusdominus-bootstrap-4.css' );

            //add touchspin bootstrap css
            wp_enqueue_style( 'quentn.jquery.bootstrap-touchspin.css', plugin_dir_url( __FILE__ ) . 'css/jquery.bootstrap-touchspin.css' );

            //add jquery ui style
            wp_enqueue_style(  'quentn.jquery.ui.min.css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css' );

            //add select2 style to select quentn tags for new users
            wp_enqueue_style( 'quentn.select2.min.css', plugin_dir_url( __FILE__ ) . 'css/select2.min.css' );

            //add custom style
            wp_enqueue_style( 'quentn.admin.style.css', plugin_dir_url( __FILE__ ) . 'css/admin-style.css' );
        }
    }


    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()  {

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

        //wordpress default jquery, jquery ui scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-slider');

        //add wordpress default script for thickbox/popup
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

        global $hook_suffix;
        if ( in_array( $hook_suffix,  array(
            'post-new.php',
            'post.php',
            'toplevel_page_quentn-dashboard',
            'quentn_page_quentn-access-pages-restrictions',
            'admin_page_quentn-page-access-overview',
        ) ) )  {

            wp_register_script( 'quentn.popper.min.js', plugin_dir_url( __FILE__ ) . 'js/popper.min.js', array(), '' );
            wp_enqueue_script( 'quentn.popper.min.js' );

            //add bootstrap for plugin
            wp_register_script( 'quentn.bootstrap.js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array(), '4.4.1' );
            wp_enqueue_script( 'quentn.bootstrap.js' );

            wp_register_script( 'quentn.moment.min.js', plugin_dir_url( __FILE__ ) . 'js/moment.min.js', array(), '' );
            wp_enqueue_script( 'quentn.moment.min.js' );

            wp_register_script( 'quentn.moment.de.js', plugin_dir_url( __FILE__ ) . 'js/moment.de.js', array(), '' );
            wp_enqueue_script( 'quentn.moment.de.js' );

            wp_register_script( 'quentn.tempusdominus-bootstrap-4.min', plugin_dir_url( __FILE__ ) . 'js/tempusdominus-bootstrap-4.min.js', array(), '4.4.1' );
            wp_enqueue_script( 'quentn.tempusdominus-bootstrap-4.min' );

            //add select2 script to select quentn tags for new users
            wp_register_script( 'quentn.select2.js', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), '4.0.3', true );
            wp_enqueue_script( 'quentn.select2.js' );

            //add touchspin script file
            wp_register_script( 'quentn.jquery.bootstrap-touchspin.js', plugin_dir_url( __FILE__ ) . 'js/jquery.bootstrap-touchspin.js', array( 'jquery' ), '', true );
            wp_enqueue_script( 'quentn.jquery.bootstrap-touchspin.js' );

            //add custom script file
            wp_register_script('quentn.admin.custom.js', plugin_dir_url( __FILE__ ) . 'js/main.js', array( 'jquery' ), $this->version );
            wp_enqueue_script( 'quentn.admin.custom.js' );

            //Localize the script with new data
            $translation_array = array(
                'choose_quentn_tags'            =>  __( 'Choose Quentn Tags', 'quentn-wp' ),
                'choose_learndash_courses'      =>  __( 'Choose LearnDash Courses', 'quentn-wp' ),
                'delete_confirmation_message'   =>  __( 'Are you sure you want to delete?', 'quentn-wp' ),
                'datepicker_lang'   =>  ( substr( get_locale(),0, 2 ) == 'de' ? 'de' : 'en' ) // if wp set in german lang then set datepicker lang in german, otherwise in english
            );
            wp_localize_script( 'quentn.admin.custom.js', 'wp_qntn', $translation_array );
        }

        //add quentn elementor api validator
        wp_register_script('quentn.elementor.validate.api.js', plugin_dir_url( __FILE__ ) . 'js/quentn-elementor-validate-api.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'quentn.elementor.validate.api.js' );
    }


    /**
     * Creates admin menues
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function register_custom_menus() {

        add_menu_page( " __( 'Quentn Plugin', 'quentn-wp' )", __( 'Quentn', 'quentn-wp' ), "manage_options", "quentn-dashboard", array( $this, 'quentn_dashboard' ), plugin_dir_url( __FILE__ ) . 'images/icon.png', 6);

        add_submenu_page( "quentn-dashboard", __( 'Quentn Integration', 'quentn-wp' ),  __( 'Integration', 'quentn-wp' ), "manage_options", "quentn-dashboard", array( $this, 'quentn_dashboard' ) );

        $hook_page_list = add_submenu_page("quentn-dashboard", __( 'Access Pages Restrictions', 'quentn-wp'),   __('Access Restrictions', 'quentn-wp'), "manage_options", "quentn-access-pages-restrictions", array( $this, 'restricted_pages_list' ) );

        add_action( "load-$hook_page_list", array( $this, 'restricted_pages_list_screen_option' ) );

        $hook_access_overview = add_submenu_page("NULL", __( 'Quentn User Access', 'quentn-wp'),   __('Access', 'quentn-wp'), "manage_options", "quentn-page-access-overview", array( $this, 'access_restrictions_list' ) );

        add_action( "load-$hook_access_overview", array( $this, 'access_overview_list_screen_option' ) );
    }

    /**
     * Print admin notices.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */

    public function display_admin_notices() {

        //add admin notices
        if ( isset( $_GET['update'] ) ) {
            $action = sanitize_key( $_GET['update'] );

            //if a user access was deleted
            if ( $action == 'quentn-access-deleted' ) {
                if ( $_GET['delete_count'] ) {
	                $this->notices[] = array( 'message' =>  sprintf( esc_html( _n( '%d user access is deleted', '%d user accesses are deleted', $_GET['delete_count'], 'quentn-wp'  ) ), $_GET['delete_count'] ), 'type' => 'success' );
                } else {
	                $this->notices[] = array( 'message' => __( 'User access could not be deleted.', 'quentn-wp' ), 'type' => 'error' );
                }
            }

            //if a user data was deleted
            if ( $action == 'quentn-user-data-deleted' ) {
                if ( $_GET['deleted'] ) {
                    $this->notices[] = array( 'message' => __( 'Data is deleted successfully', 'quentn-wp' ), 'type' => 'success' );
                }
                else {
                    $this->notices[] = array( 'message' => sprintf( esc_html__( 'There is no data found with the email address %s', 'quentn-wp' ), sanitize_email( $_GET['email'] ) ), 'type' => 'warning' );
                }
            }
            //if direct access added successfully
            if ( $action == 'quentn-direct-access-add-success' ) {
                $this->notices[] = array( 'message' => __( 'User access has been added successfully', 'quentn-wp' ), 'type' => 'success' );
            }

            //if direct access added successfully
            if ( $action == 'quentn-direct-access-email-invalid' ) {
                $this->notices[] = array( 'message' => __( 'Please enter valid email address', 'quentn-wp' ), 'type' => 'error' );
            }

            //if quentn account is disconnected
            if ( $action == 'quentn-account-removed' ) {
                $this->notices[] = array( 'message' => __( 'Quentn account has been removed', 'quentn-wp' ), 'type' => 'success' );
            }

            //if direct access failed to add
            if ( $action == 'quentn-direct-access-add-failed' ) {
                $this->notices[] = array( 'message' => __( 'User access could not be added', 'quentn-wp' ), 'type' => 'error' );
            }
        }

        if ( $this->notices ) { ?>

            <?php foreach ( $this->notices as $notice ) { ?>

                <div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
                    <?php echo wpautop( '<strong>' . $notice['message'] . '</strong>' ); ?>
                </div>

                <?php
            }
        }
    }

    /**
     * Filter screen options
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function restricted_pages_list_screen_option() {

        $option = 'per_page';
        $args   = array(
            'label'   => 'Access',
            'default' => 1,
            'option'  => 'quentn_restricted_access_records_per_page'
        );

        add_screen_option( $option, $args );
    }


    /**
     * Filter screen options
     *
     * @since  1.0.0
     * @access public
     * @return void
     */

    public function access_overview_list_screen_option() {

        $option = 'per_page';
        $args   = array(
            'label'   => 'Restrictions',
            'default' => 20,
            'option'  => 'quentn_access_overview_records_per_page'
        );

        add_screen_option( $option, $args );
    }


    /**
     * Register settins
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function register_settings() {

        //set values for register_setting
        $settings = array(
            array(
                'option_group' => 'quentn_tags_options_group',
                'option_name'  => 'quentn_tags_wp_user'
            ),
            array(
                'option_group' => 'quentn_tags_options_group',
                'option_name'  => 'quentn_add_remove_wp_user_from_host'
            ),
            array(
                'option_group' => 'quentn_miscellaneous_options_group',
                'option_name'  => 'quentn_auto_login_url'
            ),
            array(
                'option_group' => 'quentn_miscellaneous_options_group',
                'option_name'  => 'quentn_add_log'
            ),
            array(
                'option_group' => 'quentn_miscellaneous_options_group',
                'option_name'  => 'quentn_log_expire_days'
            ),
        );

        //add values for settings api, add_settings_section
        $sections = array(
            array(
                'id'       => 'quentn_tags_option',
                'title'    => __( 'Select Quentn Tags', 'quentn-wp'),
                'callback' => '__return_false',
                'page'     => 'quentn-dashboard-tags'
            ),
            array(
                'id'       => 'quentn_miscellaneous_option',
                'title'    => __( 'Miscellaneous Options', 'quentn-wp'),
                'callback' => '__return_false',
                'page'     => 'quentn-dashboard-miscellaneous'
            ),
        );

        // register setting
        foreach ( $settings as $setting ) {
            register_setting( $setting["option_group"], $setting["option_name"], ( isset( $setting["callback"] ) ? $setting["callback"] : '' ) );
        }

        // add settings section
        foreach ( $sections as $section ) {
            add_settings_section( $section["id"], $section["title"], ( isset( $section["callback"] ) ? $section["callback"] : '' ), $section["page"] );
        }
    }

    /**
     * Register fields
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function register_custom_fields() {

        $tab = ( !empty( $_GET['tab'] ) ) ? sanitize_key( $_GET['tab'] ) : '';

        $fields = array();
        if ( $tab == 'quentn_settings' ) {
            $fields[] = array(
                'id' => 'quentn_auto_login_redirect_url',
                'title' => __( 'Redirect URL', 'quentn-wp' ),
                'callback' => array( $this, 'field_quentn_auto_login_redirect_url' ),
                'page' => 'quentn-dashboard-miscellaneous',
                'section' => 'quentn_miscellaneous_option',
            );
            $fields[] = array(
                'id' => 'quentn_add_log',
                'title' => __( 'Log', 'quentn-wp' ),
                'callback' => array( $this, 'field_quentn_add_log' ),
                'page' => 'quentn-dashboard-miscellaneous',
                'section' => 'quentn_miscellaneous_option',
            );
            $fields[] = array(
                'id' => 'quentn_log_expire_days',
                'title' => __( 'Expiration', 'quentn-wp' ),
                'callback' => array( $this, 'field_quentn_log_expire_days' ),
                'page' => 'quentn-dashboard-miscellaneous',
                'section' => 'quentn_miscellaneous_option',
            );
        }

        //add tag fields for all wordpress roles
        if ( $tab == 'quentn_tags_selection' ) {
           
            $wp_roles = new WP_Roles;

            $qntn_terms = $this->api_handler->get_terms();

            //loop through all available roles of WP and set three options for each, add tag (bool), remove tag (bool), quentn tags (array)
            //set enable/disable add user from wp to quentn
            foreach ( $wp_roles->get_names() as $slug => $name ) {
                $fields[] = array(
                    'id' => 'add_wp_user_' . $slug . '_to_quentn',
                    'title' => translate_user_role( $name ),
                    'callback' => array( $this, 'input_add_wp_user_to_quentn' ),
                    'page' => 'quentn-dashboard-tags',
                    'section' => 'quentn_tags_option',
                    'args' => array( 'role' => $slug, 'role_title' => translate_user_role( $name ) )
                );

                //set quentn terms selection option
                $fields [] = array(
                    'id' => 'quentn_tags' . $slug,
                    'title' => '',
                    'callback' => array( $this, 'field_wp_role_quentn_tags' ),
                    'page' => 'quentn-dashboard-tags',
                    'section' => 'quentn_tags_option',
                    'args' => array(
                        'label_for' => __('Please Select Tags', 'quentn-wp'),
                        'role' => $slug,
                        'terms' => $qntn_terms,
                    )
                );

                //set enable/disable remove tags when user loses a role
                $fields[] = array(
                    'id' => 'delete_wp_user_' . $slug . '_from_quentn',
                    'title' => '',
                    'callback' => array( $this, 'input_delete_wp_user_to_quentn' ),
                    'page' => 'quentn-dashboard-tags',
                    'section' => 'quentn_tags_option',
                    'args' => array( 'role' => $slug )
                );
            }
        }

        // add settings field
        foreach ( $fields as $field ) {
            add_settings_field( $field["id"], $field["title"], ( isset( $field["callback"] ) ? $field["callback"] : '' ), $field["page"], $field["section"], ( isset( $field["args"] ) ? $field["args"] : '' ) );
        }
    }

    /**
     * Display notice when cookie plugin is not installed
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function display_cookie_plugin_notice() {
        global $hook_suffix;

        //add pages where we want to display notice in case cookie plugin is not activated
        if ( in_array( $hook_suffix,  array(
            'plugins.php',
        ) ) )  {
            ?>
            <div class="notice notice-warning quentn-cookie-notice is-dismissible">
                <p>
                    <?php  printf( esc_html__( 'You need to install the plugin %s to support Quentn cookies. You can download the plugin', 'quentn-wp' ),'<i><b>Cookie Notice for GDPR</i></b>' ); ?>

                    <a href="https://wordpress.org/plugins/cookie-notice/" target="_blank"><?php   _e( 'here', 'quentn-wp' ) ; ?> </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Check if members plugin notice need to display
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function check_member_plugin() {
        if( ! $this->is_member_plugin_active() && ! get_option( 'quentn_member_plugin_notice_dismiss' ) ) {
            add_action("admin_notices", array( $this, 'display_member_plugin_notice') );
        }
    }

    /**
     * Check if members plugin is active
     *
     * @since  1.0.0
     * @access public
     * @return bool
     */
    public function is_member_plugin_active() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if( is_plugin_active( 'members/members.php' ) ) {
            return true;
        }
        return false;
    }

    /**
     * Display members plugin notice
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function display_member_plugin_notice() {
        global $hook_suffix;

        //add pages where we want to display notice in case members plugin is not activated
        if ( in_array( $hook_suffix,  array(
            'plugins.php',
        ) ) )  {
            ?>
            <div class="notice notice-warning quentn-member-plugin-notice is-dismissible">
                <p>
                    <?php  printf( __( 'To create multiple user roles and define different permissions, you must install the plugin %s. You can download the plugin', 'quentn-wp' ),'<i><b>Members</i></b>' ); ?>
                    <a href="https://wordpress.org/plugins/members/" target="_blank"><?php   _e( 'here', 'quentn-wp' ) ; ?> </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Update option if user dismiss cookie plugin notice
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function cookie_plugin_notice_dismiss_handler() {
        update_option( 'quentn_cookie_notice_dismiss', true);
    }

    /**
     * Update option if user dismiss members plugin notice
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function member_plugin_notice_dismiss_ajax_handler() {
        update_option( 'quentn_member_plugin_notice_dismiss', true);
    }

    /**
     * Check if learndash plugin is active
     *
     * @since  1.0.0
     * @access public
     * @return bool
     */
    public function is_learndash_plugin_active() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if( is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
            return true;
        }
        return false;
    }


    /**
     * Add user to quentn, with appropriate tags
     *
     * @since  1.0.0
     * @access public
     * @param int $user_id id of newly created user
     * @return void
     */
    public function quentn_add_user( $user_id ) {

        // Get the user object.
        $user = new \WP_User( $user_id );

        // Get the user roles
        $user_roles = $user->roles;

        /** @var array $quentn_update_wp_user admin settings to add/remove new users to quentn host */
        $quentn_update_wp_user =  get_option( 'quentn_add_remove_wp_user_from_host' ) ;

        /** @var bool $is_add_user_to_qntn_enabled Is add user to quentn host is enabled by admin for current user role */
        $is_add_user_to_qntn_enabled = false;

        //A user can have multiple roles, if any role is enabled to add user to quentn, then we will add
        foreach ( $user_roles as $user_role ) {
            if( isset( $quentn_update_wp_user[$user_role]['add'] ) && $quentn_update_wp_user[$user_role]['add'] ) {
                $is_add_user_to_qntn_enabled = true;
            }
        }

        //if user role is not allowed to add in quentn, or user is not connected to quentn then return
        if( ! $is_add_user_to_qntn_enabled || ! $this->api_handler->is_connected_with_quentn() ) {
            return;
        }

        //get defined settings of tags selected for specific user role
        $quentn_existing_terms_settings = get_option('quentn_tags_wp_user');

        //get id's of terms for this user bases on user role, as user can have multiple roles, so we will fetch term id's for all roles
        $term_ids = array();
        foreach ( $user_roles as $user_role ) {
            //if role is enabled by admin to add terms and terms are not empty
            if( isset( $quentn_existing_terms_settings[$user_role] ) && ! empty( $quentn_existing_terms_settings[$user_role] ) ) {
                $term_ids = array_merge( $term_ids, $quentn_existing_terms_settings[$user_role] );
            }
        }

        //get user basic info to add in quentn
        $first_name = $user->first_name;
        $last_name = $user->last_name;
        $email = $user->user_email;

        //set request body for quentn
        $contact_data = array(
            'first_name'  => $first_name,
            'family_name' => $last_name,
            'mail'        => $email,
        );

        if ( ! empty( $term_ids ) ) {
            $contact_data['terms'] = apply_filters( 'quentn_contact_terms', $term_ids );
        }
        //send request to quentn api and get response having id of newly created user
        $this->api_handler->get_quentn_client()->contacts()->createContact( apply_filters( 'quentn_contact_data', $contact_data ) );
    }

    /**
     *
     * @since  1.1.0
     * @access public
     * @param string $user_login username
     * @param WP_User $user
     * @return void
     */
    public function quentn_user_login( $user_login, $user ) {
        update_user_meta( $user->ID, 'quentn_last_login', time() );
    }


    /**
     * Add Quentn tags to user
     *
     * @since  1.0.0
     * @access public
     * @param int $user_id id user
     * @param string $role_added role added
     * @return void
     */
    public function quentn_add_user_tags( $user_id, $role_added ) {

        /** @var int $user Retrieve WP_User on success. */
        $user = new \WP_User( $user_id );

        /** @var array $quentn_update_wp_user admin settings to add/remove new users to quentn host */
        $quentn_update_wp_user =  get_option( 'quentn_add_remove_wp_user_from_host' ) ;

        //get defined settings of tags selected for specific user role
        $quentn_existing_terms_settings = get_option('quentn_tags_wp_user');

        //if role is enabled by admin to add terms and terms are not empty
        $add_term_ids = array();
        if(  isset( $quentn_update_wp_user[$role_added]['add'] ) &&  $quentn_update_wp_user[$role_added]['add'] ) {
            $add_term_ids = $quentn_existing_terms_settings[$role_added];
        }

        //if we have no term id's to delete or we are not connected with quentn
        if( empty( $add_term_ids ) || ! $this->api_handler->is_connected_with_quentn() ) {
            return;
        }

        //send request to quentn api and get response having id of newly created user
        $get_response = $this->api_handler->get_quentn_client()->contacts()->findContactByMail( $user->user_email );
        $contacts = $get_response['data'];
        //we can have multiple users with same email in quentn, remove tags for all
        foreach ( $contacts as $contact ) {
            $this->api_handler->get_quentn_client()->contacts()->addContactTerms( $contact['id'], $add_term_ids );
        }
    }


    /**
     * Delete Quentn tags of user role
     *
     * @since  1.0.0
     * @access public
     * @param int $user_id id user
     * @param string $role_removed user role removed
     * @return void
     */

    public function quentn_remove_user_tags( $user_id, $role_removed ) {
        $this->quentn_delete_tags( $user_id, array( $role_removed ) );
    }


    /**
     * Delete Quentn tags of user role
     *
     * @since  1.0.0
     * @access public
     * @param int $user_id id user
     * @param  string $role_added role added
     * @param  array $removed_user_roles role added
     * @return void
     */
    public function quentn_changed_user_roles( $user_id, $new_role = '', $removed_user_roles = array() ) {
        $this->quentn_delete_tags($user_id, $removed_user_roles);
    }

    /**
     * Delete Quentn tags
     *
     * @since  1.0.0
     * @access public
     * @param int $user_id id user
     * @return void
     */
    public function quentn_delete_user( $user_id ) {
        /** @var int $user Retrieve WP_User on success. */
        $user = get_userdata( $user_id );
        /** @var  array $user_roles contains all roles of user, a user can have more than one role  */
        $removed_user_roles = $user->roles;
        $this->quentn_delete_tags( $user_id, $removed_user_roles );
    }


    /**
     * Delete Quentn tags
     *
     * @since  1.0.0
     * @access public
     * @param int $user_id id user
     * @param array $user_roles user roles
     * @return void
     */
    public function quentn_delete_tags( $user_id, $user_roles = array() ) {

        /** @var int $user Retrieve WP_User on success. */
        $user = get_userdata( $user_id );

        $quentn_update_wp_user =  get_option( 'quentn_add_remove_wp_user_from_host' ) ;
        $quentn_existing_terms_settings = get_option('quentn_tags_wp_user');


        //get id's of terms that need to be deleted
        $term_ids = array();
        foreach ( $user_roles as $user_role ) {
            //if tags removal enabled by this role and terms are not empty
            if( isset( $quentn_update_wp_user[$user_role]['remove'] ) &&  $quentn_update_wp_user[$user_role]['remove'] && isset( $quentn_existing_terms_settings[$user_role] ) && ! empty( $quentn_existing_terms_settings[$user_role] ) ) {

                $term_ids = array_merge( $term_ids, $quentn_existing_terms_settings[$user_role] );
            }
        }

        //if there is no terms to delete or we are not connected with quentn
        if( empty( $term_ids )  || ! $this->api_handler->is_connected_with_quentn() ) {
            return;
        }

        $email = $user->user_email;
        $get_response = $this->api_handler->get_quentn_client()->contacts()->findContactByMail( $email );
        $contacts = $get_response['data'];
        //there may be multiple users with same email address at quentn, we will remove tags for all
        foreach ( $contacts as $contact ) {
            $this->api_handler->get_quentn_client()->contacts()->deleteContactTerms( $contact['id'], $term_ids );
        }
    }

    /**
     * Display redirect url after auto login
     *
     * @since  1.1.0
     * @access public
     * @return void
     */
    public function field_quentn_auto_login_redirect_url()
    {
        $value = esc_attr( get_option( 'quentn_auto_login_url' ) );
        ?>
            <input  class="form-control" type="text" name="quentn_auto_login_url" id="quentn_auto_login_url" value="<?php echo $value; ?>" placeholder="<?php  _e( 'URL', 'quentn-wp' ) ?>">
            <label for="quentn_auto_login_url"> <?php printf( __( 'Redirect user to this page after auto login.', 'quentn-wp'  ) ); ?></label>
        <?php
    }

	/**
	 * Display add log checkbox field
	 *
	 * @since  1.2.8
	 * @access public
	 * @return void
	 */
	public function field_quentn_add_log()
	{
		$value = get_option( 'quentn_add_log', true );
		?>
        <input type="checkbox" class="form-control" value="1" name="quentn_add_log" id="quentn_add_log" <?php checked( $value); ?>>
        <label for="quentn_add_log" style="display:inline" > <?php printf( __( 'Save logs in the database.', 'quentn-wp'  ) ); ?></label>
		<?php
	}

    /**
	 * Display options for expire days of logs
	 *
	 * @since  1.2.8
	 * @access public
	 * @return void
	 */
	public function field_quentn_log_expire_days() {
		$value = get_option( 'quentn_log_expire_days', 365 );
		?>
        <select name="quentn_log_expire_days" id="quentn_log_expire_days">
            <option value="30" <?php selected ( $value, 30  )  ?>> 30 <?php __( 'days', 'quentn-wp' ) ?></option>
            <option value="60" <?php selected ( $value, 60  )  ?>> 60 <?php __( 'days', 'quentn-wp' ) ?></option>
            <option value="90" <?php selected ( $value, 90  )  ?>> 90 <?php __( 'days', 'quentn-wp' ) ?></option>
            <option value="180" <?php selected ( $value, 180  )  ?>> 180 <?php __( 'days', 'quentn-wp' ) ?></option>
            <option value="365" <?php selected ( $value, 365  )  ?>> 365 <?php __( 'days', 'quentn-wp' ) ?></option>
        </select>

        <label for="quentn_log_expire_days" > <?php printf( __( 'Number of days to keep the logs.', 'quentn-wp'  ) ); ?></label>
		<?php
	}

    /**
     * Display create/update checkbox field
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function input_add_wp_user_to_quentn($args)
    {
        //get existing settings
        $quentn_update_wp_user =  get_option( 'quentn_add_remove_wp_user_from_host' ) ;
        //check existing setting of this role to make it checked by default
        $is_add_user_to_qntn_enabled = false;
        if( isset( $quentn_update_wp_user[$args['role']]['add'] ) && $quentn_update_wp_user[$args['role']]['add'] ) {
            $is_add_user_to_qntn_enabled = $quentn_update_wp_user[$args['role']]['add'];
        }
        ?>
        <input type="checkbox" class="form-control add-wp-qntn" value="1" name="quentn_add_remove_wp_user_from_host[<?php echo $args['role'] ?>][add]" data-role="<?php echo $args['role']?>" id="quentn_tags_add_wp_user<?php echo $args['role']?>"  <?php checked( $is_add_user_to_qntn_enabled); disabled( ! $this->api_handler->is_connected_with_quentn() ); ?>  >
        <?php
        printf( __( 'Create or update Quentn contact if user receives role %s , with the following tags', 'quentn-wp' ), ucfirst( $args['role_title'] ) );
    }

    /**
     * Display remove tags if user looses role
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function input_delete_wp_user_to_quentn($args)
    {
        //get existing settings
        $quentn_update_wp_user =  get_option( 'quentn_add_remove_wp_user_from_host' ) ;
        //check existing setting of this role to make it checked by default
        $is_add_user_to_qntn_enabled = false;
        $is_delete_user_to_qntn_enabled = false;
        if( isset( $quentn_update_wp_user[$args['role']]['add'] ) && $quentn_update_wp_user[$args['role']]['add'] ) {
            $is_add_user_to_qntn_enabled = $quentn_update_wp_user[$args['role']]['add'];
        }
        if( isset( $quentn_update_wp_user[$args['role']]['remove'] ) && $quentn_update_wp_user[$args['role']]['remove'] ) {
            $is_delete_user_to_qntn_enabled = $quentn_update_wp_user[$args['role']]['remove'];
        }
        ?>
        <input type="checkbox" class="form-control" value="1" name="quentn_add_remove_wp_user_from_host[<?=$args['role']?>][remove]" id="quentn_tags_remove_wp_user<?=$args['role']?>" <?php checked( $is_delete_user_to_qntn_enabled); disabled( ! $this->api_handler->is_connected_with_quentn() || !$is_add_user_to_qntn_enabled);  ?>  >
        <?php
        printf( __( 'Remove tags if user looses the role %s', 'quentn-wp' ), ucfirst( $args['role'] ) );
    }

    /**
     * Display quentn tags dropdown
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function field_wp_role_quentn_tags( $args ) {

        $existing_terms = array();
        //Get defined settings for tags
        $quentn_terms_selection =  get_option( 'quentn_tags_wp_user' ) ;
        //Get defined settings for add/remove user/tags
        $quentn_update_wp_user =  get_option( 'quentn_add_remove_wp_user_from_host' ) ;

        //check existing setting of this role to make it enabled/disabled by default
        $is_add_user_to_qntn_enabled = false;
        if( isset( $quentn_update_wp_user[$args['role']]['add'] ) ) {
            $is_add_user_to_qntn_enabled = $quentn_update_wp_user[$args['role']]['add'];
        }

        //Get the selected terms for specific role
        if( isset( $quentn_terms_selection[$args['role']] ) ) {
            $existing_terms = $quentn_terms_selection[$args['role']];
        }

        ?>
        <select class="quentn-term-selection"  style="width: 70%"  name="quentn_tags_wp_user[<?php echo $args['role']?>][]" id="quentn_tags_wp_user<?php echo $args['role']?>" <?php disabled( ! $this->api_handler->is_connected_with_quentn() || !$is_add_user_to_qntn_enabled);  ?> multiple>
            <?php foreach($args['terms'] as $term) { ?>
                <option value="<?php echo $term['id']?>" <?php selected ( in_array( $term['id'], $existing_terms ) )  ?>><?php echo $term['name']?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Display Quentn dashboard, connect to Quentn options
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function quentn_dashboard()
    {
        //register fields
        $this->register_custom_fields();
        require_once QUENTN_WP_PLUGIN_DIR . '/admin/partials/quentn-wp-dashboard.php';

    }


    /**
     * Display list of all pages list
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function restricted_pages_list()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-page-restrictions-list.php';
    }

    /**
     * Display list of all access records for specific page
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function access_restrictions_list()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-access-overview-list.php';

    }

	/**
	 * Update database on plugin update if required
	 *
	 * @since  1.2.8
	 * @access public
	 * @return void
	 */

	public function quentn_plugin_upgrade_completed( $upgrader_object, $options ) {
		update_option( "quentn_db_version", '1.0' );
		// Check if the update process was for quentn plugin
		if ( isset( $options['action'] ) && $options['action'] === 'update' && isset( $options['type'] ) && $options['type'] === 'plugin' ) {
			// Check if quentn plugin was updated
			if ( isset( $options['plugins'] ) && in_array( 'quentn-wp/quentn-wp.php', $options['plugins'] ) ) {
				// Call your database update function
				$this->quentn_database_update();
			}
		}
	}

    public function quentn_database_update() {
	    if ( ! version_compare(get_option( 'quentn_db_version' ), QUENTN_WP_DB_VERSION, '<' ) ) {
            return;
        }
	    if ( is_multisite() ) {
            $site_ids = get_sites( array( 'fields' => 'ids' ) );
		    foreach ( $site_ids as $site_id ) {
			    switch_to_blog( $site_id );
			    if ( Helper::is_plugin_enabled() ){
                    if ( version_compare(QUENTN_WP_DB_VERSION, '1.2', '<' ) ) {
                        $this->quentn_database_update_log_table();
                    }
			    }
			    restore_current_blog();
		    }
		    update_option( "quentn_db_version", QUENTN_WP_DB_VERSION );
	    } else {
		    if ( version_compare(QUENTN_WP_DB_VERSION, '1.2', '<' ) ) {
			    $this->quentn_database_update_log_table();
		    }
		    update_option( "quentn_db_version", QUENTN_WP_DB_VERSION );
	    }
    }

    public function quentn_database_update_log_table() {
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
    }


    /**
     * Display error messages
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function show_errors( $error_messages = array() ) {
        if( ! empty( $error_messages ) ) { ?>
            <div class="bootstrap-qntn">
                <div class="col-md-12" style="margin-top: 30px">
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php foreach ( array_unique ( $error_messages ) as $error_message ) : ?>
                            <span class="glyphicon glyphicon-hand-right"></span>&nbsp;&nbsp;<strong><?php echo $error_message; ?></strong> <br />
                        <?php endforeach;  ?>
                    </div>
                </div>
            </div>
        <?php }
    }

    /**
     * Hook handler for deleting site
     *
     * @since  1.0.0
     * @access public
     * @param int $blog_id Site id.
     * @return void
     */
    public function action_delete_site( $blog_id ) {
        switch_to_blog( $blog_id );
        $uninstall_handler = new Quentn_Wp_Uninstall();
        $uninstall_handler->uninstall();
        restore_current_blog();
    }

    /**
     * Hook handler for adding new site
     *
     * @since  1.0.0
     * @access public
     * @param int $blog_id Site id.
     * @return void
     */
    public function quentn_add_new_site( $blog_id ) {
        switch_to_blog( $blog_id );
        $activator = new Quentn_Wp_Activator( false );
        $activator->quentn_perform_activation();
        restore_current_blog();
    }

    /**
     * Set screen options
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function set_screen_option( $status, $option, $value ) {
        return $value;
    }

    /**
     * Turn on output buffering
     *
     * @since  1.0.0
     * @access public
     * @return bool
     */
    public function start_output_buffer() {
        ob_start();
    }
}
