<?php

class Quentn_Wp_Restrict_Access
{
    private $replacement_values = array();
    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        add_shortcode( 'quentn_flipclock', array($this, 'get_flipclock_shortcode' ) );
        add_filter( 'the_content', array( $this, 'quentn_content_permission_check' ), PHP_INT_MAX );
        //restrict content in case page is build using thrive template builder
        add_filter( 'tve_landing_page_content', array( $this, 'quentn_content_permission_check' ), PHP_INT_MAX );

        add_action( 'wp_head', array( $this, 'set_countdown_clock' ) );
    }

    /**
     * Get clock shortcode
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
     public function get_flipclock_shortcode() {
         if( current_user_can( 'edit_pages' ) ) {
             return '';
         }
         return "<div class='quentn-flipclock quentn-shortcode'></div>";
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

         $m = new Mustache_Engine;
         $this->set_replacement_values();
         $content = $m->render($content, $this->get_replacement_values());

        //if user can edit posts permission or page restriction is not avtive, return content
        if( current_user_can( 'edit_pages' ) || ! $page_meta = $this->get_quentn_post_restrict_meta() ) {
            return $content;
        }
       define( 'DONOTCACHEPAGE', 1 ); // Do not cache this page
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
     * Modify title of site
     *
     * @since  1.0.0
     * @access public
     * @param  string $title
     * @return string
     */
    public function quentn_change_page_title( $title ) {
        $m = new Mustache_Engine;
        $this->set_replacement_values();
        return $m->render($title, $this->get_replacement_values());

    }

    /**
     * Set hash value for mustache
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    //todo call this function only once for both filters 'the_content' and 'the_title'
    public function set_replacement_values() {

        //get replacement values from url
        $get_url_values = $_GET;
        unset( $get_url_values['qntn'] );
        if( ! empty( $get_url_values ) ) {
            $this->add_replacement_values( $get_url_values );
        }

        //-wp user if logged in then we will get replacement values from wp user object
        $current_user = wp_get_current_user();
        if( $current_user->ID ) {
            $set_replace_values = array();
            if( $current_user->first_name != '' ) {
                $set_replace_values['first_name'] = $current_user->first_name;
            }
            if( $current_user->last_name != '' ) {
                $set_replace_values['last_name'] = $current_user->last_name;
            }
            if( $current_user->user_email != '' ) {
                $set_replace_values['email'] = $current_user->user_email;
            }

            if( ! empty( $set_replace_values ) ) {
                $this->add_replacement_values( $set_replace_values );
            }

        }

        //if replacement values send by quentn
        if( isset( $_GET['qntn'] ) ) {
            //decode quentn values
            $qntn = sanitize_text_field( $_GET['qntn'] );
            $qntn_values = json_decode( base64_decode( $qntn ), true  );

            //add quentn values in replacement values
            if( ! empty( $qntn_values ) ) {
                $this->add_replacement_values( $qntn_values );
            }

            //if quentn values have additional data along with email address, then add it into cookies and save it in database
            if( isset( $qntn_values['email'] ) && is_email( sanitize_email( $qntn_values['email'] ) ) && count($qntn_values) > 1 ) {
                $email = sanitize_email( $qntn_values['email'] );
                unset( $qntn_values['email'] );
                //set cookie
                $this->set_quentn_user_data_cookie( $email, $qntn_values );
                global $wpdb;
                //add it in database
                $wpdb->replace( $wpdb->prefix . TABLE_QUENTN_USER_DATA,['email' => $email, 'fields' => serialize( $qntn_values )], ['%s', '%s'] );
            }
        } else { //if no qntn values in url
            $valid_email_in_url = '';
            $quentn_cookie = $this->get_json_cookie( 'qntn_wp_access' );
            $user_data = array();
            //try to find if there is any valid email address in the url
            foreach ( $_GET as $value ) {
                if ( is_email( str_replace(" ","+", sanitize_email( $value ) ) ) ) {
                    $valid_email_in_url = sanitize_email( $value );
                    break;
                }
            }
            //if there is valid email address in the url
            if( $valid_email_in_url != '' ) {
                $url_email_hash = hash( 'sha256', $valid_email_in_url );
                //then try to find this email data in the cookie
                if ( isset( $quentn_cookie['qntn_user_data'] ) && array_key_exists( $url_email_hash, $quentn_cookie['qntn_user_data'] ) ) {
                    $user_data = $quentn_cookie['qntn_user_data'][$url_email_hash];
                    //decode cookie data
                    $user_data = array_map( array( $this, 'decode_cookie_values' ), $user_data);
                }else { //if there is valid email in url and no data found in cookie for that email address, try to find it in database
                    global $wpdb;
                    $table_qntn_user_data = $wpdb->prefix. TABLE_QUENTN_USER_DATA;
                    $user_data = $wpdb->get_results( "SELECT fields FROM ".$table_qntn_user_data. " WHERE email ='".$valid_email_in_url."'" );
                    if( ! empty( $user_data ) ) {
                        $user_data =  unserialize( $user_data[0]->fields );
                    }
                }
            } elseif ( isset( $quentn_cookie['qntn_user_data'] ) ) { //if not valid email address in the url then get latest data from cookie saved
                $user_data = end($quentn_cookie['qntn_user_data']);
                $user_data = array_map( array( $this, 'decode_cookie_values' ), $user_data);
            }
            //if we have user data then add it in replacement values
            if(! empty( $user_data ) ) {
                $this->add_replacement_values( $user_data );
            }
        }

    }

    /**
     * Set replacement value within the page content
     *
     * @since  1.0.0
     * @access public
     * @param  array $user_data
     * @return array
     */
    public function add_replacement_values( array $user_data ) {
        foreach ( $user_data as $key => $value ) {
            $sanitize_key = sanitize_key( $key );
            if ( ! array_key_exists( $sanitize_key, $this->replacement_values ) ) {
                $this->replacement_values[$sanitize_key] = sanitize_text_field( $value );
            }
        }
    }

    /**
     * Get replacement value
     *
     * @since  1.0.0
     * @access public
     * @return array
     */
    public function get_replacement_values() {
        return $this->replacement_values;
    }

    /**
     * Encode value to save in cookie
     *
     * @since  1.0.0
     * @access public
     * @param string $val value to be encoded
     * @return string
     */
    public function encode_cookie_values( $val ) {
         return base64_encode( $val );
    }

    /**
     * Decode value get from cookie
     *
     * @since  1.0.0
     * @access public
     * @param string $val value to be decoded
     * @return string
     */
    public function decode_cookie_values( $val ) {
         return base64_decode( $val );
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
     * Get new access if it is in url
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    public function get_new_access() {

        $get_new_access = '';

        if( isset( $_GET["qntn_wp"] ) ) { //get email address if it is in query string
            $get_new_access =   sanitize_text_field( $_GET["qntn_wp"] );
        } elseif ( isset($_GET["qntn"] ) ) { //qntn is used when request is created from quentn, it is expected in base64 and json encoded
            $qntn = json_decode( base64_decode( sanitize_text_field( $_GET["qntn"] ) ), true  );
            if( isset( $qntn['email'] ) && is_email( sanitize_email( $qntn['email'] ) ) ) { //if there is valid email in data send by quentn
                $get_new_access =   hash( 'sha256', sanitize_email( $qntn['email'] ) );
            }
        } elseif ( isset( $_GET["email"] ) ) { //if there is plain email address in url
            $email =  str_replace(" ","+", sanitize_email( $_GET["email"]  ) );
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
     * Save user data in the cookie, used for placeholders
     *
     * @since  1.0.0
     * @access public
     * @param  string $access_email email address of user
     * @param  array $data data of user e.g first_name, last_name
     * @return string
     */
    public function set_quentn_user_data_cookie( $access_email, $data ) {

        //get the existing quentn cookie
        $cookie_saved_data = $this->get_json_cookie('qntn_wp_access');
        $data = array_map( array( $this, 'encode_cookie_values' ), $data);
        $email_hash_key = hash( 'sha256', $access_email );

        //if this page access is not created
        if ( ! isset( $cookie_saved_data['qntn_user_data'][$email_hash_key] ) ) {

            //if no access is created then add access key in array
            if( ! isset( $cookie_saved_data['qntn_user_data'])) {
                $cookie_saved_data['qntn_user_data'] = array();
            }

            //add page id to access
            $add_page_id = array(
                $email_hash_key => $data,
            );
            $cookie_saved_data['qntn_user_data'] = $cookie_saved_data['qntn_user_data'] + $add_page_id;

        } else {
            $cookie_saved_data['qntn_user_data'][$email_hash_key] = $data;
        }

        $this->set_json_cookie('qntn_wp_access', $cookie_saved_data);
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
        $sql = "SELECT created_at FROM ".$wpdb->prefix . TABLE_QUENTN_RESTRICTIONS. " where page_id='".  $page_id."' and email_hash in ('".$emails."') order by created_at DESC LIMIT 1";
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
        //if user can edit posts permission or page restriction is not avtive, no need to display countdown clock
        if( current_user_can( 'edit_pages' ) || ! $quentn_post_restrict_meta = $this->get_quentn_post_restrict_meta() ) {
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
                //check if countdow stay on top is true
                if( $quentn_post_restrict_meta['quentn_countdown_stick_on_top'] ) {
                    echo "<script>jQuery(document).ready(function () { jQuery('body').prepend('<div class=\'quentn-countdown-wrapper countdown-fixed\'><div class=\"quentn-flipclock\"></div></div><div class=\"quentn-flipclock-spacer\"></div>')});</script>";
                } else {
                    echo "<script>jQuery(document).ready(function () { jQuery('body').prepend('<div class=\'quentn-countdown-wrapper\'><div class=\"quentn-flipclock\"></div></div>')});</script>";
                }
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
     * @param Datetime $expiry_date expiry date of page access
     * @return int
     */
    public function calculate_absolute_page_expire_time ( $expiry_date ) {
        $timestamp = strtotime( $expiry_date );
        return $timestamp - strtotime( current_time( "mysql", false ) );
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
    public function set_cookie( $name, $data, $until = null, $domain = null ) {

        if (!$until) {
            $until = mktime(date("H"), date("i"), date("s"), date("n") + 6);
        }
        if (!$domain) {
            $host = explode(".", $_SERVER["HTTP_HOST"]);
            $domain = $host[count($host) - 2] . "." . $host[count($host) - 1];
        }
        setcookie( $name, $data, $until, "/", "." . $domain );

    }

    /**
     * Same as set_cookie() but works with arrays
     *
     * @param string $name
     * @param array $data
     * @param timestamp $until (default: 6 months from now)
     * @param string $domain (default: current base domain)
     */
    public function set_json_cookie( $name, $data, $until = null, $domain = null ) {
        $data = base64_encode( json_encode( $data ) );
        $cookie_name = sanitize_text_field( $name );

        //set the cookie so we can access it within this session
        $_COOKIE[$cookie_name] = $data;
        $this->set_cookie($cookie_name, $data, $until, $domain);
    }

    /**
     * Returns a json decoded cookie
     *
     * @param string $name
     * @return array
     */
    public function get_json_cookie($name) {
        if ( isset( $_COOKIE[$name] ) ) {
            $cookie_value = sanitize_text_field( $_COOKIE[$name] );
            return json_decode( base64_decode( $cookie_value ), true );
        }
        return array();
    }
}

Quentn_Wp_Restrict_Access::get_instance();
