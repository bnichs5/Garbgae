

<div class="metabox metabox-247p-appearance">

	<h3><?php _e( 'Appearance', 'wp-tao' ); ?></h3>

	<?php do_action( 'wtbp_247p_metabox_before_appearance', $opt ); ?>

	<div class="wtbp-247p-form-group">
		<label>
			<?php _e( 'Location', 'wp-tao' ); ?>
		</label>
		<select name="ap_location">
			<option <?php selected( 'bottom-left', $opt[ 'ap_location' ] ); ?> value="bottom-left"><?php _e( 'Bottom left', 'wp-tao' ); ?></option>
			<option <?php selected( 'bottom-right', $opt[ 'ap_location' ] ); ?> value="bottom-right"><?php _e( 'Bottom right', 'wp-tao' ); ?></option>
			<option <?php selected( 'overlay', $opt[ 'ap_location' ] ); ?> value="overlay"><?php _e( 'Overlay', 'wp-tao' ); ?></option>
		</select>
	</div>

	<div class="wtbp-247p-form-group">
		<label for="ap_header_text"> <?php _e( 'Header text', 'wp-tao' ); ?></label>
		<input name="ap_header_text" 
			   class="long-text" 
			   type="text" 
			   value="<?php echo $opt[ 'ap_header_text' ]; ?>" />

		<p class="wtbp-247p-field-desc">
			<?php _e( 'You can use following placeholders', 'wp-tao' ); ?>: {first_name}, {last_name}
		</p>
	</div>

	<div class="wtbp-247p-form-group">
		<label for="ap_message_text"> <?php _e( 'Message text', 'wp-tao' ); ?></label>
		<input name="ap_message_text"
			   class="long-text"
			   type="text" 
			   value="<?php echo $opt[ 'ap_message_text' ]; ?>" />

		<p class="wtbp-247p-field-desc">
			<?php _e( 'You can use following placeholders', 'wp-tao' ); ?>: {first_name}, {last_name}
		</p>
	</div>

	<div class="wtbp-247p-form-group">
		<label for="ap_dist_color"> <?php _e( 'Distinctive color', 'wp-tao' ); ?></label>
		<input name="ap_dist_color"
			   class="wtbp-247p-colorpicker"
			   type="text"
			   data-default-color="#0085ba"
			   value="<?php echo $opt[ 'ap_dist_color' ]; ?>" />
	</div>

	<div class="wtbp-247p-form-group">
		<label for="wtbp-247p-tao-show-avatar"><?php _e( 'Show user avatar', 'wp-tao' ); ?></label>
		<input id="wtbp-247p-tao-show-avatar"
			   type="checkbox"
			   value="1"
			   name="ap_show_avatar"
			   <?php echo checked( '1', $opt[ 'ap_show_avatar' ] ) ? ' checked="checked"' : ''; ?> />
		<p class="wtbp-247p-field-desc"><?php _e( 'If user is identified by WP Tao and has Gravatar account, the 247 Popup will try display his avatar.', 'wp-tao' ); ?></p>
	</div>

	<?php do_action( 'wtbp_247p_metabox_after_appearance', $opt ); ?>

</div>
