<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Rest_Api
{
    /**
     * The first URL segment after core prefix
     *
     * @since  1.0.0
     * @access private
     * @var    string
     */
    private $namespace;

    /**
     * The base URL for route to create new user
     *
     * @since  1.0.0
     * @access private
     * @var    string
     */
    private $create_user;

    /**
     * The base URL for route to grant new access to page
     *
     * @since  1.0.0
     * @access private
     * @var    string
     */
    private $grant_access;

    /**
     * The base URL for route to remove existing access to page
     *
     * @since  1.0.0
     * @access private
     * @var    string
     */
    private $revoke_access;

    /**
     * The base URL for route to get existing restricted pages
     *
     * @since  1.0.0
     * @access private
     * @var    string
     */
    private $get_page_restrictions;

    /**
     * The base URL for route to get all available user roles
     *
     * @since  1.0.0
     * @access private
     * @var    string
     */
    private $get_user_roles;

    /**
     * The base URL for route to update web tracking code
     *
     * @since  1.1.1
     * @access private
     * @var    string
     */
    private $get_tracking;


    /**
     * Initialize our namespace and resource name.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        add_action( 'rest_api_init', array( $this, 'register_page_routes' ) );

        $this->namespace = '/quentn/api/v1';
        $this->create_user = '/users';
        $this->grant_access = '/pages/grant-access';
        $this->revoke_access = '/pages/revoke-access';
        $this->get_page_restrictions = '/get-page-restrictions';
        $this->get_user_roles = '/get-user-roles';
        $this->get_tracking = '/get-tracking';
    }

    /**
     * Return an instance of this class
     *
     * @since 1.0
     * @return object A single instance of this class.
     */

    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new self;
        }
        return $instance;
    }


    /**
     * Register our routes for our example endpoint
     *
     * @since 1.0
     * @return void
     */
    public function register_page_routes()  {

        //register route to add access to restricted pages from quentn
        register_rest_route( $this->namespace, $this->grant_access, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_grant_permission' ),
            // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
            'permission_callback' => array( $this, 'quentn_check_credentials' ),
            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'vu' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),

        ) );

        //register route to remove access to restricted pages from quentn
        register_rest_route( $this->namespace, $this->revoke_access, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_revoke_page_countdown_permission' ),
            // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
            'permission_callback' => array( $this, 'quentn_check_credentials' ),

            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'vu' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),

        ) );

        //register route to get list of all pages having quentn restrictions active
        register_rest_route( $this->namespace, $this->get_page_restrictions, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_get_restricted_pages' ),
            // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
            'permission_callback' => array( $this, 'quentn_check_credentials' ),

            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'vu' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),

        ));

        //register route to get list of all user roles of wp site
        register_rest_route( $this->namespace, $this->get_user_roles, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_get_user_roles' ),
            // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
            'permission_callback' => array( $this, 'quentn_check_credentials' ),
            // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'vu' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),

        ));

        //register route to get create new user in wp
        register_rest_route( $this->namespace, $this->create_user, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_create_user' ),
            // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
            'permission_callback' => array( $this, 'quentn_check_credentials' ),

            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'vu' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),

        ));

        //register route to get tracking code when settings is saved in Quentn
        register_rest_route( $this->namespace, $this->get_tracking, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_get_tracking_code' ),
            // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
            'permission_callback' => array( $this, 'quentn_check_credentials' ),

            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'vu' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),

        ));
    }

    /**
     * Add access to page
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function quentn_grant_permission( $request ) {
        global $wpdb;
        $request_body = json_decode( $request->get_body(), true );
        //decode and save request in array
        $quentn_page_timer_permission = json_decode( base64_decode( $request_body['data'] ), true );
        $emails = isset( $quentn_page_timer_permission['data']['email'] ) ? $quentn_page_timer_permission['data']['email'] : array();
        $pages = isset( $quentn_page_timer_permission['data']['page'] ) ? $quentn_page_timer_permission['data']['page'] : array();

        //set values and place holders to insert into database in one query
        $values = array();
        $place_holders = array();
        foreach ( $emails as $email ) {
            if ( $email == "" ) { //email cannot be empty
                continue;
            }
            foreach ( $pages as $page ) {
                array_push( $values, $page, $email, hash( 'sha256', $email ), time() );
                $place_holders[] = "('%d', '%s', '%s', '%d')";
            }
        }

        //insert into database
        $query = "INSERT INTO ".$wpdb->prefix . TABLE_QUENTN_RESTRICTIONS." ( page_id, email, email_hash, created_at ) VALUES ";
        $query .= implode( ', ', $place_holders );
        $wpdb->query( $wpdb->prepare( "$query ON DUPLICATE KEY UPDATE created_at= ".time(), $values ) );

        return rest_ensure_response( esc_html__( 'Permissions Timer Successfully Updated', 'quentn-wp' ) );
    }

    /**
     * remove access to page
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function quentn_revoke_page_countdown_permission( $request ) {
        global $wpdb;

        $request_body = json_decode( $request->get_body(), true );

        $quentn_page_timer_permission = json_decode ( base64_decode( $request_body['data'] ), true );

        $emails = isset( $quentn_page_timer_permission['data']['email'] ) ? $quentn_page_timer_permission['data']['email'] : array();
        $pages = isset( $quentn_page_timer_permission['data']['page'] ) ? $quentn_page_timer_permission['data']['page'] : array();

        //set values and place holders to insert into database in one query
        $values = array();
        foreach ( $emails as $email ) {
            foreach ( $pages as $page ) {
                $pageid_email = trim( $page )."|".trim( $email );
                array_push( $values, $pageid_email );
            }
        }

        //delete permissions
        $query =  "DELETE FROM ".$wpdb->prefix . TABLE_QUENTN_RESTRICTIONS." where CONCAT_WS('|', page_id, email) IN ('".implode("','", $values)."')";
        $wpdb->query( $wpdb->query( $query ) );
        return rest_ensure_response( esc_html__( 'Permissions Timer Successfully Updated', 'quentn-wp' ) );
    }

    /**
     * Get list of all pages where quentn restrictions are applied
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    public function quentn_get_restricted_pages() {
        $pages= get_pages();
        $restricted_pages = array();
        foreach( $pages as $id ) {

            //check if quentn restricted status is active
            if( get_post_meta( $id->ID, '_quentn_post_restrict_meta', true ) ) {
                $restricted_pages[] = array(
                                            "page_id"    => $id->ID,
                                            "page_title" => $id->post_title,
                                        );
            }
        }
        return rest_ensure_response( json_encode( $restricted_pages ) );
    }

    /**
     * Get list of all wp roles
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    public function quentn_get_user_roles( ) {
        $wp_roles = new WP_Roles();
        $all_roles = $wp_roles->get_names();
        return rest_ensure_response(json_encode($all_roles));
    }

    /**
     * Create wp users
     *
     * @since  1.0.0
     * @access public
     * @param string $request request received from quentn
     * @return void
     */
    public function quentn_create_user( $request ) {
        $request_body = json_decode( $request->get_body(), true );
        $request_data = json_decode( base64_decode( $request_body['data'] ), true );
        $qntn_users = isset( $request_data['data']['users'] ) ? $request_data['data']['users'] : array();

        // Instantiate the WP_Error object
        $error = new WP_Error();

        foreach ( $qntn_users as $user ) {

            $qn_userdata = $this->user_fields_supported_by_quentn( $user );
            if( empty( $qn_userdata ) ) {
                continue;
            }

            $is_send_user_email = true;
            //if username is exist, then existing user will be updated instead of creating a new one
            if ( $user_id = email_exists( $qn_userdata['user_email'] ) ) {
                //don't need to send email if we update current user
                $is_send_user_email = false;
                //update meta_value
                foreach ( $qn_userdata as $meta_key => $meta_value ) {
                    update_user_meta( $user_id, $meta_key, $meta_value );
                }
            } else {
                //no default role set
                $qn_userdata['role'] = '';
                //insert user in wordpress
                $user_id = wp_insert_user( $qn_userdata );
                // On success, add user meta last login as false
                if ( ! is_wp_error( $user_id ) ) {
                    update_user_meta( $user_id, 'quentn_last_login', 0 );
                }
            }

            //if user could not created
            if ( is_wp_error( $user_id ) ) {
                $error->add( 'Error Creating User '.$qn_userdata['user_login'], $user_id->get_error_message(), array('status' => 400 ) );
                continue;
            }

            //add roles to new user
            $new_user = new WP_User( $user_id );
            if ( isset( $request_data['data']['roles']['add_roles'] ) ) {
                $new_roles = $request_data['data']['roles']['add_roles'];
                foreach ( $new_roles as $new_role ) {
                    $new_user->add_role( trim( $new_role ) );
                }
            }

            //remove roles to new user
            if ( isset( $request_data['data']['roles']['remove_roles'] ) ) {
                $remove_roles = $request_data['data']['roles']['remove_roles'];
                foreach ( $remove_roles as $remove_role ) {
                    $new_user->remove_role( trim( $remove_role ) );
                }
            }

            do_action( 'quentn_user_register', $new_user );

            //send email if set by quentn call
            if ( $is_send_user_email && isset( $request_data['data']['notify'] ) && $request_data['data']['notify'] ) {
                wp_new_user_notification( $user_id, NULL, 'user' );
            }
        }

        if ( ! empty( $error->get_error_codes() ) ) {
            return $error;
        }

        return rest_ensure_response( 'Data Successfully Updated' );
    }

    /**
     * Get list of all wp roles
     *
     * @since  1.1.1
     * @access public
     * @return array
     */
    public function quentn_get_tracking_code( ) {
        $web_tracking = new Quentn_Wp_Web_Tracking();
        if( ! get_option('quentn_web_tracking_enabled') ) {
            return rest_ensure_response( array( 'saved' => 0 ) );
        }
        update_option("quentn_web_tracking_code", $web_tracking->get_quentn_web_tracking_code() );
        return rest_ensure_response( array( 'saved' => 1 ) );
    }

    /**
     * Validate a request argument based on details registered to the route.
     *
     * @param  mixed            $value   Value of the 'mail' argument.
     * @param  WP_REST_Request  $request The current request object.
     * @param  string           $param   Key of the parameter. In this case it is 'mail'.
     * @return WP_Error|boolean
     */
    public function quentn_validate_email_callback($value, $request, $param) {

        if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
            return new WP_Error( 'rest_invalid_param', esc_html__( 'There must be valid email address', 'quentn-wp' ), array( 'status' => 400 ) );
        }
    }

    /**
     * Add data for wp user for fields sent by quentn request
     *
     * @since  1.0.0
     * @access public
     * @return array
     */
    public function user_fields_supported_by_quentn( $userdata ) {

        $return = array();
        if( ! isset( $userdata['email'] ) || ! filter_var( $userdata['email'], FILTER_VALIDATE_EMAIL ) ) {
            return $return;
        }
        //if username is not set, we will take email address as user name
        $return['user_login'] = sanitize_text_field( $userdata['email'] );
        $return['user_email'] = sanitize_text_field( $userdata['email'] );

        if( isset( $userdata['first_name'] ) ) {
            $return['first_name'] = sanitize_text_field($userdata['first_name']);
        }
        if(isset($userdata['last_name'])) {
            $return['last_name'] = sanitize_text_field($userdata['last_name']);
        }
        if(isset($userdata['website'])) {
            $return['user_url'] = sanitize_text_field($userdata['website']);
        }

        return $return;
    }


    /**
     * Varify quentn request
     *
     * @since  1.0.0
     * @access public
     * @return bool|WP_Error
     */
    public function quentn_check_credentials($request) {

        $request_body = json_decode($request->get_body(), true);

        $api_key = ( get_option('quentn_app_key') ) ? get_option('quentn_app_key') : '';

        //check time validation for request
        if( $request_body['vu'] <= time() ) {
            return new WP_Error( 'Time Invalid', esc_html__( 'Time has expired', 'quentn-wp' ), array( 'status' => 401 ) );
        }


        if( isset( $request_body['data'] ) ) {
            $hash =  hash( 'sha256', $request_body['data'].$request_body['vu'].$api_key );
        } else {
            $hash =  hash( 'sha256', $request_body['vu'].$api_key );
        }

        if ( $hash != $request_body['hash'] ) {
            return new WP_Error( __( 'API key is not valid' ), esc_html__( 'Incorrect Api Key', 'quentn-wp' ), array( 'status' => 401 ) );
        }

        return true;
    }

    /**
     * Create user in wp
     *
     * @since  1.0.0
     * @access public
     * @return int|WP_Error
     */
    public function create_user( $email ) {
        $user_email = sanitize_email( $email );
        return wp_insert_user( ['user_login' => $user_email, 'user_email' => $user_email] );
    }
}

Quentn_Wp_Rest_Api::get_instance();