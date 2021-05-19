
<div class="wptao-report-summary wptao-row">
	<div class="wptao-report-summary-item wptao-success-color wptao-report-summary-col6">
		<h4><?php _e( 'Registrations', 'wp-tao' ); ?></h4>
		<span class="wptao-report-summary-value"><?php echo absint( $this->total_registrations ); ?></span>
	</div>
</div>


<div class="wptao-report-content wptao-row">

	<table class="wp-list-table widefat fixed striped pages">
		<thead>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-column-date" scope="col"><?php _e( 'Date', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php _e( 'New registrations', 'wp-tao' ) ?></th>
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
						<td data-colname="<?php _e( 'New registrations', 'wp-tao' ) ?>" class="wptao-cell-number"><?php echo array_key_exists( $day, $this->data ) && isset( $this->data[ $day ][ 'registrations' ] ) ? absint( $this->data[ $day ][ 'registrations' ] ) : 0; ?></td>
					</tr>
					<?php
					$i++;
				endforeach;
				?>

			<?php else: ?>
				<tr class="" id="">
					<td colspan="3"><?php _e( 'No results!', 'wp-tao' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>

		<tfoot>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-column-date" scope="col"><?php _e( 'Date', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php _e( 'New registrations', 'wp-tao' ) ?></th>
			</tr>
		</tfoot>

	</table>


</div>