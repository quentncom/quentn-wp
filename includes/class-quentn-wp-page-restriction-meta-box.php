<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Page_Restriction_Meta_Box
{

    /**
     * Constructor method.
     *
     * @since  1.0
     * @return void
     */
    public function __construct() {

        add_action( 'add_meta_boxes', array( $this, 'quentn_register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_page_meta' ) );
    }

    /**
     * Call back function to register metabox
     *
     * @since  1.0
     * @return void
     */

    public function quentn_register_meta_boxes() {
        add_meta_box( 'quentn-access-restriction-meta-box', __( 'Quentn Page Restriction', 'quentn-wp' ), array( $this, 'quentn_page_restriction_callback' ), 'page' );
    }

    /**
     * Return an instance of this class.
     *
     * @since 1.0
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

    public function quentn_page_restriction_callback( $post ) {

        //Add an nonce field so we can check for it later.
        wp_nonce_field( 'quentn_permission_meta_custom_box', 'quentn_permission_meta_custom_box_nonce' );

        $quentn_post_restrict_meta = (get_post_meta( get_the_ID(), '_quentn_post_restrict_meta' ) ) ? get_post_meta( get_the_ID(), '_quentn_post_restrict_meta', true ) : array();

        // Set up default values
        $quentn_page_restrict_status = '';
        $quentn_page_restrict_countdown = '';
        $quentn_page_restrict_countdown_type = '';
        $quentn_page_restrict_hours = 0;
        $quentn_page_restrict_minutes = 0;
        $quentn_page_restrict_seconds = 0;
        $quentn_page_access_mode = '';
        $quentn_page_restrict_absolute_date = '';
        $quentn_page_restrict_default_countdown_status = '';
        $quentn_page_countdown_stick_on_top = '';
        $quentn_page_restrict_redirection_type =  '';
        $quentn_page_restrict_redirect_url = '';
        $quentn_page_restrict_error_message = '';

        // If we're editing access, overwrite the defaults with current one's
        if( $quentn_post_restrict_meta ) {
            $quentn_page_restrict_status                    = array_key_exists( 'status', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['status'] : '';
            $quentn_page_restrict_countdown                 = array_key_exists( 'countdown', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['countdown'] : '';
            $quentn_page_restrict_countdown_type            = array_key_exists( 'countdown_type', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['countdown_type'] : '';
            $quentn_page_restrict_hours                     = array_key_exists( 'hours', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['hours'] : 0;
            $quentn_page_restrict_minutes                   = array_key_exists( 'minutes', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['minutes'] : 0;
            $quentn_page_restrict_seconds                   = array_key_exists( 'seconds', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['seconds'] : 0;
            $quentn_page_access_mode                        = array_key_exists( 'access_mode', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['access_mode'] : '';
            $quentn_page_restrict_absolute_date             = array_key_exists( 'absolute_date', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['absolute_date'] : '';
            $quentn_page_restrict_default_countdown_status  = array_key_exists( 'display_countdown_default_status', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['display_countdown_default_status'] : '';
            $quentn_page_countdown_stick_on_top             = array_key_exists( 'quentn_countdown_stick_on_top', $quentn_post_restrict_meta  ) ? ( $quentn_page_restrict_default_countdown_status && $quentn_post_restrict_meta['quentn_countdown_stick_on_top']) : '';
            $quentn_page_restrict_redirection_type          = array_key_exists( 'redirection_type', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['redirection_type'] : '';
            $quentn_page_restrict_redirect_url              = array_key_exists( 'redirect_url', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['redirect_url'] : '';
            $quentn_page_restrict_error_message             = array_key_exists( 'error_message', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['error_message'] : '';
        }

        ?>

            <div class="bootstrap-qntn">
                <div id="popover-qntn"></div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="quentn_page_access_status" id="quentn_page_access_status" value="1"  <?php checked( $quentn_page_restrict_status ); ?>>
                        <small><?php  _e( 'Activate page Restriction', 'quentn-wp' )?></small>
                    </label>
                    <a  href="#" role="button" data-toggle="popover" data-trigger="focus" title="<?php  _e( 'Help', 'quentn-wp' )?>" data-content="<?php  _e( 'Page will only be accessible by invite users', 'quentn-wp' )?>"><i class="fas fa-question-circle"></i></a>
                </div>

                <div id="quentn_permission_panel" style="display: <?php echo  ( $quentn_post_restrict_meta ) ? 'block' : 'none'; ?>">
                    <div class="form-group form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="quentn_page_access_countdown" id="quentn_page_access_countdown" value="1"  <?php checked( $quentn_page_restrict_countdown ); ?>>
                            <small><?php  _e( 'Countdown', 'quentn-wp' )?></small>
                        </label>
                        <a  href="#" role="button" data-toggle="popover" data-trigger="focus" title="<?php  _e( 'Help', 'quentn-wp' )?>" data-content="<?php  _e( 'Page will only be accessible for a certain period of time', 'quentn-wp' )?>"><i class="fas fa-question-circle"></i></a>
                    </div>
                    <div class="card" id="quentn_countdown_settings" style="display: <?php echo (($quentn_page_restrict_countdown) ? 'block' : 'none')?>">
                        <div class="card-header">
                            <i class="fas fa-cog"></i> <?php  _e( 'Countdown', 'quentn-wp' )?>
                        </div>
                        <div class="card-body">
                            <small class="form-text text-muted"><?php printf( __('You can use the shortcode %s in your page to display the countdown timer at a point of your choice.', 'quentn-wp'), '<strong>[quentn_flipclock]</strong>' ); ?></small>
                            <div class="form-group mt-3">
                                <label for="quentn_page_restrict_countdown_type"><?php _e('Select type', 'quentn-wp')?></label>
                                <select class="form-control" name="quentn_page_restrict_countdown_type" id="quentn_page_restrict_countdown_type">
                                    <option value="relative" <?php  selected('relative', $quentn_page_restrict_countdown_type); ?>><?php  _e( 'Relative', 'quentn-wp' )?></option>
                                    <option value="absolute" <?php  selected('absolute', $quentn_page_restrict_countdown_type); ?>><?php  _e( 'Absolute', 'quentn-wp' )?></option>
                                </select>
                            </div>
                            <div id="relative-div" class=" mt-3" style="display: <?php echo ($quentn_page_restrict_countdown_type == 'relative') ? 'block' : 'none'?>">
                                <div class="form-inline">
                                    <div class="qntn-countdown-type form-group">
                                        <label for="quentn_page_restrict_hours"><?php _e('Hours', 'quentn-wp')?></label>
                                        <input id="quentn_page_restrict_hours" type="text" name="quentn_page_restrict_hours" class="form-control"  value="<?php echo esc_html( $quentn_page_restrict_hours ); ?>" >
                                    </div>
                                    <div class="qntn-countdown-type form-group">
                                        <label for="quentn_page_restrict_minutes"><?php _e('Minutes', 'quentn-wp')?></label>
                                        <input id="quentn_page_restrict_minutes" type="text" name="quentn_page_restrict_minutes" class="form-control" value="<?php echo esc_html( $quentn_page_restrict_minutes ); ?>" >
                                    </div>
                                    <div class="qntn-countdown-type form-group">
                                        <label for="quentn_page_restrict_seconds"><?php _e('Seconds', 'quentn-wp')?></label>
                                        <input id="quentn_page_restrict_seconds" type="text" name="quentn_page_restrict_seconds" class="form-control" value="<?php echo esc_html( $quentn_page_restrict_seconds ); ?>">
                                    </div>
                                </div>

                                <div class="form-check  mt-3">
                                    <input type="radio" class="form-check-input" name="access_mode" id="access_mode_permission_granted" value="permission_granted_mode" checked  >
                                    <label class="form-check-label" for="access_mode_permission_granted">
                                        <small class="form-text text-muted"><?php  _e( 'Countdown starts when permission has been granted.', 'quentn-wp' ) ?></small>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="access_mode" id="access_mode_first_visit" value="first_visit_mode" <?php checked( $quentn_page_access_mode, 'first_visit_mode' ); ?>>
                                    <label class="form-check-label" for="access_mode_first_visit">
                                        <small class="form-text text-muted"><?php  _e( 'Countdown starts on the first visit. This works without access permission.', 'quentn-wp' ) ?></small>
                                    </label>
                                </div>
                            </div>

                             <div class = "form-group" id="absolute-div" style=" position:relative; display: <?=($quentn_page_restrict_countdown_type == 'absolute')?'block':'none'?>">
                                <label for = "quentn_page_restrict_datepicker" class = "control-label"><?php _e( 'Date', 'quentn-wp' )?></label>
                                <div class="input-group date" id="absolute_datetimepicker" data-target-input="nearest">
                                    <div class="input-group-append" data-target="#absolute_datetimepicker" data-toggle="datetimepicker">
                                        <input type="text" class="form-control datetimepicker-input" data-target="#absolute_datetimepicker" id="quentn_page_restrict_datepicker" name="quentn_page_restrict_datepicker" value="<?php echo esc_html( $quentn_page_restrict_absolute_date ) ?>"  placeholder = "<?php _e( 'Select date', 'quentn-wp' )?>" />
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                             </div>

                            <div>
                                <input type="checkbox" name="quentn_default_display_countdown_status" id="quentn_default_display_countdown_status" value="1" <?php checked( $quentn_page_restrict_default_countdown_status ); ?> >
                                <label class="form-check-label" for="quentn_default_display_countdown_status">
                                    <small class="form-text text-muted"><?php  _e( 'Display countdown on top of page', 'quentn-wp' )?></small>
                                </label>
                                <small id="emailHelp" class="form-text text-muted"><?php  _e( 'If this option is disabled, no countdown is displayed.', 'quentn-wp' )?></small>
                            </div>

                            <div id="countdown-position-div" class=" mt-3" style="display: <?php echo ( $quentn_page_restrict_default_countdown_status ) ? 'block' : 'none'?>">
                                <input type="checkbox" name="quentn_countdown_stick_on_top" id="quentn_countdown_stick_on_top" value="1" <?php checked( $quentn_page_countdown_stick_on_top ); ?> >
                                <label class="form-check-label" for="quentn_countdown_stick_on_top">
                                    <small class="form-text text-muted"><?php  _e( 'Countdown stays on the top while the page scrolls', 'quentn-wp' )?></small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" id="quentn_page_redirection_type_panel" style="display: <?php echo (($quentn_post_restrict_meta) ? 'block' : 'none')?>">
                    <div class="card-header">
                        <i class="fas fa-cog"></i> &nbsp;<?php  _e( 'Redirection', 'quentn-wp' ) ?>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="quentn_page_restrict_countdown_type"><?php _e('Select restriction type', 'quentn-wp')?></label>
                            <select class="form-control" name="quentn_page_redirection_type" id="quentn_page_redirection_type">
                                <option value="restricted_message" <?php selected('restricted_message', $quentn_page_restrict_redirection_type);?>><?php _e( 'Message', 'quentn-wp' )?></option>
                                <option value="restricted_url" <?php selected('restricted_url', $quentn_page_restrict_redirection_type); ?>><?php _e( 'URL', 'quentn-wp' )?></option>
                            </select>
                        </div>

                        <div class="mt-3">
                            <div  class="form-group redirection_type" id="forbidden_message_div" style="display: <?php echo ($quentn_page_restrict_redirection_type == 'restricted_message' )?'block':'none'?>">
                                <label><?php _e( 'Message', 'quentn-wp' )?></label>
                                <p>
                                    <?php
                                        wp_editor(
                                            isset( $quentn_page_restrict_error_message ) ? $quentn_page_restrict_error_message : '',
                                            'quentn_page_access_error_message',
                                            array(
                                                'drag_drop_upload' => true,
                                                'textarea_rows'    => get_option('default_post_edit_rows', 10),
                                                'quicktags' => true,
                                                'tinymce' => false,
                                            )
                                        );
                                    ?>
                                </p>
                            </div>
                            <div class = "form-group redirection_type" id="redirect_url-div" style="display: <?php echo ( $quentn_page_restrict_redirection_type == 'restricted_url' ) ? 'block' : 'none'?>">
                                <div>
                                    <label for = "quentn_page_restrict_datepicker" class = "control-label"><?php _e( 'Redirect URL', 'quentn-wp' )?></label>
                                    <input type = "text" class = "form-control" id = "quentn_page_restrict_redirect_url" name="quentn_page_restrict_redirect_url" value="<?php echo isset( $quentn_page_restrict_redirect_url ) ? esc_html( $quentn_page_restrict_redirect_url ) : ''?>"  placeholder = "<?php _e( 'Enter URL', 'quentn-wp' )?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }


    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_page_meta( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['quentn_permission_meta_custom_box_nonce'] ) ) {
            return;
        }

        $nonce = $_POST['quentn_permission_meta_custom_box_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'quentn_permission_meta_custom_box' ) ) {
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

        //getting and sanitizing metabox values
        $q_status = sanitize_text_field( $_POST['quentn_page_access_status'] );
        $q_countdown = sanitize_text_field( $_POST['quentn_page_access_countdown'] );
        $q_countdown_type = sanitize_text_field( $_POST['quentn_page_restrict_countdown_type'] );
        $q_hours = ( ! empty( $_POST['quentn_page_restrict_hours'] ) && filter_var( $_POST['quentn_page_restrict_hours'], FILTER_VALIDATE_INT ) ) ? sanitize_text_field( $_POST['quentn_page_restrict_hours'] ) : 0;
        $q_minutes = ( ! empty( $_POST['quentn_page_restrict_minutes'] ) && filter_var( $_POST['quentn_page_restrict_minutes'], FILTER_VALIDATE_INT ) ) ? sanitize_text_field( $_POST['quentn_page_restrict_minutes'] ) : 0;
        $q_seconds = ( ! empty( $_POST['quentn_page_restrict_seconds'] ) && filter_var( $_POST['quentn_page_restrict_seconds'], FILTER_VALIDATE_INT ) ) ? sanitize_text_field( $_POST['quentn_page_restrict_seconds'] ) : 0;
        $access_mode = sanitize_text_field ( $_POST['access_mode'] );
        $q_absolute_date = sanitize_text_field ( $_POST['quentn_page_restrict_datepicker'] );
        $q_default_countdown_status = sanitize_text_field( $_POST['quentn_default_display_countdown_status'] );
        $q_countdown_stick_on_top = sanitize_text_field( $_POST['quentn_countdown_stick_on_top'] );
        $q_redirection_type = sanitize_text_field( $_POST['quentn_page_redirection_type'] );
        $q_redirect_url = sanitize_text_field ( $_POST['quentn_page_restrict_redirect_url'] );
        $q_error_message = ( ! empty( $_POST['quentn_page_access_error_message'] ) ) ? wp_kses_post( wp_unslash( $_POST['quentn_page_access_error_message'] ) ) : '';

        $quentn_restrict_post_meta = array();

        //if quentn access permission status is active
        if( $q_status ) {

            //if type is absolute then date must be valid
            if ( $q_countdown && $q_countdown_type == 'absolute' && ! empty( $q_absolute_date ) && strtotime( $q_absolute_date) === false ) {
                //$q_absolute_date = '';
            }

            //if return url is not empty, then it must be valid
            if( $q_redirection_type != 'restricted_url' || ! filter_var( $q_redirect_url, FILTER_VALIDATE_URL ) ) {
                //we are not stopping complete page to save if redirect url not valid. that's why we comment this code to avoid user confusion
                //$q_redirect_url = '';
            }

            //setting values in array to save in database
            $quentn_restrict_post_meta['status'] = $q_status;
            $quentn_restrict_post_meta['countdown'] = $q_countdown;
            $quentn_restrict_post_meta['countdown_type'] = $q_countdown_type;
            $quentn_restrict_post_meta['hours'] = $q_hours;
            $quentn_restrict_post_meta['minutes'] = $q_minutes;
            $quentn_restrict_post_meta['seconds'] =  $q_seconds;
            $quentn_restrict_post_meta['access_mode'] =  $access_mode;
            $quentn_restrict_post_meta['absolute_date'] =  $q_absolute_date;
            $quentn_restrict_post_meta['display_countdown_default_status'] = $q_default_countdown_status;
            $quentn_restrict_post_meta['quentn_countdown_stick_on_top'] = $q_countdown_stick_on_top;
            $quentn_restrict_post_meta['redirection_type'] = $q_redirection_type;
            $quentn_restrict_post_meta['redirect_url'] = $q_redirect_url;
            $quentn_restrict_post_meta['error_message'] = $q_error_message;

            update_post_meta( $post_id, '_quentn_post_restrict_meta', $quentn_restrict_post_meta);
        }

        //check if quentn post meta tag was there then we will remove in case of deactivate
        elseif( get_post_meta( $post_id, '_quentn_post_restrict_meta', true ) ) {
            delete_post_meta( $post_id, '_quentn_post_restrict_meta' );
        }
    }
}


Quentn_Wp_Page_Restriction_Meta_Box::get_instance();