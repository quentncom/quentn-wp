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
        $total_items = $this->get_total_restricted_pages_count();

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
        $title = '<strong>' . esc_html($item['page_title']) . '</strong>';
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
    public function get_quentn_restrictions($per_page = 20, $page_number = 1) {
        $restricted_pages = $this->get_restricted_pages($per_page, $page_number);

        if(empty($restricted_pages)) {
            return array();
        }

        $result = array();
        $restricted_pages_ids = wp_list_pluck($restricted_pages, 'ID');
        $number_of_access_links = $this->access_links_count($restricted_pages_ids);

        foreach($restricted_pages as $restricted_page) {
            $quentn_post_restrict_meta = get_post_meta($restricted_page->ID, '_quentn_post_restrict_meta', true);

            $restriction_type = (isset($quentn_post_restrict_meta['countdown']) && $quentn_post_restrict_meta['countdown'])
                ? __('CountDown', 'quentn-wp')
                : __('Access', 'quentn-wp');

            $result[] = array(
                "page_id"            => $restricted_page->ID,
                "page_title"         => $restricted_page->post_title,
                "restriction_type"   => $restriction_type,
                "total_access_links" => isset($number_of_access_links[$restricted_page->ID])
                    ? $number_of_access_links[$restricted_page->ID]
                    : 0,
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
                'page_id' => sanitize_text_field( $_GET['page_id'] ),
            ),
                admin_url( 'admin.php?page=quentn-page-access-overview' )
            ));
        }

    }

    /**
     * Get restricted pages list with pagination support
     *
     * @since  1.2.8
     * @access public
     * @param int $per_page Number of items per page
     * @param int $page_number Current page number
     * @return array
     */
    public function get_restricted_pages($per_page = 20, $page_number = 1) {
        $args = array(
            'post_type'      => 'page',
            'meta_key'       => '_quentn_post_restrict_meta',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'posts_per_page' => $per_page,
            'paged'          => $page_number,
            'fields'         => 'all', // Get full post objects
        );

        //If WPML Multilingual CMS is active, suppress filters so that we can return pages of all languages
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            $args['suppress_filters'] = true;
        }

        $restricted_pages_query = new WP_Query($args);
        return $restricted_pages_query->posts;
    }

    /**
     * Get total count of restricted pages
     *
     * @return int
     */
    public function get_total_restricted_pages_count() {
        $args = array(
            'post_type'  => 'page',
            'meta_key'   => '_quentn_post_restrict_meta',
            'fields'     => 'ids',
            'posts_per_page' => -1,
        );

        //If WPML Multilingual CMS is active, suppress filters so that we can return pages of all languages
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            $args['suppress_filters'] = true;
        }

        $query = new WP_Query($args);
        return $query->found_posts;
    }
}

/**
 * Display all restriced pages listing
 *
 * @since  1.0.0
 * @return void
 */
function quentn_show_data_page_restrictions_list() {
    $qntn_list_table = new Quentn_Wp_Page_Restrictions_List();

    // Get current screen
    $screen = get_current_screen();

    // Add screen option if not already added
    if (!empty($screen) && !$screen->get_option('per_page')) {
        add_screen_option('per_page', [
            'default' => 20,
            'option' => 'quentn_restricted_records_per_page',
            'label' => __('Records per page', 'quentn-wp')
        ]);
    }

    echo '<div class="wrap">';
    echo '<h1>' . __('List of Pages With Limited Access', 'quentn-wp') . '</h1>';
    $qntn_list_table->prepare_items();
    $qntn_list_table->display();
    echo '</div>';
}

quentn_show_data_page_restrictions_list();

