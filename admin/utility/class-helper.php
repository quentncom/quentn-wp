<?php 

class Helper
{

    //key for openssl_encrypt
    const PLUGIN_HASH_KEY = 34321213234;

    //A non-NULL Initialization Vector for openssl_encrypt
    const PLUGIN_HASH_IV = 343212132;

    //Check if cookie notice plugin is active
    public static function is_cookie_notice_plugin_enabled() {
        $return = false;
        // https://github.com/wp-plugins/cookie-notice
        if ( function_exists('cn_cookies_accepted' ) ) {
            $return = true;
        }

        //it is another way to check if plugin is active
        /*
        if( is_plugin_active( 'cookie-notice/cookie-notice.php' ) ) {
            $return = true;
        }*/
        return $return;
    }


    //Check if cookie is accepted
    public static function is_cookie_concerned() {
        $return = false;
        if ( self::is_cookie_notice_plugin_enabled() && cn_cookies_accepted() ) {
            $return = true;
        }
        return $return;
    }


    /**
     * Gets the name of the plugin
     *
     * @return string
     */
    public static function get_plugin_name() {
        return plugin_basename( dirname( dirname( dirname( __FILE__ ) ) ) . '/quentn-wp.php' );
    }

    /**
     * Checks if cURL library is enabled
     *
     * @return bool
     */
    public static function is_curl_enabled() {
        return function_exists( 'curl_version' );
    }

    /**
     * simple method to encrypt or decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     *
     * @param string $action: can be 'encrypt' or 'decrypt'
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     */
    public static function encrypt_decrypt( $action, $string ) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = self::PLUGIN_HASH_KEY;
        $secret_iv = self::PLUGIN_HASH_IV;
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
            $output = base64_encode($output);
        } elseif( $action == 'decrypt' ) {
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }
        return $output;
    }

    //get current  browser url
    public static function  get_current_url() {
        if( is_ssl() ) {
            $http = 'https://';
        }else {
            $http = 'http://';
        }
        return $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Get the list of pages having quentn restricted status active.
     *
     * @return null|string
     */
    public static function get_restriction_activated_pages() {

        //get list of pages having meta_key _quentn_post_restrict_meta
        $pages= get_pages( array(
            'meta_key' => '_quentn_post_restrict_meta'
        ) );
        $restricted_pages = array();
        foreach( $pages as $id )
        {
            $restricted_pages[] = $id->ID;
        }
        return $restricted_pages;
    }

    /**
     * Returns if plugin is enabled.
     *
     * @return bool
     */
    public static function is_plugin_enabled() {
        if ( self::is_plugin_active_for_network() ) {
            return true;
        }

        return self::is_plugin_active_for_current_site();
    }


    /**
     * Returns if plugin is active for current site
     *
     * @return bool
     */
    public static function is_plugin_active_for_current_site() {
        $active_plugins = get_option( 'active_plugins' );
        return in_array( self::get_plugin_name(), apply_filters( 'active_plugins', $active_plugins ), true );
    }

    /**
     * Returns if plugin is active through network
     *
     * @return bool
     */
    public static function is_plugin_active_for_network() {
        if ( ! is_multisite() ) {
            return false;
        }

        $plugins = get_site_option( 'active_sitewide_plugins' );

        return isset( $plugins[ self::get_plugin_name() ] );
    }
}