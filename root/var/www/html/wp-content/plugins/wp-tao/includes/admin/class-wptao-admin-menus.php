<?php

/**
 * Setup menus in WP admin.
 *
 * @package     WPTAO/Admin
 * @category    Admin
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'WTBP_WPTAO_Admin_Menus' ) ) :

	/**
	 * WTBP_WPTAO_Admin_Menus Class
	 */
	class WTBP_WPTAO_Admin_Menus {

		/**
		 * WTBP_WPTAO_Admin_Menus Constructor.
		 */
		public function __construct() {

			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
			add_action( 'admin_menu', array( $this, 'users_menu' ), 20 );
			add_action( 'admin_menu', array( $this, 'events_menu' ), 30 );
			add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
			add_action( 'admin_menu', array( $this, 'addons_menu' ), 80 );
			add_action( 'admin_menu', array( $this, 'upgrades_menu' ), 90 );


			add_filter( 'admin_body_class', array( $this, 'add_body_class' ) );

			add_action( 'admin_init', array( $this, 'init_users_list' ), 5 );
		}

		/**
		 * Add general menu items
		 */
		public function admin_menu() {

			if ( current_user_can( 'view_wptao_reports' ) ) {
				add_menu_page( __( 'WP Tao', 'wp-tao' ), __( 'WP Tao', 'wp-tao' ), 'view_wptao_reports', 'wtbp-wptao', array( $this, 'dashboard_page' ), WTBP_WPTAO_URL . '/assets/images/wptao-ico-16.png', '50' );
			}

			add_submenu_page( 'wtbp-wptao', __( 'Dashboard', 'wp-tao' ), __( 'Dashboard', 'wp-tao' ), 'manage_options', 'wtbp-wptao', array( $this, 'dashboard_page' ) );
		}

		/**
		 * Add the users submenu item
		 */
		public function users_menu() {

			add_submenu_page( 'wtbp-wptao', __( 'Identified', 'wp-tao' ), __( 'Identified', 'wp-tao' ), 'view_wptao_reports', 'wtbp-wptao-users', array( $this, 'users_page' ) );
		}

		/**
		 * Add the events submenu item
		 */
		public function events_menu() {

			add_submenu_page( 'wtbp-wptao', __( 'Events', 'wp-tao' ), __( 'Events', 'wp-tao' ), 'view_wptao_reports', 'wtbp-wptao-events', array( $this, 'events_page' ) );
		}

		/**
		 * Add the settings submenu item
		 */
		public function settings_menu() {

			add_submenu_page( 'wtbp-wptao', __( 'WP Tao Settings', 'wp-tao' ), __( 'Settings', 'wp-tao' ), 'manage_options', 'wtbp-wptao-settings', array( $this, 'settings_page' ) );
		}

		/**
		 * Add the upgrades page
		 */
		public function upgrades_menu() {

			add_submenu_page( null, __( 'WP Tao Upgrades', 'wp-tao' ), __( 'WP Tao Upgrades', 'wp-tao' ), 'manage_options', 'wtbp-wptao-upgrades', array( $this, 'upgrades_page' ) );
		}

		/**
		 * Add an addons menu item
		 */
		public function addons_menu() {
			global $submenu;

			$class		 = 'wptao-addons-link';
			$pend_count	 = 5;

			$target = 'addons/?utm_source=plugin&utm_medium=addon_page&utm_campaign=wptao_addons';

			$url = WTBP_WPTAO_Helpers::get_wptao_url( $target );

			$submenu[ 'wtbp-wptao' ][] = array( '<span style="color:#f39c12;">' . __( 'Addons', 'wp-tao' ) . '</span> <span class="update-plugins count-' . $pend_count . '"><span class="plugin-count">' . $pend_count . '</span></span>', 'manage_options', $url, __( 'Addons', 'wp-tao' ), $class );
		}

		/**
		 * Init the dashboard page
		 */
		public function dashboard_page() {
			TAO()->dashboard->output();
		}

		/**
		 * Init the events page
		 */
		public function events_page() {
			TAO()->admin_events->output();
		}

		/**
		 * Init the users page
		 */
		public function users_page() {
			WTBP_WPTAO_Admin_Users::output();
		}

		/**
		 * Init the settings page
		 */
		public function settings_page() {
			WTBP_WPTAO_Core_Settings::output();
		}

		/**
		 * Init the upgrades page
		 */
		public function upgrades_page() {
			require_once WTBP_WPTAO_DIR . 'includes/admin/upgrades/upgrades.php';
		}

		/**
		 * Init the addons page
		 */
		public function addons_page() {
			require_once WTBP_WPTAO_DIR . 'includes/admin/addons/addons.php';
		}

		/*
		 * Adds css class to the body
		 */

		public function add_body_class( $classes ) {

			if ( TAO()->booleans->is_page_profile ) {

				$classes = $classes . 'wptao-user-profile';
			}

			return $classes;
		}

		/*
		 * Init users list object
		 */

		public function init_users_list() {
			global $wptao_users_list;

			if ( TAO()->booleans->is_page_users ) {
				$wptao_users_list = new WTBP_WPTAO_Users_List_Table();
			}
		}

	}

	endif;

return new WTBP_WPTAO_Admin_Menus();
