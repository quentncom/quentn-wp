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
        add_action('wp_head', array( $this, 'set_countdown_data' ) );
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

       //get list of all emails, from url and cookies, of current visitor
       $get_access_emails = $this->get_access_emails();

       if( ! empty($get_access_emails ) ) {

           //if email address is authroized to visit the page, get its creation date, in case of multiple email addresses, latest creation date will be considered
           if( $created_at = $this->get_user_access( $get_access_emails ) ) {

               //if restriction type is countdown, then check if it is still valid
               if( ! $page_meta['countdown'] || $this->calculate_expire_time( $created_at ) > 0  ) { //if restriction type is countdown, then check if it is still valid
                       $is_display_content = true;
               }

               //set cookie if it is new email address
               $get_access_email = $this->get_new_access();
               if( $get_access_email != '') {
                   $this->set_cookie_data( $get_access_email );
               }
           }
       }

       //return content if user is authorized
       if( $is_display_content ) {
           return $content;
       }

       //page user is not allowed, then redirect/display message
        if( $page_meta['redirection_type'] == 'restricted_url' ) {
            {
                //todo avoid rest api call, need to find a better way
                if ( strpos( Helper::get_current_url(), 'wp-json' ) === false ) {
                    wp_redirect( $page_meta['redirect_url'] );
                }
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

            $get_access_emails = ( isset( $cookie_saved_data['access'][get_the_ID()] ) ) ?  $cookie_saved_data['access'][get_the_ID()] : [];
        }
        return $get_access_emails;
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
     * Get email addresses to check access
     *
     * @since  1.0.0
     * @access public
     * @return array
     */
    public function set_cookie_data( $access_email ) {

        //get already saved cookie value
        $cookie_saved_data = $this->get_json_cookie('qntn_wp_access');
        //check if cookie access is already set
        if ( isset( $cookie_saved_data['access'] ) && ! empty($cookie_saved_data['access'] ) ) {

            //check if current page has already some users access
            if ( isset( $cookie_saved_data['access'][get_the_ID()] ) && ! empty( $cookie_saved_data['access'][get_the_ID()] ) ) {

                //if there is existing access, then we will keep it, todo change it with array_merge
                $set_cookie_data['access'] = $cookie_saved_data['access'];

                //if current email address is not in access list, add it
                if ( ! in_array( $_GET["qntn_wp"], array_values( $cookie_saved_data['access'][get_the_ID()] ) ) ) {
                    $set_cookie_data['access'][get_the_ID()][] = $access_email;
                }
            } else { //in case access is there but no access for current page
                $set_cookie_data['access'] = $cookie_saved_data['access'];
                $set_cookie_data['access'][get_the_ID()] = [$access_email];
            }
        } else { //in case there is no access at all, set it
            $set_cookie_data['access'][get_the_ID()] = [$access_email];
        }
        $this->set_json_cookie('qntn_wp_access', $set_cookie_data);
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
    public function set_countdown_data() {

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
        $get_locale =  substr( get_locale(),0, 2 );   // take first two elements from get_locale() i.e de, en
        echo  "<script>var qncountdown = {
            seconds: $quentn_expiry_page_inseconds,        
            clockFace: '".$clock_face."',
            wpLang: '".$get_locale."',
            isRedirect: $is_redirect_url_set,
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
     * @access protected
     * @return mixed
     */
    protected function calculate_expire_time( $created_at = false ){

        $return = -1;
        if( ! $created_at ) {
            $get_access_emails = $this->get_access_emails();
            $created_at = $this->get_user_access($get_access_emails);
        }

        $quentn_page_restrict_meta = $this->get_quentn_post_restrict_meta();
        if( $created_at && $quentn_page_restrict_meta['countdown'] ) {
            //get page restriction type, i.e relative or absolute
            $quentn_page_restrict_type = (array_key_exists('countdown_type', $quentn_page_restrict_meta))?$quentn_page_restrict_meta['countdown_type']:'';
            //if page restriction type is absolute
            if($quentn_page_restrict_type == 'absolute' ) {
                //get absolute expiry date
                $quentn_page_restrict_absolute_date = (array_key_exists('absolute_date', $quentn_page_restrict_meta))?$quentn_page_restrict_meta['absolute_date']:'';
                if( $quentn_page_restrict_absolute_date != '' ) {

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

                    $timestamp = strtotime( $quentn_page_restrict_absolute_date );
                    $current_time = time() + $utc_difference;
                    $return = $timestamp - $current_time;
                }
            } elseif( $quentn_page_restrict_type == 'relative' ) {

                //if page restriction type is relative, then we will add relative time set depends on access creation date
                $hours =   ( array_key_exists( 'hours', $quentn_page_restrict_meta ) ) ? $quentn_page_restrict_meta['hours'] : 0;
                $minutes = ( array_key_exists( 'minutes', $quentn_page_restrict_meta ) ) ? $quentn_page_restrict_meta['minutes'] : 0;
                $seconds = ( array_key_exists( 'seconds', $quentn_page_restrict_meta ) ) ? $quentn_page_restrict_meta['seconds'] : 0;

                //convert hours, minutes into seconds
                $quentn_page_expirty_inseconds = $hours * 3600 + $minutes * 60 + $seconds;

                //add relative expirty time set e.g page is valid for 1 hour , take user creation time, then subtract current time to get time left for page expiry
                $return = $created_at + $quentn_page_expirty_inseconds - time();
            }
        }
        return $return;
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
