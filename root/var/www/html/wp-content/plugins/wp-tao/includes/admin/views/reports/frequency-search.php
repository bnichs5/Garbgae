

<div class="wptao-report-content wptao-row">

	<table class="wp-list-table widefat fixed striped pages">
		<thead>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column" scope="col"><?php _e( 'Keyword', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Frequency', 'wp-tao' ) ?></th>
			</tr>
		</thead>

		<tbody id="the-list">
			<?php if ( !empty( $this->data ) ): ?>

				<?php
				$i = 1;
				foreach ( $this->data as $item ):
					?>
					<tr class="" id="">
						<td class="wptao-report-column-primary"><?php echo $i; ?></td>
						<td data-colname="<?php _e( 'Keyword', 'wp-tao' ) ?>"><?php echo esc_attr( $item->value ); ?></td>
						<td data-colname="<?php _e( 'Frequency', 'wp-tao' ) ?>" class="wptao-cell-number"><?php echo absint( $item->frequency ); ?></td>
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
				<th class="manage-column column-author" scope="col"><?php _e( 'Keyword', 'wp-tao' ) ?></th>
				<th class="manage-column column-author" scope="col"><?php _e( 'Frequency', 'wp-tao' ) ?></th>
			</tr>
		</tfoot>

	</table>


</div>