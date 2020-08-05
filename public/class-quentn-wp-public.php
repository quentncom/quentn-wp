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
     * Quentn API Handler
     *
     * @since  1.1.0
     * @access   private
     * @var Quentn_Wp_Api_Handler
     */

    private $api_handler;

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
        $this->api_handler = Quentn_Wp_Api_Handler::get_instance();
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

        //add files for web push notifications
        wp_enqueue_script( 'quentn.push.notifications.js', plugin_dir_url( __FILE__ ) . 'js/quentn-push-notifications.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'quentn.service-worker.js', plugin_dir_url( __FILE__ ) . 'js/quentn-service-worker.js', array( 'jquery' ), $this->version, true );

        if( Helper::is_elementor_plugin_enabled() && Helper::is_elementor_pro_plugin_enabled() ) {
            wp_enqueue_script( 'quentn-elementor-form-autofill.js', plugin_dir_url( __FILE__ ) . 'js/quentn-elementor-form-autofill.js', array( 'jquery' ), $this->version, true );
        }

        //Localize the script with new data
        $site_path = array(
            'plugin_dir_url' => plugin_dir_url( __FILE__ ),
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'create_notify_security' => wp_create_nonce( 'create-push-notification-subscription-nonce' ),
            'delete_notify_security' => wp_create_nonce( 'delete-push-notification-subscription-nonce' ),
            'update_notify_security' => wp_create_nonce( 'update-push-notification-subscription-nonce' ),
        );
        wp_localize_script( $this->plugin_name, 'wp_qntn_url', $site_path );
	}

	public function load_quentn_web_tracking() {
        if( get_option( 'quentn_web_tracking_code' ) ) {
            echo get_option( 'quentn_web_tracking_code' );
        }
    }


    /**
     * add web push notifications subscriber data
     *
     * @access public
     * @return void
     */
    public function create_push_notification_subscription() {

        check_ajax_referer( 'create-push-notification-subscription-nonce', 'security' );

        if ( ! isset( $_POST['endpoint'] ) ) {
            return;
        }

        global $wpdb;
        $subscription_data = array();
        $table = $wpdb->prefix. TABLE_QUENTN_SUBSCRIBERS;

        $subscription_data['subscription_settings'] = ( isset( $_POST['settings'] ) ) ?  json_decode( stripslashes( $_POST['settings'] ) ) : NULL;
        $subscription_data['endpoint'] = $_POST['endpoint'];
        $subscription_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $qntn_param = ( isset( $_POST['qntn'] ) ) ?  $_POST['qntn'] : '';

        $contact_id = $this->get_contact_id( $qntn_param );

        //set contact_id for api call, it can be id or valid email address
        if ( $contact_id ) {
            if ( ctype_digit( $contact_id ) ) {
                $subscription_data['contact_id'] = $contact_id;
            } elseif ( is_email( $contact_id ) ) {
                $subscription_data['email'] = $contact_id;
            }
            if ( get_option( 'quentn_tags_push_notification' ) ) {
                $subscription_data['terms'] = get_option( 'quentn_tags_push_notification' );
            }
        }

        $data = array( 'user_agent' => $subscription_data['user_agent'], 'contact_id' => ( $contact_id ) ? $contact_id : NULL  , 'created' => time(), 'endpoint' => $subscription_data['endpoint'], 'settings' => ( isset( $_POST['settings'] ) ) ?  stripslashes( $_POST['settings'] ) : NULL  );
        $format = array( '%s', '%s', '%d', '%s', '%s' );
        $status = $wpdb->insert( $table, $data,$format );
        $subscription_data['endpoint_id'] = $wpdb->insert_id;
        if ( $status !== false ) {
            $this->api_handler->update_web_push_endpoint( $subscription_data, 'POST' );
        }
    }


    /**
     * update web push notifications subscriber data
     *
     * @access public
     * @return void
     */
    public function update_push_notification_subscription() {
        check_ajax_referer( 'update-push-notification-subscription-nonce', 'security' );

        $qntn_param = ( isset( $_POST['qntn'] ) ) ?  $_POST['qntn'] : '';
        $endpoint = ( isset( $_POST['endpoint'] ) ) ?  $_POST['endpoint'] : NULL;
        $contact_id = $this->get_contact_id( $qntn_param );
        //don't need to update if there is no contact_id or endpoint found
        if ( ! $endpoint || ! $contact_id ) {
            return;
        }

        $subscriber_id = $this->get_subscriber_id( $endpoint );
        if ( ! $subscriber_id ) {
            return;
        }

        $subscription_data = array();
        $subscription_data['subscription_settings'] = ( isset( $_POST['settings'] ) ) ?  json_decode( stripslashes( $_POST['settings'] ) ) : NULL;
        $subscription_data['endpoint'] = $endpoint;
        $subscription_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        //set contact_id for api call, it can be id or valid email address
        if ( ctype_digit( $contact_id ) ) {
            $subscription_data['contact_id'] = $contact_id;
        } elseif ( is_email( $contact_id ) ) {
            $subscription_data['email'] = $contact_id;
        }
        if ( get_option( 'quentn_tags_push_notification' ) ) {
            $subscription_data['terms'] = get_option( 'quentn_tags_push_notification' );
        }


        global $wpdb;
        $table = $wpdb->prefix. TABLE_QUENTN_SUBSCRIBERS;

        $data = array( 'contact_id' => $contact_id );
        $where = array( 'id' => $subscriber_id );
        $data_format = array( '%s' );
        $where_format = array( '%d' );
        $update_status = $wpdb->update( $table, $data, $where, $data_format, $where_format );

        if ( $update_status !== false ) {
            $subscription_data['endpoint_id'] = $subscriber_id;
            $this->api_handler->update_web_push_endpoint( $subscription_data, 'PATCH' );
        }
    }

    /**
     * delete web push notifications subscriber data
     *
     * @access public
     * @return void
     */
    public function delete_push_notification_subscription() {
        check_ajax_referer( 'delete-push-notification-subscription-nonce', 'security' );

        global $wpdb;
        $table = $wpdb->prefix. TABLE_QUENTN_SUBSCRIBERS;
        $endpoint = ( isset( $_POST['endpoint'] ) ) ?  $_POST['endpoint'] : NULL;
        if ( ! $endpoint ) {
            return;
        }

        $subscriber_id = $this->get_subscriber_id( $endpoint );
        $data = array( 'id' => $subscriber_id );
        $format = array( '%d' );
        $delete_status = $wpdb->delete( $table, $data, $format );
        if ( $delete_status !== false ) {
            $this->api_handler->update_web_push_endpoint( array('endpoint_id' => $subscriber_id), 'DELETE' );
        }
    }

    /**
     * Get subscriber's id
     *
     * @access public
     * @param  string $endpoint
     * @return int|boolean
     */
     public function get_subscriber_id( $endpoint ) {
         global $wpdb;
         $sql = "SELECT `id`  FROM ". $wpdb->prefix. TABLE_QUENTN_SUBSCRIBERS. " where `endpoint` = '". $endpoint . "'";
         $subscriber = $wpdb->get_row( $sql );
         if ( ! $subscriber ) {
             return false;
         }
         return $subscriber->id;
     }

    /**
     * Get contact id
     *
     * @access public
     * @param  string $qntn_param
     * @return int|string|boolean
     */
    public function get_contact_id( $qntn_param = '' ) {
        $qntn = json_decode( base64_decode( $qntn_param ), true );
        $qntn_trck = $this->get_cookie( 'qntnTrck' );

        if( isset( $qntn["cid"] ) )  { //contact Id from url
            return $qntn["cid"];
        }
        if( isset($qntn_trck["cid"] ) )  { //contact id from cookie
            return $qntn_trck["cid"];
        }
        if( isset( $qntn_param["email"] ) && is_email( $qntn_param['email'] ) )  { //contact email from url in plain text
            return $qntn_param["email"];
        }
        if( isset( $qntn_trck["email"] ) && is_email( $qntn_trck['email'] ) )  { //contact email from cookie
            return $qntn_trck["email"];
        }
        return false;
    }


    /**
     * Returns a json decoded cookie
     *
     * @param string $name
     * @return array
     */
    public function get_cookie( $name ) {
        if ( isset( $_COOKIE[$name] ) ) {
            $cookie_value = sanitize_text_field( $_COOKIE[$name] );
            return json_decode( stripslashes( $cookie_value ), true );
        }
        return array();
    }
}
