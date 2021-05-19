<?php
/**
 * WooCoommerce integration
 * 
 * Specific functions and filters related with the WooCommerce sales platform
 * 
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle integration with WooCommerce
 * 
 * @package WPTAO/Integration
 * @category Class
 * 
 * @since 1.1.7
 * 
 */
class WTBP_WPTAO_Integration_WooCommerce {
	/*
	 * Product meta key
	 */

	public $product_key = 'woo_product_id';

	/*
	 * Order meta key
	 */
	public $order_key = 'woo_order_id';

	/*
	 * Quantity meta key
	 */
	public $quantity_key = 'woo_quantity';

	/*
	 * Constructor
	 */

	public function __construct() {

		add_action( 'admin_init', array( $this, 'check_currency' ) );

		add_filter( 'wptao_register_sales_platforms', array( $this, 'register_platform' ) );

		add_filter( 'wptao_report_mp_exclude_order', array( $this, 'exclude_orders' ), 10, 3 );
	}

	/*
	 * Check if WooCommerce is enabled
	 * 
	 * @since 1.2.1
	 * @return bool
	 */

	public function is_enabled() {
		return TAO()->diagnostic->is_woo_enabled();
	}

	/*
	 * Register WooCommerce
	 * 
	 * @since 1.2.1
	 * @param array $platforms
	 * @return array
	 */

	public function register_platform( $platforms ) {

		$platforms[ 'woocommerce' ] = array(
			'slug'			 => 'woocommerce',
			'name'			 => 'WooCommerce', // Equivalent 'sales_platform' in event meta key!
			'product_key'	 => $this->product_key,
			'order_key'		 => $this->order_key,
			'quantity_key'	 => $this->quantity_key,
			'enabled'		 => $this->is_enabled()
		);

		return $platforms;
	}

	/*
	 * Compare WP Tao and WooCommerce currencies
	 * Show admin notice if currencies are not the same.
	 */

	public function check_currency() {

		if ( !$this->is_enabled() ) {
			return;
		}

		$woo_currency = get_woocommerce_currency();

		if ( !empty( $woo_currency ) && $woo_currency !== TAO()->currency ) {

			add_action( 'admin_notices', array( $this, 'different_currencies' ) );
		}
	}

	/*
	 * Different curriencies admin notice
	 */

	public function different_currencies() {

		$woo_currency	 = '<b>' . get_woocommerce_currency() . '</b>';
		$tao_currency	 = '<b>' . TAO()->currency . '</b>';
		?>
		<div class="error">
			<p>
				<?php
				printf( __( 'WP Tao: The WooCommerce currency (%s) is different from the WP Tao currency (%s). Make up your mind on one currency!', 'wp-tao' ), $woo_currency, $tao_currency );
				?>
			</p>
		</div>
		<?php
	}

	/*
	 * Excludes orders from missed paymnets detection
	 * 
	 * Excluded statuses:
	 * 1. wc-on-hold (On Hold)
	 * 2. wc-completed (Completed)
	 * 3. wc-cancelled (Cancelled)
	 * 4. wc-refunded (Refunded)
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

			$status = get_post_field( 'post_status', $event_meta[ $this->order_key ] );

			if ( is_string( $status ) && !empty( $status ) ) {

				$statused_excluded = array(
					'wc-completed',
					'wc-cancelled',
					'wc-refunded',
					'wc-processing'
				);

				if ( in_array( $status, $statused_excluded ) ) {
					$exclude = true;
				}
			}
		}

		return $exclude;
	}

}
