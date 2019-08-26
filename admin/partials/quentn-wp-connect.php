<?php
session_start();

$base_uri = "https://my.quentn.com/public/api/v1/";
$path = "wp/connect";
$params = ["redirect_uri" => esc_url( remove_query_arg(  ['quentn_host_connection'], Helper::get_current_url() ) )];

//if quentn account is removed
if ( isset( $_GET['update'] ) and sanitize_key( $_GET['update'] == 'remove-quentn-account' ) ) {
    delete_option('quentn_app_key' );
    delete_option('quentn_base_url' );
    wp_redirect( esc_url_raw( add_query_arg( ['update' => 'quentn-account-removed' ] ) ) );
    exit;
}
?>
<div class="bootstrap-qntn" style="margin-top: 25px">
    <?php if( $this->api_handler->is_connected_with_quentn() ) {  ?>
        <div class="col-sm-10" style="text-align: center">
          <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <span class="glyphicon glyphicon-ok"></span><strong><?php  _e( 'You are connected to Quentn', 'quentn-wp' ) ?></strong>
                <hr class="message-inner-separator">
                <p>
                    <?php   printf( __( 'You are connected to Quentn host %s', 'quentn-wp' ), get_option('quentn_base_url') ) ; ?>
                </p>
            </div>
            <a href="<?php echo $base_uri.$path."?".http_build_query($params) ?>" class="btn btn-primary btn-lg"><?php _e( 'Update Quentn account', 'quentn-wp' ) ?></a>
            <a href="<?php echo Helper::get_current_url().'&update=remove-quentn-account' ?>" class="btn btn-danger btn-lg"><?php _e( 'Remove Quentn account', 'quentn-wp' ) ?></a>
        </div>
    <?php } else { ?>
        <div class="col-sm-10" style="text-align: center">
        <a href="<?php echo $base_uri.$path."?".http_build_query( $params ) ?>" class="btn btn-success btn-lg"><?php _e( 'Connect to Quentn', 'quentn-wp' ) ?></a>
        </div>
    <?php } ?>

</div>
<?php
if ( ! empty( $_GET["token"] ) ) {
    $site_url = wp_parse_url( get_home_url() );

    $site_path = ( isset( $site_url['path'] ) ? $site_url['path'] : '' );
    $path = "wp/request";
    $params = array(
        'redirect_uri'  => esc_url( remove_query_arg( 'token', Helper::get_current_url() ) ),
        'token'         => $_GET['token'],
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
        update_option( 'quentn_app_key', $this->api_handler->get_oauth_client()->getApiKey());
        update_option( 'quentn_base_url', $this->api_handler->get_oauth_client()->getBaseUrl());

        delete_option( 'quentn_client_id');
        delete_option( 'quentn_client_secret');
        wp_redirect( esc_url_raw(add_query_arg(['update' => 'qntn-account-connected' ])) );
    }
}
