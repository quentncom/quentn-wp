<?php
use QuentnWP\Admin\Utility\Helper;
session_start();

$base_uri = "https://my.quentn.com/public/api/v1/";
$path = "wp/connect";
$params = ["redirect_uri" => esc_url( remove_query_arg(  ['quentn_host_connection'], Helper::get_current_url() ) )];

//if quentn account is removed
if ( isset( $_GET['update'] ) ) {
    $action = sanitize_key( $_GET['update'] );
    if($action == 'remove-quentn-account') {
        delete_option('quentn_app_key' );
        delete_option('quentn_base_url' );

        //if elementor pro plugin is enabled, then also delete default quentn elementor api key and url
        if( Helper::is_elementor_plugin_enabled() && Helper::is_elementor_pro_plugin_enabled() ) {
            if ( class_exists( 'Quentn_Wp_Elementor_Integration' ) ) {
                $elementor_api_key = 'elementor_'. Quentn_Wp_Elementor_Integration::OPTION_NAME_API_KEY;
                $elementor_api_url = 'elementor_'. Quentn_Wp_Elementor_Integration::OPTION_NAME_API_URL;

                delete_option( $elementor_api_key );
                delete_option( $elementor_api_url );
            }
        }
        wp_redirect( esc_url_raw( add_query_arg( ['update' => 'quentn-account-removed' ] ) ) );
        exit;
    }
}
?>
<div class="bootstrap-qntn qntn-connect-button">
    <?php if( $this->api_handler->is_connected_with_quentn() ) {  ?>
        <div class="text-center">
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4 class="alert-heading"><?php  _e( 'You are connected to Quentn', 'quentn-wp' ) ?></h4>
            <hr class="message-inner-separator">
            <p>
                <?php   printf( __( 'You are connected to Quentn host %s', 'quentn-wp' ), get_option('quentn_base_url') ) ; ?>
            </p>
          </div>
          <a href="<?php echo $base_uri . $path . "?" . http_build_query( $params ) ?>" class="btn btn-primary" role="button"><?php _e( 'Update Quentn account', 'quentn-wp' ) ?></a>
          <a href="<?php echo Helper::get_current_url().'&update=remove-quentn-account' ?>" class="btn btn-danger" role="button"><?php _e( 'Remove Quentn account', 'quentn-wp' ) ?></a>
        </div>
    <?php } else { ?>
        <div class="text-center">
        <a href="<?php echo $base_uri . $path . "?" . http_build_query( $params ) ?>" class="btn btn-primary role="button"><?php _e( 'Connect to Quentn', 'quentn-wp' ) ?></a>
        </div>
    <?php } ?>

</div>
<?php
if ( ! empty( $_GET['token'] ) ) {
    $site_url = wp_parse_url( get_home_url() );

    $site_path = ( isset( $site_url['path'] ) ? $site_url['path'] : '' );
    $path = "wp/request";
    $params = array(
        'redirect_uri'  => esc_url( remove_query_arg( 'token', Helper::get_current_url() ) ),
        'token'         => sanitize_text_field( $_GET['token'] ),
    );

    //if wp is installed within a sub folder then we will add name of the installed folders in path e.g http://example/com/wordpress/site1
    if ( $site_path != '' && strcmp( $site_path, '/' ) !== 0) {
        $params['path'] = $site_path;
    }


    try {
        //send post call to quentn to get client id and client secret
        $response = $this->api_handler->get_oauth_client()->call( $path, "POST", $params );

        if( isset( $response['data']['client_id'] ) && isset( $response['data']['client_secret'] ) ) {
            $client_id = $response['data']['client_id'];
            $client_secret = $response['data']['client_secret'];

            update_option( 'quentn_client_id', $client_id );
            update_option( 'quentn_client_secret', $client_secret );

            $this->api_handler->get_oauth_client()->oauth()->setApp( array(
                'client_id'    => $client_id,
                'redirect_uri' => esc_url( remove_query_arg(  ['token', 'update'], Helper::get_current_url() ) ), //remove arg token from current url
            ) );
            //oauth call with client id and client secret
            wp_redirect( $this->api_handler->get_oauth_client()->oauth()->getAuthorizationUrl() );
        }
    } catch ( Exception $e ) {
        echo $e->getMessage();
    }
}

if( isset( $_GET['state'] ) ) {

    //set oauth with client id and client secret
    $this->api_handler->get_oauth_client()->oauth()->setApp( array(
        'client_id'     => get_option('quentn_client_id'),
        'client_secret' =>  get_option('quentn_client_secret'),
        'redirect_uri'  => Helper::get_current_url(),
    ) );

    //if authorization is successful
    if($this->api_handler->get_oauth_client()->oauth()->authorize()) {
        update_option( 'quentn_app_key', $this->api_handler->get_oauth_client()->getApiKey() );
        update_option( 'quentn_base_url', $this->api_handler->get_oauth_client()->getBaseUrl() );

        //if elementor pro plugin is enabled, then update default quentn elementor api key and url
        if( defined('ELEMENTOR_VERSION' ) && defined('ELEMENTOR_PRO_VERSION' )  && ELEMENTOR_VERSION >= '2.0.0' ) {
            if ( class_exists( 'Quentn_Wp_Elementor_Integration' ) ) {
                $elementor_api_key = 'elementor_'. Quentn_Wp_Elementor_Integration::OPTION_NAME_API_KEY;
                $elementor_api_url = 'elementor_'. Quentn_Wp_Elementor_Integration::OPTION_NAME_API_URL;

                update_option( $elementor_api_key, $this->api_handler->get_oauth_client()->getApiKey() );
                update_option( $elementor_api_url, $this->api_handler->get_oauth_client()->getBaseUrl() );
            }
        }
        delete_option( 'quentn_client_id');
        delete_option( 'quentn_client_secret');
        wp_redirect( esc_url_raw(add_query_arg(['update' => 'qntn-account-connected' ])) );
    }
}
