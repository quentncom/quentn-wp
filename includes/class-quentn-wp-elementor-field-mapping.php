<?php
use Elementor\Control_Repeater;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Fields_Map
 * @package ElementorPro\Modules\Forms\Controls
 *
 * each item needs the following properties:
 *   remote_id,
 *   remote_label
 *   remote_type
 *   remote_required
 *   local_id
 */
class Quentn_Wp_Elementor_Field_Mapping extends Control_Repeater {

	const CONTROL_TYPE = 'qntn_fields_map';

	public function get_type() {
		return self::CONTROL_TYPE;
	}

	protected function get_default_settings() {
		return array_merge( parent::get_default_settings(), [
            'render_type' => 'none',
            'fields' => [
				[
					'name' => 'local_id',
					'type' => Controls_Manager::HIDDEN,
				],
				[
					'name' => 'remote_id',
					'type' => Controls_Manager::SELECT,
				],
			],
		] );
	}

	public function enqueue()
    {
        $wp_version = get_bloginfo( 'version' );

        //The integration script extends views from Elementor's editor bundle and modules from
        //Elementor Pro's forms module, so it must never be printed before them
        $deps = array( 'jquery', 'elementor-editor' );
        if ( wp_script_is( 'elementor-pro', 'registered' ) || wp_script_is( 'elementor-pro', 'enqueued' ) ) {
            $deps[] = 'elementor-pro';
        }

        //Add different file for elementor integration for wordpress version equal or greater than 5.6
        if ( version_compare( $wp_version, '5.6', '>=' ) ) {
            wp_register_script( 'quentn.elementor.integration.js', QUENTN_WP_PLUGIN_URL . 'admin/js/quentn-elementor-integration-5.6.js', $deps, QUENTN_WP_VERSION, true );
            wp_enqueue_script( 'quentn.elementor.integration.js' );
        } else {
            wp_register_script( 'quentn.elementor.integration.js', QUENTN_WP_PLUGIN_URL . 'admin/js/quentn-elementor-integration.js', $deps, QUENTN_WP_VERSION, true );
            wp_enqueue_script( 'quentn.elementor.integration.js' );
        }

        $mappable_fields = $this->get_mappable_fields();
        $fields = [];

        foreach ( $mappable_fields as $field_id => $field_label ) {
            $fields[] = [
                'remote_id'         => $field_id,
                'remote_label'      => $field_label['label'],
                'remote_type'       => $field_label['type'],
                'remote_required'   => in_array( $field_id, ['mail'] ),
            ];
        }
        wp_localize_script( 'quentn.elementor.integration.js', 'QntnMappableFields', [
            'fields' => $fields
        ] );
    }

    /**
     * Get a list of mappable fields
     *
     * @param array $extra
     *
     * @return array
     */
    private function get_mappable_fields( $extra = [] ) {

        $defaults = [
            'title'               => array( 'label' => __( 'Title', 'quentn-wp' ), 'type' => 'select' ),
            'title2'              => array( 'label' => __( 'Title 2', 'quentn-wp' ), 'type' => 'select' ),
            'full_name'           => array( 'label' => __( 'Full Name', 'quentn-wp' ), 'type' => 'text' ),
            'first_name'          => array( 'label' => __( 'First Name', 'quentn-wp' ), 'type' => 'text' ),
            'family_name'         => array( 'label' => __( 'Family Name', 'quentn-wp' ), 'type' => 'text' ),
            'company'             => array( 'label' => __( 'Company', 'quentn-wp' ), 'type' => 'text' ),
            'job_title'           => array( 'label' => __( 'Job title', 'quentn-wp' ), 'type' => 'text' ),
            'mail'                => array( 'label' => __( 'Primary email', 'quentn-wp' ), 'type' => 'email' ),
            'mail2'               => array( 'label' => __( 'Secondary email', 'quentn-wp' ), 'type' => 'email' ),
            'phone_type'          => array( 'label' => __( 'Primary Phone Type', 'quentn-wp' ), 'type' => 'select' ),
            'phone'               => array( 'label' => __( 'Primary phone number', 'quentn-wp' ), 'type' => 'tel' ),
            'phone2_type'         => array( 'label' => __( 'Secondary Primary phone number', 'quentn-wp' ), 'type' => 'select' ),
            'phone2'              => array( 'label' => __( 'Secondary phone number', 'quentn-wp' ), 'type' => 'tel' ),
            'fax'                 => array( 'label' => __( 'Fax number', 'quentn-wp' ), 'type' => 'text' ),
            'skype'               => array( 'label' => __( 'Skype name', 'quentn-wp' ), 'type' => 'text' ),
            'fb'                  => array( 'label' => __( 'Facebook', 'quentn-wp' ), 'type' => 'text' ),
            'twitter'             => array( 'label' => __( 'Twitter', 'quentn-wp' ), 'type' => 'text' ),
            'ba_street'           => array( 'label' => __( 'Street (Billing Address)', 'quentn-wp' ), 'type' => 'text' ),
            'ba_street2'          => array( 'label' => __( 'Street 2 (Billing Address)', 'quentn-wp' ), 'type' => 'text' ),
            'ba_city'             => array( 'label' => __( 'City (Billing Address)', 'quentn-wp' ), 'type' => 'text' ),
            'ba_postal_code'      => array( 'label' => __( 'Postal Code (Billing Address)', 'quentn-wp' ), 'type' => 'text' ),
            'ba_state'            => array( 'label' => __( 'State (Billing Address)', 'quentn-wp' ), 'type' => 'text' ),
            'ba_country'          => array( 'label' => __( 'Country', 'quentn-wp' ), 'type' => 'text' ),
            'date_of_birth'       => array( 'label' => __( 'Date of birth', 'quentn-wp' ), 'type' => 'date' ),
        ];

        return $fields = array_merge( $defaults, $extra );
    }
}
