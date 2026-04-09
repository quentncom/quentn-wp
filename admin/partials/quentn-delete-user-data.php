<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

    if( isset( $_GET['update'] ) ) {
        $action = sanitize_key( $_GET['update'] );
        if($action == 'quentn-user-data-delete') {
            global $wpdb;
            if ( ! wp_verify_nonce($_REQUEST['qntn_user_data_nonce_delete_nonce'] , 'qntn_user_data_nonce' ) ) {
                die( 'Nope! Security check failed' );
            }
            //if there is plus sign in email address
            $email = str_replace(" ","+", sanitize_email( $_GET['email'] ) );
            $query =   $wpdb->delete( $wpdb->prefix . TABLE_QUENTN_USER_DATA, ['email' => $email ], [ '%s' ] );

            //add and remove items from a query string
            wp_safe_redirect( esc_url_raw( remove_query_arg( ['quentn-user-data-delete', 'qntn_user_data_nonce_delete_nonce'], esc_url_raw( add_query_arg( [ 'update' => 'quentn-user-data-deleted', 'deleted' => $query ] ) ) ) ) );
            exit;
        }
    }
?>

<form method="get" action="./admin.php?page=quentn-dashboard&tab=quentn_settings">
    <table class="form-table qntn-form">
        <tr class="form-field form-required">
            <th><?php esc_html_e('Email', 'quentn-wp' ) ?></th>
            <td>
                <input required name="email" type="email" id="email" placeholder="<?php  esc_html_e( 'Email', 'quentn-wp' ) ?>">
                <label for="email"> <?php printf( esc_html__( 'Enter email address to delete related contact data.', 'quentn-wp'  ) ); ?></label>
                <input name='page' type="hidden" value="<?php echo esc_html( sanitize_text_field( $_REQUEST['page'] ) ) ?>">
                <input name='tab' type="hidden" value="<?php echo esc_html( sanitize_text_field( $_REQUEST['tab'] ) ) ?>">
                <input name='update' type="hidden" value="quentn-user-data-delete">
                <?php wp_nonce_field( 'qntn_user_data_nonce', 'qntn_user_data_nonce_delete_nonce' ); ?>
            </td>
        </tr>

    </table>
    <p class="submit"><input type="submit" class="button button-primary" value="<?php esc_html_e( 'Delete', 'quentn-wp' ) ?>"></p>
</form>
