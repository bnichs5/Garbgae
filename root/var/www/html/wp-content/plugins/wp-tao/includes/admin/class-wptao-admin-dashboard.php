<?php

/**
 * WP Tao dashboard
 *
 * The class handles control dashboard views.
 *
 * @package     WPTAO/Admin/Dashboard
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


if ( !class_exists( 'WTBP_WPTAO_Admin_Dashboard' ) ) {

	/**
	 * WTBP_WPTAO_Admin_Dashboard Class
	 */
	class WTBP_WPTAO_Admin_Dashboard {
		/*
		 * Current report slug
		 */

		public $current_report;

		/*
		 * @var array Reports
		 */
		public $reports = array();

		/*
		 * @var array Widgets
		 */
		public $widgets = array();

		/**
		 * WTBP_WPTAO_Admin_Dashboard Constructor
		 * 
		 */
		function __construct() {

			$this->includes();

			add_action( 'admin_init', array( $this, 'register_core_reports' ), 100 );

			add_action( 'admin_init', array( $this, 'receive_form_options_screen' ), 200 );

			add_action( 'admin_init', array( $this, 'set_current_raport' ), 190 );

			add_action( 'wp_ajax_wptao_dashboard_order', array( $this, 'ajax_save_dashboard_tiles' ) );
			add_action( 'wp_ajax_wptao_hide_escpresso_report', array( $this, 'ajax_hide_espresso_report' ) );
		}

		/*
		 * Includes raports files
		 */

		private function includes() {

			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-visitors.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-frequency-search.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-most-visited-pages.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-sale-total.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-sale-user.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-abandoned-carts.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-missed-payments.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-logins.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-contact.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-user-register.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-user-identified-contacts.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-traffic-sources.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/reports/class-basic-sales-campaigns.php';
		}

		/*
		 * Set a current raport
		 *
		 */

		public function set_current_raport() {

			if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'wtbp-wptao' && isset( $_GET[ 'wptao-report' ] ) && !empty( $_GET[ 'wptao-report' ] ) ) {

				$report_slug = sanitize_title( $_GET[ 'wptao-report' ] );

				foreach ( $this->reports as $raport ) {
					if ( $report_slug === $raport->report_slug ) {

						$this->current_report = $report_slug;
					}
				}
			}
		}

		/*
		 * Initialize reports objects
		 * 
		 * Internal method of registration reports.
		 */

		public function register_core_reports() {

			//Default reports
			$core_reports = array(
				'visitors'						 => 'WTBP_WPTAO_Admin_Report_Visitors',
				'frequency-search'				 => 'WTBP_WPTAO_Admin_Report_Frequency_Search',
				'most-visited-pages'			 => 'WTBP_WPTAO_Admin_Report_Most_Visited_Pages',
				'basic-sale-total'				 => 'WTBP_WPTAO_Admin_Report_Basic_Sale_Total',
				'basic-sale-user'				 => 'WTBP_WPTAO_Admin_Report_Basic_Sale_User',
				'basic-abandoned-carts'			 => 'WTBP_WPTAO_Admin_Report_Basic_Abandoned_Carts',
				'basic-missed-payments'			 => 'WTBP_WPTAO_Admin_Report_Missed_Payments',
				'logins'						 => 'WTBP_WPTAO_Admin_Report_Logins',
				'basic-contact'					 => 'WTBP_WPTAO_Admin_Report_Basic_Contact',
				'basic-user-register'			 => 'WTBP_WPTAO_Admin_Report_Basic_User_Register',
				'basic-user-identified-contacts' => 'WTBP_WPTAO_Admin_Report_Basic_User_Identified_Contacts',
				'basic-traffic-sources'			 => 'WTBP_WPTAO_Admin_Report_Basic_Traffic_Sources',
				'basic-sales-campaigns'			 => 'WTBP_WPTAO_Admin_Report_Basic_Sales_Campaigns',
			);

			foreach ( $core_reports as $report_slug => $report_class ) {
				$this->register_report( $report_slug, $report_class );
			}
		}

		/*
		 * Register a signle report 
		 * 
		 * External method of registration reports. Allows to register reports from external plugins
		 * 
		 * @see wptao_register_report()
		 * 
		 * @param string $report_slug 
		 * @param string $report_class The name of a class that extends WTBP_WPTAO_Admin_Reports
		 */

		public function register_report( $report_slug, $report_class ) {

			$report_slug = sanitize_title( $report_slug );

			if ( TAO()->booleans->is_page_dashboard || WTBP_WPTAO_Admin_Reports::is_report( $report_slug ) ) {

				$this->reports[ $report_slug ] = new $report_class( $report_slug );
			}
		}

		/*
		 * Register widgets (espresso reports) on the WP Tao dashboard
		 * 
		 * @param array
		 * 
		 * @return NULL
		 */

		public function register_widget( $args ) {

			$defaults = array(
				'id'			 => '',
				'size'			 => 'small', // small | middle | middle-col | big
				'title'			 => '',
				'category'		 => '',
				'css_class'		 => '',
				'report_slug'	 => '', // Add link to a report (based on slug)
				'report_link'	 => '',
				'report_text'	 => __( 'See a complete report', 'wp-tao' ), // Add report link anchor.
				'priority'		 => 50,
				'value_text'	 => '',
				'value_number'	 => '',
				'dashicon'		 => '',
				'custom_html'	 => '', // Overwrites all HTML after a widget wrapper
				'visibility'	 => 'show'
			);

			$args	 = wp_parse_args( $args, $defaults );
			$args	 = (object) $args;

			$widget_id = sanitize_title( $args->id );

			if ( strlen( $widget_id ) > 0 ) {

				// Check visibility
				$visibility = get_option( 'wptao_hidden_widgets' );
				if ( !empty( $visibility ) && is_array( $visibility ) && in_array( $widget_id, $visibility ) ) {
					$args->visibility = 'hidden';
				}


				$this->widgets[] = apply_filters( 'wptao_register_widget-' . $widget_id, $args );
			}
		}

		/*
		 * Display widgets
		 */

		public function the_widgets() {

			if ( !empty( $this->widgets ) ) {

				$widgets = $this->order_widgets();

				foreach ( $widgets as $widget ) {

					$total_hints = absint( TAO()->hints->count_widget_hints( $widget->id ) );

					$html = '';

					// Widget size
					switch ( $widget->size ) {
						case 'middle':
							$size_class	 = 'wptao-dbox-md';
							break;
						case 'middle-col':
							$size_class	 = 'wptao-dbox-md-col';
							break;
						case 'big':
							$size_class	 = 'wptao-dbox-lg';
							break;
						default:
							$size_class	 = 'wptao-dbox-sm';
					}

					// Set report link
					$report_link = '';
					if ( !empty( $widget->report_slug ) ) {
						$report_link = $this->reports[ $widget->report_slug ]->report_url;
					} else if ( !empty( $widget->report_link ) ) {
						$report_link = $widget->report_link;
					}

					// Prepare HTML
					$html .= sprintf( '<div id="wptao-dbox-%1$s" class="wptao-dbox %2$s %3$s wptao-dbox-%4$s" data-id="%1$s" data-category="%4$s">', sanitize_title( $widget->id ), $size_class, sanitize_title( $widget->css_class ), sanitize_title( $widget->category ) );
					$html .= '<div class="wptao-dbox-inner">';

					//Background icon
					if ( !empty( $widget->dashicon ) ) {
						$html .= '<span class="dashicons wptao-dbox-icon ' . sanitize_title( $widget->dashicon ) . '"></span>';
					}

					$html .= '<div class="wptao-dbox-align-inner">';
					if ( !empty( $widget->custom_html ) ) {
						$html .= wp_kses_post( $widget->custom_html );
					} else {
						$html .= sprintf( '<h3 class="wptao-dbox-title">%s</h3>', wp_kses_post( $widget->title ) );

						if ( !empty( $widget->value_text ) ) {
							$html .= sprintf( '<div class="wptao-dbox-value">%s</div>', wp_kses_post( $widget->value_text ) );
						}

						if ( is_numeric( $widget->value_number ) ) {
							$html .= sprintf( '<div class="wptao-dbox-number">%s</div>', wp_kses_post( $widget->value_number ) );
						}
					}

					$html .= '</div>';
					$html .= '</div>';
					if ( !empty( $report_link ) ) {

						if ( $total_hints > 0 ) {
							$html .= sprintf( '<a href="%s" title="%s" class="wptao-dbox-num-hints"><span class="dashicons dashicons-lightbulb"></span><span class="wpta-hints-num">%d</span></a>', esc_url( $report_link ), __( 'Show hints', 'wp-tao' ), $total_hints );
						}

						$html .= sprintf( '<a href="%s" title="%s" class="wptao-dbox-report"><span class="dashicons dashicons-chart-bar"></span></a>', esc_url( $report_link ), sanitize_text_field( $widget->report_text ) );
					}

					// Draggable handler
					$html .= '<span class="wptao-dbox-handler dashicons dashicons-move"></span>';

					// Draggable handler
					$html .= '<span data-nonce="' . wp_create_nonce( 'hide-' . sanitize_title( $widget->id ) ) . '" title="' . __( 'Hide espresso report', 'wp-tao' ) . '" class="wptao-dbox-close dashicons dashicons-no-alt"></span>';

					$html .= '</div>';

					if ( $widget->visibility === 'show' ) {
						echo $html;
					}
				}
			}
		}

		/*
		 * Order widgets by priority
		 */

		private function order_widgets() {

			$widgets		 = array();
			$custom_order	 = get_option( 'wptao_dashboard_tiles_order' );

			if ( !empty( $this->widgets ) && is_array( $this->widgets ) ) {

				$widgets = $this->widgets;

				if ( !empty( $custom_order ) && is_array( $custom_order ) ) {

					$i = 0;
					foreach ( $widgets as $widget ) {

						$priority = array_search( $widget->id, $custom_order );

						if ( is_numeric( $priority ) && $priority > 0 ) {
							$widgets[ $i ]->priority = absint( $priority );
						}

						$i++;
					}
				}



				usort( $widgets, array( 'WTBP_WPTAO_Admin_Dashboard', 'order_widgets_comparison' ) );
			}

			return $widgets;
		}

		/*
		 * The comparison callback for a usort() function
		 * @see $this->order_widgets(), usort()
		 */

		private function order_widgets_comparison( $a, $b ) {

			if ( isset( $a->priority ) && isset( $b->priority ) ) {

				if ( $a->priority == $b->priority ) {
					return 0;
				}
				return ($a->priority < $b->priority) ? -1 : 1;
			}

			return 0;
		}

		/**
		 * Handles output of the dashboard
		 */
		public function output() {

			// Report processes
			if ( !empty( $this->current_report ) ) {

				if ( isset( $this->reports[ $this->current_report ] ) ) {
					$this->reports[ $this->current_report ]->output();
					return;
				}
			}

			// No correct a report's name? Show WP Tao dashboard.
			include_once( WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-dashboard.php' );
			return;
		}

		/*
		 * Receive form with options screen and save if necessary
		 * 
		 * @since 1.1.4
		 * 
		 * @return NULL
		 */

		public function receive_form_options_screen() {

			// Form was sent
			if ( isset( $_POST[ 'wptao-screen-options-apply' ] ) && isset( $_POST[ 'wptao_screen_options_nonce' ] ) ) {

				// Security check
				if ( wp_verify_nonce( $_POST[ 'wptao_screen_options_nonce' ], 'wptao_screen_options' ) !== false ) {

					$hidden = array();

					if ( !empty( $this->widgets ) ) {
						foreach ( $this->widgets as $widget ) {

							if ( !isset( $_POST[ 'wptao-screen-opt-' . $widget->id ] ) || $_POST[ 'wptao-screen-opt-' . $widget->id ] !== $widget->id ) {
								$hidden[] = sanitize_title( $widget->id );
							}
						}
					}

					update_option( 'wptao_hidden_widgets', $hidden );

					wp_redirect( admin_url( 'admin.php?page=wtbp-wptao' ) );
					exit;
				}
			}
		}

		/*
		 * Includes screen options panel (HTML)
		 * 
		 * @since 1.1.4
		 * 
		 * @return NULL
		 */

		private function the_screen_options() {
			include_once( WTBP_WPTAO_DIR . 'includes/admin/views/elements/dashboard-screen-options.php' );
		}

		/*
		 * Save dashboard tiles order by AJAX
		 * @since 1.2.3
		 */

		public function ajax_save_dashboard_tiles() {

			if ( isset( $_REQUEST[ 'token' ] ) && isset( $_REQUEST[ 'order' ] ) ) {
				$token	 = $_REQUEST[ 'token' ];
				$order	 = $_REQUEST[ 'order' ];

				if ( is_string( $order ) && !empty( $order ) && current_user_can( 'manage_options' ) && wp_verify_nonce( $_REQUEST[ 'token' ], 'wptao-dashboard-order' ) ) {

					$result = json_decode( stripslashes( $order ) );

					if ( is_array( $result ) && !empty( $result ) ) {

						$final = array();
						foreach ( $result as $priority => $widget_slug ) {
							$index = 45 + absint( $priority );

							$final[ $index ] = sanitize_title( $widget_slug );
						}

						update_option( 'wptao_dashboard_tiles_order', $final );

						echo '1';
						die();
					}
				}
			}

			echo '-1';
			die();
		}

		/*
		 * Hide espresso reports by ajax
		 * @since 1.2.4
		 */

		public function ajax_hide_espresso_report() {

			if ( isset( $_REQUEST[ 'nonce' ] ) && isset( $_REQUEST[ 'espresso_id' ] ) ) {
				$nonce		 = $_REQUEST[ 'nonce' ];
				$espresso_id = sanitize_title( $_REQUEST[ 'espresso_id' ] );

				if (
				!empty( $espresso_id ) &&
				current_user_can( 'manage_options' ) &&
				wp_verify_nonce( $nonce, 'hide-' . $espresso_id )
				) {


					$hidden_items = get_option( 'wptao_hidden_widgets', array() );

					if ( is_array( $hidden_items ) ) {
						if ( !in_array( $espresso_id, $hidden_items ) ) {

							$hidden_items[] = $espresso_id;

							update_option( 'wptao_hidden_widgets', $hidden_items );

							echo '1';
							die();
						}
					}
				}
			}

			echo '-1';
			die();
		}

	}

}