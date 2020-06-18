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
        wp_register_script( 'quentn.elementor.integration.js', QUENTN_WP_PLUGIN_URL . 'admin/js/quentn-elementor-integration.js', array(), '', true  );
        wp_enqueue_script( 'quentn.elementor.integration.js' );

        $mappable_fields = $this->get_mappable_fields();
        $fields = [];

        foreach ( $mappable_fields as $field_id => $field_label ){
            $fields[] = [
                'remote_id'         => $field_id,
                'remote_label'      => $field_label['label'],
                'remote_type'       => $field_label['type'],
                'remote_required'   => in_array( $field_id, [ 'mail' ] ),
            ];
        }
        wp_localize_script( 'quentn.elementor.integration.js', 'QntnMappableFields', [
            'fields' => $fields
        ] );
    }

    private function normalize_type( $type ) {
        static $types = [
            'text' => 'text',
            'number' => 'number',
            'address' => 'text',
            'phone' => 'text',
            'date' => 'text',
            'url' => 'url',
            'imageurl' => 'url',
            'radio' => 'radio',
            'dropdown' => 'select',
            'birthday' => 'text',
            'zip' => 'text',
        ];

        return $types[ $type ];
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
            'title'               => array( 'label' => __( 'Title' ), 'type' => 'select' ),
            'title2'              => array( 'label' => __( 'Title 2' ), 'type' => 'select' ),
            'full_name'           => array( 'label' => __( 'Full Name' ), 'type' => 'text' ),
            'first_name'          => array( 'label' => __( 'First Name' ), 'type' => 'text' ),
            'family_name'         => array( 'label' => __( 'Family Name' ), 'type' => 'text' ),
            'company'             => array( 'label' => __( 'Company' ), 'type' => 'text' ),
            'job_title'           => array( 'label' => __( 'Job title' ), 'type' => 'text' ),
            'mail'                => array( 'label' => __( 'Primary email' ), 'type' => 'email' ),
            'mail2'               => array( 'label' => __( 'Secondary email' ), 'type' => 'email' ),
            'phone_type'          => array( 'label' => __( 'Primary Phone Type' ), 'type' => 'select' ),
            'phone'               => array( 'label' => __( 'Primary phone number' ), 'type' => 'tel' ),
            'phone2_type'         => array( 'label' => __( 'Secondary Primary phone number' ), 'type' => 'select' ),
            'phone2'              => array( 'label' => __( 'Secondary phone number' ), 'type' => 'tel' ),
            'fax'                 => array( 'label' => __( 'Fax number' ), 'type' => 'text' ),
            'skype'               => array( 'label' => __( 'Skype name' ), 'type' => 'text' ),
            'fb'                  => array( 'label' => __( 'Facebook' ), 'type' => 'text' ),
            'twitter'             => array( 'label' => __( 'Twitter' ), 'type' => 'text' ),
            'ba_street'           => array( 'label' => __( 'Street (Billing Address)' ), 'type' => 'text' ),
            'ba_street2'          => array( 'label' => __( 'Street 2 (Billing Address)' ), 'type' => 'text' ),
            'ba_city'             => array( 'label' => __( 'City (Billing Address)' ), 'type' => 'text' ),
            'ba_postal_code'      => array( 'label' => __( 'Postal Code (Billing Address)' ), 'type' => 'text' ),
            'ba_state'            => array( 'label' => __( 'State (Billing Address)' ), 'type' => 'text' ),
            'ba_country'          => array( 'label' => __( 'Country' ), 'type' => 'text' ),
            'date_of_birth'       => array( 'label' => __( 'Date of birth' ), 'type' => 'date' ),
        ];

        return $fields = array_merge( $defaults, $extra );
    }
}
