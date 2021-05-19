<?php

/**
 * WP TAO Settings API data
 *
 * @package     WPTAO/Admin
 * @category    Admin
 */
if ( !class_exists( 'WTBP_WPTAO_Core_Settings' ) ) {

	class WTBP_WPTAO_Core_Settings {
		/*
		 * @var string
		 * Unique settings slug
		 */

		private $setting_slug = WTBP_WPTAO_SETTINGS_SLUG;

		/*
		 * @var object
		 * Settings API object
		 */
		public $settings_api;

		/**
		 * WTBP_WPTAO_Core_Settings Constructor.
		 */
		public function __construct() {
			global $wptao_settings;

			// Set global variable with settings
			$settings = get_option( $this->setting_slug );
			if ( !isset( $settings ) || empty( $settings ) ) {
				$wptao_settings = array();
			} else {
				$wptao_settings = $settings;
			}

			$this->settings_api = new WTBP_WPTAO_Settings_API( $this->setting_slug );

			add_action( 'admin_init', array( $this, 'settings_init' ) );
		}

		/*
		 * Set sections and fields
		 */

		public function settings_init() {

			//Set the settings
			$this->settings_api->set_sections( $this->settings_sections() );
			$this->settings_api->set_fields( $this->settings_fields() );

			//Initialize settings
			$this->settings_api->settings_init();
		}

		/*
		 * Set settings sections
		 * 
		 * @return array settings sections
		 */

		public function settings_sections() {
			$sections = array(
				array(
					'id'	 => 'wptao_general',
					'title'	 => __( 'General', 'wp-tao' )
				),
				array(
					'id'	 => 'wptao_maintenance',
					'title'	 => __( 'Maintenance', 'wp-tao' )
				),
				array(
					'id'	 => 'wptao_extensions',
					'title'	 => __( 'Extensions', 'wp-tao' )
				),
			);
			return apply_filters( 'wptao_settings_sections', $sections );
		}

		/**
		 * Create settings fields
		 *
		 * @return array settings fields
		 */
		function settings_fields() {
			$settings_fields = array(
				'wptao_general'		 => apply_filters( 'wptao_general_settings', array(
					array(
						'name'		 => 'currency',
						'label'		 => __( 'Currency', 'wp-tao' ),
						'desc'		 => __( 'Select the currency you use for sale.', 'wp-tao' ),
						'type'		 => 'select',
						'default'	 => 'EUR',
						'options'	 => WTBP_WPTAO_Helpers::get_currencies()
					),
					array(
						'name'		 => 'notice_email',
						'label'		 => __( 'Notice email', 'wp-tao' ),
						'desc'		 => __( 'Set the email for notifications from WP Tao.', 'wp-tao' ),
						'type'		 => 'text',
						'default'	 => ''
					),
					array(
						'name'		 => 'excluded_roles',
						'label'		 => __( 'Do not track users with specific roles', 'wp-tao' ),
						'type'		 => 'multicheck',
						'default'	 => '',
						'options'	 => WTBP_WPTAO_Helpers::get_users_roles()
					),
					array(
						'name'		 => 'uninstall_wipe',
						'label'		 => __( 'Wipe data when uninstalling', 'wp-tao' ),
						'desc'		 => __( 'Checking this option will delete all events and users when uninstalling the plugin via Installed plugins &#8594; WP Tao &#8594; Delete', 'wp-tao' ),
						'type'		 => 'checkbox',
						'default'	 => ''
					),
					array(
						'name'		 => 'exclude_blacklist',
						'label'		 => __( 'Exclude blacklist', 'wp-tao' ),
						'desc'		 => __( 'Check this box if you would like to exclude blacklisted users from the user list', 'wp-tao' ),
						'type'		 => 'checkbox',
						'default'	 => ''
					)
				) ),
				'wptao_maintenance'	 => apply_filters( 'wptao_maintenance_settings', array(
					array(
						'name'	 => 'db_header',
						'label'	 => __( '<h2>Database</h2>', 'wp-tao' ),
						'type'	 => 'html'
					),
					array(
						'name'	 => 'db_size',
						'label'	 => __( 'WP Tao database size', 'wp-tao' ),
						'desc'	 => WTBP_WPTAO_Helpers::get_db_size( 'formatted' ),
						'type'	 => 'html'
					),
					array(
						'name'		 => 'db_limited_events',
						'label'		 => __( 'Events with limited storage time', 'wp-tao' ),
						'desc'		 => __( 'Select which events will have <b>limited</b> storage time. Unselected items will be stored for unlimited time.', 'wp-tao' ),
						'type'		 => 'multicheck',
						'options'	 => WTBP_WPTAO_Helpers::get_event_action_options()
					),
					array(
						'name'		 => 'db_storage_time',
						'label'		 => __( 'Storage time for selected events', 'wp-tao' ),
						'desc'		 => __( 'How long keep selected events in the database?', 'wp-tao' ),
						'type'		 => 'select',
						'default'	 => '90',
						'options'	 => WTBP_WPTAO_Helpers::get_storage_time_options()
					)
				) )
			);


			return apply_filters( 'wptao_settings', $settings_fields );
		}

		/**
		 * Handles output of the settings
		 */
		public static function output() {

			$settings = TAO()->settings->settings_api;

			include_once WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-settings.php';
		}

	}

}