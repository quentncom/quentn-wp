<?php
use ElementorPro\Modules\Forms\Classes\Integration_Base as Integration_Base;
use Elementor\Controls_Manager;
use Elementor\Settings;
use Elementor\Repeater;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Modules\Forms\Widgets\Form;
use ElementorPro\Modules\Forms\Module;
use QuentnWP\Admin\Utility\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Quentn_Wp_Elementor_Integration extends Integration_Base {

    const OPTION_NAME_API_KEY = 'pro_quentn_api_key';
    const OPTION_NAME_API_URL = 'pro_quentn_api_url';

    public function __construct() {
        if ( is_admin() ) {
            add_action( 'elementor/admin/after_create_settings/' . Settings::PAGE_ID, array( $this, 'register_admin_fields' ), 15 );
            add_action( 'plugins_loaded', array( $this, 'add_quentn_action' ), 15 );
        }
        add_action( 'wp_ajax_' . self::OPTION_NAME_API_KEY . '_validate', array( $this, 'ajax_validate_api_token' ) );

        // Add attribute to elementor form field
        add_filter( 'elementor_pro/forms/render/item', array( $this, 'add_quentn_data_attribute' ), 10, 3 );
        add_filter( 'elementor_pro/forms/render/item/radio', array( $this, 'add_quentn_data_attribute_radio' ), 10, 3 );
    }

    /*
     * Add data attribute to Elementor Pro form fields
     */
    public function add_quentn_data_attribute( $field, $field_index, $form_widget ) {
        $settings = $form_widget->get_settings();

        $field_type = $field['field_type'];

        //elementor fields with html type text
        $element_input = array( 'text', 'email', 'acceptance', 'tel', 'url', 'number', 'date', 'hidden' );
        if( in_array( $field_type, $element_input ) ) {
             $element = 'input';
         } else {
             $element = $field_type;
         }

        if ( isset( $settings['quentn_fields_map'] ) && isset( $settings['quentn_fields_map'][$field_index]['remote_id'] ) && ! empty( $settings['quentn_fields_map'][$field_index]['remote_id'] ) ) {
            $remote_id =  $settings['quentn_fields_map'][$field_index]['remote_id'];
            //add data attribute to form field
            $form_widget->add_render_attribute( $element . $field_index,
                [
                    'data-qntn-field-name' => $remote_id,
                ]
            );
        }
        return $field;
    }

    /*
     * Add data attribute to Elementor Pro form radio fields
     */
    public function add_quentn_data_attribute_radio( $field, $field_index, $form_widget ) {
        $settings = $form_widget->get_settings();
        $options = preg_split( "/\\r\\n|\\r|\\n/", $field['field_options'] );
        $remote_id =  $settings['quentn_fields_map'][$field_index]['remote_id'];
                if ( $options ) {
                    foreach ( $options as $key => $option ) {
                        $element_id = $field['custom_id'] . $key;
                        //add data attribute to form field
                        $form_widget->add_render_attribute( $element_id,
                            [
                                'data-qntn-field-name' => $remote_id,
                            ]
                        );
                    }
                }
        return $field;
    }

    private function get_global_api_key() {
        return get_option( 'elementor_' . self::OPTION_NAME_API_KEY, '' );
    }

    private function get_global_api_url() {
        return get_option( 'elementor_' . self::OPTION_NAME_API_URL, '' );
    }

    public function get_name() {
        return 'quentn';
    }

    public function get_label() {
        return __( 'Quentn', 'quentn-wp' );
    }

    public function register_settings_section( $widget ) {
        $widget->start_controls_section(
            'section_quentn_elementor',
            [
                'label' => __( 'Quentn', 'quentn-wp' ),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        self::global_api_control(
            $widget,
            $this->get_global_api_key(),
            'Quentn API credentials',
            [
                'quentn_api_credentials_source' => 'default',
            ],
            $this->get_name()
        );

        $widget->add_control(
            'quentn_api_credentials_source',
            [
                'label' => __( 'API Credentials', 'quentn-wp' ),
                'type' => Controls_Manager::SELECT,
                'label_block' => false,
                'options' => [
                    'default' => 'Default',
                    'custom' => 'Custom',
                ],
                'default' => 'default',
            ]
        );

        $widget->add_control(
            'quentn_api_key',
            [
                'label' => __( 'API Key', 'quentn-wp' ),
                'type' => Controls_Manager::TEXT,
                'description' => __( 'Use this field to set a custom API Key for the current form', 'quentn-wp' ),
                'condition' => [
                    'quentn_api_credentials_source' => 'custom',
                ],
            ]
        );

        $widget->add_control(
            'quentn_api_url',
            [
                'label' => __( 'API URL', 'quentn-wp' ),
                'type' => Controls_Manager::TEXT,
                'description' => __( 'Use this field to set a custom API URL for the current form', 'quentn-wp' ),
                'condition' => [
                    'quentn_api_credentials_source' => 'custom',
                ],
            ]
        );

        $widget->add_control(
            'quentn_list',
            [
                'label' => __( 'Add Tags', 'quentn-wp' ),
                'type' => Controls_Manager::SELECT2,
                'description' => __( 'Add as many tags as you want', 'quentn-wp' ),
                'options' =>  [],
                'multiple' => true,
                'label_block' => true,
                'render_type' => 'none',
            ]
        );

        $this->register_quentn_fields_map_control( $widget );

        $widget->add_control(
            'quentn_flood_limit',
            [
                'label' => __( 'Flood Protection', 'quentn-wp' ),
                'type' => Controls_Manager::NUMBER,
                'description' => __( 'Enter the maximum form submissions per hour and unique IP-Adresses (default: 5, 0 to diable)', 'quentn-wp' ),
                'min' => 0,
                'default' => 5,
                'separator' => 'before'
            ]
        );
        $widget->add_control(
            'quentn_spam_protection',
            [
                'label' => __( 'Use Spam Protection', 'quentn-wp' ),
                'type' => Controls_Manager::SWITCHER,
                'description' => __( 'Form submissions of known Spam-Bots will be ignored', 'quentn-wp' ),
                'separator' => 'before'
            ]
        );
        $widget->end_controls_section();

        //Quentn redirect settins section
        $widget->start_controls_section(
            'section_quentn_redirect_elementor',
            [
                'label' => __( 'Quentn Redirect', 'quentn-wp' ),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );
        $widget->add_control(
            'quentn_default_redirect_url',
            [
                'label' => __( 'Redirection URL (default Redirection)', 'quentn-wp' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true
            ]
        );
        $widget->add_control(
            'quentn_confirmed_redirect_url',
            [
                'label' => __( 'Redirection URL for confirmed contact', 'quentn-wp' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true
            ]
        );
        $widget->add_control(
            'quentn_attach_contact_information',
            [
                'label' => __( 'Attach contact info to URL', 'quentn-wp' ),
                'type' => Controls_Manager::SWITCHER,
                'description' => __( "Attaches Contact's ID and Email to the URL. This is used for tracking tools (e.g. splittest tools) to verify the opt-in.", 'quentn-wp' ),
            ]
        );
        $widget->end_controls_section();
    }

    private function register_quentn_fields_map_control( Form $form ) {
        $repeater = new Repeater();

        $repeater->add_control( 'local_id', [ 'type' => Controls_Manager::HIDDEN ] );

        $repeater->add_control( 'remote_id', [ 'type' => Controls_Manager::SELECT ] );

        $fields_map_control_options = [
            'label' => __( 'Field Mapping', 'elementor-pro' ),
            'type' => Quentn_Wp_Elementor_Field_Mapping::CONTROL_TYPE,
            'separator' => 'before',
            'fields' => $repeater->get_controls(),
        ];

        if ( method_exists( $this,'get_fields_map_control_options' ) ) {
            $fields_map_control_options = array_merge( $fields_map_control_options, $this->get_fields_map_control_options() );
        }

        $form->add_control( $this->get_name() . '_fields_map', $fields_map_control_options );
    }

    public function on_export( $element ) {
        unset(
            $element['settings']['quentn_api_credentials_source'],
            $element['settings']['quentn_api_key'],
            $element['settings']['quentn_api_url'],
            $element['settings']['quentn_list'],
            $element['settings']['quentn_fields_map'],
            $element['settings']['quentn_flood_limit'],
            $element['settings']['quentn_spam_protection'],
            $element['settings']['quentn_default_redirect_url'],
            $element['settings']['quentn_confirmed_redirect_url'],
            $element['settings']['quentn_attach_contact_information']
        );

        return $element;
    }

    public function run( $record, $ajax_handler ) {

        $form_settings = $record->get( 'form_settings' );
        $subscriber = $this->create_subscriber_object( $record );

       if ( ! $subscriber ) {
            $ajax_handler->add_admin_error_message( __( 'Quentn Integration requires an email field', 'quentn-wp' ) );
            return;
        }

        if ( 'default' === $form_settings['quentn_api_credentials_source'] ) {
            $api_key = $this->get_global_api_key();
            $api_url = $this->get_global_api_url();
        } else {
            $api_key = $form_settings['quentn_api_key'];
            $api_url = $form_settings['quentn_api_url'];
        }

        try {
            $handler = new Quentn_Wp_Elementor_Handler( $api_key, $api_url );
            $response = $handler->create_subscriber( $subscriber );
            //redirect user if redirect url set in Quentn reponse
            if ( isset( $response['body']['redirection_url'] ) &&  filter_var( $response['body']['redirection_url'], FILTER_VALIDATE_URL ) ) {
                $redirect_to = $record->replace_setting_shortcodes( $response['body']['redirection_url'], true );
                $ajax_handler->add_response_data( 'redirect_url', $redirect_to );
            }
        } catch ( \Exception $exception ) {
                $ajax_handler->add_admin_error_message( 'Quentn ' . $exception->getMessage() );
        }
   }

   /**
    * Create subscriber array from submitted data and form settings
    * returns a subscriber array or false on error
    *
    * @param Form_Record $record
    *
    * @return array|bool
    */
    private function create_subscriber_object( Form_Record $record ) {
         $map = $this->map_fields( $record );

        if ( ! in_array( 'mail', $map ) ) {
            return false;
        }
        $request_data = array();
        $fields = $this->get_normalized_fields( $record );
        $contact = $this->generate_contact_with_map( $fields, $map );

        //if contact data includes date,time mapped with quentn datetime field, convert it into Unix timestamp
        foreach ( $contact as $field => $value ) {
            if ( isset( $value['date'] ) ) {
                if ( isset( $value['time'] ) && $value['time'] != '' ) {
                    $date_time = $value['date'] . ' ' . $value['time'];
                    $format = 'Y-m-d H:i';

                    /**
                     * @var $site_time_zone_obj \DateTimeZone
                     */
                    $site_time_zone_obj = Helper::get_wp_site_timezone();
                    $date = DateTime::createFromFormat( $format, $date_time, $site_time_zone_obj );
                    if ( $date !== false ) {
                        $contact[$field] = $date->getTimestamp(); //update contact date field from array to Unix timestamp
                    }
                } else {
                    $contact[$field] = $value['date'];
                }
            }
            //if only elementor time field is set against Quentn date field but no elementor date field is found, then we will ignore it as we don't have time field in Quentn
            if ( isset( $value['time'] ) && ! isset($value['date'] ) ) {
                unset($contact[$field]);
            }
        }

        //add addtional contact data fields
        $quentn_terms = $record->get_form_settings( 'quentn_list' );
        $quentn_flood_limit = $record->get_form_settings( 'quentn_flood_limit' );
        $quentn_spam_protection = $record->get_form_settings( 'quentn_spam_protection' );
        $quentn_default_redirect_url = $record->get_form_settings( 'quentn_default_redirect_url' );
        $quentn_confirmed_redirect_url = $record->get_form_settings( 'quentn_confirmed_redirect_url' );
        $quentn_attach_contact_information = $record->get_form_settings( 'quentn_attach_contact_information' );

        if( ! empty( $quentn_terms ) ) {
            $contact['terms'] = $quentn_terms;
        }
        $contact['request_ip'] = $_SERVER["REMOTE_ADDR"]; //add ip address and terms to subscriber data
        $request_data['contact'] = $contact;
        $request_data['flood_limit'] = ( $quentn_flood_limit === '' ) ? 5 : $quentn_flood_limit;
        $request_data['spam_protection'] = ( $quentn_spam_protection == 'yes' ) ? 1 : 0;
        $request_data['qntn'] = [
            "redirection_url" => ( $quentn_default_redirect_url && filter_var( $quentn_default_redirect_url, FILTER_VALIDATE_URL ) ) ? $quentn_default_redirect_url : '',
            "redirection_url_confirmed" => ( $quentn_confirmed_redirect_url && filter_var( $quentn_confirmed_redirect_url, FILTER_VALIDATE_URL ) ) ? $quentn_confirmed_redirect_url : '',
            "append_contact_info" => ( $quentn_attach_contact_information == 'yes' ) ? 1 : 0,
        ];
        return $request_data;
    }


    /*
     * get local_ids with their values
     *
     * @param Form_Record $record
     *
     * @return array  e.g  ['name' => 'Smith', 'email' => 'smith@example.com', 'address' => 'street address etc' ]
     */
    private function get_normalized_fields( Form_Record $record )
    {
        $fields = array();
        $raw_fields = $record->get( 'fields' );
        foreach ( $raw_fields as $id => $field ) {
            if ( $field['type'] == 'checkbox' ) { //convert multi select values into array
                $fields[ $id ] = explode( ",", $field['value'] );
            } elseif ( $field['type'] == 'acceptance' && $field['value'] == 'on' ) {
                //get ip address
                if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                $fields[ $id ] = array(
                    "ip" => $ip,
                    "created" => time(),
                    "source" => Helper::get_current_host_name(),
                );
            } else {
                $fields[ $id ] = $field['value'];
            }
        }
        return $fields;
    }

    /*
     * map local_ids with quentn id's e.g [ 'name' => 'first_name', 'email' => 'mail', 'address' => 'ba_street']
     *
     * @param Form_Record $record
     *
     * @return array
     */
    private function map_fields( Form_Record $record ) {
        $map = array();

        $fields_map = $record->get_form_settings( 'quentn_fields_map' );
        foreach ( $fields_map as $map_item ) {
            if ( ! empty( $map_item['remote_id'] ) ) {
                $map[$map_item['local_id']] = $map_item['remote_id'];
            }
        }
        return $map;
    }

    /**
     * Generate a contact from given associative array and a field map.
     *
     * @param $fields e.g ['name' => 'Smith', 'email' => 'smith@example.com', 'address' => 'street address etc' ]
     * @param $map  e.g [ 'name' => 'first_name', 'email' => 'mail', 'address' => 'ba_street']
     *
     * @return array e.g [ 'first_name' => 'Smith', 'mail' => 'smith@example.com' ]
     */
    private function generate_contact_with_map( $fields, $map ) {
        $args  = array();

        foreach ( $fields as $column => $value ) {

            // ignore if we are not mapping it.
            if ( ! array_key_exists ( $column, $map ) ) {
                continue;
            }

            $value = wp_unslash( $value );

            $field = $map[ $column ];

            $type = '';
            //In special cases e.g date and time, we have field type along with field id, with comma separated
            if ( strpos( $field, ',' ) !== false ) {
                list( $field, $type ) = explode( ',', $field );
            }

            switch ( $field ) {
                case 'full_name':
                    $parts              = $this->split_name( $value );
                    $args['first_name'] = sanitize_text_field( $parts[0] );
                    $args['family_name']  = sanitize_text_field( $parts[1] );
                    break;
                case 'title':
                case 'title2':
                case 'company':
                case 'job_title':
                case 'first_name':
                case 'family_name':
                case 'phone_type':
                case 'phone':
                case 'phone2_type':
                case 'phone2':
                case 'fax':
                case 'skype':
                case 'fb':
                case 'twitter':
                case 'ba_street':
                case 'ba_street2':
                case 'ba_postal_code':
                case 'ba_state':
                case 'ba_country':
                    $args[ $field ] = sanitize_text_field( $value );
                    break;
                case 'mail':
                case 'mail2':
                    $args[ $field ] = sanitize_email( $value );
                    break;
                case 'date_of_birth':
                    if( $this->isValidDate( $value ) ) {
                        $args[ $field ] = $value;
                    }
                   break;
                default:
                    if ( is_array( $value ) ) {
                        $args[ $field ] = array_map( 'sanitize_text_field', $value );
                    } elseif ( $type == 'date' || $type == 'time' ) {
                        //Date and Time are two different fields in Elementor, merge them for Quentn DateTime field
                        $args[ $field ][$type] = sanitize_text_field( $value );
                    } else {
                        $args[ $field ] = sanitize_text_field( $value );
                    }
                    break;
            }

        }

        return $args;
    }

    private function isValidDate( $value )
    {
        if ( ! $value ) {
            return false;
        }

        try {
            new \DateTime( $value );
            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Split a name into first and last.
     *
     * @param $name
     *
     * @return array
     */
    private function split_name( $name ) {
        $_name = ucwords( preg_replace( '/\s+/', ' ', $name ) );
        $name_array = explode( ' ', $_name );
        $first_name = $name_array[0];
        $last_name = "";
        unset( $name_array[0] );
        if ( count( $name_array ) ) {
            $last_name = implode( ' ', $name_array );
        }
        return array( $first_name, $last_name );
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function handle_panel_request( array $data ) {
        if ( ! empty( $data['api_cred'] ) && 'default' === $data['api_cred'] ) {
            $api_key = $this->get_global_api_key();
            $api_url = $this->get_global_api_url();
        } elseif ( ! empty( $data['api_key'] ) && ! empty( $data['api_url'] ) ) {
            $api_key = $data['api_key'];
            $api_url = $data['api_url'];
        }

        if ( empty( $api_key ) ) {
            throw new \Exception( '`api_key` is required', 400 );
        }

        if ( empty( $api_url ) ) {
            throw new \Exception( '`api_url` is required', 400 );
        }
        $handler = new Quentn_Wp_Elementor_Handler( $api_key, $api_url );
        return $handler->get_lists();
    }



    public function ajax_validate_api_token() {
        check_ajax_referer( self::OPTION_NAME_API_KEY, '_nonce' );
        if ( ! isset( $_POST['api_key'] ) || ! isset( $_POST['api_url'] ) ) {
            wp_send_json_error();
        }
        try {
            new Quentn_Wp_Elementor_Handler( $_POST['api_key'], $_POST['api_url'] );
        } catch ( \Exception $exception ) {
            wp_send_json_error();
        }
        wp_send_json_success();
    }

    public function register_admin_fields( Settings $settings ) {
        $settings->add_section( Settings::TAB_INTEGRATIONS, 'quentn', [
            'callback' => function() {
                echo '<hr><h2>' . esc_html__( 'Quentn', 'quentn-wp' ) . '</h2>';
            },
            'fields' => [
                self::OPTION_NAME_API_KEY => [
                    'label' => __( 'API Key', 'quentn-wp' ),
                    'field_args' => [
                        'type' => 'text',
                    ],
                ],
                self::OPTION_NAME_API_URL => [
                    'label' => __( 'API URL', 'quentn-wp' ),
                    'field_args' => [
                        'type' => 'url',
                        'desc' => sprintf( __( 'To integrate with our forms you need an %s', 'quentn-wp' ), '<a href="https://quentn.com/preise" target="_blank">'.__( "API Key", "quentn-wp" ).'</a>.' ) ,

                    ],
                ],
                'validate_api_data' => [
                    'field_args' => [
                        'type' => 'raw_html',
                        'html' => sprintf( '<button data-action="%s" data-nonce="%s" class="button elementor-button-spinner" id="elementor_pro_quentn_api_key_button">%s</button>', self::OPTION_NAME_API_KEY . '_validate', wp_create_nonce( self::OPTION_NAME_API_KEY ), __( 'Validate API Key', 'quentn-wp' ) ),
                    ],
                ],
            ],
        ] );
    }

    public function add_quentn_action() {
        Module::instance()->add_form_action( 'Quentn', $this );
    }

}
