
<?php if ( !empty( $this->data ) && is_array( $this->data ) ): ?>
	<div id="wptao-report-<?php echo sanitize_title( $this->report_slug ); ?>" class="wptao-report-chart wptao-report-chart-bar"></div>
<?php endif;
//new WTBP_WPTAO_RLS_Abandoned_Carts( 1, time() );
?>


<div class="wptao-report-content wptao-row">

	<table class="wp-list-table widefat fixed striped pages">
		<thead>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-column-date" scope="col"><?php _e( 'Date', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Contacts', 'wp-tao' ) ?></th>
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
						<td data-colname="<?php _e( 'Contacts', 'wp-tao' ) ?>" class="wptao-cell-number"><?php echo array_key_exists( $day, $this->data ) ? absint( $this->data[ $day ][ 'contacts' ] ) : 0; ?></td>
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
				<th class="manage-column" scope="col"><?php _e( 'Contacts', 'wp-tao' ) ?></th>
			</tr>
		</tfoot>

	</table>


</div>