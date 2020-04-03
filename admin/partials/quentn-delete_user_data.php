<?php
    if( isset( $_GET['update'] ) and $_GET['update'] == 'quentn-user-data-delete' ) {

        global $wpdb;
        if ( ! wp_verify_nonce($_REQUEST['qntn_user_data_nonce_delete_nonce'] , 'qntn_user_data_nonce' ) ) {
            die( 'Nope! Security check failed' );
        }
        //if there is plus sign in email address
         $email = str_replace(" ","+",trim( $_GET['email'] ) );
         $query =   $wpdb->delete( $wpdb->prefix . TABLE_QUENTN_USER_DATA, ['email' => $email ], [ '%s' ] );

         //add and remove items from a query string
         wp_redirect( esc_url_raw(remove_query_arg( ['quentn-user-data-delete', 'qntn_user_data_nonce_delete_nonce'], esc_url_raw(add_query_arg( [ 'update' => 'quentn-user-data-deleted', 'deleted' => $query ] ) ) ) ) );
         exit;

    }
?>

<form method="get" action="./admin.php?page=quentn-dashboard&tab=qnentn_delete_user_data">
    <table class="form-table">
        <tr class="form-field form-required">
            <th><?php _e('Email', 'quentn-wp' ) ?></th>
            <td>
                <input required name="email" type="email" id="email" style="width: 25em" placeholder="<?php  _e( 'Email', 'quentn-wp' ) ?>">
                <input name='page' type="hidden" value="<?php echo $_REQUEST['page'] ?>">
                <input name='tab' type="hidden" value="<?php echo $_REQUEST['tab'] ?>">
                <input name='update' type="hidden" value="quentn-user-data-delete">
                <input type="hidden" value="<?php echo wp_create_nonce( 'qntn_user_data_nonce' )?>" id="qntn_user_data_nonce_delete_nonce" name="qntn_user_data_nonce_delete_nonce">
            </td>
        </tr>

    </table>
    <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Delete', 'quentn-wp' ) ?>"></p>
</form>
