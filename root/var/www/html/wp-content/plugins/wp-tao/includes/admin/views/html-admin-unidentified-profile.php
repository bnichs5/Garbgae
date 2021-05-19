
<div class="wrap">

	<div class="wptao-dashboard-head">
		<h2><span class="dashicons dashicons-id"></span><?php _e( 'Undifentified profile', 'wp-tao' ); ?></h2>
	</div>

    <div id="wptao-user-profile" class="wptao-user-profile wptao-row">

		<div class="wptao-back">
			<a href="<?php echo admin_url( 'admin.php?page=wtbp-wptao-events' ); ?>" >
				<span class="dashicons dashicons-arrow-left-alt"></span>
				<?php _e( 'Back to events timeline', 'wp-tao' ); ?>
			</a>
		</div>

        <div class="wptao-pcol-1">

            <div class="wptao-module wptao-user-name wptao-module-header wptao-row">
				<div class="wptao-user-name-av">
					<?php
					echo TAO()->users->get_avatar( 'fp_' . (string)$fp->fp_data->id, 70 );
					?>
				</div>
                <h1><?php printf( __( 'Unidentified user (%d)', 'wp-tao' ), $fp->fp_data->id ); ?></h1>
            </div>


				<div class="wptao-module wptao-mod-user-fingerprints">
					<h2><?php _e( 'Who is it?', 'wp-tao' ) ?></h2>
					<?php
	
						echo '<p class="wptao-mod-user-fingerprint">';
						echo '<b>' . __( 'Fingerprint ID:', 'wp-tao' ) . '</b> ' . esc_html($fp->fp_data->id) . '<br />';
						echo '<b>' . __( 'Created:', 'wp-tao' ) . '</b> ' . WTBP_WPTAO_Helpers::get_date( "d.m.Y, H:i:s", $fp->fp_data->created_ts ) . '<br />';
						echo '<b>' . __( 'IP:', 'wp-tao' ) . '</b> ' . esc_html($fp->fp_data->ip) . '<br />';
						echo '<b>' . __( 'User agent:', 'wp-tao' ) . '</b> ' . esc_html($fp->fp_data->user_agent);
						echo '</p>';

					?>
				</div>

			<div class="wptao-module">

				<button class="wptao-toggle-module dashicons"></button>

				<?php $timeline->the_timeline_filter(); ?>
			</div>

        </div>

        <div class="wptao-pcol-2">

			<div class="wptao-events-desk">

				<div id="wptao-activity" class="wptao-events-content" data-token="<?php echo wp_create_nonce( 'wptao-events-ajax' ); ?>">
					<div class="wptao-events-content-inner">

						<?php $timeline->the_timeline(); ?>

					</div>

				</div>

				<div class = "wptao-event-preloader hidden"><img src = "<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" width = "40" height = "40" /></div>
				<div class = "wptao-older-events-wrapp">
					<button id = "wptao-older-events" class = "button-secondary center"><?php _e( 'Load older events', 'wp-tao' ); ?></button>
				</div>
			</div>

        </div>

    </div>
</div>