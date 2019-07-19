<?php

if (!defined('ABSPATH')) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Quentn_Wp_Access_Overview extends \WP_List_Table {

    /**
     * Page id for access overview
     *
     * @since  1.0.0
     * @access private
     * @var    int
     */
    private $page_id;

    /**
     * page meta containing quentn page restriction data
     *
     * @since  1.0.0
     * @access private
     * @var    array
     */
    private $quentn_page_restriction_data;

    public function __construct( $page_id, $args = array( ))
    {
        parent::__construct( $args );

        $this->page_id = $page_id;

        $this->quentn_page_restriction_data = get_post_meta( $this->page_id, '_quentn_post_restrict_meta', true );

    }

    /**
     * Load filtered levels for current query
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function prepare_items()
    {
        //get columns to display
        $columns = $this->get_columns();

        //get sortable column
        $sortable = $this->get_sortable_columns();

        //set column headers
        $this->_column_headers = array(
            $columns,
            array(),
            $sortable,
        );

        /** Process bulk action */
        $this->process_bulk_action();

        //apply pagination
        $per_page     = $this->get_items_per_page( 'quentn_access_overview_records_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count( $this->page_id );


        $this->set_pagination_args( array(
            "total_items" => $total_items,
            "per_page"    => $per_page,
        ));

        $this->items = $this->get_quentn_restrictions( $per_page, $current_page, $this->page_id );

    }

    /**
     * Table columns with titles
     *
     * @since  1.0
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'            => "<input type='checkbox' />",
            'email'         => __( 'Email', 'quentn-wp' ),
            'created_at'    => __( 'Created', 'quentn-wp' ),
        );

        if( isset( $this->quentn_page_restriction_data['countdown'] ) && $this->quentn_page_restriction_data['countdown'] ) {
            $columns['valid_until'] = __( 'Valid Until', 'quentn-wp' );
        }
        $columns['delete-access'] = __( 'Delete', 'quentn-wp' );
        $columns['view_access']       = __( 'View Access', 'quentn-wp' );

        return $columns;
    }

    /**
     * Render checkbox column
     *
     * @since  1.0
     * @param  object $item
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="quentn-bulk-delete-access[]" value="%s" /><input type="hidden" name="page_id" value="%d" />', $item['page_id']."|".$item["email"], $item['page_id']
        );
    }

    /**
     * Default fallback for column render
     *
     * @since  1.0
     * @param  object  $item
     * @param  string  $column_name
     * @return mixed
     */
    public function column_default( $item, $column_name )
    {
        $delete_nonce = wp_create_nonce( 'qntn_delete_access' );
        $page_id = absint( $item['page_id'] );
        switch ($column_name) {
            case 'email':
                //case 'created_at_user_readable':
            case 'valid_until':
            case 'email_hash':
                return $item[$column_name];
            case 'created_at':
                $date_created = getdate( $item['created_at'] );
                return $date_created['mday']." ".substr($date_created['month'], 0, 3)." ".$date_created['year']." ".$date_created['hours'].":".$date_created['minutes'].":".$date_created['seconds'];;
            case 'delete-access':
                return  sprintf( '<a href="?page=%s&action=%s&page_id=%s&email=%s&_wpnonce=%s" onclick="return confirm(\'%s\')" >%s</a>', esc_attr( $_REQUEST['page'] ), 'qntn-delete', $page_id, trim($item['email']), $delete_nonce, __( "Are you sure you want to delete?", 'quentn-wp' ),  __( "Delete", 'quentn-wp' ) );
            case 'view_access':
                return  sprintf( "<input type='text' class='get_access_url' readonly  value='%s' /><button class='copy_access_url'>%s</button>", get_page_link( $_GET['page_id'] ).'?qntn_wp='.$item['email_hash'], __( 'Copy URL' ) );
            default:
                return __( "no value", 'quentn-wp' );
        }
    }

    /**
     * Render page id column
     *
     * @since  1.0
     * @param  object  $item
     * @return string
     */
    public function column_page_id( $item ) {
        $delete_nonce = wp_create_nonce( 'qntn_delete_access' );
        $action = array(
            'qntn-delete' => sprintf( '<a href="?page=%s&action=%s&page_id=%s&email=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'qntn-delete', absint( $item['page_id'] ), trim( $item['email'] ), $delete_nonce, __("Delete", 'quentn-wp') ),
        );
        return $item['page_id']. $this->row_actions( $action );
    }

    /**
     * Columns to make sortable.
     *
     * @since  1.0.0
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'email'                    => array( 'email', true ),
            'created_at' => array( 'created_at', true )
        );

        return $sortable_columns;
    }


    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            'quentn-bulk-delete-access' => __("Delete", 'quentn-wp'),
        );

        return $actions;
    }


    /**
     * Retrieve restrictions data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public function get_quentn_restrictions( $per_page = 5, $page_number = 1, $page_id ) {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix . QUENTN_TABLE_NAME. " where page_id='". $page_id."'";
        //set search
        if ( ! empty( $_REQUEST['s'] ) ) {
            $sql .= " and email LIKE '%" . $_REQUEST['s']."%'";
        }

        //set order by
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $results = $wpdb->get_results( $sql, 'ARRAY_A' );

        $is_countdown = ( isset( $this->quentn_page_restriction_data['countdown'] ) && $this->quentn_page_restriction_data['countdown'] ) ?  true : false ;

        $countdown_type = ( isset( $this->quentn_page_restriction_data['countdown_type'] ) ) ?  $this->quentn_page_restriction_data['countdown_type'] : '' ;

        //in case absolute date, expiry date will be same for all users, but for relative expirty date need to calculate for every user
        $valid_until = '';
        if( $countdown_type == 'absolute' ) {
            $valid_until = $this->quentn_page_restriction_data['absolute_date'];
        } elseif( $countdown_type == 'relative' ) {
            $hours = ( isset( $this->quentn_page_restriction_data['hours'] ) && $this->quentn_page_restriction_data['hours'] != '' ) ?  $this->quentn_page_restriction_data['hours'] : 0 ;
            $minutes = ( isset( $this->quentn_page_restriction_data['minutes'] ) && $this->quentn_page_restriction_data['minutes'] != '' ) ?  $this->quentn_page_restriction_data['minutes'] : 0 ;
            $seconds = ( isset( $this->quentn_page_restriction_data['seconds'] ) && $this->quentn_page_restriction_data['seconds'] != '' ) ?  $this->quentn_page_restriction_data['seconds'] : 0 ;
            //convert hours, minutes into seconds
            $quentn_page_expirty_inseconds = $hours * 3600 + $minutes * 60 + $seconds;
        }

        //add expiry date for all users
        foreach ( $results as $key => $result ) {
            //if its page restriction type is relative, then count for every user
            if( $is_countdown && $countdown_type == 'relative' ) {
                //page access time start when access assigned to specific user + time valid for page - current time
                $quentn_expiry_page_inseconds = $result['created_at'] + $quentn_page_expirty_inseconds - time();
                //set postfix text
                if( $quentn_expiry_page_inseconds > 0 ) {
                    $text_with_expiry_time = __( 'Time Left To Expire', 'quentn-wp' );
                } else {
                    $text_with_expiry_time = __( 'Ago Page Has Expired', 'quentn-wp' );
                }
                //add number of seconds left to current record array
                $results[$key]['seconds'] = $quentn_expiry_page_inseconds;
                $valid_until = gmdate( "H:i:s", abs( $quentn_expiry_page_inseconds ) ) . " " . $text_with_expiry_time;
            }
            //add expiry date/valid until into records we got from database
            $results[$key]['valid_until'] = $valid_until;
        }
        return $results;
    }

    /**
     * Delete a restriction record
     *
     * @param int $page_id
     * @param string $email
     * @return int|bool
     */
    public function delete_restriction( $page_id, $email ) {
        global $wpdb;
         return $wpdb->delete( $wpdb->prefix . QUENTN_TABLE_NAME, [ 'page_id' => $page_id, 'email' => $email ], [ '%d', '%s' ] );
    }

    /**
     * Returns the count of records in the database.
     *
     * @return int
     */
    public function record_count($page_id) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM ".$wpdb->prefix . QUENTN_TABLE_NAME. " where page_id='".$page_id."'";
        if ( ! empty( $_REQUEST['s'] ) ) {
            $sql .= " and email LIKE '%".$_REQUEST['s']."%'";
        }

        return $wpdb->get_var( $sql );
    }

    /**
     * Text when no record found
     *
     * @since  1.0.0
     * @return void
     */
    public function no_items() {
        _e( 'No record avaliable.', 'quentn-wp' );
    }

    /**
     * Displays the list table.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function display() {

        $this->views();

        parent::display();
    }

    /**
     * Get views (level statuses), Add access button
     *
     * @since  1.0
     * @return array
     */

    public function get_views(){
        $views = array();

        $views['add_access'] = '<a href="#TB_inline?&width=400&height=250&inlineId=qntn-add-access" title="' . __( "Access Overview For Page", "quentn-wp" ). ' ' . get_the_title($this->page_id).'" class="button action thickbox">'.__( 'Add Access', 'quentn-wp' ).'</a>';

        return $views;
    }


    /**
     * process actions
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function process_bulk_action() {

        //process single delete access record
        if ( 'qntn-delete' === $this->current_action() ) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'qntn_delete_access' ) ) {
                die( 'Nope! Security check failed' );
            }
            else {
                $num_records_deleted = $this->delete_restriction( absint( $_GET['page_id'] ), str_replace(" ","+",trim( $_GET['email'] ) ) );
                wp_redirect( esc_url_raw( remove_query_arg( ['action', 'email', '_wpnonce'], esc_url_raw(add_query_arg( ['page_id' => $this->page_id, 'update' => 'quentn-access-deleted', 'deleted' => $num_records_deleted ] ) ) ) ) );
                exit;
            }
        }

        // If the delete bulk action is triggered
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'quentn-bulk-delete-access' )
            || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'quentn-bulk-delete-access' )
        ) {

            global $wpdb;

            $delete_restrict_pages_ids = esc_sql( $_REQUEST['quentn-bulk-delete-access'] );

            if( ! empty( $delete_restrict_pages_ids ) ) {
                //delete multiple accesses
                $query =  "DELETE FROM ".$wpdb->prefix . QUENTN_TABLE_NAME." where CONCAT_WS('|', page_id, email) IN ('".implode("', '", $delete_restrict_pages_ids)."')";
                $wpdb->query( $wpdb->query( $query ) );
                //add and remove items from a query string
                wp_redirect( esc_url_raw(remove_query_arg( ['action', 'action2', '_wpnonce', '_wp_http_referer', 'quentn-bulk-delete-access'], esc_url_raw(add_query_arg( ['page_id' => $this->page_id, 'update' => 'quentn-access-deleted', 'deleted' => count( $delete_restrict_pages_ids ) ] ) ) ) ) );
                exit;
            }
        }

        //process when access is added in wp
        if( isset( $_GET['update'] ) and $_GET['update'] == 'quentn-direct-access-add' ) {

            global $wpdb;
            if ( ! wp_verify_nonce($_REQUEST['qntn_direct_access_submit_nonce'] , 'qntn_direct_access_nonce' ) ) {
                die( 'Nope! Security check failed' );
            }
            $email = $_REQUEST['email_direct_access'];
            //if email is not valid, then display error message and redirect
            if ( ! filter_var($email, FILTER_VALIDATE_EMAIL ) ) {
                wp_redirect( esc_url_raw( add_query_arg( ['page_id' => $this->page_id, 'update' => 'quentn-direct-access-email-invalid' ] ) ) );
                exit;
            }
            //add/update access, if email address already exist, then only its creation date will be updated
            $wpdb->replace( $wpdb->prefix . QUENTN_TABLE_NAME,['page_id' => $this->page_id, 'email' => $email, 'email_hash' => hash( 'sha256', $email ), 'created_at' => time()], ['%d', '%s', '%s', '%d'] );
        }

    }
}

/**
 * Display access listing
 *
 * @since  1.0.0
 * @return void
 */

function quentn_show_data_access_overview_list() {

    //if someone try to access page without page id, then redirect to list of all restricted pages
    if( ! isset($_REQUEST['page_id'] ) ) {
        wp_redirect( admin_url( 'admin.php?page=quentn-access-pages-restrictions' ) );
    }

    $qntn_list_table = new Quentn_Wp_Access_Overview( $_REQUEST['page_id'] );
    $qntn_list_table->prepare_items();

    ?>

    <h3><?php  printf( __( 'Access Overview For Page %s', 'quentn-wp' ),  get_the_title($_REQUEST['page_id'] ) ); ?></h3>

    <form method="get">
        <?php  $qntn_list_table->search_box(__('Search Email', 'quentn-wp' ), "search_email_id");  ?>
        <input name='page' value="quentn-page-access-overview" type="hidden">
        <input name='page_id' value="<?php echo $_REQUEST['page_id'] ?>" type="hidden">
        <?php  $qntn_list_table->display(); ?>
    </form>
    <div id='qntn-add-access' style='display:none;'>
        <p>
        <form method="get" name="frm-email-direct-access" id="frm-email-direct-access" >
            <table>
                <tr>
                    <td><?php _e('Email', 'quentn-wp' ) ?></td>
                    <td><input required type="email"  name='email_direct_access' id='email_direct_access' size='25'>
                        <input name='page' type="hidden" value="<?php echo $_REQUEST['page'] ?>">
                        <input name='page_id' type="hidden" value="<?php echo $_REQUEST['page_id'] ?>">
                        <input name='update' type="hidden" value="quentn-direct-access-add">
                        <input type="hidden" value="<?php echo wp_create_nonce( 'qntn_direct_access_nonce' )?>" id="qntn_direct_access_submit_nonce" name="qntn_direct_access_submit_nonce">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" value="Add Access" class="button action" id="submit_email">
                    </td>
                </tr>
            </table>
        </form>
        </p>
    </div>

    <?php
}

quentn_show_data_access_overview_list();