
<?php
do_action( 'wtbp_247p_before_optin_form', $this );

if ( !empty( $this->content[ 'signup_form' ] ) ):
	?>
	<div class="wtbp-247p-optin">
		<form id="wtbp-247p-optin-form" class="wtbp-247p-popup-form" method="POST" target="_blank">	

			<div class="wtbp-247p-optin-essence">

				<?php if ( $this->options[ 'opt_fname_visible' ] == 1 ): ?>
					<input value="" class="wtbp-247p-fname" type="text" name="wtbp_247p_fname" placeholder="<?php _e( 'First name', WTBP_247P_DOMAIN ); ?>">
				<?php endif; ?>

				<input value="" class="wtbp-247p-email" type="text" name="wtbp_247p_email" placeholder="<?php _e( 'E-mail address', WTBP_247P_DOMAIN ); ?>">

				<?php if ( $this->options[ 'pp_visible' ] === 'show' && !empty( $this->options[ 'final_url' ] ) ): ?>

					<div class="wtbp-247p-privacy-policy" >

						<input id="wtbp-247p-privacy-agree" class="wtbp-247p-privacy-agree" value="on" type="checkbox" name="wtbp_247p_privacy_agree">
						<label for="wtbp-247p-privacy-agree"><?php printf( __( 'I agree to <a href="%s">privacy policy</a>', 'wp-tao' ), esc_url( $this->options[ 'final_url' ] ) ); ?></label>
					</div>

				<?php endif; ?>

				<div class="wtbp-247p-errors"></div>

				<input type="hidden" name="wtbp_247p_exclude_from_tracking" />

				<input type="submit" id="wtbp-247p-popup-submit" class="wtbp-247p-popup-submit-btn" value="<?php echo $this->content[ 'submit_text' ]; ?>"/>
			</div>

			<div class="wtbp-247p-cloned-inputs">          

				<?php
				if ( $this->options[ 'opt_fname_visible' ] == 1 ) {
					foreach ( wtbp_247p_get_alt_input_name() as $input_name ) {
						echo '<input class="wtbp-247p-possible-names" name="' . trim( sanitize_text_field( $input_name ) ) . '" type="hidden" value="" />';
					}
				}


				foreach ( wtbp_247p_get_alt_input_email() as $input_email ) {
					echo '<input class="wtbp-247p-possible-emails" name="' . trim( sanitize_text_field( $input_email ) ) . '" type="hidden" value="" />';
				}
				?>
			</div>
		</form>

		<div class="wtbp-247p-hidden-form">
			<?php echo $this->content[ 'signup_form' ]; ?>
		</div>

	</div>



<?php else: ?>


	<div class="wtbp-247p-optin">
		<form id="wtbp-247p-optin-form" class="wtbp-247p-popup-form" method="POST">	

			<div class="wtbp-247p-optin-essence">


				<input value="" class="wtbp-247p-email" type="text" name="email" placeholder="<?php _e( 'E-mail address', 'wp-tao' ); ?>">

				<?php if ( $this->options[ 'pp_visible' ] === 'show' && !empty( $this->options[ 'final_url' ] ) ): ?>

					<div class="wtbp-247p-privacy-policy" >

						<input id="wtbp-247p-privacy-agree" class="wtbp-247p-privacy-agree" value="on" type="checkbox" name="wtbp_247p_privacy_agree">
						<label for="wtbp-247p-privacy-agree"><?php printf( __( 'I agree to <a href="%s">privacy policy</a>', 'wp-tao' ), esc_url( $this->options[ 'final_url' ] ) ); ?></label>
					</div>

				<?php endif; ?>

				<div class="wtbp-247p-errors"></div>

				<input type="hidden" name="wtbp_247p_exclude_from_tracking" />

				<input type="submit" id="wtbp-247p-popup-submit" class="wtbp-247p-popup-submit-btn" value="<?php echo $this->content[ 'submit_text' ]; ?>"/>
			</div>

		</form>

	</div>


<?php
endif;

do_action( 'wtbp_247p_after_optin_form', $this );
?>