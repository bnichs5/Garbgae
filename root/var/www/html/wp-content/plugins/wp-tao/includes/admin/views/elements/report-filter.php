
<?php if ( isset( $this->options[ 'filters' ] ) && is_array( $this->options[ 'filters' ] ) && !empty( $this->options[ 'filters' ] ) ): ?>
	<div class="wptao-report-filter">

		<form class="wptao-row" method="POST" action="<?php echo esc_url( $this->report_url ); ?>">

			<?php if ( in_array( 'date_range', $this->options[ 'filters' ] ) ): ?>
				<div class="wptao-report-filter-field">

					<?php $this->the_date_range(); ?>

				</div>


			<?php endif; ?>

			<?php if ( in_array( 'limit', $this->options[ 'filters' ] ) ): ?>

				<div class="wptao-report-items-limit" title="<?php _e( 'Number of records:', 'wp-tao' ); ?>">
					<div class="wptao-report-filter-button wptao-date-button wptao-ril-button" role="button" aria-expanded="true">
						<span class="wptao-date-arrow-down"></span>
						<div class="wptao-date-btn-inner">
							<?php echo isset( $_GET[ 'ipp' ] ) && is_numeric( $_GET[ 'ipp' ] ) ? (int) abs( $_GET[ 'ipp' ] ) : esc_attr( $this->items_per_page ); ?>
						</div>
					</div>
					<div class="wptao-filter-expanded">
						<div class="wptao-filter-expanded-inner">
							<div class="wptao-date-exp-label"><?php _e( 'Number of records:', 'wp-tao' ); ?></div>
							<div class="wptao-ril-inputs-wrapp">
								<input type="number" id="wptao-row-reports-number" name="wptao-row-reports-number" class="small-text" value="<?php echo isset( $_GET[ 'ipp' ] ) && is_numeric( $_GET[ 'ipp' ] ) ? (int) abs( $_GET[ 'ipp' ] ) : esc_attr( $this->items_per_page ); ?>" />
								<input type="submit" value="<?php _e( 'Sort', 'wp-tao' ); ?>" class="button button-primary">
							</div>
						</div>
					</div>
				</div>

			<?php endif; ?>	

			<input type="hidden" value="wptao-report-<?php echo esc_attr( $this->report_slug ); ?>" name="action" />

			<?php wp_nonce_field( 'wptao-report-filter', 'wptao-report-filter-' . esc_attr( $this->report_slug ) ) ?>


		</form>
	</div>
<?php endif; ?>
