

<div class="wptao-report-content wptao-row">

	<table class="wp-list-table widefat fixed striped pages">
		<thead>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-table-column-big" scope="col"><?php _e( 'Source', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Hits', 'wp-tao' ) ?></th>
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
						<td data-colname="<?php _e( 'Page', 'wp-tao' ) ?>"><?php echo TAO()->traffic->get_source_analyzed( null, $item->referer, array( 'noprot' => true, 'link' => true ) ); ?></td>
						<td data-colname="<?php _e( 'Hits', 'wp-tao' ) ?>" class="wptao-cell-number"><?php echo absint( $item->cnt ); ?></td>
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
				<th class="manage-column wptao-table-column-big" scope="col"><?php _e( 'Source', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Hits', 'wp-tao' ) ?></th>
			</tr>
		</tfoot>

	</table>


</div>