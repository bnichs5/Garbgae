<?php if ( empty( $events ) || !is_array( $events ) ): ?>
	<div class="wptao-event wptao-event-nores">
		<h3><?php _e( 'No events found!', 'wp-tao' ); ?></h3>
	</div>  
<?php endif; ?>

<?php if ( is_array( $events ) && !empty( $events ) ): foreach ( $events as $event ): ?>

		<?php $style = TAO()->events->get_action_style( $event[ 'action' ] ); ?>

		<div class="wptao-event wptao-event-item wptao-row <?php echo 'wpta-event-cat-' . esc_attr( $event[ 'category' ] ); ?> <?php echo!isset( $style[ 'icon' ] ) ? 'wptao-non-style' : ''; ?>" data-year="<?php echo WTBP_WPTAO_Helpers::get_date( 'Y', $event[ 'event_ts' ] ); ?>" data-month="<?php echo WTBP_WPTAO_Helpers::get_date_i18n( 'F Y', $event[ 'event_ts' ] ); ?>" data-day-text="<?php echo WTBP_WPTAO_Helpers::get_date_i18n( 'l, d.m.Y', $event[ 'event_ts' ] ); ?>" data-day="<?php echo WTBP_WPTAO_Helpers::get_date( 'd', $event[ 'event_ts' ] ); ?>" data-event-id="<?php echo $event[ 'event_id' ]; ?>">
			<div class="wptao-event-meta">
				<a href="<?php echo esc_url( add_query_arg( array( 'a' => esc_attr( $event[ 'action' ] ) ), $this->base_url ) ); ?>" class="wptao-event-icon" <?php echo ($style !== false) ? 'style="background-color:' . $style[ 'color' ] . ';"' : ''; ?> title="<?php printf( __( 'Sort by %s', 'wp-tao' ), esc_attr( $event[ 'action' ] ) ); ?>">
					<?php
					if ( isset( $style[ 'icon' ] ) ):
						echo $style[ 'icon' ];
					else:
						?>
						<i class="dashicons dashicons-megaphone wpta-event-dashicon"></i>
					<?php endif; ?>
				</a>
			</div>
			<div class="wptao-event-content" <?php echo ($style !== false) ? 'style="border-left-color:' . $style[ 'color' ] . ';"' : ''; ?>>
				<div class="wptao-arrow-left" <?php echo ($style !== false) ? 'style="border-color:' . $style[ 'color' ] . ';"' : ''; ?>></div>
				<div class="wptao-row wptao-event-content-row">

					<span class="wptao-event-date"><?php echo WTBP_WPTAO_Helpers::get_date( get_option( 'time_format' ), $event[ 'event_ts' ] ); ?></span>					
					<div class="wptao-event-content-inner">
						<h3 class="wptao-event-title"><?php echo wp_kses_post( $event[ 'title' ] ); ?></h3>    
						<?php if ( !empty( $event[ 'description' ] ) ): ?>
							<div class="wptao-event-desc">
								<?php echo wp_kses_post( $event[ 'description' ] ); ?>
							</div>
						<?php endif; ?>

					</div>

					<?php if ( TAO()->booleans->is_page_events || strpos( $this->base_url, WTBP_WPTAO_EVENTS_SUBPAGE_SLUG ) !== FALSE ): ?>
						<div class="wptao-event-content-user">

							<?php
							$profile_url = '';
							if ( isset( $event[ 'user' ]->id ) && !empty( $event[ 'user' ]->id ) ) {
								$profile_url = sprintf( admin_url( 'admin.php?page=wtbp-wptao-users&action=wptao-profile&user=%d' ), $event[ 'user' ]->id );
							} else {
								$profile_url = sprintf( admin_url( 'admin.php?page=wtbp-wptao-users&action=wptao-unident-profile&fp=%d' ), $event[ 'fingerprint_id' ] );
							}
							?>
							
							<a href="<?php echo esc_url($profile_url); ?>" title="<?php _e( 'See who the user is and what the user did.', 'wp-tao' ) ?>" class="wptao-event-content-profile wptao-row">
							<div class="wptao-user-name-av">
								<?php
								if ( isset( $event[ 'user' ] ) && !empty( $event[ 'user' ] ) ) {
									echo TAO()->users->get_avatar( $event[ 'user' ], 20 );
								} else {
									echo TAO()->users->get_avatar( 'fp_' . (string) $event[ 'fingerprint_id' ], 20 );
								}
								?>
							</div>

							<div class="wptao-user-name-dis">
								<?php
								if ( isset( $event[ 'user' ] ) && !empty( $event[ 'user' ] ) ) {
									echo TAO()->users->display_name( $event[ 'user' ] );
								} else {
									printf( __( 'Unidentified user (%d)', 'wp-tao' ), $event[ 'fingerprint_id' ] );
								}
								?>
							</div>
							
							</a>

						</div>

					<?php endif; ?>		

					<div class="wptao-event-actions-panel wptao-row">
						<div class="wptao-event-actions-panel-inner">

							<span class="wptao-event-actions-panel-item wptao-event-id wptao-event-action-delete" data-event-id="<?php echo $event[ 'event_id' ]; ?>" data-nonce="<?php echo wp_create_nonce( 'wptao-delete-event-id-' . $event[ 'event_id' ] ); ?>">
								<span class="dashicons dashicons-trash"></span>
								<?php _e( 'Delete', 'wp-tao' ); ?>
							</span>

						</div>
					</div>
				</div>

			</div>
		</div>  
		<?php
	endforeach;
endif;
?>
