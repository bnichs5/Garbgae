

<h4><?php _e( 'Privacy Policy agreement', 'wp-tao' ); ?></h4>
<p class="wtbp-247p-field-desc"><?php _e( 'Includes checkbox with privacy policy agreement', 'wp-tao' ); ?>.</p>

<div class="wtbp-247p-form-group wtbp-247p-radio-type">

	<div class="wtbp-247p-radio-labels">
		<label>
			<input <?php checked( 'hide', $opt[ 'pp_visible' ] ); ?> type="radio" value="hide" name="pp_visible">
			<?php _e( 'Hide', 'wp-tao' ); ?>
		</label>
		<label>
			<input <?php checked( 'show', $opt[ 'pp_visible' ] ); ?> type="radio" value="show" name="pp_visible">
			<?php _e( 'Show', 'wp-tao' ); ?>
		</label>
	</div>
</div>

<div class="metabox-247p-pp-extend metabox-247p-extend-fields">
	<h4><?php _e( 'Privacy Policy extended', 'wp-tao' ); ?></h4>
	<p class="wtbp-247p-field-desc"><?php _e( '247 Popup show link to the subpage with full privacy policy content. Select the appropriate page with the privacy policy terms and complete a link text', 'wp-tao' ); ?>.</p>


	<div class="wtbp-247p-form-group">
		<label for="pp_page_id"> <?php _e( 'Select page', 'wp-tao' ); ?></label>
		<select name="pp_page_id" class="wtbp-247-select">
			<?php if ( !empty( $pages ) ): foreach ( $pages as $page ): ?>
					<option <?php selected( $opt[ 'pp_page_id' ], $page[ 'id' ] ); ?> value="<?php echo absint( $page[ 'id' ] ); ?>"> <?php echo esc_html( $page[ 'title' ] ); ?></option>
					<?php
				endforeach;
			endif;
			?>
		</select>

	</div>

	<div class="wtbp-247p-form-group">
		<label for="pp_url"> <?php _e( 'or enter the direct URL', 'wp-tao' ); ?></label>
		<input placeholder="<?php _e( 'Direct URL of the Privacy Policy', 'wp-tao' ); ?>"
			   name="pp_url" 
			   class="regular-text" 
			   type="text" 
			   value="<?php echo esc_url( $opt[ 'pp_url' ] ); ?>" />
		(<?php _e( 'overwrites the above', 'wp-tao' ); ?>!)
	</div>

</div>


<script>
	( function ( $ ) {

		var name = 'pp_visible',
			extendClass = 'metabox-247p-pp-extend';

		// On document ready
		$( document ).on( 'ready', function () {
			var currentValue = $( 'input[name=' + name + ']:checked', '#post' ).val();

			if ( currentValue !== 'show' ) {
				$( '.' + extendClass ).hide();
			}

		} );

		// On change
		$( 'input[name=' + name + ']' ).on( 'change', function () {
			if ( $( this ).val() === 'show' ) {
				$( '.' + extendClass ).slideDown();
			} else {
				$( '.' + extendClass ).slideUp();
			}
		} );

	} )( jQuery );
</script>
