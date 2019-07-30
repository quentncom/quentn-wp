<?php

class Quentn_Wp_Restrict_Access
{
    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        add_shortcode( 'quentn_flipclock', array($this, 'get_flipclock_shortcode'));
        if( ! is_admin() ) {
            add_filter( 'the_content', array($this, 'quentn_content_permission_check'));
        }
        add_action('wp_head', array( $this, 'set_countdown_clock' ) );
    }

    /**
     * Get clock shortcode
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
     public function get_flipclock_shortcode() {
         return "<div class='quentn-flipclock'></div>";
     }

    /**
     * Return an instance of this class.
     *
     * @since 1.0.0
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
     * Check access right to page content
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
     public function quentn_content_permission_check( $content ) {

        //if status is not avtive, return content
        if( ! $page_meta = $this->get_quentn_post_restrict_meta() ) {
            return $content;
        }

       $is_display_content = false;

        //If page restriction is countdown then calculate expiry time
        if ( isset( $page_meta['countdown'] ) && $page_meta['countdown'] ) {
            if ( $this->calculate_expire_time() > 0) {
                $is_display_content = true;
            }
        } else { //if it is not countdown then check its access from database
            $get_access_emails = $this->get_access_emails();
            if( $this->get_user_access( $get_access_emails ) ) {
                $is_display_content = true;
            }
        }

        //return content if user is authorized
        if( $is_display_content ) {
            //set cookie if it is new email address
            $get_access_email = $this->get_new_access();
            if( $get_access_email != '' ) {
                $this->set_cookie_data( $get_access_email );
            }
            return $content;
        }

        //page user is not allowed, then redirect/display message
        if( $page_meta['redirection_type'] == 'restricted_url' && $page_meta['redirect_url'] != '') {
            //todo avoid rest api call, need to find a better way
            if ( strpos( Helper::get_current_url(), 'wp-json' ) === false ) {
                wp_redirect( $page_meta['redirect_url'] );
            }
        }
        else {
            return ( $page_meta['error_message'] ) ? $page_meta['error_message'] : '<h3>' . __('Access denied', 'quentn-wp' ) . '</h3>';
        }
    }

    /**
     * Get email addresses to check access
     *
     * @since  1.0.0
     * @access public
     * @return array
     */
    public function get_access_emails() {
        $get_access_emails = array();

        //get email address if it is in url/ new access
        $get_access_email = $this->get_new_access();
        //if it is new access/in url then add it in access emails list
        if( $get_access_email != '') {
            $get_access_emails = array( $get_access_email );
        }

        //get already saved cookie value if dont find in url
        if( empty( $get_access_emails ) ) {

            $cookie_saved_data = $this->get_json_cookie( 'qntn_wp_access' );

            $get_access_emails = ( isset( $cookie_saved_data['access'][get_the_ID()] ) ) ?  $cookie_saved_data['access'][get_the_ID()] : array();
        }
        return $get_access_emails;
    }


    /**
     * Check if user is first time visiting restricted page
     *
     * @since  1.0.0
     * @access public
     * @return bool
     */

    public function is_new_visitor() {
        return ( ! isset( $this->get_json_cookie( 'qntn_wp_access' )['is_visited'][get_the_ID()] ) ) ?  true : false;
    }

    /**
     * Get new access to if it is in url
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    public function get_new_access() {

        $get_new_access = '';

        if( isset( $_GET["qntn_wp"] ) ) { //get email address if it is in query string
            $get_new_access =   trim( $_GET["qntn_wp"] );
        } elseif ( isset($_GET["qntn"] ) ) { //qntn is used when request is created from quentn, it will be in base64 and json encoded
            $qntn = json_decode( base64_decode( $_GET["qntn"] ), true  );
            if( isset( $qntn['email'] ) ) { //if there is email in query string
                $get_new_access =   hash( 'sha256', trim( $qntn['email'] ) );
            }
        } elseif ( isset( $_GET["email"] ) ) { //if there is plain email address in url
            $email = str_replace(" ","+",trim( $_GET["email"] ) );
            $get_new_access =  hash( 'sha256', $email );
        }

        return $get_new_access;
    }

    /**
     * Set current time as starting time for first time visitor of page in quentn cookie
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function set_visitor_cookie() {
        //get the existing quentn cookie
        $cookie_saved_data = $this->get_json_cookie('qntn_wp_access');

        //set current time as starting time of visitor
        $set_visited_time = array(
            get_the_ID() => time(),
        );

        //if is_visited key is already set then just add current on into existing one
        if ( isset( $cookie_saved_data['is_visited'] ) ) {
            $set_visited_time = $set_visited_time + $cookie_saved_data['is_visited'];
        }

        $set_visitors_data['is_visited'] = $set_visited_time;

        //merge all data into existing quentn cookie data
        $set_cookie_data = array_merge($cookie_saved_data, $set_visitors_data);

        //set cookie
        $this->set_json_cookie('qntn_wp_access', $set_cookie_data);
    }

    /**
     * Set cookie data for different pages with access email addresses
     *
     * @since  1.0.0
     * @access public
     * @param string $access_email emails need to add in existing quentn cookie
     * @return array
     */
    public function set_cookie_data( $access_email ) {

        //get the existing quentn cookie
        $cookie_saved_data = $this->get_json_cookie('qntn_wp_access');

        //if this page access is not created
        if ( ! isset( $cookie_saved_data['access'][get_the_ID()] ) ) {
            //if no access is created then add access key in array
            if( ! isset( $cookie_saved_data['access'])) {
                $cookie_saved_data['access'] = array();
            }

            //add page id to access
            $add_page_id = array(
                get_the_ID() => [$access_email],
            );
            $cookie_saved_data['access'] = $cookie_saved_data['access'] +  $add_page_id;

        } elseif ( ! in_array( $access_email, $cookie_saved_data['access'][get_the_ID()] ) ) { //if page is set, then add new value only if not exist
            $cookie_saved_data['access'][get_the_ID()][] = $access_email;

        }
        $this->set_json_cookie('qntn_wp_access', $cookie_saved_data);
    }

    /**
     * Check if user has authorized to visit page
     *
     * @since  1.0.0
     * @access public
     * @param array $emails emails need to check whether it is authrized
     * @param int $page_id id of page we are checking access
     * @return bool|string
     */

    public function get_user_access( array $emails, $page_id = '' ) {
        if ( ! $page_id ) {
            $page_id = get_the_ID();
        }

        $emails = implode( "','",$emails );
        global $wpdb;
        //if email address is authroized to visit the page, get its creation date, in case of multiple email addresses, latest creation date will be considered
        $sql = "SELECT created_at FROM ".$wpdb->prefix . QUENTN_TABLE_NAME. " where page_id='".  $page_id."' and email_hash in ('".$emails."') order by created_at DESC LIMIT 1";
        $qntn_access = $wpdb->get_row( $sql );
        if ( ! $qntn_access ) {
            return false;
        }
        return  $qntn_access->created_at;
    }

    /**
     * Set flipclock settings
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function set_countdown_clock() {
        //if status is not avtive, return content
        if( ! $quentn_post_restrict_meta = $this->get_quentn_post_restrict_meta() ) {
            return;
        }

        $is_display_clock = false;
        //if restriction type is countdown
        if( isset( $quentn_post_restrict_meta['countdown'] ) && $quentn_post_restrict_meta['countdown'] )  {
            $quentn_expiry_page_inseconds = $this->calculate_expire_time();
            if( $quentn_expiry_page_inseconds > 0 ) {
                $is_display_clock = true;
            }
        }

        if( ! $is_display_clock ) {
            return;
        }

        //if redirect url set then page will be redirected to that url, otherwise it will just reload
        $is_redirect_url_set = 0;
        $redirect_url = '';
        //if redirection type is redirect to url
        if( $quentn_post_restrict_meta && array_key_exists( 'redirection_type', $quentn_post_restrict_meta ) && $quentn_post_restrict_meta['redirection_type'] == 'restricted_url' ) {
            $redirect_url = $quentn_post_restrict_meta['redirect_url'];
            $is_redirect_url_set = 1;
        }

        $clock_face = 'HourlyCounter';
        //if hours are more than 24 then show clock with days
        if( $quentn_expiry_page_inseconds > 86400 ) {
            $clock_face = 'DailyCounter';
        }
        global $post;
        $is_display_quentn_flipclock = 0;
        $get_locale =  substr( get_locale(),0, 2 );   // take first two elements from get_locale() i.e de, en
        if ( $quentn_post_restrict_meta['display_countdown_default_status'] || ( is_a( $post, 'WP_Post' ) &&  has_shortcode( $post->post_content, 'quentn_flipclock') ) ) {
            $is_display_quentn_flipclock = 1;
        }

        echo  "<script>var qncountdown = {
            seconds: $quentn_expiry_page_inseconds,        
            clockFace: '".$clock_face."',
            wpLang: '".$get_locale."',
            isRedirect: $is_redirect_url_set,
            isDisplayQuentnFlipclock: $is_display_quentn_flipclock,
            redirect_url: '".$redirect_url."',
                                
            };</script>";


        //if user set show clock on top of page
        if( $quentn_post_restrict_meta['display_countdown_default_status'] ) {
            global $post;
            if ( is_a( $post, 'WP_Post' ) ) {
                echo "<script>jQuery(document).ready(function () { jQuery('body').prepend('<div id=\'countdown-wrapper\'><div class=\"quentn-flipclock\"></div></div>')});</script>";
            }
        }
    }


    /**
     * Get page restriction data
     *
     * @since  1.0.0
     * @access public
     * @return mixed
     */
    public function get_quentn_post_restrict_meta() {
        return get_post_meta( get_the_ID(), '_quentn_post_restrict_meta', true );
    }

    /**
     * Calculate page expiry time
     *
     * @since  1.0.0
     * @access public
     * @return int|bool
     */
    public function calculate_expire_time(){

        $return = -1;
        $quentn_page_restrict_meta = $this->get_quentn_post_restrict_meta();
        //if page is countdown and countdown start from first visit
        if ( isset( $quentn_page_restrict_meta['access_mode'] ) && isset( $quentn_page_restrict_meta['countdown_type'] ) && $quentn_page_restrict_meta['access_mode'] == 'first_visit_mode' && $quentn_page_restrict_meta['countdown_type'] == 'relative') {
            //create cookie if new visitor
            if( $this->is_new_visitor() ) {
                $this->set_visitor_cookie();
            }
            //get starting time
            $created_at =  $this->get_json_cookie( 'qntn_wp_access' )['is_visited'][get_the_ID()];
        } else { //countdown is not stat from fist time visit but permission granted, then get starting point from database
            $get_access_emails = $this->get_access_emails();
            $created_at = $this->get_user_access( $get_access_emails ); //check if user is authorised to visit the page
        }

        //if user is authorised to vist the page, and page restriction type is countdown, then calculate expiry time
        if( $created_at && $quentn_page_restrict_meta['countdown'] ) {

            //if page restriction type is absolute
            if( $quentn_page_restrict_meta['countdown_type'] == 'absolute' ) {
                //get absolute expiry date
                if ( isset( $quentn_page_restrict_meta['absolute_date'] ) && strtotime( $quentn_page_restrict_meta['absolute_date']) !== false ) {
                    $return = $this->calculate_absolute_page_expire_time( $quentn_page_restrict_meta['absolute_date'] );
                }
            } elseif( $quentn_page_restrict_meta['countdown_type'] == 'relative' ) { //if page restriction type is relative, then we will add relative time set depends on access creation date
                $return = $this->calculate_relative_page_expire_time( $created_at );
            }
        }
        return $return;
    }

    /**
     * Calculate page expiry time if its countdown type is absolute
     *
     * @since  1.0.0
     * @access public
     * @return int
     */
    public function calculate_absolute_page_expire_time ( $expiry_date ) {

        /*==== getting timezone set in admin settings=== */
        //if time zone is selected by region by admin, then follow it
        $timezone_string = get_option('timezone_string');
        $utc_difference = 0;
        if ( ! empty($timezone_string ) ) {
            date_default_timezone_set($timezone_string);
        }else {
            //if timezone is not selected by region but with UTC difference like +1, -1.5 then set UTC as default and calculate difference in seconds
            date_default_timezone_set("UTC");
            $utc_difference = get_option('gmt_offset')*3600;
        }

        $timestamp = strtotime( $expiry_date );
        $current_time = time() + $utc_difference;
        return  $timestamp - $current_time;
    }

    /**
     * Calculate page expiry time if its countdown type is relative
     *
     * @since  1.0.0
     * @access public
     * @param int $created_at starting time since to calculate expiry time
     * @return int
     */
    public function calculate_relative_page_expire_time ( $created_at ) {
        $quentn_page_restrict_meta = $this->get_quentn_post_restrict_meta();

        $hours =   ( array_key_exists( 'hours', $quentn_page_restrict_meta ) ) ? $quentn_page_restrict_meta['hours'] : 0;
        $minutes = ( array_key_exists( 'minutes', $quentn_page_restrict_meta ) ) ? $quentn_page_restrict_meta['minutes'] : 0;
        $seconds = ( array_key_exists( 'seconds', $quentn_page_restrict_meta ) ) ? $quentn_page_restrict_meta['seconds'] : 0;

        //convert hours, minutes into seconds
        $quentn_page_expirty_inseconds = $hours * 3600 + $minutes * 60 + $seconds;

        //add relative expirty time set e.g page is valid for 1 hour , take user creation time, then subtract current time to get time left for page expiry
        return  $created_at + $quentn_page_expirty_inseconds - time();
    }
    /**
     * Sets a cookie
     *
     * @param string $name
     * @param string $data
     * @param timestamp $until (default: 6 months from now)
     * @param string $domain (default: current base domain)
     */
    public function set_cookie($name, $data, $until = null, $domain = null) {

        if (!$until) {
            $until = mktime(date("H"), date("i"), date("s"), date("n") + 6);
        }
        if (!$domain) {
            $host = explode(".", $_SERVER["HTTP_HOST"]);
            $domain = $host[count($host) - 2] . "." . $host[count($host) - 1];
        }
        setcookie($name, $data, $until, "/", "." . $domain);

    }

    /**
     * Same as set_cookie() but works with arrays
     *
     * @param string $name
     * @param array $data
     * @param timestamp $until (default: 6 months from now)
     * @param string $domain (default: current base domain)
     */
    public function set_json_cookie($name, $data, $until = null, $domain = null) {
        $data = base64_encode(json_encode($data));

        //set the cookie so we can access it within this session
        $_COOKIE[$name] = $data;
        $this->set_cookie($name, $data, $until, $domain);
    }

    /**
     * Returns a json decoded cookie
     *
     * @param string $name
     * @return array
     */
    public function get_json_cookie($name) {
        if ( isset( $_COOKIE[$name] ) ) {
            return json_decode(base64_decode($_COOKIE[$name]), true);
        }
        return array();
    }
}

Quentn_Wp_Restrict_Access::get_instance();
