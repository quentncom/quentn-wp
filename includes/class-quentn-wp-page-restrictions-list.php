<?php

if (!defined('ABSPATH')) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Quentn_Wp_Page_Restrictions_List extends \WP_List_Table {

    public function __construct() {

        parent::__construct(array(
            'singular' => __( 'Page', 'quentn-wp' ),
            'plural'   => __( 'Pages', 'quentn-wp' ),
        ));
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

        //get sortable columns
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
        $per_page     = $this->get_items_per_page( 'quentn_restricted_records_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items  = count( Helper::get_restriction_activated_pages() );

        $this->set_pagination_args( array(
            "total_items" => $total_items,
            "per_page"    => $per_page
        ));

        $this->items = $this->get_quentn_restrictions( $per_page, $current_page );
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
            'page_title'         => __( 'Page Title', 'quentn-wp' ),
            'restriction_type'   => __( 'Restriction Type', 'quentn-wp' ),
            'total_access_links' => __( 'Access Links', 'quentn-wp' ),
            'show_access'        => '',
        );

        return $columns;
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
        switch ( $column_name ) {
            case 'page_title':
            case 'restriction_type':
            case 'total_access_links':
                return $item[$column_name];
            case 'show_access':
                return sprintf( '<a href="?page=%s&page_id=%s">%s</a>', esc_attr( 'quentn-page-access-overview' ), absint( $item['page_id'] ), __( "Show Access", 'quentn-wp' ));
            default:
                return __( "no value", 'quentn-wp' );
        }
    }

    /**
     * Filter page title column
     *
     * @since  1.0
     * @param  object  $item
     * @return string
     */
    public function column_page_title( $item ) {
        $title = '<strong>' . $item['page_title'] . '</strong>';
        $actions = array(
            'show-access' => sprintf( '<a href="?page=%s&page_id=%s">%s</a>', esc_attr( 'quentn-page-access-overview' ), absint( $item['page_id'] ), __( "Show Access", 'quentn-wp' ) )
        );

        return $title . $this->row_actions( $actions );
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {

        $sortable_columns = array(
            'page_title'         => array( 'page_title', true ),
            'restriction_type'   => array( 'restriction_type', true ),
            'total_access_links' => array( 'total_access_links', true ),
        );

        return $sortable_columns;
    }

    /**
     * Retrieve restrictions data from the database
     *
     * @param int $per_page
     * @param int $page_number
     * @return array
     */
    public function get_quentn_restrictions( $per_page = 20, $page_number = 1 ) {

        $restriction_activated_pages = Helper::get_restriction_activated_pages();
        if( count( $restriction_activated_pages ) < 1 ) {
            return array();
        }

        //set order by
        $args = array();
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sort_column  = ( $_REQUEST['orderby'] == 'page_title' ) ? 'post_title' : esc_sql( $_REQUEST['orderby'] );

            $sort_order  = ! empty( $_REQUEST['order'] ) ? esc_sql( $_REQUEST['order'] ) : 'asc';
            $args = array(
                'sort_order'  => $sort_order,
                'sort_column' => $sort_column,
            );
        }

        //add args to set pagination
        $args['number']   = $per_page;

        $args['offset']   = ( $page_number - 1 ) * $per_page;

        $args['include']  = $restriction_activated_pages;

        $restricted_pages= get_pages( array(
            'meta_key' => '_quentn_post_restrict_meta'
        ) );

        $result = array();

        //get number of access links for all restricted pages
        $number_of_access_links = $this->access_links_count( Helper::get_restriction_activated_pages() );

        foreach( $restricted_pages as $restricted_page )
        {
            //get meta of page
            $quentn_post_restrict_meta = get_post_meta( $restricted_page->ID, '_quentn_post_restrict_meta', true );

            $restriction_type = ( isset( $quentn_post_restrict_meta['countdown'] ) && $quentn_post_restrict_meta['countdown'] ) ? __( 'CountDown', 'quentn-wp' ): __( 'Access', 'quentn-wp' );

            $result[] = array(
                "page_id"            => $restricted_page->ID,
                "page_title"         => $restricted_page->post_title,
                "restriction_type"   => $restriction_type,
                "total_access_links" => ( isset( $number_of_access_links[$restricted_page->ID] ) )? $number_of_access_links[$restricted_page->ID] : 0 ,
            );
        }
        return $result;
    }

    /**
     * Returns the count of records in the database.
     *
     * @param array $page_ids Page ids
     * @return array
     */
    public function access_links_count( $page_ids ) {
        global $wpdb;

        $sql = "SELECT page_id, COUNT(*) as totoal_access FROM ". $wpdb->prefix . TABLE_QUENTN_RESTRICTIONS. " where page_id IN (".implode(",",$page_ids).")  GROUP BY page_id";

        $rows = $wpdb->get_results( $sql );
        $pages = array();
        foreach ( $rows as $row ) {
            $pages[$row->page_id] =  $row->totoal_access;
        }
        return $pages;
    }

    /**
     * Text for no members
     *
     * @since  1.0
     * @return void
     */
    public function no_items() {
        _e( 'To create access restricted pages, please check your page\'s settings.', 'quentn-wp' );
    }

    /**
     * process bulk actions
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function process_bulk_action() {

        if ( 'show-access' === $this->current_action() ) {

            wp_redirect( add_query_arg( array(
                'page_id' => $_GET['page_id'],
            ),
                admin_url( 'admin.php?page=quentn-page-access-overview' )
            ));
        }

    }
}

/**
 * Display all restriced pages listing
 *
 * @since  1.0.0
 * @return void
 */
function quentn_show_data_page_restrictions_list() {
    ?>
    <h3><?php _e( 'List of Pages With Limited Access', 'quentn-wp' ); ?></h3>
    <?php
    $qntn_list_table = new Quentn_Wp_Page_Restrictions_List();
    $qntn_list_table->prepare_items();
    $qntn_list_table->display();
}

quentn_show_data_page_restrictions_list();

