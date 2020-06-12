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
                'remote_label'      => $field_label,
                'remote_type'       => 'text',
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
            'title'               => __( 'Title' ),
            'title2'              => __( 'Title 2' ),
            'full_name'           => __( 'Full Name' ),
            'first_name'          => __( 'First Name' ),
            'family_name'         => __( 'Family Name' ),
            'company'             => __( 'Company' ),
            'job_title'           => __( 'Job title' ),
            'mail'                => __( 'Primary email' ),
            'mail2'               => __( 'Secondary email' ),
            'phone_type'          => __( 'Primary Phone Type' ),
            'phone'               => __( 'Primary phone number' ),
            'phone2_type'         => __( 'Secondary Primary phone number' ),
            'phone2'              => __( 'Secondary phone number' ),
            'fax'                 => __( 'Fax number' ),
            'skype'               => __( 'Skype name' ),
            'fb'                  => __( 'Facebook' ),
            'twitter'             => __( 'Twitter' ),
            'ba_street'           => __( 'Street (Billing Address)' ),
            'ba_street2'          => __( 'Street 2 (Billing Address)' ),
            'ba_city'             => __( 'City (Billing Address)' ),
            'ba_postal_code'      => __( 'Postal Code (Billing Address)' ),
            'ba_state'            => __( 'State (Billing Address)' ),
            'ba_country'          => __( 'Country ' ),
            'date_of_birth'       => __( 'Date of birth' ),
        ];

        return $fields = array_merge( $defaults, $extra );
    }
}
