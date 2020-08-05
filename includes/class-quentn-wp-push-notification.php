<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Push_Notification
{

    /**
     * Constructor method.
     *
     * @since  1.1.0
     * @return void
     */
    public function __construct() {
        add_shortcode( 'quentn_enable_push_notification', array( $this, 'enable_push_notification_shortcode' ) );
        add_shortcode( 'quentn_disable_push_notification', array( $this, 'disable_push_notification_shortcode' ) );
        add_action( 'add_meta_boxes', array( $this, 'quentn_register_push_notification_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_push_notification_settings' ) );
    }

    /**
     * Call back function to register metabox
     *
     * @since  1.1.0
     * @return void
     */

    public function quentn_register_push_notification_meta_boxes() {
        add_meta_box( 'quentn-push-notification-meta-box', __( 'Quentn Push Notification', 'quentn-wp' ), array( $this, 'quentn_push_notification_callback' ), 'post', 'side' );
    }

    /**
     * Return an instance of this class.
     *
     * @since 1.1.0
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
     * Meta box display callback.
     *
     * @param WP_Post $post Current post object.
     */

    public function quentn_push_notification_callback( $post ) {

        //Add an nonce field so we can check for it later.
        wp_nonce_field( 'quentn_push_notification_meta_box', 'quentn_push_notification_meta_box_nonce' );
        $quentn_push_notification_settings = get_post_meta( $post->ID, 'quentn_send_notification', true );
        ?>
            <label><input name="quentn_send_notification" type="checkbox" value="1" <?php if ( get_post_meta( $post->ID, 'quentn_send_notification', true ) ) echo "checked"; ?>> Send push notifications</label>
        <?php
    }


    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_push_notification_settings( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['quentn_push_notification_meta_box_nonce'] ) ) {
            return;
        }

        $nonce = $_POST['quentn_push_notification_meta_box_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'quentn_push_notification_meta_box' ) ) {
            return;
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

        $should_send = get_post_status ( $post_id ) != 'publish' ? isset ( $_POST ['quentn_send_notification'] ) : false;

        update_post_meta ( $post_id, 'quentn_send_notification', $_POST ['quentn_send_notification'] );

    }

    /**
     * Get push notification enable button shortcode
     *
     * @since  1.1.0
     * @access public
     * @return string
     */
    public function enable_push_notification_shortcode( $atts, $content = null ) {
        //set default values
        $default = shortcode_atts( array(
            'display' => 'none',
        ), $atts );

        //set default display status none
        $css_atts = ( $atts ) ? array_replace( $atts, $default ) : $default;

        $style = implode('; ', array_map(
            function ( $val, $key ) { return sprintf("%s:%s", esc_attr( $key ), esc_attr( $val ) ); },
            $css_atts,
            array_keys( $css_atts )
        ) );

        if ( ! $content ) {
            $content = __( "Enable Push Messaging", "quentn-wp" );
        }
        $css_prop = "style='".$style."'";

        return "<p><button class='quentn-enable-push-notification-btn' $css_prop>$content</button></p>";
    }

    /**
     * Get push notification disable button shortcode
     *
     * @since  1.1.0
     * @access public
     * @return string
     */
    public function disable_push_notification_shortcode( $atts, $content = null ) {
        //set default values
        $default = shortcode_atts( array(
            'display' => 'none',
        ), $atts );

        //set default display status none
        $css_atts = ( $atts ) ? array_replace( $atts, $default ) : $default;

        $style = implode('; ', array_map(
            function ($val, $key) { return sprintf( "%s:%s", esc_attr( $key ), esc_attr( $val ) ); },
            $css_atts,
            array_keys( $css_atts )
        ) );
        if ( ! $content ) {
            $content = __( "Enable Push Messaging", "quentn-wp" );
        }
        $css_prop = "style='".$style."'";
        return "<p><button class='quentn-disable-push-notification-btn' $css_prop>$content</button></p>";
    }
}

Quentn_Wp_Push_Notification::get_instance();