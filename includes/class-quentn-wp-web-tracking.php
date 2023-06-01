<?php
use QuentnWP\Admin\Utility\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Web_Tracking
{
    /**
     * Quentn API Handler
     *
     * @since  1.1.1
     * @access   private
     * @var Quentn_Wp_Api_Handler
     */

    private $api_handler;

    /**
     * Constructor method.
     */
    public function __construct() {
        //add web tracking settings
        add_action( 'admin_init', array( $this, 'register_web_tracking_settings' ) );
        $this->api_handler = Quentn_Wp_Api_Handler::get_instance();
    }

    /**
     * Register settings
     *
     * @access public
     * @return void
     */
    public function register_web_tracking_settings() {

        //set values for register_setting
        $settings = array(
            array(
                'option_group' => 'web_tracking_options_group',
                'option_name'  => 'quentn_web_tracking_enabled'
            ),
            array(
                'option_group' => 'web_tracking_options_group',
                'option_name'  => 'quentn_web_tracking_consent_method'
            )
        );

        //add values for settings api, add_settings_section
        $section = array(
                'id'        => 'quentn_web_tracking_option',
                'title'     =>  __( 'Web Tracking Settings', 'quentn-wp'),
                'callback'  => '__return_false',
                'page'      => 'quentn-dashboard-web-tracking'
        );

        // register setting
        foreach ( $settings as $setting ) {
            register_setting( $setting["option_group"], $setting["option_name"], ( isset( $setting["callback"] ) ? $setting["callback"] : '' ) );
        }

        // add settings section
        add_settings_section( $section["id"], $section["title"], ( isset( $section["callback"] ) ? $section["callback"] : '' ), $section["page"] );

    }

    /**
     * Register fields
     *
     * @access public
     * @return void
     */
    public function register_web_tracking_fields() {
        $fields = array();
        $fields[] = array(
            'id'        => 'quentn_web_tracking_enabled',
            'title'     =>  __( 'Web Tracking', 'quentn-wp' ),
            'callback'  => array( $this, 'field_quentn_web_tracking' ),
            'page'      => 'quentn-dashboard-web-tracking',
            'section'   => 'quentn_web_tracking_option',
        );

        if( $this->is_confirmation_required_for_web_tracking() ) {
            $fields[] = array(
                'id'        => 'quentn_web_tracking_consent_method',
                'title'     => __('Consent Method', 'quentn-wp'),
                'callback'  => array( $this, 'field_quentn_consent_method' ),
                'page'      => 'quentn-dashboard-web-tracking',
                'section'   => 'quentn_web_tracking_option',
            );
        }

        // add settings field
        foreach ( $fields as $field ) {
            add_settings_field( $field["id"], $field["title"], ( isset( $field["callback"] ) ? $field["callback"] : '' ), $field["page"], $field["section"], ( isset( $field["args"] ) ? $field["args"] : '' ) );
        }
    }

    /**
     * Display web tracking enabled field
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function field_quentn_web_tracking()
    {
        $value = esc_attr( get_option( 'quentn_web_tracking_enabled' ) );
        ?>
        <input type="checkbox" class="form-control" value="1" name="quentn_web_tracking_enabled" id="quentn_web_tracking_enabled" <?php checked( $value); disabled( ! $this->api_handler->is_connected_with_quentn() || ! $this->api_handler->is_web_tracking_enabled() || ! $this->is_domain_registered( $_SERVER['HTTP_HOST'], $this->api_handler->get_registered_domains() )  ); ?>>
        <?php
    }

    /**
     * Display web tracking consent methods dropdown
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function field_quentn_consent_method()
    {
        $value = esc_attr( get_option( 'quentn_web_tracking_consent_method' ) );
        ?>
        <select name="quentn_web_tracking_consent_method" id="quentn_web_tracking_consent_method" <?php disabled(  ! get_option( 'quentn_web_tracking_enabled'  ) || ! $this->api_handler->is_connected_with_quentn() || ! $this->api_handler->is_web_tracking_enabled() || ! $this->is_domain_registered( $_SERVER['HTTP_HOST'], $this->api_handler->get_registered_domains() ) ); ?>>
            <option value="confirm-by-server" <?php  selected( $value, 'confirm-by-server' ); ?>><?php _e( 'Confirm By Server', 'quentn-wp' ) ?></option>
            <option value="cookie-notice" <?php  selected( $value, 'cookie-notice' ); disabled( ! Helper::is_cookie_notice_plugin_enabled() ) ?>>Cookie Notice</option>
            <option value="quentn-overlay" <?php  selected( $value, 'quentn-overlay' ); ?>><?php _e('Quentn Overlay', 'quentn-wp' ) ?></option>
        </select>
        <?php
    }

    /**
     * Check if web tracking is enabled at Quentn host
     *
     * @since  1.0.0
     * @access public
     * @return bool
     */
    public function is_confirmation_required_for_web_tracking()
    {
        $return = false;
        //get web tracking response
        $response = $this->api_handler->get_web_tracking_response();

        if( ! isset( $response['data']['domains'] ) ) {
            return $return;
        }
        //get domain ID among all registered domain at quentn host
        $domain_id = $this->get_domain_id( $_SERVER['HTTP_HOST'], $response['data']['domains'] );
        //get array index
        if ( ! $domain_id ) {
            return $return;
        }

        $domain_key = array_search( $domain_id, array_column( $response['data']['domains'], 'id' ) );

        $domain_data = $response['data']['domains'][$domain_key];

        if( isset( $domain_data['confirmation_required'] ) && $domain_data['confirmation_required'] ) {
            $return = true;
        }

        return $return;
    }

    /**
     * Get web tracking code
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    public function get_quentn_web_tracking_code() {
        //get web tracking response
        $response = $this->api_handler->get_web_tracking_response();
        //if domains are not found then return empty string
        if( ! isset( $response['data']['domains'] ) ) {
            return '';
        }
        //get domain ID
        $domain_id = $this->get_domain_id( $_SERVER['HTTP_HOST'], $response['data']['domains'] );

        if ( ! $domain_id ) {
            return '';
        }
        //get array key from domain ID
        if ( $domain_id ) {
            $domain_key = array_search($domain_id, array_column($response['data']['domains'], 'id'));
        }

        $tracking_host_url = isset( $response['data']['trackingHostUrl'] ) ? $response['data']['trackingHostUrl'] : '';
        $system_host_url = isset( $response['data']['systemHostUrl'] ) ? $response['data']['systemHostUrl'] : '';
        $js_source = isset( $response['data']['js_source'] ) ? $response['data']['js_source'] : '';
        $domain_data = isset( $response['data']['domains'][$domain_key] ) ? $response['data']['domains'][$domain_key] : '';

        $set_values_data = array(
            'idSite'             =>     $domain_data['piwik_site_id'],
            'trackingHostUrl'    =>     "'$tracking_host_url'",
            'trackAnonymusUser'  =>     $domain_data['track_anonymous'],
            'creq'               =>     $domain_data['confirmation_required'],
            'systemHostUrl'      =>     "'$system_host_url'",
        );

        //set tracking values in required format for quentn
        $set_value_string = $this->set_tracking_values( $set_values_data );

        $web_tracking = "<!-- Quentn tracking code -->
            <script>
                var _qntn = _qntn || [];
                {$set_value_string}
                (function (d, s, id, q){
                    if (d.readyState === 'complete') {
                        q.push(['domReady']);
                    } else {
                        d.addEventListener('DOMContentLoaded', function () {
                        q.push(['domReady']);
                    });
                   }
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id))
                        return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = '{$js_source}';
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'quentn-tracking-jssdk', _qntn));
                </script><!-- /Quentn tracking code -->";

        if( $domain_data['confirmation_required'] && get_option( 'quentn_web_tracking_consent_method' ) == 'quentn-overlay' && isset( $domain_data['confirmation_overlay'] ) ) {
            $web_tracking.= $domain_data['confirmation_overlay'];
        }elseif($domain_data['confirmation_required'] && get_option('quentn_web_tracking_consent_method')=='cookie-notice' && Helper::is_cookie_notice_plugin_enabled()) {
            $web_tracking.= "<!-- Quentn bridge to Cookie Notice -->
                    <script>
                    ( function ( $ ) {
                        $( document ).ready( function () {
                                _qntn.push(['getConfirmation', function(status) { 
                                    if (status && !$.fn.getCookieNotice()) {
                                        $.fn.setCookieNotice('accept');	
                                    }
                                }]);
                            } );
                        $( document ).on( 'setCookieNotice', function ( e) {
                            _qntn.push(['setConfirmation', true]);
                        } );
                    } )( jQuery );
                    </script>
                        <!-- /Quentn bridge to Cookie Notice -->";
        }
        return $web_tracking;
    }

    /**
     * Convert tracking values into string format to print in web analytics code
     *
     * @since  1.0.0
     * @access private
     * @param array $data
     * @return string
     */
    private function set_tracking_values( $data ) {
        $return = '';
        foreach ( $data as $key=>$value ) {
            if( $value ) {
                //convert 1 to true for javascript text
                $val = ( $value==1 ) ? 'true' : $value;
                $return.= "_qntn.push(['setValue','{$key}', {$val}]);\n";
            }
        }
        return $return;
    }

    /**
     * Searches the array for a given domain and returns the ID of domain if found
     *
     * @since  1.0.0
     * @access public
     * @param string $needle_domain the searched domain
     * @param array $haystack_domains
     * @return int|bool
     */
    public function get_domain_id( $needle_domain, $haystack_domains ) {

        foreach( $haystack_domains as $domain ) {
            if ( $this->compare_domain( $needle_domain, $domain['domain'] ) ) {
                return  $domain['id'];
            }
        }
        return false;
    }

    /**
     * Searches the array for a given value
     *
     * @since  1.0.0
     * @access public
     * @param string $needle_domain the searched domain
     * @param array $haystack_domains
     * @return bool
     */
    public function is_domain_registered( $needle_domain, $haystack_domains )
    {
        foreach ( $haystack_domains as $domain ) {
            if ( $this->compare_domain( $needle_domain, $domain ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Compare the two domain
     *
     * @since  1.0.0
     * @access public
     * @param string $domain_1
     * @param string $domain_2
     * @param int $level the level upto which the domain will be compared
     * @return bool
     */
    public function compare_domain( $domain_1, $domain_2, $level = 0 ) {

        //add http:// if needed
        $domain_1 = ( parse_url( $domain_1, PHP_URL_SCHEME ) ) ? $domain_1 : 'http://'.$domain_1;
        $domain_2 = ( parse_url( $domain_2, PHP_URL_SCHEME ) ) ? $domain_2 : 'http://'.$domain_2;

        //skip domain upto specific level
        $domain_1_host = $this->skip_subdomain_upto_specific_level( parse_url( $domain_1, PHP_URL_HOST ), $level );
        $domain_2_host = $this->skip_subdomain_upto_specific_level( parse_url( $domain_2, PHP_URL_HOST ), $level );

        if ( $domain_1_host == $domain_2_host ) {
            return true;
        }
        return false;
    }

    /**
     * Skip domain parts to specifc level
     *
     * @since  1.0.0
     * @access public
     * @param string $host url to skip
     * @param int $level the level to skip the parts
     * @return string
     */
    public function skip_subdomain_upto_specific_level( $host, $level = 0 ) {

        $host_data = explode( ".", $host );

        //if host contains www , ignore it by adding one more level to skip
        if( $host_data[0] === 'www' ) {
            $level += 1;
        }
        //check if host contains subdomains
        if ( count( $host_data  ) > 2 ) {
            //skip sudomains upto given level
            for ( $x = 0; $x < $level; $x++ ) {
                unset( $host_data[$x] );
                //keep alteast last two part of host e.g example.com
                if( count( $host_data ) == 2 ) {
                    break;
                }
            }
        }
        return implode( ".", $host_data );
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

Quentn_Wp_Web_Tracking::get_instance();