<?php

/**
 *
 * This class defines all api call to quentn.
 *
 * @since      1.0.0
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 * @author     Quentn Team < info@quentn.com>
 */
class Quentn_Wp_Api_Handler
{

    /**
     * Quentn object
     *
     * @since  1.0.0
     * @access private
     * @var    object
     */
    private $quentn;

    /**
     * Quentn oauth object
     *
     * @since  1.0.0
     * @access private
     * @var    object
     */
    private $quentn_oauth;

    /**
     * If wp plugin is connected with quentn
     *
     * @since  1.0.0
     * @access private
     * @var    bool
     */
    private $is_connected_with_quentn;

    /**
     * Stores the web tracking response from quentn
     *
     * @since  1.0.0
     * @access private
     * @var    array
     */
    private $web_tracking_response;

    /**
     * Stores if web tracking is enabled in quentn server
     *
     * @since  1.0.0
     * @access private
     * @var    bool
     */
    private $is_web_tracking_enabled;

    /**
     * Stores all terms from quentn
     *
     * @since  1.0.0
     * @access private
     * @var    array
     */
    private $get_terms;

    /**
     * Stores error messages from quentn calls
     *
     * @since  1.0.0
     * @access private
     * @var    array
     */
    public $error_messages = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct()
    {

        $this->quentn = new \Quentn\Quentn([
            'api_key'  => get_option('quentn_app_key'),
            'base_url' => get_option('quentn_base_url'),
        ]);

        $this->quentn_oauth = new Quentn\Quentn([
            'base_url' => 'https://my.quentn.com/public/api/v1/',
        ]);

    }

    /**
     * Return an instance of this class.
     *
     * @since 1.0.0
     * @return object A single instance of this class.
     */

    public static function get_instance()
    {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new self;
        }

        return $instance;
    }

    /**
     * Gets Quentn Client
     *
     * @return object
     */

    public function get_quentn_client()
    {
        return $this->quentn;
    }

    /**
     * Gets Quentn Oauth Client
     *
     * @return object
     */

    public function get_oauth_client()
    {
        return $this->quentn_oauth;
    }



    /**
     * Check if plugin is connected with quentn account
     *
     * @return boolean
     */
    public function is_connected_with_quentn()
    {
        if ( null === $this->is_connected_with_quentn ) {
            $this->is_connected_with_quentn = false;
            if( get_option('quentn_app_key') && get_option('quentn_base_url') ) {
                try {
                    if ( $this->quentn->test() ) {
                        $this->is_connected_with_quentn = true;
                    } else {
                        $this->error_messages[] = __( 'Please connect a Quentn account to use this feature', 'quentn-wp' );
                    }
                } catch ( Exception $e ) {
                    $this->error_messages[] = $e->getMessage();
                }
            }
        }

        return $this->is_connected_with_quentn;
    }

    /**
     * Finds a web tracking response from quentn
     * @return array
     */

    public function get_web_tracking_response()
    {
        if ( null === $this->web_tracking_response && $this->is_connected_with_quentn() ) {
            try {
                $this->web_tracking_response = self::get_quentn_client()->call('tracking');

            } catch ( Exception $e ) {
                $this->web_tracking_response = array();
                $this->error_messages[] = $e->getMessage();
            }
        }
        return $this->web_tracking_response;
    }


    /**
     * Get list of all terms
     * @return array List of all terms
     */
    public function get_terms()
    {
        if ( null === $this->get_terms && $this->is_connected_with_quentn() ) {
            try {
                $get_response = self::get_quentn_client()->terms()->getTerms();
                $this->get_terms = $get_response['data'];
            } catch ( Exception $e ) {
                $this->get_terms = array();
            }
        }
        return $this->get_terms;
    }

    /**
     * Check if web tracking is enabled in quentn server
     *
     * @return boolean
     */
    public function is_web_tracking_enabled()
    {
        if ( null === $this->is_web_tracking_enabled ) {

            $response = $this->get_web_tracking_response();
            $this->is_web_tracking_enabled = false;
            if ( isset( $response['data']['tracking_enabled'] ) && $response['data']['tracking_enabled'] ) {
                $this->is_web_tracking_enabled = true;
            }
        }

        if( ! $this->is_web_tracking_enabled ) {
            $this->error_messages[] = __( 'Tracking is not enabled from your quentn host', 'quentn-wp' );
        }

        return $this->is_web_tracking_enabled;
    }

    /**
     * Get list of all domains registered in quentn server for web tracking
     *
     * @return array
     */
    public function get_registered_domains()
    {
        $return = array();
        $response = $this->get_web_tracking_response();
        if( isset( $response['data']['domains'] ) ) {
            $host_domains = $response['data']['domains'];
            $return = array_column( $host_domains, 'domain' );
        }
        return $return;
    }

}



