<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Page_Restrictions_Controller
{

    /**
     * Constructor method.
     *
     * @since  1.0
     * @return void
     */
    public function __construct() {

        add_action( 'add_meta_boxes', array($this, 'quentn_register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_page_meta' ) );
    }

    /**
     * Call back function to register metabox
     *
     * @since  1.0
     * @return void
     */

    public function quentn_register_meta_boxes() {
        add_meta_box( 'quentn-access-restriction-meta-box', __( 'Quentn Page Restriction', 'quentn' ), array( $this, 'quentn_page_restriction_callback' ), 'page' );
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
        $quentn_page_restrict_absolute_date = '';
        $quentn_page_restrict_default_countdown_status = '';
        $quentn_page_restrict_redirection_type =  '';
        $quentn_page_restrict_redirect_url = '';
        $quentn_page_restrict_error_message = '';

        // If we're editing access, overwrite the defaults with current one's
        if( $quentn_post_restrict_meta ) {
            $quentn_page_restrict_status = array_key_exists( 'status', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['status'] : '';
            $quentn_page_restrict_countdown = array_key_exists( 'countdown', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['countdown'] : '';
            $quentn_page_restrict_countdown_type = array_key_exists( 'countdown_type', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['countdown_type'] : '';
            $quentn_page_restrict_hours = array_key_exists( 'hours', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['hours'] : '';
            $quentn_page_restrict_minutes = array_key_exists( 'minutes', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['minutes'] : '';
            $quentn_page_restrict_seconds = array_key_exists( 'seconds', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['seconds'] : '';
            $quentn_page_restrict_absolute_date = array_key_exists( 'absolute_date', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['absolute_date'] : '';
            $quentn_page_restrict_default_countdown_status = array_key_exists( 'display_countdown_default_status', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['display_countdown_default_status'] : '';
            $quentn_page_restrict_redirection_type = array_key_exists( 'redirection_type', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['redirection_type'] : '';
            $quentn_page_restrict_redirect_url = array_key_exists( 'redirect_url', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['redirect_url'] : '';
            $quentn_page_restrict_error_message = array_key_exists( 'error_message', $quentn_post_restrict_meta  ) ? $quentn_post_restrict_meta['error_message'] : '';
        }

        ?>
        <div class="bootstrap-qntn">
            <div class="form-group">
                <input type="checkbox"  name="quentn_page_access_status" id="quentn_page_access_status" value="1" <?php checked( $quentn_page_restrict_status ); ?> >
                <small class="form-text text-muted"><?php  _e( 'Activate page Restriction', 'quentn' )?></small>
                <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="<?php  _e( 'Help', 'quentn' )?>" data-content="<?php  _e( 'Page will only be accessible by invite users', 'quentn' )?>"><span class="glyphicon glyphicon-question-sign"></a>
            </div>

            <div id="quentn_permission_panel" style="display: <?php echo  ( $quentn_post_restrict_meta )?'block':'none'; ?>">
                <div class="form-group">
                    <input type="checkbox" name="quentn_page_access_countdown" id="quentn_page_access_countdown" value="1" <?php echo checked( $quentn_page_restrict_countdown ); ?>>
                    <small class="form-text text-muted"><?php  _e( 'Use Countdown', 'quentn' )?></small>
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="<?php  _e( 'Help', 'quentn' )?>" data-content="<?php  _e( 'Page will only be accessible for a certain period of time', 'quentn' )?>"><span class="glyphicon glyphicon-question-sign"></a>
                </div>

                <div class="panel-group" id="quentn_countdown_settings" style="display: <?php echo (($quentn_page_restrict_countdown)?'block':'none')?>">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php  _e( 'Countdown Settings', 'quentn' )?></div>
                        <div class="panel-body">
                            <div class = "form-group">
                                <p class="help-block">
                                    <?php printf( __('You can use the shortcode %s in your page to make the countdown timer appear', 'quentn'), '<strong>[quentn_flipclock]</strong>' ); ?>
                                </p>
                                <label for="quentn_page_restrict_countdown_type"><?php _e('Select type', 'quentn')?></label>
                                <select  class = "form-control" name="quentn_page_restrict_countdown_type" id="quentn_page_restrict_countdown_type">
                                    <option value="relative" <?php  selected('relative', $quentn_page_restrict_countdown_type); ?>><?php  _e( 'Relative', 'quentn' )?></option>
                                    <option value="absolute" <?php  selected('absolute', $quentn_page_restrict_countdown_type); ?>><?php  _e( 'Absolute', 'quentn' )?></option>
                                </select>
                            </div>


                            <div class="countdown-type form-inline"  id="relative-div" style="display: <?=($quentn_page_restrict_countdown_type == 'relative')?'block':'none'?>">
                                <div class="form-group">
                                    <label for="quentn_page_restrict_hours"><?php _e('Hours', 'quentn')?></label>
                                    <input id="quentn_page_restrict_hours" type="text" name="quentn_page_restrict_hours" class="form-control"  value="<?php echo $quentn_page_restrict_hours; ?>" >
                                </div>
                                <div class="form-group">
                                    <label for="quentn_page_restrict_minutes"><?php _e('Minutes', 'quentn')?></label>
                                    <input id="quentn_page_restrict_minutes" type="text" name="quentn_page_restrict_minutes" class="form-control" value="<?php echo $quentn_page_restrict_minutes; ?>" >
                                </div>
                                <div class="form-group">
                                    <label for="quentn_page_restrict_seconds"><?php _e('Seconds', 'quentn')?></label>
                                    <input id="quentn_page_restrict_seconds" type="text" name="quentn_page_restrict_seconds" class="form-control" value="<?php echo $quentn_page_restrict_seconds; ?>">
                                </div>
                            </div>

                            <div class = "form-group" id="absolute-div" style=" position:relative; display: <?=($quentn_page_restrict_countdown_type == 'absolute')?'block':'none'?>">
                                <div>
                                    <label for = "quentn_page_restrict_datepicker" class = "control-label"><?php _e( 'Date', 'quentn' )?></label>
                                    <input type = "text" class = "form-control" id = "quentn_page_restrict_datepicker" name="quentn_page_restrict_datepicker" value="<?php echo $quentn_page_restrict_absolute_date ?>"  placeholder = "<?php _e( 'Select date', 'quentn' )?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class = "checkbox">
                                    <label><input type = "checkbox"><input type="checkbox" name="quentn_default_display_countdown_status" id="quentn_default_display_countdown_status" value="1" <?php checked( $quentn_page_restrict_default_countdown_status ); ?> ></label>
                                    <small class="form-text text-muted"><?php  _e( 'Display countdown on top of page', 'quentn' )?></small>
                                    <p class="help-block">
                                        <?php  _e( 'No countdown will be shown by default if this option is disabled', 'quentn' )?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-group" id="quentn_page_redirection_type_panel" style="display: <?php echo (($quentn_post_restrict_meta)?'block':'none')?>">
                <div class="panel panel-default">
                    <div class="panel-heading"><?php  _e( 'Redirection Settings', 'quentn' )?></div>
                    <div class="panel-body">
                        <div class = "form-group">
                            <label for="quentn_page_redirection_type"><?php _e( 'Select restriction type', 'quentn' )?></label>
                            <select  class = "form-control" name="quentn_page_redirection_type" id="quentn_page_redirection_type">
                                <option value="restricted_message" <?php selected('restricted_message', $quentn_page_restrict_redirection_type);?>><?php _e( 'Message', 'quentn' )?></option>
                                <option value="restricted_url" <?php selected('restricted_url', $quentn_page_restrict_redirection_type); ?>><?php _e( 'URL', 'quentn' )?></option>
                            </select>
                        </div>

                        <div style="margin-top: 5px;">
                            <div  class="form-group redirection_type" id="forbidden_message_div" style="display: <?php echo ($quentn_page_restrict_redirection_type == 'restricted_message' )?'block':'none'?>">
                                <label><?php _e( 'Message', 'quentn' )?></label>
                                <p>
                                    <?php
                                        wp_editor(
                                            isset( $quentn_page_restrict_error_message ) ? $quentn_page_restrict_error_message : '',
                                            'quentn_page_access_error_message',
                                            array(
                                                'drag_drop_upload' => true,
                                                'editor_height'    => 150
                                            )
                                        );
                                    ?>
                                </p>
                            </div>
                            <div class = "form-group redirection_type" id="redirect_url-div" style="display: <?php echo ( $quentn_page_restrict_redirection_type == 'restricted_url' ) ? 'block' : 'none'?>">
                                <div>
                                    <label for = "quentn_page_restrict_datepicker" class = "control-label"><?php _e( 'Redirect URL', 'quentn' )?></label>
                                    <input type = "text" class = "form-control" id = "quentn_page_restrict_redirect_url" name="quentn_page_restrict_redirect_url" value="<?php echo isset( $quentn_page_restrict_redirect_url ) ? $quentn_page_restrict_redirect_url : ''?>"  placeholder = "<?php _e( 'Enter URL', 'quentn' )?>">
                                </div>
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
        $q_absolute_date = sanitize_text_field ( $_POST['quentn_page_restrict_datepicker'] );
        $q_default_countdown_status = sanitize_text_field( $_POST['quentn_default_display_countdown_status'] );
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
                //$q_redirect_url = '';
            }

            //setting values in array to save in database
            $quentn_restrict_post_meta['status'] = $q_status;
            $quentn_restrict_post_meta['countdown'] = $q_countdown;
            $quentn_restrict_post_meta['countdown_type'] = $q_countdown_type;
            $quentn_restrict_post_meta['hours'] = $q_hours;
            $quentn_restrict_post_meta['minutes'] = $q_minutes;
            $quentn_restrict_post_meta['seconds'] =  $q_seconds;
            $quentn_restrict_post_meta['absolute_date'] =  $q_absolute_date;
            $quentn_restrict_post_meta['display_countdown_default_status'] = $q_default_countdown_status;
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


Quentn_Wp_Page_Restrictions_Controller::get_instance();