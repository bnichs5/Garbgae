

<div class="metabox-prefs" id="screen-meta">

	<div aria-label="<?php _e( 'Screen options', 'wp-tao' ); ?>" tabindex="-1" class="hidden" id="screen-options-wrap">
		<form method="post" action="<?php echo admin_url( 'admin.php?page=wtbp-wptao' ); ?>" id="adv-settings">
			<fieldset class="screen_options">
				<legend><?php _e( 'Espresso reports', 'wp-tao' ); ?></legend>

				<?php if ( !empty( $this->widgets ) ): ?>
					<?php foreach ( $this->widgets as $widget ): ?>
						<label>
							<input type="checkbox" <?php checked('show', $widget->visibility); ?> value="<?php echo esc_html( $widget->id ); ?>" id="wptao-screen-opt-<?php echo esc_html( $widget->id ); ?>" name="wptao-screen-opt-<?php echo esc_html( $widget->id); ?>">
							<?php echo esc_html( $widget->title ); ?>
						</label>
					<?php endforeach; ?>
				<?php endif; ?>

			</fieldset>
			<p class="submit">
				<input type="submit" value="<?php _e( 'Apply', 'wp-tao' ); ?>" class="button button-primary" id="wptao-screen-options-apply" name="wptao-screen-options-apply">
			</p>
			<?php wp_nonce_field( 'wptao_screen_options', 'wptao_screen_options_nonce' ); ?>
		</form>
	</div>		</div>

<div id="screen-meta-links">
	<div class="hide-if-no-js screen-meta-toggle" id="screen-options-link-wrap">
		<button aria-expanded="true" aria-controls="screen-options-wrap" class="button show-settings" id="show-settings-link" type="button">Opcje ekranu</button>
	</div>
</div>