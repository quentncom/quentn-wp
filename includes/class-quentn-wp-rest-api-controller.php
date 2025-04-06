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
     * The first URL segment after core prefix
     *
     * @since  1.2.8
     * @access private
     * @var    string
     */
    private $namespace_v2;

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
     * The base URL for route to get quentn logs
     *
     * @since  1.2.8
     * @access private
     * @var    string
     */
    private $get_logs;

    /**
     * The base URL for route to get page access
     *
     * @since  1.2.8
     * @access private
     * @var    string
     */
    private $get_page_access;

    /**
     * The base URL for route to get a page restriction settings
     *
     * @since  1.2.8
     * @access private
     * @var    string
     */
    private $page_restriction_settings;


    /**
     * Initialize our namespace and resource name.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        add_action( 'rest_api_init', array( $this, 'register_page_routes' ) );

        $this->namespace = 'quentn/api/v1';
        $this->namespace_v2 = 'quentn/api/v2';
        $this->create_user = '/users';
        $this->grant_access = '/pages/grant-access';
        $this->revoke_access = '/pages/revoke-access';
        $this->get_page_restrictions = '/get-page-restrictions';
        $this->get_user_roles = '/get-user-roles';
        $this->get_tracking = '/get-tracking';
        $this->get_logs = '/log';
        $this->get_page_access = '/page-access/(?P<pid>\d+)';
        $this->page_restriction_settings = '/page-restriction-settings/(?P<pid>\d+)';
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
            'callback' => array( $this, 'quentn_grant_access' ),
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
            'callback' => array( $this, 'quentn_revoke_access' ),
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

        //register route to get list of all pages having quentn restrictions active
        register_rest_route( $this->namespace_v2, $this->get_page_restrictions, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_get_restricted_pages_v2' ),
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

        //register route to create new user in wp
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

        //register route to get logs
        register_rest_route( $this->namespace, $this->get_logs, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_get_logs' ),
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

        //register route to get page access
        register_rest_route( $this->namespace, $this->get_page_access, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_get_page_access' ),
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

        //register route to get page restriction settings
        register_rest_route( $this->namespace, $this->page_restriction_settings, array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods' => \WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array( $this, 'quentn_get_page_restriction_settings' ),
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
     * @param WP_REST_Request $request The current request object.
     * @return WP_Error|WP_REST_Response
     */
    public function quentn_grant_access( $request ) {
        global $wpdb;

        $request_body = json_decode( $request->get_body(), true );
        $quentn_data = json_decode( base64_decode( $request_body['data'] ), true );

        $emails = isset( $quentn_data['data']['email'] ) ? $quentn_data['data']['email'] : array();
        $pages = isset( $quentn_data['data']['page'] ) ? $quentn_data['data']['page'] : array();

        $placeholders = [];
        $values = [];
        $now = time();

        foreach ( $emails as $email ) {
            $email = sanitize_email( $email );
            if ( empty( $email ) ) {
                continue;
            }

            $email_hash = hash( 'sha256', $email );

            foreach ( $pages as $page ) {
                $page = intval( $page );
                $placeholders[] = "(%d, %s, %s, %d)";
                array_push( $values, $page, $email, $email_hash, $now );
            }
        }

        if ( empty( $placeholders ) ) {
            return new WP_Error( 'no_values', __( 'No valid values to grant access.', 'quentn-wp' ), array( 'status' => 400 ) );
        }

        $table = $wpdb->prefix . TABLE_QUENTN_RESTRICTIONS;
        $sql = "INSERT INTO $table (page_id, email, email_hash, created_at) VALUES ";
        $sql .= implode( ', ', $placeholders );
        $sql .= " ON DUPLICATE KEY UPDATE created_at = VALUES(created_at)";

        $prepared = $wpdb->prepare( $sql, $values );
        $wpdb->query( $prepared );

        do_action( 'quentn_access_granted', $emails, $pages, QUENTN_WP_ACCESS_ADDED_BY_API );

        return rest_ensure_response( esc_html__( 'Permissions Timer Successfully Updated', 'quentn-wp' ) );
    }

    /**
     * remove access to page
     *
     * @since  1.0.0
     * @access public
     * @param WP_REST_Request $request The current request object.
     * @return WP_Error|WP_REST_Response
     */
    public function quentn_revoke_access( $request ) {
        global $wpdb;

        $request_body = json_decode( $request->get_body(), true );
        $quentn_data = json_decode( base64_decode( $request_body['data'] ), true );

        $emails = isset( $quentn_data['data']['email'] ) ? $quentn_data['data']['email'] : array();
        $pages = isset( $quentn_data['data']['page'] ) ? $quentn_data['data']['page'] : array();

        // Collect safe page-email pairs
        $conditions = [];
        $values     = [];

        foreach ( $emails as $email ) {
            $email = sanitize_email( $email );
            foreach ( $pages as $page ) {
                $page = intval( $page );
                $conditions[] = "(page_id = %d AND email = %s)";
                $values[] = $page;
                $values[] = $email;
            }
        }

        if ( empty( $conditions ) ) {
            return new WP_Error( 'no_values', __( 'No valid values to revoke access.', 'quentn-wp' ), array( 'status' => 400 ) );
        }

        // Build secure query
        $where = implode( ' OR ', $conditions );
        $query = "DELETE FROM {$wpdb->prefix}" . TABLE_QUENTN_RESTRICTIONS . " WHERE $where";

        $prepared_query = $wpdb->prepare( $query, $values );
        $affected_rows = $wpdb->query( $prepared_query );

        if ( $affected_rows ) {
            do_action( 'quentn_access_revoked', $emails, $pages, QUENTN_WP_ACCESS_REVOKED_BY_API );
        }

        return rest_ensure_response( esc_html__( 'Permissions Successfully Updated', 'quentn-wp' ) );
    }

    /**
     * Get list of all pages where quentn restrictions are applied
     *
     * @since  1.0.0
     * @access public
     * @return WP_Error|WP_REST_Response
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
        //todo remove json_encode function
        return rest_ensure_response( json_encode( $restricted_pages ) );
    }

    /**
     * Get list of all pages where quentn restrictions are applied
     *
     * @since  1.2.8
     * @access public
     * @param WP_REST_Request $request The current request object.
     * @return WP_Error|WP_REST_Response
     */
    public function quentn_get_restricted_pages_v2( $request ) {
        global $wpdb;

        $request_body = json_decode( $request->get_body(), true );
        $request_body_data = json_decode( base64_decode( $request_body['data'] ), true );
        $request_data = $request_body_data['data'];

        // Sanitize and validate parameters
        $allowed_orderby = [ 'title', 'date', 'modified', 'menu_order' ];
        $allowed_sort    = [ 'ASC', 'DESC' ];

        $limit     = isset( $request_data['limit'] ) ? absint( $request_data['limit'] ) : 50;
        $offset    = isset( $request_data['offset'] ) ? absint( $request_data['offset'] ) : 0;
        $order_by = ( isset( $request_data['order_by'] ) && in_array( $request_data['order_by'], $allowed_orderby ) )  ? $request_data['order_by']  : 'date';
        $sort = ( isset( $request_data['sort'] ) && in_array( strtoupper( $request_data['sort'] ), $allowed_sort ) )  ? strtoupper( $request_data['sort'] ) : 'DESC';


        $args = [
            'post_type'      => 'page',
            'meta_key'       => '_quentn_post_restrict_meta',
            'orderby'        => $order_by,
            'order'          => $sort,
            'offset'         => $offset,
            'posts_per_page' => $limit,
        ];

        // Query restricted pages
        $restricted_pages_query = new WP_Query( $args );
        $restricted_pages = [];

        if ( $restricted_pages_query->have_posts() ) {
            $page_ids = array_column( $restricted_pages_query->posts, 'ID' );

            $pages_access_links = [];

            if ( ! empty( $page_ids ) ) {
                $placeholders = implode( ',', array_fill( 0, count( $page_ids ), '%d' ) );
                $sql = $wpdb->prepare(
                    "SELECT page_id, COUNT(*) as totoal_access
                 FROM {$wpdb->prefix}" . TABLE_QUENTN_RESTRICTIONS . "
                 WHERE page_id IN ($placeholders)
                 GROUP BY page_id",
                    $page_ids
                );
                $rows = $wpdb->get_results( $sql );

                foreach ( $rows as $row ) {
                    $pages_access_links[ $row->page_id ] = $row->totoal_access;
                }
            }

            foreach ( $restricted_pages_query->posts as $restricted_page ) {
                $quentn_post_restrict_meta = get_post_meta( $restricted_page->ID, '_quentn_post_restrict_meta', true );

                $restricted_pages[] = [
                    'page_id'          => $restricted_page->ID,
                    'page_title'       => $restricted_page->post_title,
                    'page_public_url'  => get_page_link( $restricted_page->ID ),
                    'restriction_type' => ! empty( $quentn_post_restrict_meta['countdown'] ) ? 'countdown' : 'access',
                    'access_links'     => $pages_access_links[ $restricted_page->ID ] ?? 0,
                ];
            }
        }
        $response = [
            'success'   => true,
            'total'     => count( $restricted_pages ),
            'limit'     => $limit,
            'offset'    => $offset,
            'order_by'  => $order_by,
            'sort'      => $sort,
            'data'      => $restricted_pages,
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Get list of all wp roles
     *
     * @since  1.0.0
     * @access public
     * @return WP_Error|WP_REST_Response
     */
    public function quentn_get_user_roles( ) {
        $wp_roles = new WP_Roles();
        $all_roles = $wp_roles->get_names();
        //todo remove json_encode function
        return rest_ensure_response( json_encode( $all_roles ) );
    }

    /**
     * Create wp users
     *
     * @since  1.0.0
     * @access public
     * @param WP_REST_Request $request $request The current request object.
     * @return WP_Error|WP_REST_Response
     *
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
                do_action( 'quentn_user_updated', $qn_userdata['user_email'], $user_id );
            } else {
                //no default role set
                $qn_userdata['role'] = '';
                //insert user in wordpress
                $user_id = wp_insert_user( $qn_userdata );
                // On success, add user meta last login as false
                if ( ! is_wp_error( $user_id ) ) {
                    update_user_meta( $user_id, 'quentn_last_login', 0 );
                    do_action( 'quentn_user_created', $qn_userdata['user_email'], $user_id );
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
                    $role_to_add  = trim( $new_role );

                    // Skip if the role is 'administrator'
                    if ( strtolower($role_to_add) === 'administrator' ) {
                        continue;
                    }

                    $new_user->add_role( $role_to_add );
                    do_action( 'quentn_user_role_added', $new_user->user_email, $user_id, trim( $new_role ) );
                }
            }

            //remove roles to new user
            if ( isset( $request_data['data']['roles']['remove_roles'] ) ) {
                $remove_roles = $request_data['data']['roles']['remove_roles'];
                foreach ( $remove_roles as $remove_role ) {
                    $new_user->remove_role( trim( $remove_role ) );
                    do_action( 'quentn_user_role_removed', $new_user->user_email, $user_id, trim( $remove_role ) );
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
     * Get list of logs
     *
     * @since  1.2.8
     * @access public
     * @param WP_REST_Request $request The current request object.
     * @return WP_Error|WP_REST_Response
     */
    public function quentn_get_logs( $request ) {
        global $wpdb;

        $request_body = json_decode( $request->get_body(), true );
        $request_body_data = json_decode( base64_decode( $request_body['data'] ), true );

        if ( ! isset( $request_body_data['data'] ) || ! is_array( $request_body_data['data'] ) ) {
            return new WP_Error( 'invalid_data', __( 'Malformed data structure.', 'quentn-wp' ), [ 'status' => 400 ] );
        }
        $request_data = $request_body_data['data'];

        $conditions = [];
        $values = [];

        $sql = "SELECT * FROM {$wpdb->prefix}" . TABLE_QUENTN_LOG;

        // Safe filtering
        if ( ! empty( $request_data['events'] ) ) {
            $event_ids = array_map( 'intval', $request_data['events'] );
            $placeholders = implode( ',', array_fill( 0, count( $event_ids ), '%d' ) );
            $conditions[] = "event IN ($placeholders)";
            $values = array_merge( $values, $event_ids );
        }

        if ( ! empty( $request_data['emails'] ) ) {
            $email_placeholders = implode( ',', array_fill( 0, count( $request_data['emails'] ), '%s' ) );
            $conditions[] = "email IN ($email_placeholders)";
            $values = array_merge( $values, $request_data['emails'] );
        }

        if ( ! empty( $request_data['pages'] ) ) {
            $page_ids = array_map( 'intval', $request_data['pages'] );
            $placeholders = implode( ',', array_fill( 0, count( $page_ids ), '%d' ) );
            $conditions[] = "page_id IN ($placeholders)";
            $values = array_merge( $values, $page_ids );
        }

        if ( ! empty( $request_data['from'] ) ) {
            $conditions[] = "created_at >= %d";
            $values[] = intval( $request_data['from'] );
        }

        if ( ! empty( $request_data['to'] ) ) {
            $conditions[] = "created_at <= %d";
            $values[] = intval( $request_data['to'] );
        }

        if ( ! empty( $conditions ) ) {
            $sql .= " WHERE " . implode( " AND ", $conditions );
        }

        // Validate and whitelist sort fields
        $allowed_order_by = [ 'created_at', 'event', 'email', 'page_id' ];
        $allowed_sort = [ 'ASC', 'DESC' ];

        $order_by = ( isset( $request_data['order_by'] ) && in_array( $request_data['order_by'], $allowed_order_by ) ) ? $request_data['order_by'] : 'created_at';
        $sort_by = ( isset( $request_data['sort'] ) && in_array( strtoupper( $request_data['sort'] ), $allowed_sort ) ) ? strtoupper( $request_data['sort'] ) : 'DESC';

        $sql .= " ORDER BY $order_by $sort_by";

        // Limit + offset
        $limit = ! empty( $request_data['limit'] ) ? absint( $request_data['limit'] ) : 50;
        $offset = ! empty( $request_data['offset'] ) ? absint( $request_data['offset'] ) : 0;

        $sql .= " LIMIT %d OFFSET %d";
        $values[] = $limit;
        $values[] = $offset;

        // Prepare and execute query
        $prepared_sql = $wpdb->prepare( $sql, $values );
        $results = $wpdb->get_results( $prepared_sql, 'ARRAY_A' );

        if ( $wpdb->last_error ) {
            return new WP_Error( 'log_call_failed', $wpdb->last_error );
        }

        // Process results
        $logs = [];
        foreach ( $results as $log ) {
            if ( ! empty( $log['page_id'] ) ) {
                $log['page_title'] = get_the_title( $log['page_id'] );
                $log['page_public_url'] = get_page_link( $log['page_id'] );
            } else {
                $log['page_title'] = '';
                $log['page_public_url'] = '';
            }

            $logs[] = $log;
        }

        // Load event labels
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/config.php';

        $requested_events = [];
        if ( ! empty( $request_data['events'] ) ) {
            foreach ( $request_data['events'] as $event ) {
                if ( isset( $events[ $event ] ) ) {
                    $requested_events[ $event ] = $events[ $event ];
                }
            }
        }

        $response = [
            'success'          => true,
            'total'            => count( $results ),
            'limit'            => $limit,
            'offset'           => $offset,
            'order_by'         => $order_by,
            'sort'             => $sort_by,
            'events'           => $events,
            'requested_events' => $requested_events,
            'data'             => $logs,
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Get list of get page access
     *
     * @since  1.2.8
     * @access public
     * param WP_REST_Request $request The current request object.
     * @return WP_Error|WP_REST_Response
     */
    public function quentn_get_page_access( $request ) {
        $request_body = json_decode( $request->get_body(), true );
        $request_body_data = json_decode( base64_decode( $request_body['data'] ), true );
        $request_data = $request_body_data['data'];
        $page_id = $request->get_param('pid');

        global $wpdb;
        $table_name = $wpdb->prefix . TABLE_QUENTN_RESTRICTIONS;

        // Validation against allowed values
        $allowed_order_by = ['email', 'email_hash', 'created_at'];
        $order_by = in_array($request_data['order_by'], $allowed_order_by) ? $request_data['order_by'] : 'created_at';

        $allowed_sorts = ['asc', 'desc'];
        $sort_by = in_array(strtolower($request_data['sort']), $allowed_sorts) ? $request_data['sort'] : 'desc';

        $limit = isset($request_data['limit']) ? intval($request_data['limit']) : 50;
        $offset = isset($request_data['offset']) ? intval($request_data['offset']) : 0;

        $sql = "SELECT email, email_hash, created_at FROM $table_name WHERE page_id = %d ORDER BY $order_by $sort_by LIMIT %d, %d";

        $results = $wpdb->get_results($wpdb->prepare($sql, $page_id, $offset, $limit), ARRAY_A);
        if ($wpdb->last_error) {
            return new WP_Error('page_access_call_failed', $wpdb->last_error);
        }

        //prepare response data
        $page_accesses = [];
        $separator = ( parse_url( get_page_link( $page_id ), PHP_URL_QUERY ) ) ? '&' : '?';
        foreach ( $results as $page_access ) {
            $page_access['access_link'] = get_page_link( $page_id ) . $separator.'qntn_wp=' . $page_access['email_hash'];
            unset( $page_access['email_hash'] ); //email not included in response
            $page_accesses[] = $page_access;
        }

        $response = [
            'success' => true,
            'page_id' => $page_id,
            'page_title' => get_the_title( $page_id ),
            'page_public_url' => get_page_link( $page_id ),
            'total' => count( $page_accesses ),
            'limit' => $limit,
            'offset' => $offset,
            'order_by' => $order_by,
            'sort' => $sort_by,
            'data' => $page_accesses,
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Get list of get page restriction settings
     *
     * @since  1.2.8
     * @access public
     * @param WP_REST_Request $request The current request object.
     * @return WP_Error|WP_REST_Response
     */
    public function quentn_get_page_restriction_settings( $request ) {

        $page_id = absint( $request->get_param('pid') );

        if ( ! $page_id ) {
            return new WP_Error( 'invalid_page_id', __( 'Page ID is required.', 'quentn-wp' ), [ 'status' => 400 ] );
        }

        $restricted_data = get_post_meta( $page_id, '_quentn_post_restrict_meta', true );

        $response[] = true;
        $response = [
            'success' => true,
            'page_id' => $page_id,
            'page_title' => get_the_title( $page_id ),
            'page_public_url' => get_page_link( $page_id ),
        ];
        $response['restriction_enabled'] = boolval( $restricted_data['status'] );
        if ( ! empty( $restricted_data ) ) {
            $response['restriction_type'] = ! empty( $restricted_data['countdown'] ) ? 'countdown' : 'access';
            $response['countdown_type'] = $restricted_data['countdown_type'];
            $response['countdown_absolute_date'] = $restricted_data['absolute_date'];
            $response['countdown_relative_settings'] = [
                'hours' => $restricted_data['hours'],
                'minutes' => $restricted_data['minutes'],
                'seconds' => $restricted_data['seconds'],
            ];
            $response['countdown_relative_start_type'] = $restricted_data['access_mode'] == 'permission_granted_mode' ? 'permission_granted' : 'first_visit';
            $response['display_countdown'] = $restricted_data['display_countdown_default_status'];
            $response['countdown_top_page'] = $restricted_data['quentn_countdown_stick_on_top'];
            $response['redirection_type'] = $restricted_data['redirection_type'] == 'restricted_message' ? 'message' : 'url';
            $response['redirection_url'] = $restricted_data['redirect_url'];
            $response['redirection_message'] = $restricted_data['error_message'];
        }

        return rest_ensure_response( $response );
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
     * @param WP_REST_Request $request The current request object.
     * @return bool|WP_Error
     */
    public function quentn_check_credentials( $request ) {
        $request_body = json_decode( $request->get_body(), true );

        // Basic input validation
        if ( !isset( $request_body['vu'] ) || !isset( $request_body['hash'] ) ) {
            return new WP_Error( 'missing_params', esc_html__( 'Required parameters missing', 'quentn-wp' ), array( 'status' => 400 ) );
        }

        $api_key = get_option( 'quentn_app_key', '' );

        // Return error if API key is empty (critical security check)
        if ( empty( $api_key ) ) {
            return new WP_Error( 'api_key_not_set', esc_html__( 'API key is not configured', 'quentn-wp' ), array( 'status' => 401 ) );
        }

        // validate time
        if( $request_body['vu'] <= time() ) {
            return new WP_Error( 'time_expired', esc_html__( 'Time has expired', 'quentn-wp' ), array( 'status' => 401 ) );
        }

        // Calculate expected hash
        $components = [ $request_body['vu'], $api_key ];
        if ( isset( $request_body['data'] ) ) {
            array_unshift( $components, $request_body['data'] );
        }

        // Use hash_equals() for timing-safe comparison
        $expected_hash = hash( 'sha256', implode( '', $components ) );
        if ( !hash_equals( $expected_hash, $request_body['hash'] ) ) {
            return new WP_Error( 'invalid_key', esc_html__( 'Authentication failed', 'quentn-wp' ), array( 'status' => 401 ) );
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