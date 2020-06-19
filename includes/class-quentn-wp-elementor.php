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
        add_action( 'plugins_loaded', array( $this, 'load_dependencies' ) );
        add_action( 'elementor/controls/controls_registered', array( $this, 'init_controls' ) );
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
     * Load Elementor Integration dependencies
     *
     * @since  1.0.6
     * @access public
     */
    public function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-elementor-integration.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-elementor-handler.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-quentn-wp-elementor-field-mapping.php';
    }

    /**
     * Init Controls
     *
     * @since  1.0.6
     * @access public
     */
    public function init_controls()
    {
        \Elementor\Plugin::$instance->controls_manager->register_control('qntn_fields_map', new \Quentn_Wp_Elementor_Field_Mapping());
    }

    /**
     * Init Controls
     *
     * @since  1.0.6
     * @access public
     */
    public function init_elementor()
    {
        $quentn_action = new \Quentn_Wp_Elementor_Integration;
        // Register the action with form widget
        \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action( $quentn_action->get_name(), $quentn_action );
    }
}

Quentn_Wp_Elementor::get_instance();