
<?php global $wptao_settings; ?>

<div class="wptao-report-summary wptao-row">
	<div class="wptao-report-summary-item wptao-report-summary-col6">
		<h4><?php _e( 'Amount', 'wp-tao' ); ?></h4>
		<span class="wptao-report-summary-value"><?php echo WTBP_WPTAO_Helpers::amount_format( $this->total_amount, $wptao_settings[ 'currency' ] ); ?></span>
	</div>
	<div class="wptao-report-summary-item wptao-report-summary-col6">
		<h4><?php _e( 'Orders', 'wp-tao' ); ?></h4>
		<span class="wptao-report-summary-value"><?php echo absint( $this->total_orders ); ?></span>
	</div>
</div>

<?php if ( !empty( $this->data ) && is_array( $this->data ) ): ?>
	<div id="wptao-report-<?php echo sanitize_title( $this->report_slug ); ?>-amount" class="wptao-report-chart"></div>
	<div id="wptao-report-<?php echo sanitize_title( $this->report_slug ); ?>-orders" class="wptao-report-chart"></div>
<?php endif; ?>


<div class="wptao-report-content wptao-row">

	<table class="wp-list-table widefat fixed striped pages">
		<thead>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-column-date" scope="col"><?php _e( 'Date', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php printf( __( 'Amount [%s]', 'wp-tao' ), WTBP_WPTAO_Helpers::get_currency_symbol( $wptao_settings[ 'currency' ] ) ); ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Orders', 'wp-tao' ) ?></th>
			</tr>
		</thead>

		<tbody id="the-list">
			<?php if ( !empty( $this->days ) ): ?>

				<?php
				$i = 1;
				foreach ( $this->days as $day ):
					?>
					<tr>
						<td class="wptao-report-column-primary"><?php echo $i; ?></td>
						<td data-colname="<?php _e( 'Date', 'wp-tao' ) ?>"><b><?php echo date_i18n( get_option( 'date_format' ), strtotime( $day ) ); ?></b></td>
						<td data-colname="<?php printf( __( 'Amount [%s]', 'wp-tao' ), WTBP_WPTAO_Helpers::get_currency_symbol( $wptao_settings[ 'currency' ] ) ); ?>" class="wptao-cell-number"><?php echo array_key_exists( $day, $this->data ) ? WTBP_WPTAO_Helpers::amount_format( $this->data[ $day ][ 'amount' ], '' ) : 0; ?></td>
						<td data-colname="<?php _e( 'Orders', 'wp-tao' ) ?>" class="wptao-cell-number"><?php echo array_key_exists( $day, $this->data ) ? absint( $this->data[ $day ][ 'orders' ] ) : 0; ?></td>
					</tr>
					<?php
					$i++;
				endforeach;
				?>

			<?php else: ?>
				<tr class="" id="">
					<td colspan="4"><?php _e( 'No results!', 'wp-tao' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>

		<tfoot>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-column-date" scope="col"><?php _e( 'Date', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php printf( __( 'Amount [%s]', 'wp-tao' ), WTBP_WPTAO_Helpers::get_currency_symbol( $wptao_settings[ 'currency' ] ) ); ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Orders', 'wp-tao' ) ?></th>
			</tr>
		</tfoot>

	</table>


</div>