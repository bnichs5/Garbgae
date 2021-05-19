<?php

/**
 * Uninstall WP Tracker and Optimizer 
 *
 * @package 
 * @category Core
 * @since      1.0
 */
// Exit if accessed directly
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

// Load WP Tao file
include_once( 'wp-tao.php' );

global $wpdb, $wptao_settings;

if ( isset( $wptao_settings[ 'uninstall_wipe' ] ) && !empty( $wptao_settings[ 'uninstall_wipe' ] ) ) {


	// Delete all the Plugin Options
	delete_option( WTBP_WPTAO_SETTINGS_SLUG );
	delete_option( 'wptao_version' );
	delete_option( $wpdb->prefix . 'wptao_events_db_version' );
	delete_option( $wpdb->prefix . 'wptao_events_meta_db_version' );
	delete_option( $wpdb->prefix . 'wptao_events_tags_db_version' );
	delete_option( $wpdb->prefix . 'wptao_fingerprints_db_version' );
	delete_option( $wpdb->prefix . 'wptao_users_db_version' );
	delete_option( $wpdb->prefix . 'wptao_users_meta_db_version' );
	delete_option( 'wptao_completed_upgrades' );
	delete_option( 'wptao_mail_notice_dissmis' );
	delete_option( 'wptao_subscribed' );
	delete_option( 'wptao_currency_set' );
	delete_option( 'wptao_hidden_widgets' );
	delete_option( 'wptao_dashboard_tiles_order' );
	delete_option( 'wptao_promobox_custom_work_dissmis' );



	// Remove all database tables
	$wpdb->query( 'DROP TABLE IF EXISTS ' . TAO()->events->table_name );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . TAO()->events_meta->table_name );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . TAO()->events_tags->table_name );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . TAO()->fingerprints->table_name );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . TAO()->users->table_name );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . TAO()->users_meta->table_name );
}
