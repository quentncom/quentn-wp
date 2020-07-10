<?php
use ElementorPro\Modules\Forms\Classes\Rest_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Quentn_Wp_Elementor_Handler {

	private $rest_client = null;

	private $api_key = '';

	public function __construct( $api_key, $base_url ) {
		if ( empty( $api_key ) ) {
			throw new \Exception( 'Invalid API key' );
		}

		if ( empty( $base_url ) ) {
			throw new \Exception( 'Invalid API key' );
		}

		$this->init_rest_client( $api_key, $base_url );

		if ( ! $this->is_valid_api_key() ) {
			throw new \Exception( 'Invalid API key or URL' );
		}
	}

	private function init_rest_client( $api_key, $base_url ) {
        $this->api_key = $api_key;

        $this->rest_client = new Rest_Client( trailingslashit( $base_url ) );
        $this->rest_client->add_headers( [
            'Authorization' => 'Bearer '.$this->api_key,
            'Content-Type' => 'application/json',
        ] );
	}

	/**
	 * validate api key
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function is_valid_api_key() {
        $results = $this->rest_client->get('check-credentials');
        if ( isset( $results['body']['success'] ) && $results['body']['success'] ) {
            return true;
        }
		$this->api_key = '';
		return false;
	}

	/**
	 * get quentn terms associated with API key
	 * @return array
	 * @throws \Exception
	 */
	public function get_lists() {

        $quentn_terms = $this->rest_client->get( 'terms' );
        $terms = array();
        if( ! empty( $quentn_terms['body'] ) ) {
            foreach ( $quentn_terms['body'] as $index => $term ) {
                if ( ! is_array( $term ) ) {
                    continue;
                }
                $terms[ $term['id'] ] = $term['name'];
            }
        }
		$return_array = array(
			'terms' => $terms,
			'fields' => $this->get_custom_fields(),
        );

		return $return_array;
	}

	/**
	 * get Quentn custom fields associated with API key
	 * @return array
	 * @throws \Exception
	 */
	private function get_custom_fields() {

        $quentn_custom_fields = $this->rest_client->get('custom-fields');
        $custom_fields = array();
        //custom fields
        if ( ! empty( $quentn_custom_fields['body'] ) ) {
            foreach ( $quentn_custom_fields['body'] as $custom_field ) {
                if ( ! is_array( $custom_fields ) ) {
                    continue;
                }
                $custom_fields[] = [
                    'remote_id'       => $custom_field['field_name'],
                    'remote_label'    => $custom_field['label'],
                    'remote_type'     => $this->normalize_type( $custom_field['type'], $custom_field['multiple_selection'] ),
                    'remote_required' => ( $custom_field['required'] ) ? true : false,
                ];
            }
        }
        return $custom_fields;
	}

	private function normalize_type( $type, $is_multiple_select = false ) {
		 $types = [
			'text_textfield' => 'text',
			'text' => 'text',
			'number' => 'number',
			'options_buttons' => ( $is_multiple_select ) ? 'checkbox' : 'select',
			'options_select' => 'select',
			'checkbox_confirmation' => 'acceptance',
		];

		return $types[ $type ];
	}

	/**
	 * create contact at quentn via api
	 *
	 * @param array $subscriber_data
	 * @param array $quentn_terms
	 * @param array $new_terms
	 *
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function create_subscriber( $subscriber_data = [], $quentn_terms = [] ) {
        $data = array();
        //add ip address and terms to subscriber data
        $subscriber_data['request_ip'] = $_SERVER["REMOTE_ADDR"];

        if( ! empty( $quentn_terms ) ) {
            $subscriber_data['terms'] = $quentn_terms;
        }

        $data['contact'] = $subscriber_data;

        //add contact at quentn
        $this->rest_client->post( 'contact', $data );
	}
}
