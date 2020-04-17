<?php
//get active tab
if( isset($_GET['tab'])) {
    $active_tab = $_GET[ 'tab' ];
} else {
    $active_tab = 'quentn_host_connection';
}
?>
<div class="wrap">
    <h1><?php _e( 'Quentn Options', 'quentn-wp' ) ?></h1>
    <?php settings_errors(); ?>

        <!-- provides the styling for tabs. -->
        <h2 class="nav-tab-wrapper">
            <a href="?page=quentn-dashboard&tab=quentn_host_connection" class="nav-tab <?php if ( $active_tab == 'quentn_host_connection' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Connect', 'quentn-wp' ); ?></a>
            <a href="?page=quentn-dashboard&tab=qnentn_tags_selection" class="nav-tab <?php if ( $active_tab == 'qnentn_tags_selection' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Roles', 'quentn-wp' ); ?></a>
            <a href="?page=quentn-dashboard&tab=qnentn_web_tracking_tab" class="nav-tab <?php if ( $active_tab == 'qnentn_web_tracking_tab' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Web Tracking', 'quentn-wp' ); ?></a>
            <a href="?page=quentn-dashboard&tab=qnentn_delete_user_data" class="nav-tab <?php if ( $active_tab == 'qnentn_delete_user_data' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Delete User Data', 'quentn-wp' ); ?></a>
            <?php if( $this->is_learndash_plugin_active() ) { ?>
            <a href="?page=quentn-dashboard&tab=qnentn_learndash_course_settings" class="nav-tab <?php if ( $active_tab == 'qnentn_learndash_course_settings' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'LearnDash', 'quentn-wp' ); ?></a>
            <?php } ?>
        </h2>
        <?php
        $submit_button_attributes = array();
        if( $active_tab != "qnentn_delete_user_data" && $active_tab != "qnentn_learndash_course_settings" && ! $this->api_handler->is_connected_with_quentn() ) {
            $submit_button_attributes =  array(
                'disabled' => true
            );
        ?>
        <div class="bootstrap-qntn">
            <div class="alert alert-danger mt-3">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4 class="alert-heading"><?php  _e( 'You are not connected to Quentn', 'quentn-wp' ) ?></h4>
                <hr class="message-inner-separator">
                <p class="mb-0">
                    <?php  echo _e( 'Please connect a Quentn account to use this feature', 'quentn-wp' )  ?>
                </p>
            </div>
        </div>
        <?php
        }
        //display quentn tags selection options for wp user roles
        if ( $active_tab == "qnentn_tags_selection" ) {
            echo '<form method="post" action="options.php">';
            settings_fields( "quentn_tags_options_group" );
            do_settings_sections( "quentn-dashboard-tags" );
            submit_button( NULL, 'primary', 'submit', true, $submit_button_attributes );
            echo '</form>';
        } elseif ( $active_tab == "qnentn_learndash_course_settings" && $this->is_learndash_plugin_active() ) {
            echo '<form method="post" action="options.php">';
            settings_fields( "quentn_learndash_options_group" );
            do_settings_sections( "quentn-learn-dash" );
            submit_button( NULL, 'primary', 'submit', true, $submit_button_attributes );
            echo '</form>';
        } elseif ( $active_tab == "qnentn_web_tracking_tab" ) { //display web tracking options
            echo '<form method="post" action="options.php">';
            if( get_option('quentn_web_tracking_enabled') ) {
                update_option("quentn_web_tracking_code", $this->get_quentn_web_tracking_code() );
            } else { //if web tracking option is disabled, we will delete previously saved tracking code
                delete_option('quentn_web_tracking_code');
            }

            //check if plugin is connected with quentn web tracking is not enabled
            if( $this->api_handler->is_connected_with_quentn() && ! $this->api_handler->is_web_tracking_enabled() ) {
                if ( ! empty( $this->api_handler->error_messages ) ) {
                    $this->show_errors( $this->api_handler->error_messages );
                }
                $submit_button_attributes =  array(
                    'disabled' => true
                );
            }
            elseif( $this->api_handler->is_connected_with_quentn() && ! $this->is_domain_registered( $_SERVER['HTTP_HOST'], $this->api_handler->get_registered_domains() ) ) {
                if ( ! empty( $this->api_handler->error_messages ) ) {
                    $this->show_errors( $this->api_handler->error_messages );
                }
                $submit_button_attributes =  array(
                    'disabled' => true
                );
                ?>
                <div class="bootstrap-qntn">
                    <div class="col-md-12 mt-5" style="margin-top: 30px">
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                ×</button>
                            <span class="glyphicon glyphicon-hand-right"></span> <strong><?php printf(__( "Could not find %s at the connected Quentn server.  Please add %s as tracked domain in order to use this feature.", 'quentn-wp' ),"'".$_SERVER['HTTP_HOST']."''", "'".$_SERVER['HTTP_HOST']."''" ); ?></strong>
                        </div>
                    </div>
                </div>
            <?php
            }
            settings_fields("web_tracking_options_group");
            do_settings_sections("quentn-dashboard-web-tracking");
            submit_button( NULL, 'primary', 'submit', true, $submit_button_attributes );
            echo '</form>';
        } elseif ( $active_tab == "quentn_host_connection" ) {
            require_once QUENTN_WP_PLUGIN_DIR . '/admin/partials/quentn-wp-connect.php';
        }
        elseif ( $active_tab == "qnentn_delete_user_data" ) {
            require_once QUENTN_WP_PLUGIN_DIR . '/admin/partials/quentn-delete-user-data.php';
        }
        ?>
</div>