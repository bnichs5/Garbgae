<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * WPTAO DB base class base on:
 * https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/d42f8686fb74ef3aedbaa816a6212938237da977/includes/class-edd-db.php
 *
 * @package WPTAO/Classes
 * @category Class
 */
abstract class WTBP_WPTAO_DB {

	/**
	 * Current timestamp
	 */
	protected $ts_now;

	/**
	 * The name of our database table
	 *
	 * @access  public
	 */
	public $table_name;

	/**
	 * The version of our database table
	 *
	 * @access  public
	 */
	public $version;

	/**
	 * The name of the primary column
	 *
	 * @access  public
	 */
	public $primary_key;

	/**
	 * Get things started
	 *
	 * @access  public
	 */
	public function __construct() {

		$this->ts_now = time();
	}

	/**
	 * Whitelist of columns
	 *
	 * @access  public
	 * @return  array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Default column values
	 *
	 * @access  public
	 * @return  array
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @access  public
	 * @return  object
	 */
	public function get( $row_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @access  public
	 * @return  object
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @access  public
	 * @return  string
	 */
	public function get_column( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @access  public
	 * @return  string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;
		$column_where	 = esc_sql( $column_where );
		$column			 = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", $column_value ) );
	}

	/**
	 * Retrieve a specific columns values
	 *
	 * @access  public
	 * 
	 * @param array $columns
	 * @param string $column_where
	 * @param string $column_value
	 * 
	 * @return  bool|object
	 */
	public function get_columns_by( $columns, $column_where, $column_value ) {
		global $wpdb;
		$column_where = esc_sql( $column_where );

		if ( empty( $columns ) || !is_array( $columns ) ) {
			return false;
		}

		$columns_string = esc_sql( implode( ', ', $columns ) );

		return $wpdb->get_results( $wpdb->prepare(
		"
			SELECT $columns_string
			FROM $this->table_name
			WHERE $column_where = %s;
		", $column_value ) );
	}

	/**
	 * Returns the number of records in a table:
	 *
	 * @access  public
	 * @return  int
	 */
	public function count_rows() {
		global $wpdb;

		return $wpdb->get_var( "SELECT COUNT(*) FROM $this->table_name" );
	}

	/**
	 * Insert a new row
	 *
	 * @access  public
	 * @return  int
	 */
	public function insert( $data, $type = '' ) {
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys		 = array_keys( $data );
		$column_formats	 = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );

		return $wpdb->insert_id;
	}

	/**
	 * Update a row
	 *
	 * @access  public
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys		 = array_keys( $data );
		$column_formats	 = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @access  public
	 * @return  bool
	 */
	public function delete( $row_id = 0, $where = '' ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $where = %d", $row_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the given table exists
	 *
	 * @param  string $table The table name
	 * @return bool          If the table name exists
	 */
	public function table_exists( $table ) {
		global $wpdb;
		$table = sanitize_text_field( $table );

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
	}

}
