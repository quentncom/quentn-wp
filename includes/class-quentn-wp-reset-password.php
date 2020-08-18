<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Reset_Password
{
    /**
     * Constructor method.
     */
    public function __construct() {
        add_action( 'login_init', array( $this, 'quentn_reset_password' ) );
        add_action( 'after_password_reset', array( $this, 'quentn_after_reset_password' ), 10 );
    }

    /**
     * Reset Password
     *
     * @since  1.1.0
     * @access public
     * @return void
     */
    public function quentn_reset_password() {
        if ( $_GET['qntn_pwd'] ) {
            $qntn_pwd = sanitize_text_field ( $_GET['qntn_pwd'] );
            $data = json_decode( base64_decode( $qntn_pwd ), true );
            $api_key = get_option( 'quentn_app_key' );

            if ( ! isset( $data['hash'] ) || ! isset( $data['email'] ) || ! isset( $data['vu'] ) || ! $api_key  ) {
                wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=invalidkey' ) );
                exit;
            }
            $hash =  hash( 'sha256', $data['vu'].$data['email'].$api_key.$data['force_login'] );
            //Validate hash
            if ( $hash !== $data["hash"] ) {
                wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=invalidkey' ) );
                exit;
            }
            //check expiry time
            if( $data['vu'] <= time() ) {
                wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=expiredkey' ) );
                exit;
            }
            $qntn_user_email = sanitize_email( $data['email'] );
            //get user object
            $user = get_user_by( 'email', $qntn_user_email ) ;
            if ( ! $user instanceof WP_User ) {
                add_filter( 'authenticate', function( $user ) use ( $qntn_user_email ) {
                    $user = new WP_Error( 'denied', sprintf( __( '<strong>ERROR</strong>: Link invalid. Email \'%s\' does not exist.' ), $qntn_user_email ) );
                    return $user;
                }, 10 );
            } else  {
                //check if key already been used
                if ( in_array($data['vu'], get_user_meta( $user->ID, 'quentn_reset_pwd_vu' ) ) ) {
                    wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=invalidkey' ) );
                    exit;
                }
                //if force_login is true or user is ever logged in before, login automatically
                if ( ! empty( $data['force_login'] ) || get_user_option( 'quentn_last_login', $user->ID ) ) {
                    wp_clear_auth_cookie();
                    wp_set_current_user( $user->ID );
                    wp_set_auth_cookie( $user->ID );
                    add_user_meta( $user->ID, 'quentn_reset_pwd_vu', $data['vu'] );
                    update_user_meta( $user->ID, 'quentn_last_login', time() );
                    update_user_caches( $user );
                    $redirect_to = $this->get_redirect_url( $user );
                    wp_safe_redirect( $redirect_to );
                    exit();
                } else { //ask user to reset password
                    $key = get_password_reset_key( $user );
                    $user_login = rawurlencode( $user->user_login );
                    $qntn_rp_cookie = 'qntn-wp-resetpass-'.$key;
                    $value = $data['vu'];
                    $site_url = wp_parse_url( get_home_url() );
                    $domain = ( isset( $site_url['host'] ) ) ? $site_url['host'] : '';
                    //set cookie to get vu value for key used to reset password
                    setcookie( $qntn_rp_cookie, $value, 0, '/', $domain, is_ssl(), true );
                    $rp_link = network_site_url( "wp-login.php?action=rp&key=$key&login=" . $user_login, 'login' );
                    wp_safe_redirect( $rp_link );
                    exit;
                }
            }
        }
    }

    /**
     * Get default wp redirect url
     *
     * @since  1.1.0
     * @access private
     * @param WP_User $user
     * @return string
     */
    private function get_redirect_url( $user ) {
        //if redirect url set by admin
        $quentn_auto_login_url = get_option( 'quentn_auto_login_url' );
        if ( $quentn_auto_login_url ) {
            return $quentn_auto_login_url;
        }

        //if redirect url not set by admin, then take default redirect url for the user
        if ( is_multisite() && !get_active_blog_for_user( $user->ID ) && !is_super_admin( $user->ID ) )
            $redirect_to = user_admin_url();
        elseif ( is_multisite() && !$user->has_cap('read') )
            $redirect_to = get_dashboard_url( $user->ID );
        elseif ( !$user->has_cap('edit_posts') )
            $redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();
        else
            $redirect_to = admin_url();

        return $redirect_to;
    }

    /**
     * Get default wp redirect url
     *
     * @since  1.1.0
     * @access public
     * @param WP_User $user
     * @return string
     */
    public function quentn_after_reset_password( $user ) {
        //Consider reset password as logged in
        update_user_meta( $user->ID, 'quentn_last_login', time() );
        $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
        //get cookie value
        if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
            list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
            if ( isset( $_COOKIE['qntn-wp-resetpass-'.$rp_key] ) ) {
                add_user_meta( $user->ID, 'quentn_reset_pwd_vu', $_COOKIE['qntn-wp-resetpass-'.$rp_key]);
            }
        }
    }

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new self;
        }

        return $instance;
    }
}

Quentn_Wp_Reset_Password::get_instance();