<?php

/**
 * WP Tao Popups
 * Author: WP Tao Co.
 * Author URI: https://wptao.net
 * 
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WTBP_247_Popup_Core' ) ) {

	final class WTBP_247_Popup_Core {

		private static $instance;
		private $tnow;
		public $settings;
		public $popups = array();

		public static function get_instance() {
			if ( !isset( self::$instance ) && !( self::$instance instanceof WTBP_247_Popup_Core ) ) {
				self::$instance = new WTBP_247_Popup_Core;
				self::$instance->constants();
				self::$instance->start_session();
				self::$instance->includes();
				self::$instance->hooks();
			}
			self::$instance->tnow = time();

			return self::$instance;
		}

		/**
		 * Constructor Function
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Setup plugin constants
		 */
		private function constants() {


			$this->define( 'WTBP_247P_FILE', __FILE__ );
			$this->define( 'WTBP_247P_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'WTBP_247P_URL', plugin_dir_url( __FILE__ ) );

			$this->define( 'WTBP_247P_POST_TYPE', 'wtbp_247p_popup' );

			$this->define( 'WTBP_247P_COOKIE_PREFIX', 'wtbp_247p_' );

			$this->define( 'WTBP_247P_POPUP_META_KEY', 'wtbp-247p-settings' );

			$this->define( 'WTBP_247P_DEBUG', true );
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( !defined( $name ) ) {
				define( $name, $value );
			}
		}

		/*
		 * Start PHP sesssion
		 */

		private function start_session() {
			if ( !session_id() )
				session_start();
		}

		/**
		 * Include required core files.
		 */
		public function includes() {

			require_once WTBP_247P_DIR . 'includes/functions.php';

			require_once WTBP_247P_DIR . 'includes/admin/settings.php';

			require_once WTBP_247P_DIR . 'includes/admin/register-popup.php';

			require_once WTBP_247P_DIR . 'includes/class-popup.php';

			require_once WTBP_247P_DIR . 'includes/ajax.php';

			require_once WTBP_247P_DIR . 'includes/events/display-popup.php';

			require_once WTBP_247P_DIR . 'includes/events/success-popup.php';
		}

		/**
		 * Actions and filters
		 */
		private function hooks() {

			add_action( 'admin_init', array( $this, 'admin_scripts' ) );

			add_action( 'template_redirect', array( $this, 'print_listeners' ), 5 );
		}

		/*
		 * Enqueue admin sripts
		 */

		public function admin_scripts() {

			if (
			(isset( $_GET[ 'post' ] ) && isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'edit' && WTBP_247P_POST_TYPE === get_post_type( $_GET[ 'post' ] )) ||
			(isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] === WTBP_247P_POST_TYPE)
			) {
				wp_enqueue_style( 'wp-color-picker' );


				// Register CSS
				wp_register_style( 'wtbp-247p-admin-style', WTBP_247P_URL . 'assets/css/admin-style.css', array(), WTBP_WPTAO_VERSION );

				// Enqueue CSS            
				wp_enqueue_style( array(
					'wtbp-247p-admin-style'
				) );

				// Register JS
				wp_register_script( 'wtbp-247p-admin-js', WTBP_247P_URL . 'assets/js/247popup-admin.js', array( 'wp-color-picker' ), WTBP_WPTAO_VERSION );

				// Enqueue JS          
				wp_enqueue_script( 'wtbp-247p-admin-js' );
			}
		}

		/*
		 * Print JS listeners
		 */

		public function print_listeners() {
			if ( !defined( 'WTBP_247P_LISTENERS_PRINTED' ) ) {

				foreach ( wtbp_247p_get_all_public_popups() as $popup ) {
					$this->popups[ $popup->ID ] = new WTBP_247P_Popup( $popup->ID );
					$this->popups[ $popup->ID ]->print_listeners();
				}
			}

			define( 'WTBP_247P_LISTENERS_PRINTED', true );
		}

	}

// Init the plugin
	function WTBP_247P() {
		return WTBP_247_Popup_Core::get_instance();
	}

	add_action( 'init', 'WTBP_247P' );
}