

<div class="wrap wptao-events-page">

	<div class="wptao-events-page-head wptao-dashboard-head">
		<h2><span class="dashicons dashicons-megaphone"></span><?php _e( 'WP TAO Events overview', 'wp-tao' ); ?></h2>
	</div>

    <div class="wptao-row">

        <div class="wptao-pcol-1">

			<div class="wptao-module wptao-module-header wptao-events-summary wptao-row">

				<button class="wptao-toggle-module dashicons"></button>

				<h2><?php _e( 'Total events', 'wp-tao' ); ?></h2>
				<span class="wptao-events-summary-time">
					<?php
					_e( 'From', 'wp-tao' );
					$format = get_option( 'date_format' );
					?>
					<b><?php echo isset( TAO()->events->query_vars[ 'date_start' ] ) ? ' ' . date_i18n( $format, TAO()->events->query_vars[ 'date_start' ] ) : ' ' . date_i18n( $format, $timeline->get_first_event_ts() ); ?></b>
					<?php _e( 'to', 'wp-tao' ); ?>
					<b><?php echo isset( TAO()->events->query_vars[ 'date_end' ] ) ? ' ' . date_i18n( $format, TAO()->events->query_vars[ 'date_end' ] ) : date_i18n( $format, time() ); ?></b>
				</span>

				<div class="wptao-module-content">

					<?php if ( isset( $timeline->summary_data ) && !empty( $timeline->summary_data ) && is_array( $timeline->summary_data ) ): ?>
						<?php foreach ( $timeline->summary_data as $action ): ?>

							<?php
							$style			 = TAO()->events->get_action_style( $action->action );
							$current_actions = isset( TAO()->events->query_vars[ 'event_action' ] ) && is_array( TAO()->events->query_vars[ 'event_action' ] ) ? TAO()->events->query_vars[ 'event_action' ] : array();
							?>

							<a href="<?php echo esc_url( add_query_arg( array( 'a' => esc_attr( $action->action ) ), $timeline->base_url ) ); ?>" class="wptao-events-summary-item wptao-row<?php echo in_array( $action->action, $current_actions ) ? ' wptao-events-summary-active' : ''; ?>">
								<div class="wptao-event-icon" <?php echo ($style !== false) ? 'style="background-color:' . $style[ 'color' ] . ';"' : ''; ?>>
									<?php
									if ( isset( $style[ 'icon' ] ) ):
										echo $style[ 'icon' ];
									else:
										?>
										<i class="dashicons dashicons-megaphone wpta-event-dashicon"></i>
									<?php endif; ?>
								</div>
								<div class="wptao-events-summary-title">
									<?php echo isset( TAO()->events->actions[ $action->action ] ) ? TAO()->events->actions[ $action->action ][ 'title' ] : sanitize_text_field( $action->action ); ?> <b>(<?php echo esc_attr( $action->total ); ?>)</b>
								</div>
							</a>

						<?php endforeach; ?>
					<?php else: ?>
					<?php _e( 'No events found!', 'wp-tao' ); ?>
					<?php endif; ?>
				</div>

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

						<?php $this->timeline->the_timeline(); ?>

					</div>

				</div>

				<div class="wptao-event-preloader hidden"><img src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" width="40" height="40" /></div>
				<div class="wptao-older-events-wrapp">
					<button id = "wptao-older-events" class = "button-secondary center"><?php _e( 'Load older events', 'wp-tao' ); ?></button>
				</div>
			</div>
		</div>

    </div>
</div>