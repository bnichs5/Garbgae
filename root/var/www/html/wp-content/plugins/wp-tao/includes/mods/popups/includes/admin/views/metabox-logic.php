
<div class="metabox metabox-247p-logic">

	<h3><?php _e( 'Logic', 'wp-tao' ); ?></h3>
	<?php if ( count( $this->get_logic_scenarios() ) > 1 ): ?>
		<div class="wtbp-247p-form-group">
			<label>
				<?php _e( 'Logic scenarios', 'wp-tao' ); ?>
			</label>

			<select name="logic_scenario">
				<?php foreach ( $this->get_logic_scenarios() as $slug => $name ): ?>
					<option <?php selected( sanitize_title( $slug ), $opt[ 'logic_scenario' ] ); ?> value="<?php echo sanitize_title( $slug ) ?>"><?php echo wp_strip_all_tags( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php do_action( 'wtbp_247p_metabox_before_restrictions', $opt); ?>

	<div class="metabox-247p-extend-247popup metabox-247p-extend-fields">
		<h4><?php _e( 'Restrictions', 'wp-tao' ); ?></h4>

		<div class="wtbp-247p-form-group">
			<label>
				<?php _e( 'Show popup on', 'wp-tao' ); ?>
			</label>
			<select name="rest_show_on">
				<option <?php selected( 'all', $opt[ 'rest_show_on' ] ); ?> value="all"><?php _e( 'All pages', 'wp-tao' ); ?></option>
				<option <?php selected( 'custom', $opt[ 'rest_show_on' ] ); ?> value="custom"><?php _e( 'Custom rules', 'wp-tao' ); ?></option>
			</select>
		</div>

		<div class="wtbp-247p-custom-rules">

			<div class="wtbp-247p-form-group">
				<label>
					<?php _e( 'Post types', 'wp-tao' ); ?>
				</label>
				<div class="wtbp-247p-rest-mg">
					<?php foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ): ?>

						<input id="wtbp-247p-rest-cb-<?php echo sanitize_title( $post_type->name ); ?>"
							   type="checkbox" value="<?php echo sanitize_title( $post_type->name ); ?>"
							   name="rest_posts_types[]"
							   <?php echo in_array( $post_type->name, $opt[ 'rest_posts_types' ] ) ? ' checked="checked"' : ''; ?> />

						<label for="wtbp-247p-rest-cb-<?php echo sanitize_title( $post_type->name ); ?>"><?php echo esc_html( $post_type->labels->name ); ?></label>
						<br />
					<?php endforeach; ?>
				</div>

			</div>

			<div class="wtbp-247p-form-group">
				<label>
					<?php _e( 'Other views', 'wp-tao' ); ?>
				</label>
				<div class="wtbp-247p-rest-mg">

					<input id="wtbp-247p-rest-cb-frontpage"
						   type="checkbox"
						   value="frontpage"
						   name="rest_other_views[]"
						   <?php echo in_array( 'frontpage', $opt[ 'rest_other_views' ] ) ? ' checked="checked"' : ''; ?> />

					<label for="wtbp-247p-rest-cb-frontpage"><?php _e( 'Front page', 'wp-tao' ); ?></label>
					<br />

					<input id="wtbp-247p-rest-cb-archives"
						   type="checkbox"
						   value="archives"
						   name="rest_other_views[]"
						   <?php echo in_array( 'archives', $opt[ 'rest_other_views' ] ) ? ' checked="checked"' : ''; ?> />
					<label for="wtbp-247p-rest-cb-archives"><?php _e( 'Archives', 'wp-tao' ); ?></label>
					<br />
				</div>

			</div>

			<div class="wtbp-247p-form-group wtbp-247p-form-group-full">
				<label>
					<?php _e( 'URL containing keywords', 'wp-tao' ); ?>
				</label>
				<p class="wtbp-247p-field-desc"><?php _e( 'Use commas to separate keywords', 'wp-tao' ); ?></p>

				<input name="rest_url_containing" 
					   class="large-text" 
					   type="text" 
					   value="<?php echo esc_html( $opt[ 'rest_url_containing' ] ); ?>" />

			</div>

		</div>


	</div>

	<?php do_action( 'wtbp_247p_metabox_after_restrictions', $opt); ?>



	<?php do_action( 'wtbp_247p_metabox_before_triggers', $opt); ?>

	<div class="metabox-247p-extend-247popup metabox-247p-extend-fields">

		<h4><?php _e( 'Trigger popup on', 'wp-tao' ); ?></h4>

		<div class="wtbp-247p-form-group">
			<label>
				<?php _e( 'Event', 'wp-tao' ); ?>
			</label>
			<select name="trigg_event">
				<option <?php selected( 'after-load', $opt[ 'trigg_event' ] ); ?> value="after-load"><?php _e( 'After page load', 'wp-tao' ); ?></option>
				<option <?php selected( 'after-scroll', $opt[ 'trigg_event' ] ); ?> value="after-scroll"><?php _e( 'After scroll down', 'wp-tao' ); ?></option>
			</select>
		</div>

		<div class="wtbp-247p-form-group">
			<label for="trigg_timeout"> <?php _e( 'Timeout', 'wp-tao' ); ?></label>
			<input name="trigg_timeout" 
				   class="small-text" 
				   type="number" 
				   value="<?php echo $opt[ 'trigg_timeout' ]; ?>" /> <?php _e( 'seconds', 'wp-tao' ); ?>
		</div>

	</div>

	<?php do_action( 'wtbp_247p_metabox_after_triggers', $opt); ?>

</div>


<script>
    ( function ( $ ) {

        var name = 'logic_scenario',
            extendClass = 'metabox-247p-extend-fields',
            prefix = 'metabox-247p-extend-';

        // On document ready
        $( document ).on( 'ready', function () {
            var scenario = $( 'select[name=' + name + ']' );

            if ( scenario.length > 0 ) {
                var currentValue = scenario.val();

                $( '.' + extendClass ).hide();
                $( '.' + prefix + currentValue ).show();

            } else {
                $( '.' + extendClass ).show();
            }

        } );

        // On change
        $( 'select[name=' + name + ']' ).on( 'change', function () {
            var currentValue = $( this ).val();

            $( '.' + extendClass ).hide();
            $( '.' + prefix + currentValue ).fadeIn();
        } );

    } )( jQuery );

    ( function ( $ ) {

        var name = 'rest_show_on',
            extendClass = 'wtbp-247p-custom-rules';

        // On document ready
        $( document ).on( 'ready', function () {
            var scenario = $( 'select[name=' + name + ']' );

            if ( scenario.length > 0 && scenario.val() === 'custom' ) {
                $( '.' + extendClass ).show();
            }

        } );

        // On change
        $( 'select[name=' + name + ']' ).on( 'change', function () {
            if ( $( this ).val() === 'custom' ) {
                $( '.' + extendClass ).fadeIn( 300 );
            } else {
                $( '.' + extendClass ).fadeOut( 300 );
            }
        } );

    } )( jQuery );

</script>