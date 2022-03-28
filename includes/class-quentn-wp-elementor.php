<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Elementor
{

    /**
     * Constructor method.
     */
    public function __construct() {
        //As of Elementor 3.5, registering new controls hook and method is changed
        if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
           $action_name = 'register';
        } else {
            $action_name = 'controls_registered';
        }
        add_action( 'elementor/controls/'.$action_name, array( $this, 'init_controls' ) );
        add_action( 'elementor_pro/init', array( $this, 'init_elementor' ) );
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

    /**
     * Init Controls
     *
     * @since  1.0.6
     * @access public
     */
    public function init_controls()
    {
        //As of Elementor 3.5, registering new controls hook and method is changed
        if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
            \Elementor\Plugin::$instance->controls_manager->register( new \Quentn_Wp_Elementor_Field_Mapping() );
        } else {
            \Elementor\Plugin::$instance->controls_manager->register_control( 'qntn_fields_map', new \Quentn_Wp_Elementor_Field_Mapping() );
        }
    }

    /**
     * Init Controls
     *
     * @since  1.0.6
     * @access public
     */
    public function init_elementor()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-elementor-integration.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-elementor-handler.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-elementor-field-mapping.php';

        $quentn_action = new \Quentn_Wp_Elementor_Integration;
        // Register the action with form widget
        \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action( $quentn_action->get_name(), $quentn_action );
    }
}

Quentn_Wp_Elementor::get_instance();